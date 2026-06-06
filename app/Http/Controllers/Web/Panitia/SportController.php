<?php

namespace App\Http\Controllers\Web\Panitia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SportController extends Controller
{
    /**
     * Display the sports dashboard page.
     */
    public function index()
    {
        return view('dashboard.panitia.sports.index');
    }
}
