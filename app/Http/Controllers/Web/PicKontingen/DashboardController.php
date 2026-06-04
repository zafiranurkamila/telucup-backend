<?php

namespace App\Http\Controllers\Web\PicKontingen;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * PIC Kontingen dashboard overview.
     *
     * Menampilkan:
     * - Info kontingen yang dikelola
     * - Jumlah anggota
     * - Jumlah tim terdaftar
     * - Pertandingan hari ini milik kontingen
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $contingent = $user->managedContingent;

        $playerCount = 0;
        $registrationCount = 0;
        $todayMatches = collect();

        if ($contingent) {
            $contingent->loadCount('players');
            $playerCount = $contingent->players_count;

            $registrationCount = \App\Models\Registration::where('contingent_id', $contingent->id)->count();

            // Ambil pertandingan hari ini yang melibatkan kontingen ini
            $registrationIds = \App\Models\Registration::where('contingent_id', $contingent->id)
                ->pluck('id');

            $todayMatches = Game::with(['sport', 'registrationA.contingent', 'registrationB.contingent'])
                ->whereDate('match_date', today())
                ->where(function ($q) use ($registrationIds) {
                    $q->whereIn('registration_a_id', $registrationIds)
                      ->orWhereIn('registration_b_id', $registrationIds);
                })
                ->orderBy('match_time')
                ->get();
        }

        return view('dashboard.pic-kontingen.index', [
            'user'              => $user,
            'contingent'        => $contingent,
            'playerCount'       => $playerCount,
            'registrationCount' => $registrationCount,
            'todayMatches'      => $todayMatches,
        ]);
    }
}
