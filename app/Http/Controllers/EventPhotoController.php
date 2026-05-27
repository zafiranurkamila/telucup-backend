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
}
