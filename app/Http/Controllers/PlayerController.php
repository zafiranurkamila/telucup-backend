<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayerController extends Controller
{
    /**
     * @OA\Get(
     *      path="/summary/contingent",
     *      operationId="contingentSummary",
     *      tags={"Players"},
     *      summary="Rangkuman tingkat risiko per kontingen",
     *      description="Menghasilkan data summary jumlah pemain dengan status high, moderate, dan low risk berdasarkan kontingen.",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="success"),
     *              @OA\Property(
     *                  property="data", type="array",
     *                  @OA\Items(
     *                      @OA\Property(property="contingent", type="string"),
     *                      @OA\Property(property="high_risk_count", type="integer"),
     *                      @OA\Property(property="moderate_risk_count", type="integer"),
     *                      @OA\Property(property="low_risk_count", type="integer"),
     *                      @OA\Property(property="total_players", type="integer")
     *                  )
     *              )
     *          )
     *      )
     * )
     * FR-01.4: Menghasilkan rangkuman tingkat risiko per kontingen
     */
    public function contingentSummary()
    {
        $summary = DB::table('players')
            ->join('self_assessments', 'players.id', '=', 'self_assessments.player_id')
            ->select(
                'players.contingent',
                DB::raw("count(case when risk_label = 'high' then 1 end) as high_risk_count"),
                DB::raw("count(case when risk_label = 'moderate' then 1 end) as moderate_risk_count"),
                DB::raw("count(case when risk_label = 'low' then 1 end) as low_risk_count"),
                DB::raw("count(players.id) as total_players")
            )
            ->groupBy('players.contingent')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }

    /**
     * @OA\Post(
     *      path="/players",
     *      operationId="storePlayer",
     *      tags={"Players"},
     *      summary="Menambah data pemain baru",
     *      description="Menyimpan data pemain baru ke database.",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","nim_nip","sport_branch","contingent"},
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="nim_nip", type="string", example="1301234567"),
     *              @OA\Property(property="sport_branch", type="string", example="Basket"),
     *              @OA\Property(property="contingent", type="string", example="Fakultas Informatika")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Player created"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     * Menambah data pemain baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'nim_nip' => 'required|string|unique:players',
            'sport_branch' => 'required|string',
            'contingent' => 'required|string',
        ]);

        $player = Player::create($validated);

        return response()->json(['message' => 'Player created', 'player' => $player]);
    }
}
