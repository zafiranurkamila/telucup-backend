<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\SportsmanshipPoster;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SportsmanshipPosterController extends Controller
{
    private function upload(string $filePath): array
    {
        $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
        return $cloudinary->uploadApi()->upload($filePath, [
            'folder' => 'telucup/sportsmanship_posters',
        ]);
    }

    private function delete(string $publicId): void
    {
        $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
        $cloudinary->uploadApi()->destroy($publicId);
    }

    #[OA\Get(
        path: "/api/sportsmanship-posters",
        operationId: "listSportsmanshipPosters",
        tags: ["Sportsmanship Posters"],
        summary: "Daftar semua poster sportifitas",
        description: "Panitia mengambil semua poster sportifitas. Mendukung filter `is_active`, pencarian `search`, dan pagination via `per_page`.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "is_active", in: "query", required: false,
        description: "Filter berdasarkan status aktif",
        schema: new OA\Schema(type: "boolean"))]
    #[OA\Parameter(name: "search", in: "query", required: false,
        description: "Cari berdasarkan judul atau deskripsi poster",
        schema: new OA\Schema(type: "string", maxLength: 100))]
    #[OA\Parameter(name: "per_page", in: "query", required: false,
        description: "Jumlah item per halaman. Jika tidak diisi, mengembalikan semua data tanpa pagination.",
        schema: new OA\Schema(type: "integer", minimum: 1, maximum: 100))]
    #[OA\Response(
        response: 200,
        description: "Berhasil mengambil data poster sportifitas",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Berhasil mengambil data poster sportifitas."),
                new OA\Property(property: "data",    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/SportsmanshipPosterObject")),
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Akses ditolak", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function index(Request $request)
    {
        $request->validate([
            'is_active' => 'nullable|in:0,1,true,false',
            'search'    => 'nullable|string|max:100',
            'per_page'  => 'nullable|integer|min:1|max:100',
        ]);

        $query = SportsmanshipPoster::orderBy('sort_order')->orderByDesc('created_at');

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn($q) =>
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
            );
        }

        $data = $request->filled('per_page')
            ? $query->paginate((int) $request->per_page)
            : $query->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil mengambil data poster sportifitas.',
            'data'    => $data,
        ]);
    }

    #[OA\Post(
        path: "/api/sportsmanship-posters",
        operationId: "storeSportsmanshipPoster",
        tags: ["Sportsmanship Posters"],
        summary: "Upload poster sportifitas baru",
        description: "Panitia mengunggah poster sportifitas baru ke Cloudinary. Field `image` wajib diisi.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                required: ["title", "image"],
                properties: [
                    new OA\Property(property: "title",       type: "string",  maxLength: 150, example: "Junjung Tinggi Sportifitas"),
                    new OA\Property(property: "description", type: "string",  maxLength: 1000, nullable: true,
                        example: "Hormati lawan, wasit, dan keputusan pertandingan."),
                    new OA\Property(property: "image",       type: "string",  format: "binary",
                        description: "File gambar poster. Format: jpeg/png/jpg/webp. Maksimal 5MB."),
                    new OA\Property(property: "is_active",   type: "boolean", example: true,
                        description: "Status aktif poster. Default true."),
                    new OA\Property(property: "sort_order",  type: "integer", minimum: 0, example: 0,
                        description: "Urutan tampil poster. Default 0."),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Poster sportifitas berhasil diunggah",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Poster sportifitas berhasil diunggah."),
                new OA\Property(property: "data",    ref: "#/components/schemas/SportsmanshipPosterObject"),
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Akses ditolak", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Validasi gagal", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 500, description: "Gagal upload ke Cloudinary", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        try {
            $uploadResult = $this->upload($request->file('image')->getRealPath());

            DB::beginTransaction();

            $poster = SportsmanshipPoster::create([
                'title'                => $validated['title'],
                'description'          => $validated['description'] ?? null,
                'image_url'            => $uploadResult['secure_url'],
                'cloudinary_public_id' => $uploadResult['public_id'],
                'is_active'            => $validated['is_active'] ?? true,
                'sort_order'           => $validated['sort_order'] ?? 0,
                'uploaded_by'          => $request->user()->id,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Poster sportifitas berhasil diunggah.',
                'data'    => $poster,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal mengunggah poster sportifitas.'], 500);
        }
    }

    #[OA\Get(
        path: "/api/sportsmanship-posters/{id}",
        operationId: "showSportsmanshipPoster",
        tags: ["Sportsmanship Posters"],
        summary: "Detail poster sportifitas",
        description: "Panitia mengambil detail satu poster sportifitas berdasarkan ID.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data",   ref: "#/components/schemas/SportsmanshipPosterObject"),
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Akses ditolak", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 404, description: "Poster tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function show($id)
    {
        $poster = SportsmanshipPoster::findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $poster]);
    }

    #[OA\Patch(
        path: "/api/sportsmanship-posters/{id}",
        operationId: "updateSportsmanshipPoster",
        tags: ["Sportsmanship Posters"],
        summary: "Update poster sportifitas",
        description: "Panitia memperbarui data poster. Jika field `image` dikirim, gambar lama dihapus dari Cloudinary dan diganti dengan gambar baru. Field yang tidak dikirim tidak akan diubah.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\RequestBody(
        required: false,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: "title",       type: "string",  maxLength: 150, example: "Junjung Tinggi Sportifitas"),
                    new OA\Property(property: "description", type: "string",  maxLength: 1000, nullable: true),
                    new OA\Property(property: "image",       type: "string",  format: "binary",
                        description: "Gambar baru (opsional). Jika dikirim, gambar lama otomatis dihapus."),
                    new OA\Property(property: "is_active",   type: "boolean", example: true),
                    new OA\Property(property: "sort_order",  type: "integer", minimum: 0, example: 1),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Poster berhasil diperbarui",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Poster sportifitas berhasil diperbarui."),
                new OA\Property(property: "data",    ref: "#/components/schemas/SportsmanshipPosterObject"),
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Akses ditolak", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 404, description: "Poster tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Validasi gagal", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 500, description: "Gagal memperbarui poster", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function update(Request $request, $id)
    {
        $poster = SportsmanshipPoster::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:150',
            'description' => 'sometimes|nullable|string|max:1000',
            'image'       => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_active'   => 'sometimes|boolean',
            'sort_order'  => 'sometimes|integer|min:0',
        ]);

        try {
            $updateData = array_filter(
                $validated,
                fn($key) => in_array($key, ['title', 'description', 'is_active', 'sort_order']),
                ARRAY_FILTER_USE_KEY
            );

            if ($request->hasFile('image')) {
                $uploadResult = $this->upload($request->file('image')->getRealPath());

                $updateData['image_url']            = $uploadResult['secure_url'];
                $updateData['cloudinary_public_id'] = $uploadResult['public_id'];

                $oldPublicId = $poster->cloudinary_public_id;

                DB::beginTransaction();
                $poster->update($updateData);
                DB::commit();

                if ($oldPublicId) {
                    try {
                        $this->delete($oldPublicId);
                    } catch (\Throwable) {}
                }
            } else {
                DB::beginTransaction();
                if (!empty($updateData)) {
                    $poster->update($updateData);
                }
                DB::commit();
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Poster sportifitas berhasil diperbarui.',
                'data'    => $poster->fresh(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal memperbarui poster sportifitas.'], 500);
        }
    }

    #[OA\Delete(
        path: "/api/sportsmanship-posters/{id}",
        operationId: "deleteSportsmanshipPoster",
        tags: ["Sportsmanship Posters"],
        summary: "Hapus poster sportifitas",
        description: "Panitia menghapus poster. Gambar dihapus terlebih dahulu dari Cloudinary sebelum record dihapus dari database.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\Response(
        response: 200,
        description: "Poster berhasil dihapus",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Poster sportifitas berhasil dihapus."),
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Akses ditolak", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 404, description: "Poster tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 500, description: "Gagal menghapus dari Cloudinary", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function destroy($id)
    {
        $poster = SportsmanshipPoster::findOrFail($id);

        try {
            if ($poster->cloudinary_public_id) {
                $this->delete($poster->cloudinary_public_id);
            }

            $poster->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Poster sportifitas berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus poster sportifitas.'], 500);
        }
    }

    #[OA\Patch(
        path: "/api/sportsmanship-posters/{id}/toggle",
        operationId: "toggleSportsmanshipPoster",
        tags: ["Sportsmanship Posters"],
        summary: "Aktifkan atau nonaktifkan poster",
        description: "Panitia mengubah status aktif poster sportifitas.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer", example: 1))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["is_active"],
            properties: [
                new OA\Property(property: "is_active", type: "boolean", example: false,
                    description: "true untuk mengaktifkan, false untuk menonaktifkan poster."),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Status poster berhasil diperbarui",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Status poster sportifitas berhasil diperbarui."),
                new OA\Property(property: "data",    ref: "#/components/schemas/SportsmanshipPosterObject"),
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Akses ditolak", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 404, description: "Poster tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Validasi gagal", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function toggle(Request $request, $id)
    {
        $poster = SportsmanshipPoster::findOrFail($id);

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $poster->update(['is_active' => $validated['is_active']]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Status poster sportifitas berhasil diperbarui.',
            'data'    => $poster->fresh(),
        ]);
    }

    #[OA\Patch(
        path: "/api/sportsmanship-posters/reorder",
        operationId: "reorderSportsmanshipPosters",
        tags: ["Sportsmanship Posters"],
        summary: "Atur ulang urutan poster",
        description: "Panitia memperbarui `sort_order` beberapa poster sekaligus untuk mengatur urutan tampilan carousel.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["items"],
            properties: [
                new OA\Property(
                    property: "items",
                    type: "array",
                    items: new OA\Items(
                        required: ["id", "sort_order"],
                        properties: [
                            new OA\Property(property: "id",         type: "integer", example: 1),
                            new OA\Property(property: "sort_order", type: "integer", minimum: 0, example: 1),
                        ]
                    ),
                    example: [["id" => 1, "sort_order" => 1], ["id" => 2, "sort_order" => 2]]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Urutan berhasil diperbarui",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Urutan poster sportifitas berhasil diperbarui."),
                new OA\Property(property: "data",    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/SportsmanshipPosterObject")),
            ]
        )
    )]
    #[OA\Response(response: 403, description: "Akses ditolak", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Validasi gagal — ID tidak ditemukan atau sort_order tidak valid", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.id'         => 'required|integer|exists:sportsmanship_posters,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                SportsmanshipPoster::where('id', $item['id'])
                    ->update(['sort_order' => $item['sort_order']]);
            }
        });

        $posters = SportsmanshipPoster::orderBy('sort_order')->orderByDesc('created_at')->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Urutan poster sportifitas berhasil diperbarui.',
            'data'    => $posters,
        ]);
    }

    #[OA\Get(
        path: "/api/sportsmanship-posters/active",
        operationId: "getActiveSportsmanshipPosters",
        tags: ["Sportsmanship Posters"],
        summary: "Daftar poster sportifitas aktif",
        description: "Mengambil poster sportifitas yang aktif (`is_active = true`), diurutkan berdasarkan `sort_order`. Endpoint ini ditujukan untuk ditampilkan kepada peserta setelah selesai mengisi self-assessment.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil mengambil poster aktif",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Berhasil mengambil poster sportifitas aktif."),
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id",          type: "integer", example: 1),
                            new OA\Property(property: "title",       type: "string",  example: "Junjung Tinggi Sportifitas"),
                            new OA\Property(property: "description", type: "string",  nullable: true,
                                example: "Hormati lawan, wasit, dan keputusan pertandingan."),
                            new OA\Property(property: "image_url",   type: "string",
                                example: "https://res.cloudinary.com/demo/image/upload/v1/telucup/sportsmanship_posters/abc.jpg"),
                            new OA\Property(property: "sort_order",  type: "integer", example: 1),
                        ]
                    )
                ),
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function active()
    {
        $posters = SportsmanshipPoster::where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'description', 'image_url', 'sort_order']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil mengambil poster sportifitas aktif.',
            'data'    => $posters,
        ]);
    }
}
