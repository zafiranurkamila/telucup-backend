<?php

namespace App\Http\Controllers\Web\Panitia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    /**
     * Halaman Verifikasi Tim (Check-in Lapangan)
     *
     * Berisi interaktif UI (menggunakan Alpine.js) untuk mengecek dan
     * menandai kehadiran pemain sebelum pertandingan dimulai.
     */
    public function index(Request $request): View
    {
        $matchId = $request->query('match_id');

        return view('dashboard.panitia.verifikasi', [
            'matchId' => $matchId,
        ]);
    }
}
