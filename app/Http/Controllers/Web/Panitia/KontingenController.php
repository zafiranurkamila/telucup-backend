<?php

namespace App\Http\Controllers\Web\Panitia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KontingenController extends Controller
{
    /**
     * Display the kelola kontingen dashboard page.
     */
    public function index()
    {
        return view('dashboard.panitia.kontingen.index');
    }
}
