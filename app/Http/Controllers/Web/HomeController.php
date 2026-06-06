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
     * Halaman galeri publik.
     */
    public function galeri(): View
    {
        return view('public.galeri');
    }
}
