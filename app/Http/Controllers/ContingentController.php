<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\Contingent;
use App\Models\Player;
use App\Models\User;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ContingentController extends Controller
{
    #[OA\Get(
        path: "/api/contingents",
        operationId: "listContingents",
        tags: ["Contingents"],
        summary: "Daftar semua kontingen",
        description: "Mengembalikan semua kontingen beserta informasi PIC dan jumlah anggota. Dapat diakses publik."
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "name", type: "string", example: "Fakultas Informatika"),
                        new OA\Property(property: "pic", type: "object", nullable: true),
                        new OA\Property(property: "players_count", type: "integer", example: 12),
                    ]
                )),
            ]
        )
    )]
    public function index()
    {
        $contingents = Contingent::with(['pic:id,name,email'])
            ->withCount('players')
            ->get();

        return response()->json(['status' => 'success', 'data' => $contingents]);
    }

    #[OA\Get(
        path: "/api/contingents/{id}",
        operationId: "showContingent",
        tags: ["Contingents"],
        summary: "Detail kontingen beserta anggota",
        description: "Mengembalikan detail kontingen termasuk daftar seluruh player yang terdaftar."
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data",   ref: "#/components/schemas/ContingentObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Kontingen tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function show($id)
    {
        $contingent = Contingent::with([
            'pic:id,name,email',
            'players:id,contingent_id,name,nim_nip,sport_id,sport_category_id,photo_path,verification_status',
        ])->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $contingent]);
    }

    #[OA\Get(
        path: "/api/pic-kontingen",
        operationId: "listPicKontingen",
        tags: ["Contingents"],
        summary: "Daftar semua PIC kontingen",
        description: "Mengembalikan semua user dengan role pic_kontingen beserta data kontingen yang mereka kelola. Hanya dapat diakses oleh admin dan panitia.",
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
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id",    type: "integer", example: 3),
                            new OA\Property(property: "name",  type: "string",  example: "Budi Santoso"),
                            new OA\Property(property: "email", type: "string",  example: "budi@telkomuniversity.ac.id"),
                            new OA\Property(property: "managed_contingent", type: "object", nullable: true,
                                properties: [
                                    new OA\Property(property: "id",   type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string",  example: "Fakultas Informatika"),
                                ]
                            ),
                        ]
                    )
                ),
            ]
        )
    )]
    public function picList()
    {
        $pics = User::where('role', 'pic_kontingen')
            ->with('managedContingent:id,name,pic_user_id')
            ->get(['id', 'name', 'email', 'role']);

        return response()->json(['status' => 'success', 'data' => $pics]);
    }

    #[OA\Post(
        path: "/api/contingents",
        operationId: "createContingent",
        tags: ["Contingents"],
        summary: "Buat kontingen baru",
        description: "Admin/panitia membuat kontingen baru yang mewakili satu fakultas atau divisi.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Fakultas Informatika"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Kontingen berhasil dibuat",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Kontingen berhasil dibuat."),
                new OA\Property(property: "data",    ref: "#/components/schemas/ContingentObject"),
            ]
        )
    )]
    #[OA\Response(response: 422, description: "Nama sudah digunakan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:contingents,name',
        ]);

        $contingent = Contingent::create(['name' => $validated['name']]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kontingen berhasil dibuat.',
            'data'    => $contingent,
        ], 201);
    }

    #[OA\Put(
        path: "/api/contingents/{id}",
        operationId: "updateContingent",
        tags: ["Contingents"],
        summary: "Update nama kontingen",
        description: "Admin/panitia mengupdate nama kontingen.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name"],
            properties: [new OA\Property(property: "name", type: "string", example: "Fakultas Teknik")]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil diperbarui",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Kontingen berhasil diperbarui."),
                new OA\Property(property: "data",    ref: "#/components/schemas/ContingentObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Kontingen tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function update(Request $request, $id)
    {
        $contingent = Contingent::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:contingents,name,' . $contingent->id,
        ]);

        $contingent->update(['name' => $validated['name']]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kontingen berhasil diperbarui.',
            'data'    => $contingent->fresh()->load('pic:id,name,email'),
        ]);
    }

    #[OA\Delete(
        path: "/api/contingents/{id}",
        operationId: "deleteContingent",
        tags: ["Contingents"],
        summary: "Hapus kontingen",
        description: "Admin/panitia menghapus kontingen. Player yang terhubung akan memiliki contingent_id = null.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Berhasil dihapus", content: new OA\JsonContent(ref: "#/components/schemas/SuccessMessage"))]
    #[OA\Response(response: 404, description: "Kontingen tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function destroy($id)
    {
        $contingent = Contingent::findOrFail($id);
        $contingent->delete();

        return response()->json(['status' => 'success', 'message' => 'Kontingen berhasil dihapus.']);
    }

    #[OA\Put(
        path: "/api/contingents/{id}/assign-pic",
        operationId: "assignPic",
        tags: ["Contingents"],
        summary: "Tugaskan PIC kontingen",
        description: "Admin/panitia menugaskan seorang user sebagai PIC dari kontingen. Role user otomatis diubah menjadi `pic_kontingen` dan player-nya dimasukkan ke kontingen tersebut. Jika kontingen sudah memiliki PIC lain, PIC lama otomatis di-demote ke role `player`. Jika user sudah menjadi PIC di kontingen lain, relasi lama diputus.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["user_id"],
            properties: [
                new OA\Property(property: "user_id", type: "integer", example: 5, description: "ID user yang akan dijadikan PIC"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "PIC berhasil ditugaskan",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Budi berhasil ditugaskan sebagai PIC kontingen Fakultas Informatika."),
                new OA\Property(property: "data",    ref: "#/components/schemas/ContingentObject"),
            ]
        )
    )]
    #[OA\Response(response: 422, description: "User adalah panitia/admin", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function assignPic(Request $request, $id)
    {
        $contingent = Contingent::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->role === 'panitia') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tidak dapat menugaskan panitia sebagai PIC kontingen.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Demote PIC lama jika kontingen sudah punya PIC lain
            if ($contingent->pic_user_id && $contingent->pic_user_id !== $user->id) {
                $oldPic = User::find($contingent->pic_user_id);
                if ($oldPic) {
                    $oldPic->update(['role' => 'player']);
                }
            }

            // Lepas user dari kontingen lain jika sudah jadi PIC di tempat lain
            Contingent::where('pic_user_id', $user->id)
                ->where('id', '!=', $contingent->id)
                ->update(['pic_user_id' => null]);

            $user->update(['role' => 'pic_kontingen']);
            $contingent->update(['pic_user_id' => $user->id]);

            if ($user->player) {
                $user->player->update(['contingent_id' => $contingent->id]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "{$user->name} berhasil ditugaskan sebagai PIC kontingen {$contingent->name}.",
                'data'    => $contingent->fresh()->load('pic:id,name,email'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: "/api/contingents/my",
        operationId: "myContingent",
        tags: ["Contingents"],
        summary: "Detail kontingen milik PIC yang login",
        description: "PIC kontingen melihat detail kontingennya sendiri beserta seluruh anggota.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data",   ref: "#/components/schemas/ContingentObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Belum ditugaskan sebagai PIC", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function myContingent(Request $request)
    {
        $contingent = $request->user()->managedContingent;

        if (!$contingent) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.',
            ], 404);
        }

        $contingent->load([
            'pic:id,name,email',
            'players:id,contingent_id,name,nim_nip,sport_id,sport_category_id,photo_path,verification_status',
        ]);
        $contingent->loadCount('players');

        return response()->json(['status' => 'success', 'data' => $contingent]);
    }

    #[OA\Get(
        path: "/api/contingents/my/players",
        operationId: "myContingentPlayers",
        tags: ["Contingents"],
        summary: "PIC melihat semua player di kontingennya",
        description: "Mengembalikan seluruh data player yang terdaftar di kontingen PIC yang sedang login, lengkap dengan informasi akun, cabang olahraga, sub-kategori, dan status.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",      type: "string",  example: "success"),
                new OA\Property(property: "contingent",  type: "object",
                    properties: [
                        new OA\Property(property: "id",   type: "integer", example: 1),
                        new OA\Property(property: "name", type: "string",  example: "Fakultas Informatika"),
                    ]
                ),
                new OA\Property(property: "total",       type: "integer", example: 12),
                new OA\Property(property: "data",        type: "array",
                    items: new OA\Items(ref: "#/components/schemas/PlayerObject")),
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Belum ditugaskan sebagai PIC", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function myPlayers(Request $request)
    {
        $contingent = $request->user()->managedContingent;

        if (!$contingent) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.'], 403);
        }

        $players = \App\Models\Player::with([
            'user:id,name,email,role,is_kacamata',
            'sport:id,name',
            'sportCategory:id,name',
        ])
            ->where('contingent_id', $contingent->id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status'     => 'success',
            'contingent' => $contingent->only(['id', 'name']),
            'total'      => $players->count(),
            'data'       => $players,
        ]);
    }

    #[OA\Post(
        path: "/api/contingents/my/players/register",
        operationId: "registerPlayerToContingent",
        tags: ["Contingents"],
        summary: "PIC membuat akun player baru dan langsung masuk ke kontingennya",
        description: "PIC kontingen membuat akun User + Player baru sekaligus langsung menambahkan player tersebut ke kontingennya. Berbeda dengan `POST /api/contingents/my/players` yang hanya menambahkan player yang sudah ada.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "email", "password"],
            properties: [
                new OA\Property(property: "name",     type: "string",  maxLength: 255, example: "Ahmad Fauzi"),
                new OA\Property(property: "email",    type: "string",  format: "email", example: "ahmad@telkomuniversity.ac.id"),
                new OA\Property(property: "password", type: "string",  format: "password", minLength: 8, example: "rahasia123"),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Akun player berhasil dibuat dan ditambahkan ke kontingen",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Akun Ahmad Fauzi berhasil dibuat dan ditambahkan ke kontingen Fakultas Informatika."),
                new OA\Property(property: "data",    type: "object",
                    properties: [
                        new OA\Property(property: "player", ref: "#/components/schemas/PlayerObject"),
                        new OA\Property(property: "user",   ref: "#/components/schemas/UserObject"),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Belum ditugaskan sebagai PIC", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Email sudah digunakan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 500, description: "Server error", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function registerPlayer(Request $request)
    {
        $contingent = $request->user()->managedContingent;

        if (!$contingent) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.'], 403);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role'     => 'player',
            ]);

            $player = Player::create([
                'user_id'       => $user->id,
                'name'          => $validated['name'],
                'contingent_id' => $contingent->id,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Akun {$validated['name']} berhasil dibuat dan ditambahkan ke kontingen {$contingent->name}.",
                'data'    => [
                    'player' => $player->load(['user:id,name,email,role,is_kacamata', 'contingent:id,name']),
                    'user'   => $user->only(['id', 'name', 'email', 'role', 'is_kacamata', 'created_at']),
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal membuat akun player: ' . $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: "/api/contingents/my/players",
        operationId: "addPlayerToContingent",
        tags: ["Contingents"],
        summary: "PIC menambahkan player ke kontingen",
        description: "PIC kontingen menambahkan seorang player ke dalam kontingennya. Player harus sudah memiliki akun di sistem.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["player_id"],
            properties: [
                new OA\Property(property: "player_id", type: "integer", example: 7),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Player berhasil ditambahkan",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Ahmad Fauzi berhasil ditambahkan ke kontingen Fakultas Informatika."),
                new OA\Property(property: "data",    ref: "#/components/schemas/PlayerObject"),
            ]
        )
    )]
    #[OA\Response(response: 422, description: "Player sudah ada di kontingen lain", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 403, description: "Belum ditugaskan sebagai PIC", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function addPlayer(Request $request)
    {
        $user       = $request->user();
        $contingent = $user->managedContingent;

        if (!$contingent) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.'], 403);
        }

        $validated = $request->validate([
            'player_id' => 'required|integer|exists:players,id',
        ]);

        $player = Player::findOrFail($validated['player_id']);

        if ($player->contingent_id && $player->contingent_id !== $contingent->id) {
            return response()->json(['status' => 'error', 'message' => 'Player ini sudah terdaftar di kontingen lain.'], 422);
        }

        $player->update(['contingent_id' => $contingent->id]);

        return response()->json([
            'status'  => 'success',
            'message' => "{$player->name} berhasil ditambahkan ke kontingen {$contingent->name}.",
            'data'    => $player->fresh(['contingent']),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  UPLOAD / HAPUS GAMBAR KONTINGEN (Panitia — by ID)
    // ──────────────────────────────────────────────────────────────

    #[OA\Post(
        path: "/api/contingents/{id}/image",
        operationId: "uploadContingentImage",
        tags: ["Contingents"],
        summary: "Upload atau ganti gambar kontingen",
        description: "Panitia mengunggah gambar representasi kontingen. Gambar diunggah ke Cloudinary. Jika sudah ada gambar sebelumnya, gambar lama dihapus otomatis dari Cloudinary.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                required: ["image"],
                properties: [
                    new OA\Property(property: "image", type: "string", format: "binary",
                        description: "File gambar. Format: jpeg/png/jpg. Maksimal 2MB."),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Gambar berhasil diunggah",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Gambar kontingen berhasil diunggah."),
                new OA\Property(property: "data",    ref: "#/components/schemas/ContingentObject"),
            ]
        )
    )]
    #[OA\Response(response: 422, description: "Validasi gagal", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 500, description: "Gagal upload ke Cloudinary", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function uploadImage(Request $request, $id)
    {
        $contingent = Contingent::findOrFail($id);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

            if ($contingent->cloudinary_public_id) {
                $cloudinary->uploadApi()->destroy($contingent->cloudinary_public_id);
            }

            $result = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath(), [
                'folder' => 'telucup/contingents',
            ]);

            $contingent->update([
                'cloudinary_public_id' => $result['public_id'],
                'image_url'            => $result['secure_url'],
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Gambar kontingen berhasil diunggah.',
                'data'    => $contingent->fresh()->load('pic:id,name,email'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengunggah gambar: ' . $e->getMessage()], 500);
        }
    }

    #[OA\Delete(
        path: "/api/contingents/{id}/image",
        operationId: "deleteContingentImage",
        tags: ["Contingents"],
        summary: "Hapus gambar kontingen",
        description: "Panitia menghapus gambar kontingen. Gambar dihapus dari Cloudinary dan kolom image_url dikosongkan.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Gambar berhasil dihapus", content: new OA\JsonContent(ref: "#/components/schemas/SuccessMessage"))]
    #[OA\Response(response: 404, description: "Kontingen tidak punya gambar", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 500, description: "Gagal hapus dari Cloudinary", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function deleteImage($id)
    {
        $contingent = Contingent::findOrFail($id);

        if (!$contingent->cloudinary_public_id) {
            return response()->json(['status' => 'error', 'message' => 'Kontingen ini belum memiliki gambar.'], 404);
        }

        try {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            $cloudinary->uploadApi()->destroy($contingent->cloudinary_public_id);

            $contingent->update([
                'cloudinary_public_id' => null,
                'image_url'            => null,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Gambar kontingen berhasil dihapus.']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus gambar: ' . $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  UPLOAD / HAPUS GAMBAR KONTINGEN (PIC — kontingen sendiri)
    // ──────────────────────────────────────────────────────────────

    #[OA\Post(
        path: "/api/contingents/my/image",
        operationId: "uploadMyContingentImage",
        tags: ["Contingents"],
        summary: "PIC upload atau ganti gambar kontingennya",
        description: "PIC kontingen mengunggah gambar representasi kontingennya sendiri. Gambar diunggah ke Cloudinary. Jika sudah ada gambar sebelumnya, gambar lama dihapus otomatis.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                required: ["image"],
                properties: [
                    new OA\Property(property: "image", type: "string", format: "binary",
                        description: "File gambar. Format: jpeg/png/jpg. Maksimal 2MB."),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Gambar berhasil diunggah",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Gambar kontingen berhasil diunggah."),
                new OA\Property(property: "data",    ref: "#/components/schemas/ContingentObject"),
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Belum ditugaskan sebagai PIC", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 500, description: "Gagal upload ke Cloudinary", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function uploadMyImage(Request $request)
    {
        $contingent = $request->user()->managedContingent;

        if (!$contingent) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.'], 403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

            if ($contingent->cloudinary_public_id) {
                $cloudinary->uploadApi()->destroy($contingent->cloudinary_public_id);
            }

            $result = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath(), [
                'folder' => 'telucup/contingents',
            ]);

            $contingent->update([
                'cloudinary_public_id' => $result['public_id'],
                'image_url'            => $result['secure_url'],
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Gambar kontingen berhasil diunggah.',
                'data'    => $contingent->fresh()->load('pic:id,name,email'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengunggah gambar: ' . $e->getMessage()], 500);
        }
    }

    #[OA\Delete(
        path: "/api/contingents/my/image",
        operationId: "deleteMyContingentImage",
        tags: ["Contingents"],
        summary: "PIC hapus gambar kontingennya",
        description: "PIC kontingen menghapus gambar kontingennya sendiri. Gambar dihapus dari Cloudinary dan kolom dikosongkan.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(response: 200, description: "Gambar berhasil dihapus", content: new OA\JsonContent(ref: "#/components/schemas/SuccessMessage"))]
    #[OA\Response(response: 403, description: "Belum ditugaskan sebagai PIC", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 404, description: "Kontingen belum memiliki gambar", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function deleteMyImage(Request $request)
    {
        $contingent = $request->user()->managedContingent;

        if (!$contingent) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.'], 403);
        }

        if (!$contingent->cloudinary_public_id) {
            return response()->json(['status' => 'error', 'message' => 'Kontingen ini belum memiliki gambar.'], 404);
        }

        try {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            $cloudinary->uploadApi()->destroy($contingent->cloudinary_public_id);

            $contingent->update([
                'cloudinary_public_id' => null,
                'image_url'            => null,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Gambar kontingen berhasil dihapus.']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus gambar: ' . $e->getMessage()], 500);
        }
    }

    #[OA\Delete(
        path: "/api/contingents/my/players/{player_id}",
        operationId: "removePlayerFromContingent",
        tags: ["Contingents"],
        summary: "PIC mengeluarkan player dari kontingen",
        description: "PIC kontingen mengeluarkan seorang player dari kontingennya. PIC tidak dapat mengeluarkan dirinya sendiri.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "player_id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Player berhasil dikeluarkan", content: new OA\JsonContent(ref: "#/components/schemas/SuccessMessage"))]
    #[OA\Response(response: 403, description: "Player bukan anggota kontingen ini", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function removePlayer(Request $request, $playerId)
    {
        $user       = $request->user();
        $contingent = $user->managedContingent;

        if (!$contingent) {
            return response()->json(['status' => 'error', 'message' => 'Anda belum ditugaskan sebagai PIC kontingen manapun.'], 403);
        }

        $player = Player::findOrFail($playerId);

        if ($player->contingent_id !== $contingent->id) {
            return response()->json(['status' => 'error', 'message' => 'Player ini bukan anggota kontingen Anda.'], 403);
        }

        if ($player->user_id === $user->id) {
            return response()->json(['status' => 'error', 'message' => 'PIC tidak dapat dikeluarkan dari kontingen melalui endpoint ini.'], 422);
        }

        $player->update(['contingent_id' => null]);

        return response()->json([
            'status'  => 'success',
            'message' => "{$player->name} berhasil dikeluarkan dari kontingen {$contingent->name}.",
        ]);
    }
}
