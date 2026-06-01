<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;

class PlayerController extends Controller
{
    #[OA\Get(
        path: "/api/summary/contingent",
        operationId: "contingentSummary",
        tags: ["Players"],
        summary: "Rangkuman risiko kesehatan per kontingen",
        description: "Mengembalikan jumlah pemain dengan status high, moderate, dan low risk berdasarkan kontingen. Hanya menghitung assessment terbaru per player."
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "contingent",           type: "string",  example: "Fakultas Informatika"),
                            new OA\Property(property: "high_risk_count",      type: "integer", example: 2),
                            new OA\Property(property: "moderate_risk_count",  type: "integer", example: 5),
                            new OA\Property(property: "low_risk_count",       type: "integer", example: 10),
                            new OA\Property(property: "total_players",        type: "integer", example: 17),
                        ]
                    )
                ),
            ]
        )
    )]
    public function contingentSummary()
    {
        $summary = DB::table('players')
            ->join('self_assessments', 'players.id', '=', 'self_assessments.player_id')
            ->leftJoin('contingents', 'contingents.id', '=', 'players.contingent_id')
            ->select(
                'contingents.name as contingent',
                DB::raw("count(case when risk_label = 'high'     then 1 end) as high_risk_count"),
                DB::raw("count(case when risk_label = 'moderate' then 1 end) as moderate_risk_count"),
                DB::raw("count(case when risk_label = 'low'      then 1 end) as low_risk_count"),
                DB::raw("count(players.id) as total_players")
            )
            ->groupBy('contingents.id', 'contingents.name')
            ->get();

        return response()->json(['status' => 'success', 'data' => $summary]);
    }

    #[OA\Get(
        path: "/api/players/{id}",
        operationId: "showPlayer",
        tags: ["Players"],
        summary: "Detail data player",
        description: "Mengembalikan data lengkap satu player beserta informasi akun, cabang olahraga, sub-kategori, dan kontingen. Dapat diakses oleh siapa saja tanpa autentikasi."
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"), description: "ID player")]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data",   ref: "#/components/schemas/PlayerObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Player tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function show($id)
    {
        $player = Player::with([
            'user:id,name,email,role,is_kacamata',
            'contingent:id,name',
        ])->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $player]);
    }

    #[OA\Get(
        path: "/api/players",
        operationId: "listPlayers",
        tags: ["Players"],
        summary: "Daftar semua player",
        description: "Mengembalikan semua player beserta data user dan kontingen. Hanya dapat diakses oleh admin dan panitia.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/PlayerObject")
                ),
            ]
        )
    )]
    public function index()
    {
        $players = Player::with([
            'user:id,name,email,role,is_kacamata',
            'contingent:id,name',
        ])->get();

        return response()->json(['status' => 'success', 'data' => $players]);
    }

    #[OA\Post(
        path: "/api/players",
        operationId: "storePlayer",
        tags: ["Players"],
        summary: "Buat akun player baru",
        description: "Admin, panitia, atau PIC kontingen membuat akun player baru. Field `password` dipakai sekaligus sebagai NIM/NIP player — harus unik. Profil lengkap diisi kemudian oleh player sendiri via `PATCH /api/player/profile`.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "email", "password"],
            properties: [
                new OA\Property(property: "name",     type: "string", maxLength: 255, example: "Ahmad Fauzi"),
                new OA\Property(property: "email",    type: "string", format: "email", example: "ahmad@telkomuniversity.ac.id"),
                new OA\Property(property: "password", type: "string", format: "password", minLength: 8, example: "1301234567",
                    description: "Password login sekaligus NIM/NIP player. Harus unik di seluruh data player."),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Player berhasil dibuat",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Akun Ahmad Fauzi berhasil dibuat."),
                new OA\Property(property: "data",    type: "object",
                    properties: [
                        new OA\Property(property: "player", ref: "#/components/schemas/PlayerObject"),
                        new OA\Property(property: "user",   ref: "#/components/schemas/UserObject"),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: "Validasi gagal — email sudah digunakan atau NIM/NIP (password) sudah terpakai",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 403,
        description: "Role tidak diizinkan",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 500,
        description: "Server error",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8|unique:players,nim_nip',
        ]);

        DB::beginTransaction();
        try {
            $user = \App\Models\User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => 'player',
            ]);

            $player = Player::create([
                'user_id' => $user->id,
                'name'    => $validated['name'],
                'nim_nip' => $validated['password'],
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Akun {$validated['name']} berhasil dibuat.",
                'data'    => [
                    'player' => $player->load(['user:id,name,email,role,is_kacamata', 'contingent:id,name']),
                    'user'   => $user->only(['id', 'name', 'email', 'role', 'is_kacamata', 'created_at']),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal membuat akun player: ' . $e->getMessage()], 500);
        }
    }

    #[OA\Patch(
        path: "/api/player/profile",
        operationId: "updateProfile",
        tags: ["Players"],
        summary: "Lengkapi atau update profil pemain",
        description: "Player yang sedang login melengkapi profilnya: NIM/NIP, status kacamata, status kepegawaian, lokasi kerja, dan password. Kontingen dikelola oleh PIC via `POST /api/contingents/my/players`. Cabang olahraga didaftarkan melalui registrasi tim.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "nim_nip",         type: "string",  example: "1301234567",
                    description: "NIM (mahasiswa) atau NIP (dosen/karyawan). Harus unik."),
                new OA\Property(property: "is_kacamata",     type: "boolean", example: false,
                    description: "Penanda pengguna kacamata — disimpan di tabel users untuk pemantauan AI"),
                new OA\Property(property: "employee_status", type: "string",  example: "Pegawai Tetap", nullable: true,
                    description: "Status kepegawaian pemain, mis. Mahasiswa, Pegawai Tetap, TPA."),
                new OA\Property(property: "work_location",   type: "string",  example: "Kampus A", nullable: true,
                    description: "Lokasi kerja/unit pemain."),
                new OA\Property(property: "current_password", type: "string", example: "oldpassword",
                    description: "Password saat ini — wajib diisi jika ingin mengganti password."),
                new OA\Property(property: "password",         type: "string", example: "newpassword",
                    description: "Password baru (minimal 8 karakter). Wajib disertai current_password dan password_confirmation."),
                new OA\Property(property: "password_confirmation", type: "string", example: "newpassword",
                    description: "Konfirmasi password baru — harus sama dengan password."),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Profil berhasil diperbarui",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Profil player berhasil diperbarui"),
                new OA\Property(property: "data",    type: "object",
                    properties: [
                        new OA\Property(property: "player",      ref: "#/components/schemas/PlayerObject"),
                        new OA\Property(property: "is_kacamata", type: "boolean", example: false),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Profil player belum tersedia untuk akun ini",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 422,
        description: "Validasi gagal — NIM/NIP sudah dipakai atau password tidak sesuai",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function updateProfile(Request $request)
    {
        $user   = $request->user();
        $player = $user->player;

        if (!$player) {
            return response()->json(['status' => 'error', 'message' => 'Profil player belum tersedia untuk akun ini.'], 404);
        }

        $rules = [
            'is_kacamata'          => 'nullable|boolean',
            'employee_status'      => 'nullable|string|max:100',
            'work_location'        => 'nullable|string|max:255',
            'current_password'     => 'required_with:password|string',
            'password'             => 'nullable|string|min:8|confirmed',
        ];

        if ($request->has('nim_nip') && $request->nim_nip !== $player->nim_nip) {
            $rules['nim_nip'] = 'required|string|unique:players,nim_nip';
        } else {
            $rules['nim_nip'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Password saat ini tidak sesuai.',
                    'errors'  => ['current_password' => ['Password saat ini tidak sesuai.']],
                ], 422);
            }
            $user->update(['password' => $validated['password']]);
        }

        if (array_key_exists('is_kacamata', $validated)) {
            $user->update(['is_kacamata' => (bool) $validated['is_kacamata']]);
        }

        $playerFields = array_filter(
            $validated,
            fn($key) => in_array($key, ['nim_nip', 'employee_status', 'work_location']),
            ARRAY_FILTER_USE_KEY
        );

        if (!empty($playerFields)) {
            $player->update($playerFields);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Profil player berhasil diperbarui',
            'data'    => [
                'player'      => $player->fresh(),
                'is_kacamata' => $user->fresh()->is_kacamata,
            ],
        ]);
    }

    #[OA\Put(
        path: "/api/players/{id}/assign-contingent",
        operationId: "assignPlayerContingent",
        tags: ["Players"],
        summary: "Assign player ke kontingen",
        description: "Panitia atau admin menugaskan player ke kontingen tertentu. Kirim `contingent_id: null` untuk melepas player dari kontingen.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"), description: "ID player")]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "contingent_id", type: "integer", nullable: true, example: 3,
                    description: "ID kontingen tujuan. Kirim null untuk melepas dari kontingen."),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Player berhasil di-assign ke kontingen",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Player berhasil di-assign ke kontingen."),
                new OA\Property(property: "data",    ref: "#/components/schemas/PlayerObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Player atau kontingen tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Validasi gagal", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function assignContingent(Request $request, $id)
    {
        $request->validate([
            'contingent_id' => 'nullable|integer|exists:contingents,id',
        ]);

        $player = Player::find($id);
        if (!$player) {
            return response()->json(['status' => 'error', 'message' => 'Player tidak ditemukan.'], 404);
        }

        $player->update(['contingent_id' => $request->contingent_id]);

        return response()->json([
            'status'  => 'success',
            'message' => $request->contingent_id
                ? 'Player berhasil di-assign ke kontingen.'
                : 'Player berhasil dilepas dari kontingen.',
            'data' => $player->fresh()->load([
                'user:id,name,email,role,is_kacamata',
                'contingent:id,name',
            ]),
        ]);
    }

    #[OA\Post(
        path: "/api/players/enroll-face",
        operationId: "enrollFace",
        tags: ["Players"],
        summary: "Upload foto profil untuk face recognition",
        description: "Player mengunggah foto profil ke Cloudinary. Foto kemudian dikirim sinkron ke AI Engine (FastAPI) untuk ekstraksi vektor wajah 512D menggunakan model AdaFace dan disimpan ke tabel `face_embeddings`.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                required: ["photo"],
                properties: [
                    new OA\Property(property: "photo", type: "string", format: "binary",
                        description: "File gambar wajah pemain. Format: jpeg/png/jpg. Maksimal 5MB. Pastikan wajah terlihat jelas dan tidak tertutup."),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Foto diunggah dan wajah berhasil diregistrasi ke AI Engine",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Foto profil berhasil diunggah dan vektor wajah berhasil diregistrasi."),
                new OA\Property(property: "data",    type: "object",
                    properties: [
                        new OA\Property(property: "player_id", type: "integer",  example: 7),
                        new OA\Property(property: "photo_url", type: "string",   example: "https://res.cloudinary.com/demo/image/upload/v1/telucup/player_profiles/player_7.jpg"),
                        new OA\Property(property: "ai_result", type: "object",   description: "Response mentah dari AI Engine FastAPI",
                            properties: [
                                new OA\Property(property: "player_id",    type: "integer", example: 7),
                                new OA\Property(property: "status",       type: "string",  example: "registered"),
                                new OA\Property(property: "face_detected",type: "boolean", example: true),
                            ]
                        ),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Profil player belum tersedia untuk akun ini",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 422,
        description: "Validasi gagal atau wajah tidak terdeteksi oleh AI Engine",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 502,
        description: "Gagal berkomunikasi dengan AI Engine (FastAPI tidak merespons)",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 500,
        description: "Server error internal",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function enrollFace(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $user   = $request->user();
        $player = $user->player;

        if (!$player) {
            // Auto create player profile so they can enroll their face
            $player = \App\Models\Player::create([
                'user_id' => $user->id,
                'name'    => $user->name,
                'nim_nip' => null,
            ]);
        }

        try {
            DB::beginTransaction();

            $cloudinary   = new Cloudinary(env('CLOUDINARY_URL'));
            $uploadResult = $cloudinary->uploadApi()->upload($request->file('photo')->getRealPath(), [
                'folder'    => 'telucup/player_profiles',
                'public_id' => 'player_' . $player->id,
                'overwrite' => true,
            ]);

            $imageUrl = $uploadResult['secure_url'];
            $player->update(['photo_path' => $imageUrl]);

            $fastApiBaseUrl = rtrim(str_replace('/api/process-photo', '', env('FASTAPI_URL', 'http://127.0.0.1:8001')), '/');
            $registerUrl    = $fastApiBaseUrl . '/api/register-face';

            Log::info("Mengirim face enrollment untuk Player ID {$player->id} ke {$registerUrl}");

            $aiResponse = Http::timeout(30)->post($registerUrl, [
                'player_id' => $player->id,
                'image_url' => $imageUrl,
            ]);

            if (!$aiResponse->successful()) {
                $errorDetail = $aiResponse->json('detail') ?? 'AI Engine tidak merespon dengan benar.';
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Face enrollment gagal: ' . $errorDetail], 422);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Foto profil berhasil diunggah dan vektor wajah berhasil diregistrasi.',
                'data'    => [
                    'player_id' => $player->id,
                    'photo_url' => $imageUrl,
                    'ai_result' => $aiResponse->json(),
                ],
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            DB::rollBack();
            Log::error("Koneksi ke AI Engine gagal: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal terhubung ke AI Engine. Pastikan service berjalan.'], 502);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Face enrollment error: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal memproses face enrollment: ' . $e->getMessage()], 500);
        }
    }
}
