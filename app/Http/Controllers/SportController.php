<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\SportCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SportController extends Controller
{
    /**
     * GET /sports — Daftar semua cabang olahraga beserta sub-kategorinya
     */
    public function index()
    {
        $sports = Sport::with('categories')->get();
        return response()->json([
            'status' => 'success',
            'data'   => $sports,
        ]);
    }

    /**
     * GET /sports/{id} — Detail satu cabang olahraga beserta kategorinya
     */
    public function show($id)
    {
        $sport = Sport::with('categories')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data'   => $sport,
        ]);
    }

    /**
     * POST /sports — Buat cabang olahraga baru (admin/panitia only)
     *
     * Contoh body sport TANPA sub-kategori (Futsal, Basket):
     * {
     *   "name": "Futsal",
     *   "max_members": 5
     * }
     *
     * Contoh body sport DENGAN sub-kategori (Badminton):
     * {
     *   "name": "Badminton",
     *   "categories": [
     *     { "name": "Tunggal Putra", "max_members": 1 },
     *     { "name": "Ganda Putri",   "max_members": 2 }
     *   ]
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                      => 'required|string|max:255|unique:sports,name',
            'icon_path'                 => 'nullable|string|max:500',
            'max_members'               => 'nullable|integer|min:1',
            'categories'                => 'nullable|array',
            'categories.*.name'         => 'required|string|max:100',
            'categories.*.max_members'  => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $sport = Sport::create([
                'name'        => $validated['name'],
                'icon_path'   => $validated['icon_path'] ?? null,
                'max_members' => $validated['max_members'] ?? null,
            ]);

            if (!empty($validated['categories'])) {
                foreach ($validated['categories'] as $cat) {
                    SportCategory::create([
                        'sport_id'    => $sport->id,
                        'name'        => $cat['name'],
                        'max_members' => $cat['max_members'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Cabang olahraga berhasil dibuat.',
                'data'    => $sport->load('categories'),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat cabang olahraga: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PUT /sports/{id} — Update cabang olahraga (admin/panitia only)
     *
     * Jika field "categories" dikirim, seluruh kategori lama akan digantikan.
     */
    public function update(Request $request, $id)
    {
        $sport = Sport::findOrFail($id);

        $validated = $request->validate([
            'name'                      => 'sometimes|string|max:255|unique:sports,name,' . $sport->id,
            'icon_path'                 => 'nullable|string|max:500',
            'max_members'               => 'nullable|integer|min:1',
            'categories'                => 'nullable|array',
            'categories.*.name'         => 'required|string|max:100',
            'categories.*.max_members'  => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $sport->update([
                'name'        => $validated['name'] ?? $sport->name,
                'icon_path'   => array_key_exists('icon_path', $validated)
                                    ? $validated['icon_path']
                                    : $sport->icon_path,
                'max_members' => array_key_exists('max_members', $validated)
                                    ? $validated['max_members']
                                    : $sport->max_members,
            ]);

            // Jika categories dikirim, ganti seluruh kategori
            if (array_key_exists('categories', $validated)) {
                $sport->categories()->delete();

                if (!empty($validated['categories'])) {
                    foreach ($validated['categories'] as $cat) {
                        SportCategory::create([
                            'sport_id'    => $sport->id,
                            'name'        => $cat['name'],
                            'max_members' => $cat['max_members'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Cabang olahraga berhasil diperbarui.',
                'data'    => $sport->fresh()->load('categories'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memperbarui cabang olahraga: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /sports/{id} — Hapus cabang olahraga (admin/panitia only)
     */
    public function destroy($id)
    {
        $sport = Sport::findOrFail($id);
        $sport->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Cabang olahraga berhasil dihapus.',
        ]);
    }
}
