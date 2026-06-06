<?php

namespace App\Http\Controllers\Web\Panitia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GaleriController extends Controller
{
    /**
     * Halaman Kelola Galeri Event
     */
    public function index(Request $request): View
    {
        return view('dashboard.panitia.galeri.index', [
            'isViewer' => false
        ]);
    }
}
