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
        $teamRegistrations = collect();
        $waitingVerificationCount = 0;

        if ($contingent) {
            $contingent->loadCount('players');
            $playerCount = $contingent->players_count;

            $teamRegistrations = \App\Models\Registration::with(['sport', 'sportCategory'])
                ->where('contingent_id', $contingent->id)
                ->get();

            $registrationCount = $teamRegistrations->count();
            $waitingVerificationCount = $teamRegistrations->whereIn('status', ['submitted', 'pending'])->count();

            // Ambil pertandingan hari ini yang melibatkan kontingen ini
            $registrationIds = $teamRegistrations->pluck('id');

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
            'user'                     => $user,
            'contingent'               => $contingent,
            'playerCount'              => $playerCount,
            'registrationCount'        => $registrationCount,
            'waitingVerificationCount' => $waitingVerificationCount,
            'todayMatches'             => $todayMatches,
            'teamRegistrations'        => $teamRegistrations,
        ]);
    }
}
