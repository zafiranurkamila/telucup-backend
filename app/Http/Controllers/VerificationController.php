<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\Player;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    #[OA\Get(
        path: "/api/field/verification",
        operationId: "getVerifications",
        tags: ["Verification"],
        summary: "Daftar pemain untuk check-in lapangan",
        description: "Panitia lapangan mengambil daftar semua pemain beserta warna risiko kesehatan mereka untuk keperluan verifikasi fisik sebelum pertandingan. Warna Merah (high) wajib melapor ke tim medis terlebih dahulu.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "panitia_note", type: "string",
                    example: "Warna Merah (High) wajib cek tim medis!"),
                new OA\Property(
                    property: "players",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id",          type: "integer", example: 7),
                            new OA\Property(property: "name",        type: "string",  example: "Ahmad Fauzi"),
                            new OA\Property(property: "nim_nip",     type: "string",  nullable: true, example: "1301234567"),
                            new OA\Property(property: "sport",       type: "string",  nullable: true, example: "Badminton"),
                            new OA\Property(property: "risk_color",  type: "string",
                                enum: ["high", "medium", "low", "grey"],
                                example: "low",
                                description: "grey = belum mengisi self-assessment"),
                            new OA\Property(property: "checked_in",  type: "boolean", example: false),
                        ]
                    )
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "Unauthenticated",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 403,
        description: "Role tidak diizinkan",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function index()
    {
        $players = Player::with('selfAssessment')->get()->map(function ($player) {
            return [
                'id'         => $player->id,
                'name'       => $player->name,
                'nim_nip'    => $player->nim_nip,
                'sport'      => $player->sport_id,
                'risk_color' => $player->selfAssessment->risk_label ?? 'grey',
                'checked_in' => (bool) $player->checked_in_at,
            ];
        });

        return response()->json([
            'panitia_note' => 'Warna Merah (High) wajib cek tim medis!',
            'players'      => $players,
        ]);
    }

    #[OA\Post(
        path: "/api/field/checkin/{id}",
        operationId: "checkInPlayer",
        tags: ["Verification"],
        summary: "Check-in pemain di lapangan",
        description: "Menandai pemain sebagai sudah hadir secara fisik. Mengisi `checked_in_at` dengan timestamp saat ini.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID player yang akan di-check-in",
        schema: new OA\Schema(type: "integer", example: 7)
    )]
    #[OA\Response(
        response: 200,
        description: "Check-in berhasil",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message",      type: "string",          example: "Pemain Ahmad Fauzi berhasil check-in!"),
                new OA\Property(property: "player_id",    type: "integer",         example: 7),
                new OA\Property(property: "checked_in_at",type: "string", format: "date-time", example: "2026-06-15T08:45:00Z"),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Player tidak ditemukan",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    #[OA\Response(
        response: 403,
        description: "Role tidak diizinkan",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function checkIn($id)
    {
        $player = Player::findOrFail($id);
        $player->update([
            'checked_in_at' => now(),
        ]);

        return response()->json([
            'message'       => "Pemain {$player->name} berhasil check-in!",
            'player_id'     => $player->id,
            'checked_in_at' => $player->fresh()->checked_in_at,
        ]);
    }
}
