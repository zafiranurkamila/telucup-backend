<?php

namespace App\Http\Controllers\Web\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Contingent;
use App\Models\Game;
use App\Models\SelfAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Panitia/Admin dashboard overview.
     *
     * Menampilkan:
     * - Total kontingen
     * - Tim menunggu verifikasi
     * - Pertandingan hari ini
     * - Peringatan medis (red flags)
     * - List pertandingan hari ini
     * - Kontingen terdaftar (top 5)
     */
    public function index(Request $request): View
    {
        $totalKontingen = Contingent::count();

        $timMenunggu = \App\Models\Registration::where('status', 'pending')->count();

        $pertandinganHariIni = Game::whereDate('match_date', today())->count();

        $redFlags = SelfAssessment::where('risk_label', 'high')->count();

        $matchesToday = Game::with(['sport', 'registrationA.contingent', 'registrationB.contingent'])
            ->whereDate('match_date', today())
            ->orderBy('match_time')
            ->limit(6)
            ->get();

        $contingents = Contingent::withCount('players')
            ->with('pic:id,name')
            ->orderByDesc('players_count')
            ->limit(5)
            ->get();

        return view('dashboard.panitia.index', [
            'stats' => [
                'totalKontingen'      => $totalKontingen,
                'timMenunggu'         => $timMenunggu,
                'pertandinganHariIni' => $pertandinganHariIni,
                'redFlags'            => $redFlags,
            ],
            'matchesToday' => $matchesToday,
            'contingents'  => $contingents,
        ]);
    }
}
