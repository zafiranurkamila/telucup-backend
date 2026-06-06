<?php

namespace App\Http\Controllers\Web\PicKontingen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrasiController extends Controller
{
    /**
     * Tampilkan halaman registrasi tim.
     */
    public function index(Request $request): View
    {
        return view('dashboard.pic-kontingen.registrasi.index');
    }
}
