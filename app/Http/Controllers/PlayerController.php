<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;

class PlayerController extends Controller
{
    #[OA\Get(
        path: "/summary/contingent",
        operationId: "contingentSummary",
        tags: ["Players"],
        summary: "Rangkuman tingkat risiko per kontingen",
        description: "Menghasilkan data summary jumlah pemain dengan status high, moderate, dan low risk berdasarkan kontingen."
    )]
    #[OA\Response(
        response: 200,
        description: "Successful operation",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "contingent", type: "string"),
                            new OA\Property(property: "high_risk_count", type: "integer"),
                            new OA\Property(property: "moderate_risk_count", type: "integer"),
                            new OA\Property(property: "low_risk_count", type: "integer"),
                            new OA\Property(property: "total_players", type: "integer")
                        ]
                    )
                )
            ]
        )
    )]
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

    #[OA\Post(
        path: "/players",
        operationId: "storePlayer",
        tags: ["Players"],
        summary: "Menambah data pemain baru dan akun User",
        description: "Menyimpan data pemain baru ke database dan membuat akun User terkait (diperuntukkan bagi Admin/Panitia atau PIC Kontingen).",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "email", "password"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "John Doe"),
                new OA\Property(property: "email", type: "string", example: "john@example.com"),
                new OA\Property(property: "password", type: "string", example: "password123")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Player created")]
    #[OA\Response(response: 422, description: "Validation error")]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();
        try {
            // Create user account for the player
            $user = \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role' => 'player'
            ]);

            // Create player record associated with the user
            $player = Player::create([
                'user_id' => $user->id,
                'name' => $user->name,
                // nim_nip, sport_branch, contingent can be null initially
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Player and user account created successfully',
                'player' => $player,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create player',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Patch(
        path: "/player/profile",
        operationId: "updateProfile",
        tags: ["Players"],
        summary: "Melengkapi profil pemain",
        description: "Melengkapi data profil pemain seperti nim_nip, sport_branch, dan contingent.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "nim_nip", type: "string", example: "1301234567"),
                new OA\Property(property: "sport_branch", type: "string", example: "Basket"),
                new OA\Property(property: "contingent", type: "string", example: "Fakultas Informatika"),
                new OA\Property(property: "is_kacamata", type: "boolean", example: false, description: "Penanda pengguna kacamata (flag mitra untuk pemantauan ekstra)")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Profile updated successfully")]
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $player = $user->player;

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil player belum tersedia untuk akun ini.'
            ], 404);
        }

        // Jika nim_nip berubah, pastikan unik di tabel players
        $rules = [
            'sport_branch' => 'nullable|string',
            'contingent' => 'nullable|string',
            'is_kacamata' => 'nullable|boolean',
        ];

        if ($request->has('nim_nip') && $request->nim_nip !== $player->nim_nip) {
            $rules['nim_nip'] = 'required|string|unique:players,nim_nip';
        } else {
            $rules['nim_nip'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        // is_kacamata disimpan di User, bukan Player (atribut personal, bukan per-event)
        if (array_key_exists('is_kacamata', $validated)) {
            $user->update(['is_kacamata' => (bool) $validated['is_kacamata']]);
            unset($validated['is_kacamata']);
        }

        $player->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil player berhasil diperbarui',
            'data' => [
                'player'      => $player->fresh(),
                'is_kacamata' => $user->fresh()->is_kacamata,
            ],
        ]);
    }

    #[OA\Post(
        path: "/players/enroll-face",
        operationId: "enrollFace",
        tags: ["Players", "Face Recognition"],
        summary: "Enroll foto profil pemain untuk face recognition",
        description: "Upload foto profil pemain ke Cloudinary, lalu kirim ke FastAPI untuk ekstraksi vektor wajah 512D AdaFace dan simpan ke tabel face_embeddings.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                required: ["photo"],
                properties: [
                    new OA\Property(property: "photo", type: "string", format: "binary", description: "Foto profil pemain (max 5MB)")
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: "Face enrollment berhasil")]
    #[OA\Response(response: 404, description: "Profil player belum tersedia untuk akun ini")]
    #[OA\Response(response: 422, description: "Validasi gagal atau wajah tidak terdeteksi")]
    #[OA\Response(response: 502, description: "Gagal berkomunikasi dengan AI Engine")]
    public function enrollFace(Request $request)
    {
        // 1. Validasi file upload
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
        ]);

        $user = $request->user();

        // 2. Cari player yang terkait dengan user yang login
        $player = $user->player;

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil player belum tersedia untuk akun ini.'
            ], 404);
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