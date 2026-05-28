<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\Sport;
use App\Models\SportCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;

class SportController extends Controller
{
    #[OA\Get(
        path: "/api/sports",
        operationId: "listSports",
        tags: ["Sports"],
        summary: "Daftar semua cabang olahraga",
        description: "Mengembalikan semua cabang olahraga beserta sub-kategori dan batas maksimal anggota tim. Dapat diakses publik."
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
                        new OA\Property(property: "name", type: "string", example: "Badminton"),
                        new OA\Property(property: "icon_path", type: "string", example: "icons/badminton.png"),
                        new OA\Property(property: "max_members", type: "integer", example: null),
                        new OA\Property(property: "categories", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "name", type: "string", example: "Tunggal Putra"),
                                new OA\Property(property: "max_members", type: "integer", example: 1),
                            ]
                        )),
                    ]
                )),
            ]
        )
    )]
    public function index()
    {
        $sports = Sport::with('categories')->get();
        return response()->json(['status' => 'success', 'data' => $sports]);
    }

    #[OA\Get(
        path: "/api/sports/{id}",
        operationId: "showSport",
        tags: ["Sports"],
        summary: "Detail satu cabang olahraga",
        description: "Mengembalikan detail cabang olahraga termasuk semua sub-kategori dan batas anggota."
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data",   ref: "#/components/schemas/SportObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function show($id)
    {
        $sport = Sport::with('categories')->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $sport]);
    }

    #[OA\Post(
        path: "/api/sports",
        operationId: "createSport",
        tags: ["Sports"],
        summary: "Buat cabang olahraga baru",
        description: "Admin/panitia membuat cabang olahraga baru. Gunakan `multipart/form-data` jika ingin mengupload icon. Untuk categories, kirim sebagai `categories[0][name]`, `categories[0][max_members]`, dst.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name",        type: "string",  example: "Futsal"),
                    new OA\Property(property: "icon",        type: "string",  format: "binary",
                        description: "File gambar icon cabang olahraga. Format: jpeg/png/jpg/svg. Maks 2MB."),
                    new OA\Property(property: "max_members", type: "integer", example: 5,
                        description: "Diisi jika sport TIDAK memiliki sub-kategori"),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Cabang olahraga berhasil dibuat",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Cabang olahraga berhasil dibuat."),
                new OA\Property(property: "data",    ref: "#/components/schemas/SportObject"),
            ]
        )
    )]
    #[OA\Response(response: 422, description: "Validasi gagal", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 500, description: "Server error / gagal upload ke Cloudinary", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function store(Request $request)
    {
        $request->validate([
            'name'                      => 'required|string|max:255|unique:sports,name',
            'icon'                      => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'max_members'               => 'nullable|integer|min:1',
            'categories'                => 'nullable|array',
            'categories.*.name'         => 'required|string|max:100',
            'categories.*.max_members'  => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $sport = Sport::create([
                'name'        => $request->name,
                'max_members' => $request->max_members,
            ]);

            if ($request->hasFile('icon')) {
                $cloudinary   = new Cloudinary(env('CLOUDINARY_URL'));
                $uploadResult = $cloudinary->uploadApi()->upload(
                    $request->file('icon')->getRealPath(),
                    [
                        'folder'    => 'telucup/sport_icons',
                        'public_id' => 'sport_' . $sport->id,
                        'overwrite' => true,
                    ]
                );
                $sport->update(['icon_path' => $uploadResult['secure_url']]);
            }

            if ($request->filled('categories')) {
                foreach ($request->categories as $cat) {
                    SportCategory::create([
                        'sport_id'    => $sport->id,
                        'name'        => $cat['name'],
                        'max_members' => $cat['max_members'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => 'Cabang olahraga berhasil dibuat.',
                'data'    => $sport->fresh()->load('categories'),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Sport store error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: "/api/sports/{id}/icon",
        operationId: "uploadSportIcon",
        tags: ["Sports"],
        summary: "Upload atau ganti icon cabang olahraga",
        description: "Admin/panitia mengupload atau mengganti icon cabang olahraga yang sudah ada. File diupload ke Cloudinary dan URL-nya disimpan sebagai `icon_path`.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                required: ["icon"],
                properties: [
                    new OA\Property(property: "icon", type: "string", format: "binary",
                        description: "File gambar icon. Format: jpeg/png/jpg/svg. Maks 2MB."),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Icon berhasil diupload",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Icon berhasil diupload."),
                new OA\Property(property: "data",    ref: "#/components/schemas/SportObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Sport tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Validasi gagal", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 500, description: "Gagal upload ke Cloudinary", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function uploadIcon(Request $request, $id)
    {
        $request->validate([
            'icon' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $sport = Sport::findOrFail($id);

        try {
            $cloudinary   = new Cloudinary(env('CLOUDINARY_URL'));
            $uploadResult = $cloudinary->uploadApi()->upload(
                $request->file('icon')->getRealPath(),
                [
                    'folder'    => 'telucup/sport_icons',
                    'public_id' => 'sport_' . $sport->id,
                    'overwrite' => true,
                ]
            );

            $sport->update(['icon_path' => $uploadResult['secure_url']]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Icon berhasil diupload.',
                'data'    => $sport->fresh()->load('categories'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Sport uploadIcon error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal mengupload icon: ' . $e->getMessage()], 500);
        }
    }

    #[OA\Put(
        path: "/api/sports/{id}",
        operationId: "updateSport",
        tags: ["Sports"],
        summary: "Update cabang olahraga",
        description: "Admin/panitia mengupdate data cabang olahraga. Jika `categories` dikirim, seluruh sub-kategori lama akan digantikan.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Futsal"),
                new OA\Property(property: "icon_path", type: "string", example: "icons/futsal.png"),
                new OA\Property(property: "max_members", type: "integer", example: 5),
                new OA\Property(property: "categories", type: "array", items: new OA\Items(
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "Ganda Putra"),
                        new OA\Property(property: "max_members", type: "integer", example: 2),
                    ]
                )),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil diperbarui",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Cabang olahraga berhasil diperbarui."),
                new OA\Property(property: "data",    ref: "#/components/schemas/SportObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function update(Request $request, $id)
    {
        $sport = Sport::findOrFail($id);

        $validated = $request->validate([
            'name'                      => 'sometimes|string|max:255|unique:sports,name,' . $sport->id,
            'icon_path'                 => 'nullable|string|max:500',
            'max_members'               => 'nullable|integer|min:1',
            'categories'                => 'nullable|array',
            'categories.*.name'         => 'required|string|max:100',
            'categories.*.max_members'  => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $sport->update([
                'name'        => $validated['name'] ?? $sport->name,
                'icon_path'   => array_key_exists('icon_path', $validated) ? $validated['icon_path'] : $sport->icon_path,
                'max_members' => array_key_exists('max_members', $validated) ? $validated['max_members'] : $sport->max_members,
            ]);

            if (array_key_exists('categories', $validated)) {
                $sport->categories()->delete();
                foreach ($validated['categories'] ?? [] as $cat) {
                    SportCategory::create([
                        'sport_id'    => $sport->id,
                        'name'        => $cat['name'],
                        'max_members' => $cat['max_members'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status'  => 'success',
                'message' => 'Cabang olahraga berhasil diperbarui.',
                'data'    => $sport->fresh()->load('categories'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    #[OA\Delete(
        path: "/api/sports/{id}",
        operationId: "deleteSport",
        tags: ["Sports"],
        summary: "Hapus cabang olahraga",
        description: "Admin/panitia menghapus cabang olahraga. Sub-kategori terhapus otomatis (cascade). Player yang terhubung akan memiliki sport_id = null.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Berhasil dihapus", content: new OA\JsonContent(ref: "#/components/schemas/SuccessMessage"))]
    #[OA\Response(response: 404, description: "Tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function destroy($id)
    {
        $sport = Sport::findOrFail($id);
        $sport->delete();
        return response()->json(['status' => 'success', 'message' => 'Cabang olahraga berhasil dihapus.']);
    }
}
