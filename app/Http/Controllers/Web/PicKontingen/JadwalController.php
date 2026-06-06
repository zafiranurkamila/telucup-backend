<?php

namespace App\Http\Controllers\Web\PicKontingen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Game;
use App\Models\Registration;
use App\Models\Sport;

class JadwalController extends Controller
{
    /**
     * Tampilkan halaman Jadwal & Pertandingan untuk PIC Kontingen.
     */
    public function index(Request $request): View
    {
        $contingent = $request->user()->managedContingent;

        $allMatches = collect();
        if ($contingent) {
            $registrationIds = Registration::where('contingent_id', $contingent->id)->pluck('id');

            $allMatches = Game::with([
                'sport',
                'sportCategory',
                'registrationA.contingent',
                'registrationB.contingent',
                'winner.contingent',
            ])
            ->where('status', '!=', 'bye')
            ->where(function ($q) use ($registrationIds) {
                $q->whereIn('registration_a_id', $registrationIds)
                  ->orWhereIn('registration_b_id', $registrationIds);
            })
            ->orderBy('sport_id')
            ->orderBy('round')
            ->orderBy('match_number')
            ->orderByRaw('match_date IS NULL, match_date')
            ->orderByRaw('match_time IS NULL, match_time')
            ->get();
        }

        $sports = Sport::with('categories')->get();

        return view('dashboard.pic-kontingen.jadwal.index', [
            'allMatches' => $allMatches,
            'sports'     => $sports,
        ]);
    }
}
