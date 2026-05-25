<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\PhotoFace;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    #[OA\Get(
        path: "/my-gallery",
        operationId: "myGallery",
        tags: ["Gallery"],
        summary: "Galeri foto pemain yang terdeteksi oleh AI",
        description: "Mengambil daftar photo_faces yang cocok dengan authenticated player, beserta data event_photo (URL Cloudinary). Mendukung filter ?status=pending|accepted|rejected.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "status",
        in: "query",
        required: false,
        description: "Filter berdasarkan validation_status",
        schema: new OA\Schema(type: "string", enum: ["pending", "accepted", "rejected"])
    )]
    #[OA\Response(response: 200, description: "Berhasil mengambil galeri")]
    #[OA\Response(response: 404, description: "Profil player belum tersedia untuk akun ini")]
    public function index(Request $request)
    {
        $user = $request->user();

        // Ambil player_id dari user yang login
        $player = $user->player;

        if (!$player) {
            // Fallback: coba cari berdasarkan nama atau ID langsung
            // Dalam setup produksi, User harus punya foreign key ke player
            return response()->json([
                'status' => 'error',
                'message' => 'Profil player belum tersedia untuk akun ini.'
            ], 404);
        }

        $query = PhotoFace::where('matched_player_id', $player->id)
            ->with(['eventPhoto:id,image_url,cloudinary_public_id,created_at']);

        // Filter berdasarkan validation_status jika diberikan
        if ($request->has('status') && in_array($request->status, ['pending', 'accepted', 'rejected'])) {
            $query->where('validation_status', $request->status);
        }

        $gallery = $query->orderByDesc('created_at')->get();

        return response()->json([
            'status' => 'success',
            'data' => $gallery
        ]);
    }

    #[OA\Patch(
        path: "/my-gallery/{photo_face_id}/validate",
        operationId: "validatePhotoFace",
        tags: ["Gallery"],
        summary: "Validasi/tolak hasil deteksi wajah",
        description: "Pemain dapat meng-accept atau me-reject deteksi wajah yang dilakukan AI.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "photo_face_id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["status"],
            properties: [
                new OA\Property(property: "status", type: "string", enum: ["accepted", "rejected"], example: "accepted")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Status berhasil diperbarui")]
    #[OA\Response(response: 403, description: "Tidak memiliki akses ke record ini")]
    #[OA\Response(response: 404, description: "Profil player belum tersedia untuk akun ini atau Photo face tidak ditemukan")]
    public function validate(Request $request, int $photoFaceId)
    {
        $request->validate([
            'status' => 'required|string|in:accepted,rejected',
        ]);

        $user = $request->user();

        // Cari player terkait user
        $player = $user->player;

        if (!$player) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profil player belum tersedia untuk akun ini.'
            ], 404);
        }

        // Cari photo_face record
        $photoFace = PhotoFace::find($photoFaceId);

        if (!$photoFace) {
            return response()->json([
                'status' => 'error',
                'message' => 'Record photo face tidak ditemukan.'
            ], 404);
        }

        // SECURITY CHECK: Pastikan pemain yang login adalah pemilik record ini
        if ($photoFace->matched_player_id !== $player->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk memvalidasi foto ini.'
            ], 403);
        }

        // Update status validasi
        $photoFace->validation_status = $request->status;
        $photoFace->save();

        return response()->json([
            'status' => 'success',
            'message' => "Status validasi berhasil diubah menjadi '{$request->status}'.",
            'data' => $photoFace
        ]);
    }
}
