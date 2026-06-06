<?php

namespace App\Http\Controllers\Web\PicKontingen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DokumentasiController extends Controller
{
    /**
     * Tampilkan halaman Dokumentasi Event untuk PIC Kontingen.
     */
    public function index(Request $request): View
    {
        return view('dashboard.pic-kontingen.dokumentasi.index');
    }
}
