<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * FR-04.1 & FR-04.2: Menampilkan daftar pemain dengan indikator warna
     */
    public function index()
    {
        $players = Player::with('selfAssessment')->get()->map(function ($player) {
            return [
                'id' => $player->id,
                'name' => $player->name,
                'nim_nip' => $player->nim_nip,
                'sport' => $player->sport_branch,
                'risk_color' => $player->selfAssessment->risk_label ?? 'grey', // Merah, Kuning, Hijau
                'status' => $player->verification_status,
                'checked_in' => $player->checked_in_at ? true : false,
            ];
        });

        return response()->json([
            'panitia_note' => 'Warna Merah (High) wajib cek tim medis!',
            'players' => $players
        ]);
    }

    /**
     * FR-04.4: Melakukan check-in pemain di lapangan
     */
    public function checkIn($id)
    {
        $player = Player::findOrFail($id);
        $player->update([
            'checked_in_at' => now(),
            'verification_status' => 'verified'
        ]);

        return response()->json(['message' => "Pemain {$player->name} berhasil check-in!"]);
    }
}
