<?php

namespace App\Http\Controllers\Web\Panitia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\Sport;

class VerifikasiTimController extends Controller
{
    /**
     * Halaman Verifikasi Pendaftaran Tim
     *
     * Berisi antarmuka untuk mengecek, memverifikasi, atau menolak
     * pendaftaran tim kontingen beserta kelengkapan pemainnya.
     */
    public function index(Request $request): View
    {
        $sports = Sport::orderBy('name')->get();
        return view('dashboard.panitia.verifikasi-tim', compact('sports'));
    }
}
