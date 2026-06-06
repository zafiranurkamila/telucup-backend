<?php

namespace App\Http\Controllers\Web\Panitia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosterController extends Controller
{
    /**
     * Halaman Kelola Poster Sportifitas
     */
    public function index(Request $request): View
    {
        return view('dashboard.panitia.poster.index');
    }
}
