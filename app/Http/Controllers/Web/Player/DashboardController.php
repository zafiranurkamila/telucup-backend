<?php

namespace App\Http\Controllers\Web\Player;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Player dashboard — profil singkat dan quick links.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $player = $user->player;

        return view('dashboard.player.index', [
            'user'   => $user,
            'player' => $player,
        ]);
    }
}
