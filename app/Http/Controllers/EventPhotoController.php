<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventPhoto;
use App\Jobs\ProcessEventPhoto;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\DB;

class EventPhotoController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Maksimal 5MB
        ]);

        try {
            DB::beginTransaction();

            // 2. Upload ke Cloudinary
            // Menggunakan SDK asli cloudinary/cloudinary_php karena cloudinary-laravel tidak mensupport Laravel 13
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            $uploadResult = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath(), [
                'folder' => 'telucup/event_photos' // Simpan di folder rapi di Cloudinary
            ]);

            // 3. Simpan record ke database Laravel (PostgreSQL)
            $eventPhoto = EventPhoto::create([
                'cloudinary_public_id' => $uploadResult['public_id'],
                'image_url'            => $uploadResult['secure_url'],
                'uploaded_by'          => auth()->id() ?? 1, // Hubungkan dengan panitia yang login
            ]);

            // 4. Lemparkan tugas pengenalan wajah ke Antrean (Background Job)
            ProcessEventPhoto::dispatch($eventPhoto);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Foto berhasil diunggah dan sedang diproses oleh AI.',
                'data'    => $eventPhoto
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengunggah foto: ' . $e->getMessage()
            ], 500);
        }
    }
}