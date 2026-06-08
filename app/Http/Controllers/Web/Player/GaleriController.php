<?php

namespace App\Http\Controllers\Web\Player;

use App\Http\Controllers\Controller;
use App\Models\PhotoFace;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GaleriController extends Controller
{
    public function index(Request $request): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $player = $user->player;

        if (!$player) {
            $player = Player::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'nim_nip' => null,
            ]);
        }

        $photos = PhotoFace::where('matched_player_id', $player->id)
            ->with(['eventPhoto:id,image_url,cloudinary_public_id,gallery_folder_id,created_at'])
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard.player.galeri', compact('user', 'player', 'photos'));
    }
}