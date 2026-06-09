<?php

namespace App\Http\Controllers\Web\Player;

use App\Http\Controllers\Controller;
use App\Models\SelfAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Player dashboard — profil singkat dan quick links.
     */
    public function index(Request $request)
    {
        return redirect()->route('dashboard.player.profil.show');
    }

    public function profil(Request $request): View
    {
        $user = Auth::user();
        $player = $user->player;

        $assessment = null;

        if ($player) {
            $assessment = SelfAssessment::where('player_id', $player->id)
                ->latest()
                ->first();
        }

        return view('dashboard.player.profil', [
            'user' => $user,
            'player' => $player,
            'assessment' => $assessment,
        ]);
    }

    public function editProfil(Request $request): View
    {
        $user = Auth::user();
        $player = $user->player;

        return view('dashboard.player.edit-profil', [
            'user' => $user,
            'player' => $player,
        ]);
    }

    public function updateProfil(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $player = $user->player;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nim_nip' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user->name = $validated['name'];
        $user->save();

        if ($player) {
            $player->nim_nip = $validated['nim_nip'] ?? null;

            if (array_key_exists('phone', $validated)) {
                $player->phone = $validated['phone'];
            }

            if ($request->hasFile('photo')) {
                $player->photo_path = $request->file('photo')->store('players', 'public');
            }

            $player->save();
        }

        return redirect()
            ->route('dashboard.player.profil.show')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function selfAssessment(Request $request): View
    {
        return view('dashboard.player.self-assessment');
    }
}
