<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\Contingent;
use App\Models\Game;
use App\Models\Registration;
use App\Models\SelfAssessment;
use App\Models\Template;
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

    #[OA\Get(
        path: "/api/dashboard/panitia",
        operationId: "getPanitiaDashboard",
        tags: ["Admin"],
        summary: "Dashboard ringkasan panitia",
        description: "Mengambil data ringkasan untuk dashboard panitia: total kontingen, tim menunggu verifikasi, pertandingan hari ini, peringatan medis, dan daftar pertandingan hari ini.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "total_kontingen",           type: "integer", example: 24),
                new OA\Property(property: "tim_menunggu_verifikasi",   type: "integer", example: 12),
                new OA\Property(property: "pertandingan_hari_ini",     type: "integer", example: 8),
                new OA\Property(property: "peringatan_medis",          type: "integer", example: 3),
                new OA\Property(
                    property: "daftar_pertandingan_hari_ini",
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/MatchObject")
                ),
            ]
        )
    )]
    public function dashboard()
    {
        $today = now()->toDateString();

        $latestAssessmentIds = SelfAssessment::selectRaw('MAX(id) as id')
            ->groupBy('player_id');

        $peringatanMedis = SelfAssessment::joinSub($latestAssessmentIds, 'latest', fn($j) => $j->on('self_assessments.id', '=', 'latest.id'))
            ->where('risk_label', 'high')
            ->whereNull('is_allowed_to_play')
            ->count();

        $pertandinganHariIni = Game::with([
            'sport',
            'sportCategory',
            'registrationA.contingent',
            'registrationB.contingent',
        ])
            ->whereDate('match_date', $today)
            ->orderBy('match_time')
            ->get();

        return response()->json([
            'total_kontingen'           => Contingent::count(),
            'tim_menunggu_verifikasi'   => Registration::where('status', 'submitted')->count(),
            'pertandingan_hari_ini'     => $pertandinganHariIni->count(),
            'peringatan_medis'          => $peringatanMedis,
            'daftar_pertandingan_hari_ini' => $pertandinganHariIni,
        ]);
    }

    #[OA\Put(
        path: "/api/admin/users/{id}/promote-to-pic",
        operationId: "promoteUserToPic",
        tags: ["Admin"],
        summary: "Promosikan user menjadi PIC Kontingen",
        description: "Mengubah role user menjadi pic_kontingen.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Berhasil")]
    public function promoteToPic($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->role = 'pic_kontingen';
        $user->save();

        return response()->json(['message' => "User {$user->name} berhasil dipromosikan menjadi PIC."]);
    }
}
