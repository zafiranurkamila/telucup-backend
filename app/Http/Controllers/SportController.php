<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use Illuminate\Http\Request;

class SportController extends Controller
{
    /**
     * Gambar 3: Daftar Cabang Olahraga untuk Registrasi
     */
    public function index()
    {
        return response()->json(Sport::all());
    }

    /**
     * Gambar 4: Mengambil Kategori per Cabang (Putra/Putri)
     */
    public function show($id)
    {
        $sport = Sport::findOrFail($id);
        return response()->json($sport);
    }
}
