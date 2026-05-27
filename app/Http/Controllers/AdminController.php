<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\Template;
use App\Models\Game;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    #[OA\Get(
        path: "/api/admin/templates",
        operationId: "getTemplates",
        tags: ["Admin"],
        summary: "Daftar template turnamen",
        description: "Mengambil daftar template tahun turnamen yang tersedia.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(
                type: "object",
                properties: [
                    new OA\Property(property: "id",         type: "integer", example: 1),
                    new OA\Property(property: "name",       type: "string",  example: "Telucup 2026"),
                    new OA\Property(property: "year",       type: "integer", example: 2026),
                    new OA\Property(property: "created_at", type: "string",  format: "date-time"),
                ]
            )
        )
    )]
    public function templates()
    {
        return response()->json(Template::all());
    }

    #[OA\Get(
        path: "/api/admin/schedules",
        operationId: "getAdminSchedules",
        tags: ["Admin"],
        summary: "Jadwal pertandingan",
        description: "Mengambil daftar jadwal pertandingan berdasarkan tanggal dan cabang olahraga. Gunakan GET /api/bracket untuk tampilan bagan lengkap.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "date", in: "query", required: false, schema: new OA\Schema(type: "string", format: "date"))]
    #[OA\Parameter(name: "sport_id", in: "query", required: false, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/MatchObject")
        )
    )]
    public function schedules(Request $request)
    {
        $query = Game::with(['sport', 'registrationA.contingent', 'registrationB.contingent']);

        if ($request->date) {
            $query->whereDate('match_date', $request->date);
        }

        if ($request->sport_id) {
            $query->where('sport_id', $request->sport_id);
        }

        return response()->json($query->orderBy('match_date')->orderBy('match_time')->get());
    }

    #[OA\Put(
        path: "/api/admin/users/{id}/promote-to-pic",
        operationId: "promoteToPic",
        tags: ["Admin"],
        summary: "Promosikan player menjadi PIC Kontingen",
        description: "Mengubah role pengguna menjadi `pic_kontingen`. Untuk juga menugaskan ke kontingen tertentu, gunakan PUT /api/contingents/{id}/assign-pic.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Berhasil dipromosikan",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "message", type: "string", example: "User berhasil dipromosikan menjadi PIC Kontingen"),
                new OA\Property(property: "user",    ref: "#/components/schemas/UserObject"),
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Tidak dapat mengubah role panitia",
        content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
    )]
    public function promoteToPic(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        if ($user->role === 'panitia') {
            return response()->json(['message' => 'Tidak dapat mengubah role panitia!'], 400);
        }

        $user->update(['role' => 'pic_kontingen']);

        return response()->json([
            'message' => 'User berhasil dipromosikan menjadi PIC Kontingen',
            'user'    => $user,
        ]);
    }
}
