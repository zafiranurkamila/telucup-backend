<?php

namespace App\Http\Controllers\Web\PicKontingen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Cloudinary\Cloudinary;
use Illuminate\Http\RedirectResponse;

class KontingenController extends Controller
{
    /**
     * Tampilkan profil kontingen beserta data anggota.
     */
    public function show(Request $request): View
    {
        $contingent = Auth::user()->managedContingent;

        if ($contingent) {
            $contingent->load(['pic']);
            $contingent->loadCount('players');
        }

        return view('dashboard.pic-kontingen.profil-kontingen.index', [
            'contingent' => $contingent,
        ]);
    }

    /**
     * Upload logo kontingen ke Cloudinary.
     */
    public function updateLogo(Request $request): RedirectResponse
    {
        $contingent = Auth::user()->managedContingent;

        if (!$contingent) {
            return back()->with('error', 'Anda tidak mengelola kontingen apapun.');
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'image.required' => 'Pilih file gambar terlebih dahulu.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        try {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));

            // Hapus gambar lama jika ada
            if ($contingent->cloudinary_public_id) {
                $cloudinary->uploadApi()->destroy($contingent->cloudinary_public_id);
            }

            // Upload gambar baru
            $result = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath(), [
                'folder' => 'telucup/contingents',
            ]);

            $contingent->update([
                'cloudinary_public_id' => $result['public_id'],
                'image_url'            => $result['secure_url'],
            ]);

            return back()->with('success', 'Logo kontingen berhasil diunggah.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengunggah logo: ' . $e->getMessage());
        }
    }

    /**
     * Hapus logo kontingen dari Cloudinary.
     */
    public function deleteLogo(Request $request): RedirectResponse
    {
        $contingent = Auth::user()->managedContingent;

        if (!$contingent) {
            return back()->with('error', 'Anda tidak mengelola kontingen apapun.');
        }

        if (!$contingent->cloudinary_public_id) {
            return back()->with('error', 'Kontingen ini belum memiliki gambar logo.');
        }

        try {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            $cloudinary->uploadApi()->destroy($contingent->cloudinary_public_id);

            $contingent->update([
                'cloudinary_public_id' => null,
                'image_url'            => null,
            ]);

            return back()->with('success', 'Logo kontingen berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus logo: ' . $e->getMessage());
        }
    }
}
