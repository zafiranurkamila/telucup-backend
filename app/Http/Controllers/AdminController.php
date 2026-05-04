<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\Registration;
use App\Models\Player;
use App\Models\Game;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Gambar 1: Manajemen Template
     */
    public function templates()
    {
        return response()->json(Template::all());
    }

    /**
     * Gambar 2: Verifikasi Pemain (List Registrasi)
     */
    public function registrations(Request $request)
    {
        $query = Registration::query();

        if ($request->sport_branch) {
            $query->where('sport_branch', $request->sport_branch);
        }

        if ($request->contingent) {
            $query->where('contingent', $request->contingent);
        }

        if ($request->search) {
            $query->where('pic_name', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->paginate(10));
    }

    /**
     * Gambar 3: Detail Registrasi & Verifikasi
     */
    public function verifyRegistration(Request $request, $id)
    {
        $registration = Registration::findOrFail($id);
        $registration->update(['status' => $request->status]); // verified / rejected

        return response()->json([
            'message' => 'Status registrasi berhasil diperbarui!',
            'status' => $registration->status
        ]);
    }

    /**
     * Gambar 4: Jadwal Pertandingan
     */
    public function schedules(Request $request)
    {
        $query = Game::query();

        if ($request->date) {
            $query->whereDate('match_date', $request->date);
        }

        if ($request->sport_branch) {
            $query->where('sport_branch', $request->sport_branch);
        }

        return response()->json($query->get());
    }
}
