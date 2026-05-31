<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\EventPhoto;
use App\Models\GalleryFolder;
use App\Jobs\ProcessEventPhoto;
use Cloudinary\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GalleryFolderController extends Controller
{
    #[OA\Get(
        path: "/api/gallery-folders",
        operationId: "listGalleryFolders",
        tags: ["Gallery Folder"],
        summary: "Daftar folder galeri",
        description: "Mengembalikan folder galeri. Default hanya folder root (parent_id = null). Gunakan query `parent_id` untuk mendapatkan sub-folder dari folder tertentu.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "parent_id", in: "query", required: false,
        description: "Tampilkan sub-folder dari folder ini. Kosongkan untuk folder root.",
        schema: new OA\Schema(type: "integer", nullable: true))]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data",   type: "array",
                    items: new OA\Items(ref: "#/components/schemas/GalleryFolderObject")),
            ]
        )
    )]
    public function index(Request $request)
    {
        $query = GalleryFolder::withCount(['children', 'photos']);

        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->input('parent_id'));
        } else {
            $query->whereNull('parent_id');
        }

        return response()->json([
            'status' => 'success',
            'data'   => $query->orderBy('name')->get(),
        ]);
    }

    #[OA\Post(
        path: "/api/gallery-folders",
        operationId: "createGalleryFolder",
        tags: ["Gallery Folder"],
        summary: "Buat folder galeri baru",
        description: "Panitia membuat folder galeri baru. Opsional isi `parent_id` untuk membuat sub-folder.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name"],
            properties: [
                new OA\Property(property: "name",      type: "string", example: "Pembukaan"),
                new OA\Property(property: "parent_id", type: "integer", nullable: true, example: null,
                    description: "ID folder induk. null atau tidak diisi = folder root."),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Folder berhasil dibuat",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Folder berhasil dibuat."),
                new OA\Property(property: "data",    ref: "#/components/schemas/GalleryFolderObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "parent_id tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Validasi gagal", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:gallery_folders,id',
        ]);

        $folder = GalleryFolder::create([
            'name'       => $validated['name'],
            'parent_id'  => $validated['parent_id'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Folder berhasil dibuat.',
            'data'    => $folder->loadCount(['children', 'photos']),
        ], 201);
    }

    #[OA\Get(
        path: "/api/gallery-folders/{id}",
        operationId: "showGalleryFolder",
        tags: ["Gallery Folder"],
        summary: "Detail folder galeri",
        description: "Menampilkan detail folder beserta daftar sub-folder langsung di bawahnya.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "data",   ref: "#/components/schemas/GalleryFolderObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Folder tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function show($id)
    {
        $folder = GalleryFolder::withCount(['children', 'photos'])
            ->with(['parent:id,name', 'children' => fn($q) => $q->withCount(['children', 'photos'])])
            ->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $folder]);
    }

    #[OA\Patch(
        path: "/api/gallery-folders/{id}",
        operationId: "updateGalleryFolder",
        tags: ["Gallery Folder"],
        summary: "Rename atau pindah folder",
        description: "Ganti nama folder dan/atau pindahkan ke folder induk lain. Sistem mencegah circular reference (folder tidak bisa dipindah ke dirinya sendiri atau ke turunannya).",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name",      type: "string",  example: "Penutupan"),
                new OA\Property(property: "parent_id", type: "integer", nullable: true, example: 2,
                    description: "ID folder induk baru. Kirim null untuk jadikan folder root."),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Folder berhasil diperbarui",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Folder berhasil diperbarui."),
                new OA\Property(property: "data",    ref: "#/components/schemas/GalleryFolderObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Folder tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Validasi gagal atau circular reference terdeteksi", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function update(Request $request, $id)
    {
        $folder = GalleryFolder::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'parent_id' => 'sometimes|nullable|integer|exists:gallery_folders,id',
        ]);

        if (array_key_exists('parent_id', $validated) && $validated['parent_id'] !== null) {
            $newParentId = $validated['parent_id'];

            if ($newParentId === $folder->id) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Folder tidak dapat menjadi induk dari dirinya sendiri.',
                ], 422);
            }

            if ($folder->isAncestorOf($newParentId)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Folder tidak dapat dipindah ke salah satu turunannya sendiri.',
                ], 422);
            }
        }

        $folder->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Folder berhasil diperbarui.',
            'data'    => $folder->fresh()->loadCount(['children', 'photos']),
        ]);
    }

    #[OA\Delete(
        path: "/api/gallery-folders/{id}",
        operationId: "deleteGalleryFolder",
        tags: ["Gallery Folder"],
        summary: "Hapus folder galeri",
        description: "Hapus folder. Folder tidak dapat dihapus jika masih memiliki foto atau sub-folder di dalamnya.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Folder berhasil dihapus",
        content: new OA\JsonContent(properties: [
            new OA\Property(property: "status",  type: "string", example: "success"),
            new OA\Property(property: "message", type: "string", example: "Folder berhasil dihapus."),
        ])
    )]
    #[OA\Response(response: 404, description: "Folder tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Folder masih memiliki foto atau sub-folder", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function destroy($id)
    {
        $folder = GalleryFolder::withCount(['children', 'photos'])->findOrFail($id);

        if ($folder->photos_count > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Folder tidak dapat dihapus karena masih memiliki foto. Pindahkan atau hapus foto terlebih dahulu.',
            ], 422);
        }

        if ($folder->children_count > 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Folder tidak dapat dihapus karena masih memiliki sub-folder di dalamnya.',
            ], 422);
        }

        $folder->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Folder berhasil dihapus.',
        ]);
    }

    #[OA\Get(
        path: "/api/gallery-folders/{id}/photos",
        operationId: "listFolderPhotos",
        tags: ["Gallery Folder"],
        summary: "Daftar foto dalam folder",
        description: "Mengembalikan semua foto event yang berada di dalam folder tertentu.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string",  example: "success"),
                new OA\Property(property: "folder",  type: "object",
                    properties: [
                        new OA\Property(property: "id",   type: "integer", example: 3),
                        new OA\Property(property: "name", type: "string",  example: "Pembukaan"),
                    ]
                ),
                new OA\Property(property: "total",   type: "integer", example: 12),
                new OA\Property(property: "data",    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/EventPhotoObject")),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Folder tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function photos($id)
    {
        $folder = GalleryFolder::findOrFail($id);

        $photos = EventPhoto::where('gallery_folder_id', $folder->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => 'success',
            'folder' => $folder->only(['id', 'name']),
            'total'  => $photos->count(),
            'data'   => $photos,
        ]);
    }

    #[OA\Post(
        path: "/api/gallery-folders/{id}/photos",
        operationId: "uploadPhotoToFolder",
        tags: ["Gallery Folder"],
        summary: "Upload foto ke folder tertentu",
        description: "Panitia mengunggah foto langsung ke dalam folder galeri tertentu. Foto diupload ke Cloudinary dan job AI face detection langsung dikirim.",
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
                        description: "File foto event. Format: jpeg/png/jpg. Maksimal 5MB."),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Foto berhasil diunggah ke folder",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Foto berhasil diunggah ke folder Pembukaan."),
                new OA\Property(property: "data",    ref: "#/components/schemas/EventPhotoObject"),
            ]
        )
    )]
    #[OA\Response(response: 404, description: "Folder tidak ditemukan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 422, description: "Validasi gagal", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 500, description: "Gagal upload ke Cloudinary", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function storePhoto(Request $request, $id)
    {
        $folder = GalleryFolder::findOrFail($id);

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            DB::beginTransaction();

            $cloudinary   = new Cloudinary(env('CLOUDINARY_URL'));
            $uploadResult = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath(), [
                'folder' => 'telucup/event_photos',
            ]);

            $eventPhoto = EventPhoto::create([
                'cloudinary_public_id' => $uploadResult['public_id'],
                'image_url'            => $uploadResult['secure_url'],
                'uploaded_by'          => $request->user()->id,
                'gallery_folder_id'    => $folder->id,
            ]);

            ProcessEventPhoto::dispatch($eventPhoto);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Foto berhasil diunggah ke folder {$folder->name}.",
                'data'    => $eventPhoto,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal mengunggah foto: ' . $e->getMessage()], 500);
        }
    }
}
