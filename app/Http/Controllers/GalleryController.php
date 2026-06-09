<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\PhotoFace;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    #[OA\Get(
        path: "/api/my-gallery",
        operationId: "myGallery",
        tags: ["Gallery"],
        summary: "Galeri foto pemain hasil deteksi AI",
        description: "Player melihat daftar foto event di mana wajahnya terdeteksi oleh AI. Mendukung filter berdasarkan `status` validasi.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "status",
        in: "query",
        required: false,
        description: "Filter berdasarkan status validasi pemain",
        schema: new OA\Schema(type: "string", enum: ["pending", "needs_review", "accepted", "rejected"])
    )]
    #[OA\Parameter(
        name: "folder_id",
        in: "query",
        required: false,
        description: "Filter foto berdasarkan folder galeri tertentu",
        schema: new OA\Schema(type: "integer")
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
                            new OA\Property(property: "id",                type: "integer", example: 3),
                            new OA\Property(property: "event_photo_id",    type: "integer", example: 15),
                            new OA\Property(property: "matched_player_id", type: "integer", example: 7),
                            new OA\Property(property: "validation_status", type: "string",
                                enum: ["pending", "needs_review", "accepted", "rejected"], example: "pending"),
                            new OA\Property(property: "similarity_score",  type: "number", format: "float",
                                example: 0.924, description: "Skor kemiripan wajah (0–1) dari model AdaFace"),
                            new OA\Property(property: "bounding_box",      type: "object", nullable: true,
                                description: "Koordinat kotak wajah dalam foto",
                                properties: [
                                    new OA\Property(property: "x",      type: "integer", example: 120),
                                    new OA\Property(property: "y",      type: "integer", example: 80),
                                    new OA\Property(property: "width",  type: "integer", example: 64),
                                    new OA\Property(property: "height", type: "integer", example: 64),
                                ]
                            ),
                            new OA\Property(property: "event_photo",       type: "object",
                                properties: [
                                    new OA\Property(property: "id",                   type: "integer", example: 15),
                                    new OA\Property(property: "image_url",            type: "string",
                                        example: "https://res.cloudinary.com/demo/image/upload/v1/telucup/event_photos/abc123.jpg"),
                                    new OA\Property(property: "cloudinary_public_id", type: "string",
                                        example: "telucup/event_photos/abc123"),
                                    new OA\Property(property: "created_at",           type: "string", format: "date-time"),
                                ]
                            ),
                            new OA\Property(property: "created_at", type: "string", format: "date-time"),
                        ]
                    )
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Profil player belum tersedia untuk akun ini",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function index(Request $request)
    {
        $user   = $request->user();
        $player = $user->player;

        if (!$player) {
            // Auto create player profile so they don't get 404 error
            $player = \App\Models\Player::create([
                'user_id' => $user->id,
                'name'    => $user->name,
                'nim_nip' => null,
            ]);
        }

        $query = PhotoFace::where('matched_player_id', $player->id)
            ->with(['eventPhoto:id,image_url,cloudinary_public_id,gallery_folder_id,created_at']);

        if ($request->has('status') && in_array($request->status, ['pending', 'needs_review', 'accepted', 'rejected'], true)) {
            $query->where('validation_status', $request->status);
        }

        if ($request->filled('folder_id')) {
            $query->whereHas('eventPhoto', fn($q) => $q->where('gallery_folder_id', $request->input('folder_id')));
        }

        $gallery = $query->orderByDesc('created_at')->get();

        return response()->json(['status' => 'success', 'data' => $gallery]);
    }

    #[OA\Patch(
        path: "/api/my-gallery/{photo_face_id}/validate",
        operationId: "validatePhotoFace",
        tags: ["Gallery"],
        summary: "Terima atau tolak deteksi wajah AI",
        description: "Player mengkonfirmasi apakah wajah yang terdeteksi oleh AI benar-benar dirinya (`accepted`) atau salah deteksi (`rejected`).",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "photo_face_id",
        in: "path",
        required: true,
        description: "ID record photo_face yang akan divalidasi",
        schema: new OA\Schema(type: "integer", example: 3)
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["status"],
            properties: [
                new OA\Property(property: "status", type: "string",
                    enum: ["accepted", "rejected"],
                    example: "accepted",
                    description: "`accepted` = wajah benar milik saya, `rejected` = bukan saya"),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Status validasi berhasil diperbarui",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status",  type: "string", example: "success"),
                new OA\Property(property: "message", type: "string", example: "Status validasi berhasil diubah menjadi 'accepted'."),
                new OA\Property(property: "data",    type: "object",
                    properties: [
                        new OA\Property(property: "id",                type: "integer", example: 3),
                        new OA\Property(property: "validation_status", type: "string",  example: "accepted"),
                        new OA\Property(property: "matched_player_id", type: "integer", example: 7),
                        new OA\Property(property: "event_photo_id",    type: "integer", example: 15),
                        new OA\Property(property: "similarity_score",  type: "number",  format: "float", example: 0.924),
                        new OA\Property(property: "updated_at",        type: "string",  format: "date-time"),
                    ]
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 403,
        description: "Anda tidak memiliki akses untuk memvalidasi foto ini",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 404,
        description: "Profil player atau record photo_face tidak ditemukan",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function validate(Request $request, int $photoFaceId)
    {
        $request->validate([
            'status' => 'required|string|in:accepted,rejected',
        ]);

        $user   = $request->user();
        $player = $user->player;

        if (!$player) {
            return response()->json(['status' => 'error', 'message' => 'Profil player belum tersedia untuk akun ini.'], 404);
        }

        $photoFace = PhotoFace::find($photoFaceId);

        if (!$photoFace) {
            return response()->json(['status' => 'error', 'message' => 'Record photo face tidak ditemukan.'], 404);
        }

        if ($photoFace->matched_player_id !== $player->id) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses untuk memvalidasi foto ini.'], 403);
        }

        $photoFace->validation_status = $request->status;
        $photoFace->save();

        return response()->json([
            'status'  => 'success',
            'message' => "Status validasi berhasil diubah menjadi '{$request->status}'.",
            'data'    => $photoFace,
        ]);
    }
}
