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
}
