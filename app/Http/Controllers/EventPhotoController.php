<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use Illuminate\Http\Request;
use App\Models\EventPhoto;
use App\Jobs\ProcessEventPhoto;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\DB;

class EventPhotoController extends Controller
{
    #[OA\Get(
        path: "/api/event-photos",
        operationId: "getEventPhotos",
        tags: ["Event Photo"],
        summary: "Mengambil semua data foto event",
        description: "Mengambil semua data foto dokumentasi event yang telah diunggah.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil mengambil data foto event",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Berhasil mengambil data foto event"),
                new OA\Property(property: "data", type: "array", items: new OA\Items(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "cloudinary_public_id", type: "string", example: "telucup/event_photos/abc123"),
                        new OA\Property(property: "image_url", type: "string", example: "https://res.cloudinary.com/demo/image/upload/v1/telucup/event_photos/abc123.jpg"),
                        new OA\Property(property: "uploaded_by", type: "integer", example: 2),
                        new OA\Property(property: "created_at", type: "string", format: "date-time"),
                        new OA\Property(property: "updated_at", type: "string", format: "date-time")
                    ]
                ))
            ]
        )
    )]
    public function index()
    {
        $photos = EventPhoto::orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengambil data foto event',
            'data' => $photos
        ], 200);
    }

    #[OA\Post(
        path: "/api/event-photos",
        operationId: "storeEventPhoto",
        tags: ["Event Photo"],
        summary: "Upload foto event untuk deteksi wajah AI",
        description: "Panitia mengunggah foto yang diambil selama event berlangsung. Foto diupload ke Cloudinary, lalu job background dikirim ke AI Engine (FastAPI) untuk mendeteksi dan mencocokkan wajah semua peserta yang terlihat dalam foto tersebut.",
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
                        description: "File foto event. Format: jpeg/png/jpg. Maksimal 5MB."),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Foto berhasil diunggah dan sedang diproses AI secara background",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Foto berhasil diunggah dan sedang diproses oleh AI."),
                new OA\Property(property: "data",    type: "object",
                    properties: [
                        new OA\Property(property: "id",                   type: "integer", example: 15),
                        new OA\Property(property: "cloudinary_public_id", type: "string",  example: "telucup/event_photos/abc123"),
                        new OA\Property(property: "image_url",            type: "string",  example: "https://res.cloudinary.com/demo/image/upload/v1/telucup/event_photos/abc123.jpg"),
                        new OA\Property(property: "uploaded_by",          type: "integer", example: 2,
                            description: "ID user (panitia) yang mengupload"),
                        new OA\Property(property: "created_at",           type: "string",  format: "date-time"),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: "Validasi gagal — bukan file gambar atau ukuran melebihi 5MB",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 403,
        description: "Role tidak diizinkan (hanya panitia)",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 500,
        description: "Gagal upload ke Cloudinary atau server error",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function store(Request $request)
    {
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
                'uploaded_by'          => auth()->id() ?? 1,
            ]);

            ProcessEventPhoto::dispatch($eventPhoto);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Foto berhasil diunggah dan sedang diproses oleh AI.',
                'data'    => $eventPhoto,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal mengunggah foto: ' . $e->getMessage()], 500);
        }
    }

    #[OA\Delete(
        path: "/api/event-photos/{id}",
        operationId: "deleteEventPhoto",
        tags: ["Event Photo"],
        summary: "Menghapus foto event",
        description: "Panitia menghapus foto event berdasarkan ID. Tindakan ini juga akan menghapus foto dari Cloudinary beserta relasi wajah yang terdeteksi.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        description: "ID foto event",
        required: true,
        in: "path",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Foto berhasil dihapus",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Foto event berhasil dihapus.")
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Foto tidak ditemukan",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 500,
        description: "Gagal menghapus foto atau server error",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $eventPhoto = EventPhoto::find($id);

            if (!$eventPhoto) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Foto event tidak ditemukan.'
                ], 404);
            }

            // Hapus relasi wajah yang terdeteksi di foto ini
            $eventPhoto->photoFaces()->delete();

            // Hapus dari Cloudinary jika memiliki public_id
            if ($eventPhoto->cloudinary_public_id) {
                $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
                $cloudinary->uploadApi()->destroy($eventPhoto->cloudinary_public_id);
            }

            // Hapus dari database
            $eventPhoto->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Foto event berhasil dihapus.'
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus foto: ' . $e->getMessage()
            ], 500);
        }
    }
}
