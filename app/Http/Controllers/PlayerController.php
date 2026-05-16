<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;

class PlayerController extends Controller
{
    /**
     * @OA\Get(
     *      path="/summary/contingent",
     *      operationId="contingentSummary",
     *      tags={"Players"},
     *      summary="Rangkuman tingkat risiko per kontingen",
     *      description="Menghasilkan data summary jumlah pemain dengan status high, moderate, dan low risk berdasarkan kontingen.",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(
     *                  property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="contingent", type="string"),
     *                      @OA\Property(property="high_risk_count", type="integer"),
     *                      @OA\Property(property="moderate_risk_count", type="integer"),
     *                      @OA\Property(property="low_risk_count", type="integer"),
     *                      @OA\Property(property="total_players", type="integer")
     *                  )
     *              )
     *          )
     *      )
     * )
     * FR-01.4: Menghasilkan rangkuman tingkat risiko per kontingen
     */
    public function contingentSummary()
    {
        $summary = DB::table('players')
            ->join('self_assessments', 'players.id', '=', 'self_assessments.player_id')
            ->select(
                'players.contingent',
                DB::raw("count(case when risk_label = 'high' then 1 end) as high_risk_count"),
                DB::raw("count(case when risk_label = 'moderate' then 1 end) as moderate_risk_count"),
                DB::raw("count(case when risk_label = 'low' then 1 end) as low_risk_count"),
                DB::raw("count(players.id) as total_players")
            )
            ->groupBy('players.contingent')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }

    /**
     * @OA\Post(
     *      path="/players",
     *      operationId="storePlayer",
     *      tags={"Players"},
     *      summary="Menambah data pemain baru",
     *      description="Menyimpan data pemain baru ke database.",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","nim_nip","sport_branch","contingent"},
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="nim_nip", type="string", example="1301234567"),
     *              @OA\Property(property="sport_branch", type="string", example="Basket"),
     *              @OA\Property(property="contingent", type="string", example="Fakultas Informatika")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Player created"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     * Menambah data pemain baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'nim_nip' => 'required|string|unique:players',
            'sport_branch' => 'required|string',
            'contingent' => 'required|string',
        ]);

        $player = Player::create($validated);

        return response()->json(['message' => 'Player created', 'player' => $player]);
    }

    /**
     * @OA\Post(
     *      path="/players/enroll-face",
     *      operationId="enrollFace",
     *      tags={"Players", "Face Recognition"},
     *      summary="Enroll foto profil pemain untuk face recognition",
     *      description="Upload foto profil pemain ke Cloudinary, lalu kirim ke FastAPI untuk ekstraksi vektor wajah 512D AdaFace dan simpan ke tabel face_embeddings.",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"photo"},
     *                  @OA\Property(property="photo", type="string", format="binary", description="Foto profil pemain (max 5MB)")
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Face enrollment berhasil"),
     *      @OA\Response(response=403, description="Profil pemain tidak ditemukan"),
     *      @OA\Response(response=422, description="Validasi gagal atau wajah tidak terdeteksi"),
     *      @OA\Response(response=502, description="Gagal berkomunikasi dengan AI Engine")
     * )
     * Phase 1: Real Face Enrollment (Ground Truth Registration)
     */
    public function enrollFace(Request $request)
    {
        // 1. Validasi file upload
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
        ]);

        $user = $request->user();

        // 2. Cari player yang terkait dengan user yang login
        $player = Player::where('nim_nip', $user->email)->first();

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil pemain tidak ditemukan untuk akun ini. Pastikan data pemain sudah didaftarkan.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // 3. Upload foto ke Cloudinary
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            $uploadResult = $cloudinary->uploadApi()->upload($request->file('photo')->getRealPath(), [
                'folder' => 'telucup/player_profiles',
                'public_id' => 'player_' . $player->id,
                'overwrite' => true, // Timpa jika sudah ada foto sebelumnya
            ]);

            $imageUrl = $uploadResult['secure_url'];

            // 4. Update photo_path di tabel players
            $player->update(['photo_path' => $imageUrl]);

            // 5. Kirim request SINKRON ke FastAPI /api/register-face
            $fastApiBaseUrl = rtrim(str_replace('/api/process-photo', '', env('FASTAPI_URL', 'http://127.0.0.1:8001')), '/');
            $registerUrl = $fastApiBaseUrl . '/api/register-face';

            Log::info("Mengirim face enrollment untuk Player ID {$player->id} ke {$registerUrl}");

            $aiResponse = Http::timeout(30)->post($registerUrl, [
                'player_id' => $player->id,
                'image_url' => $imageUrl,
            ]);

            if (!$aiResponse->successful()) {
                $errorDetail = $aiResponse->json('detail') ?? 'AI Engine tidak merespon dengan benar.';
                DB::rollBack();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Face enrollment gagal: ' . $errorDetail,
                ], 422);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Foto profil berhasil diunggah dan vektor wajah berhasil diregistrasi.',
                'data' => [
                    'player_id' => $player->id,
                    'photo_url' => $imageUrl,
                    'ai_result' => $aiResponse->json(),
                ]
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            DB::rollBack();
            Log::error("Koneksi ke AI Engine gagal: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke AI Engine. Pastikan service berjalan.',
            ], 502);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Face enrollment error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses face enrollment: ' . $e->getMessage(),
            ], 500);
        }
    }
}
