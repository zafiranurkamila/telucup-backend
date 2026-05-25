<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    #[OA\Post(
        path: "/bracket/generate",
        operationId: "generateBracket",
        tags: ["Bracket"],
        summary: "Generate Bracket Otomatis dari list kontingen",
        description: "Membuat bagan pertandingan babak 1 secara otomatis berdasarkan list kontingen.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["contingents", "sport"],
            properties: [
                new OA\Property(property: "contingents", type: "array", items: new OA\Items(type: "string"), example: ["Informatika", "Elektro", "MBTI", "DKV"]),
                new OA\Property(property: "sport", type: "string", example: "Basket")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Bagan Babak 1 Berhasil Dibuat")]
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
                'round_name' => 'Round 1',
                'status' => 'scheduled',
                'match_number' => $i + 1,
            ]);
        }

        return response()->json(['message' => 'Bagan Babak 1 Berhasil Dibuat Secara Otomatis!']);
    }

    #[OA\Post(
        path: "/bracket/update-score/{id}",
        operationId: "updateScore",
        tags: ["Bracket"],
        summary: "Update Skor & Pemenang Lanjut Otomatis",
        description: "Memperbarui skor pertandingan, menentukan pemenang, dan memajukannya ke babak selanjutnya secara otomatis.",
        security: [["bearerAuth" => []]]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["score_a", "score_b"],
            properties: [
                new OA\Property(property: "score_a", type: "integer", example: 2),
                new OA\Property(property: "score_b", type: "integer", example: 1)
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Skor diupdate, pertandingan selesai, dan pemenang lanjut")]
    #[OA\Response(response: 404, description: "Game not found")]
    public function updateScore(Request $request, $id)
    {
        $game = Game::findOrFail($id);
        
        // Tentukan pemenang berdasarkan skor
        // Tentukan pemenang berdasarkan skor (Jika seri, Team A menang default/asumsi adu penalti)
        $winner = $request->score_a >= $request->score_b ? $game->team_a : $game->team_b;

        $game->update([
            'score_a' => $request->score_a,
            'score_b' => $request->score_b,
            'winner' => $winner,
            'status' => 'finished' // Otomatis selesai jika skor diisi
        ]);

        // LOGIKA AUTO-ADVANCE:
        $nextMatchNumber = ceil($game->match_number / 2);
        $nextRound = $game->round + 1;

        // Tentukan Nama Babak
        $roundNames = [2 => 'Quarter Finals', 3 => 'Semi Finals', 4 => 'Grand Finals'];

        $nextGame = Game::firstOrCreate(
            [
                'round' => $nextRound, 
                'match_number' => $nextMatchNumber, 
                'sport_branch' => $game->sport_branch
            ],
            [
                'team_a' => null, 
                'team_b' => null,
                'round_name' => $roundNames[$nextRound] ?? "Round $nextRound",
                'status' => 'scheduled'
            ]
        );

        // Update Team di babak berikutnya
        if ($game->match_number % 2 != 0) {
            $nextGame->update(['team_a' => $winner]);
        } else {
            $nextGame->update(['team_b' => $winner]);
        }

        return response()->json([
            'message' => 'Skor diupdate, pertandingan selesai, dan pemenang lanjut!',
            'winner' => $winner,
            'next_round' => $nextGame->round_name
        ]);
    }

    #[OA\Get(
        path: "/bracket/detail/{id}",
        operationId: "getGameDetail",
        tags: ["Bracket"],
        summary: "Detail Pertandingan untuk Modal",
        description: "Mengambil detail informasi dari sebuah pertandingan berdasarkan ID."
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Berhasil mengambil detail pertandingan",
        content: new OA\JsonContent(type: "object")
    )]
    #[OA\Response(response: 404, description: "Game not found")]
    public function show($id)
    {
        $game = Game::findOrFail($id);
        return response()->json($game);
    }
}
