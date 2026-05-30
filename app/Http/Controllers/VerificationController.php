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
        summary: "Daftar pemain untuk verifikasi lapangan",
        description: "Panitia lapangan mengambil daftar semua pemain beserta warna risiko kesehatan mereka. Warna Merah (high) wajib melapor ke tim medis sebelum bertanding. Check-in per pertandingan dikelola via `POST /api/matches/{id}/checkin/{player_id}`.",
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
                        type: "object",
                        properties: [
                            new OA\Property(property: "id",         type: "integer", example: 7),
                            new OA\Property(property: "name",       type: "string",  example: "Ahmad Fauzi"),
                            new OA\Property(property: "nim_nip",    type: "string",  nullable: true, example: "1301234567"),
                            new OA\Property(property: "risk_color", type: "string",
                                enum: ["high", "medium", "low", "grey"],
                                example: "low",
                                description: "Warna risiko kesehatan: grey = belum mengisi self-assessment"),
                        ]
                    )
                ),
            ]
        )
    )]
    #[OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    #[OA\Response(response: 403, description: "Role tidak diizinkan", content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse"))]
    public function index()
    {
        $players = Player::all()->map(fn($player) => [
            'id'         => $player->id,
            'name'       => $player->name,
            'nim_nip'    => $player->nim_nip,
            'risk_color' => $player->risk_lvl === 'not_yet' ? 'grey' : $player->risk_lvl,
        ]);

        return response()->json([
            'panitia_note' => 'Warna Merah (High) wajib cek tim medis!',
            'players'      => $players,
        ]);
    }
}
