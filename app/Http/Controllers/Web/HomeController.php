<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use App\Models\Sport;

class HomeController extends Controller
{
    /**
     * Halaman publik utama untuk penonton / publik tanpa akun.
     */
    public function index(): View
    {
        return view('public.home');
    }

    /**
     * Halaman bagan publik.
     */
    public function bagan(): View
    {
        $sports = Sport::with('categories')->orderBy('name')->get();
        return view('public.bagan', compact('sports'));
    }

    /**
     * Halaman peserta publik.
     */
    public function participants(): View
    {
        return view('public.participants');
    }

    /**
     * Halaman detail peserta publik.
     */
    public function participantDetail($name): View
    {
        $participantsMap = [
            'Bidang I' => 'logo_Bidang_1.png',
            'Bidang II' => 'logo_Bidang_2.png',
            'Bidang III' => 'logo_Bidang_3.png',
            'Bidang IV' => 'logo_Bidang_4.png',
            'CS' => 'logo_CS.png',
            'FEB' => 'logo_FEB.png',
            'FIF' => 'logo_FIF.png',
            'FIK' => 'logo_FIK.png',
            'FIT' => 'logo_FIT.png',
            'FKS' => 'logo_FKS.png',
            'FRI' => 'logo_FRI.png',
            'FTE' => 'logo_FTE.png',
            'PAM' => 'logo_PAM.png',
            'Rektorat' => 'logo_Rektorat.png',
            'TUJ' => 'logo_TUKJ.png',
            'TUP' => 'logo_TUP.png',
            'TUS' => 'logo_TUS.png',
        ];

        $img = $participantsMap[$name] ?? 'default.png';

        $contingent = \App\Models\Contingent::where('name', $name)->first();
        $sports = \App\Models\Sport::with('categories')->get();

        $playersBySport = [];
        if ($contingent) {
            $registrations = \App\Models\Registration::with(['sport', 'sportCategory', 'players'])
                ->where('contingent_id', $contingent->id)
                ->get();
            
            foreach ($registrations as $reg) {
                $sportName = $reg->sport->name;
                if ($reg->sportCategory && !in_array(strtolower($reg->sportCategory->name), ['reguler', 'individu', 'team'])) {
                    $sportName .= ' ' . $reg->sportCategory->name;
                }
                $playersBySport[$sportName] = $reg->players;
            }
        }

        return view('public.participant-detail', compact('name', 'img', 'contingent', 'sports', 'playersBySport'));
    }

    /**
     * Halaman galeri publik.
     */
    public function galeri(): View
    {
        return view('public.galeri');
    }

    /**
     * Halaman detail pertandingan publik.
     */
    public function pertandingan($id)
    {
        return view('public.pertandingan', compact('id'));
    }
}