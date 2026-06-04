<?php

namespace App\Http\Controllers\Web\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BracketController extends Controller
{
    /**
     * Kelola Bagan — halaman utama pengelolaan bracket turnamen.
     *
     * Halaman ini menggunakan Alpine.js + fetch ke API yang sudah ada
     * untuk interaktivitas (generate, refresh, edit match, drag-drop).
     * Server hanya perlu menyediakan daftar sports sebagai data awal.
     */
    public function index(Request $request): View
    {
        $sports = Sport::with('categories')->orderBy('name')->get();

        return view('dashboard.panitia.kelola-bagan', [
            'sports' => $sports,
        ]);
    }
}
