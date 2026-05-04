<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayerController extends Controller
{
    /**
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
