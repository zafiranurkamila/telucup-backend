<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Halaman publik utama untuk penonton / publik tanpa akun.
     */
    public function index(): View
    {
        return view('public.home');
    }
}
