<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * FR-02.1: Generate Bracket Otomatis dari list kontingen
     */
    public function generateBracket(Request $request)
    {
        $contingents = $request->contingents; // Contoh: ['Informatika', 'Elektro', 'MBTI', 'DKV']
        $sport = $request->sport;
        
        shuffle($contingents); // Mengacak posisi agar adil
        $matchCount = count($contingents) / 2;

        for ($i = 0; $i < $matchCount; $i++) {
            Game::create([
                'sport_branch' => $sport,
                'team_a' => $contingents[$i * 2],
                'team_b' => $contingents[($i * 2) + 1],
                'round' => 1,
                'match_number' => $i + 1,
            ]);
        }

        return response()->json(['message' => 'Bagan Babak 1 Berhasil Dibuat Secara Otomatis!']);
    }

    /**
     * FR-02.2 & FR-02.3: Update Skor & Pemenang Lanjut Otomatis
     */
    public function updateScore(Request $request, $id)
    {
        $game = Game::findOrFail($id);
        
        // Tentukan pemenang berdasarkan skor
        $winner = $request->score_a > $request->score_b ? $game->team_a : $game->team_b;

        $game->update([
            'score_a' => $request->score_a,
            'score_b' => $request->score_b,
            'winner' => $winner
        ]);

        // LOGIKA AUTO-ADVANCE:
        // Cari posisi di babak berikutnya (Round + 1)
        $nextMatchNumber = ceil($game->match_number / 2);
        $nextRound = $game->round + 1;

        // Cari apakah pertandingan babak berikutnya sudah ada, jika belum buat baru
        $nextGame = Game::firstOrCreate(
            [
                'round' => $nextRound, 
                'match_number' => $nextMatchNumber, 
                'sport_branch' => $game->sport_branch
            ],
            ['team_a' => null, 'team_b' => null]
        );

        // Jika nomor pertandingan ganjil, masuk ke Team A. Jika genap, masuk ke Team B.
        if ($game->match_number % 2 != 0) {
            $nextGame->update(['team_a' => $winner]);
        } else {
            $nextGame->update(['team_b' => $winner]);
        }

        return response()->json([
            'message' => 'Skor diupdate dan pemenang otomatis lanjut!',
            'winner' => $winner,
            'next_round_info' => "Lolos ke Babak {$nextRound}"
        ]);
    }
}
