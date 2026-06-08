<?php

namespace App\Http\Controllers\Web\PicKontingen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\SelfAssessment;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman Profil Saya untuk PIC Kontingen.
     */
    public function show(Request $request): View
    {
        $user = Auth::user();
        
        // Memuat data relasi yang diperlukan:
        // - Contingent yang dikelola (managedContingent) beserta jumlah pemainnya
        // - Player data diri PIC (jika pernah register sbg pemain)
        $user->load(['managedContingent.players', 'player']);
        
        $contingent = $user->managedContingent;
        $playersCount = $contingent ? $contingent->players->count() : 0;
        
        // Mengambil self assessment terakhir milik PIC ini
        $assessment = SelfAssessment::where(function ($query) use ($user) {
            $query->where('user_id', $user->id);
            if ($user->player) {
                $query->orWhere('player_id', $user->player->id);
            }
        })
        ->latest()
        ->first();

        return view('dashboard.pic-kontingen.profil-saya.index', [
            'user'         => $user,
            'contingent'   => $contingent,
            'playersCount' => $playersCount,
            'assessment'   => $assessment,
        ]);
    }
}
