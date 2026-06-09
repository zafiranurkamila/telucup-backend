<?php

namespace App\Http\Controllers\Web\PicKontingen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnggotaController extends Controller
{
    /**
     * Tampilkan halaman pengelolaan anggota kontingen.
     */
    public function index(Request $request): View
    {
        return view('dashboard.pic-kontingen.anggota.index');
    }

    public function show($id): View
    {
        $member = \App\Models\Player::with(['user', 'contingent', 'selfAssessment'])->findOrFail($id);
        
        $picKontingen = auth()->user()->picKontingen;
        if (!$picKontingen || $member->contingent_id !== $picKontingen->contingent_id) {
            abort(403, 'Anda tidak berhak melihat data anggota ini.');
        }

        $assessment = \App\Models\SelfAssessment::where('player_id', $member->id)->latest()->first();

        return view('dashboard.pic-kontingen.anggota.show', compact('member', 'assessment'));
    }
}
