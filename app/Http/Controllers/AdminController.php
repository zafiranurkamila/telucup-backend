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
     * @OA\Get(
     *      path="/admin/templates",
     *      operationId="getTemplates",
     *      tags={"Admin"},
     *      summary="Manajemen Template",
     *      description="Mengambil daftar template yang tersedia.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Berhasil mengambil data template"
     *      )
     * )
     * Gambar 1: Manajemen Template
     */
    public function templates()
    {
        return response()->json(Template::all());
    }

    /**
     * @OA\Get(
     *      path="/admin/registrations",
     *      operationId="getRegistrations",
     *      tags={"Admin"},
     *      summary="Verifikasi Pemain (List Registrasi)",
     *      description="Mengambil daftar registrasi pemain dengan filter cabang olahraga, kontingen, dan pencarian.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="sport_branch",
     *          in="query",
     *          required=false
     *      ),
     *      @OA\Parameter(
     *          name="contingent",
     *          in="query",
     *          required=false
     *      ),
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          required=false
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Berhasil mengambil data registrasi"
     *      )
     * )
     * Gambar 2: Verifikasi Pemain (List Registrasi)
     */
    public function registrations(Request $request)
    {
        $query = Registration::with('players');

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
     * @OA\Post(
     *      path="/admin/registrations/{id}/verify",
     *      operationId="verifyRegistration",
     *      tags={"Admin"},
     *      summary="Detail Registrasi & Verifikasi",
     *      description="Memperbarui status verifikasi dari sebuah registrasi (verified / rejected).",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"status"},
     *              @OA\Property(property="status", type="string", enum={"verified", "rejected"})
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Status registrasi berhasil diperbarui!"
     *      ),
     *      @OA\Response(response=404, description="Registration not found")
     * )
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
     * @OA\Get(
     *      path="/admin/schedules",
     *      operationId="getAdminSchedules",
     *      tags={"Admin"},
     *      summary="Jadwal Pertandingan",
     *      description="Mengambil daftar jadwal pertandingan berdasarkan tanggal dan cabang olahraga.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="date",
     *          in="query",
     *          required=false
     *      ),
     *      @OA\Parameter(
     *          name="sport_branch",
     *          in="query",
     *          required=false
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Berhasil mengambil data jadwal"
     *      )
     * )
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

    /**
     * @OA\Put(
     *      path="/admin/users/{id}/promote-to-pic",
     *      operationId="promoteToPic",
     *      tags={"Admin"},
     *      summary="Promote Player to PIC Kontingen",
     *      description="Mengubah peran pengguna menjadi PIC Kontingen.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User berhasil dipromosikan menjadi PIC Kontingen"
     *      ),
     *      @OA\Response(response=400, description="Tidak dapat mengubah role panitia")
     * )
     * Promote Player to PIC Kontingen
     */
    public function promoteToPic(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        if ($user->role === 'panitia') {
            return response()->json(['message' => 'Tidak dapat mengubah role panitia!'], 400);
        }

        $user->update(['role' => 'pic_kontingen']);

        return response()->json([
            'message' => 'User berhasil dipromosikan menjadi PIC Kontingen',
            'user' => $user
        ]);
    }
}
