<?php

namespace App\Http\Controllers\Web\Panitia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedisController extends Controller
{
    /**
     * Halaman Tinjauan Medis
     *
     * Berisi antarmuka untuk panitia medis memverifikasi 
     * hasil self-assessment peserta.
     */
    public function index(Request $request): View
    {
        return view('dashboard.panitia.medis.index');
    }
}
