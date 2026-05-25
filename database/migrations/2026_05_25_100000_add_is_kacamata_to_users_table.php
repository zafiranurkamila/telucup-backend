<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom is_kacamata ke tabel users.
 *
 * Latar belakang:
 * Berdasarkan permintaan mitra, peserta yang menggunakan kacamata perlu
 * diberi label khusus sehingga panitia dapat memberikan pemantauan lebih
 * terhadap potensi risiko fisik (mis. kacamata pecah saat olahraga kontak,
 * keterbatasan visual saat lensa lepas).
 *
 * Kolom ini disimpan di level user (bukan di player) karena merupakan
 * atribut personal yang melekat pada individu, bukan pada keikutsertaan
 * di satu cabor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_kacamata')
                ->default(false)
                ->after('role')
                ->comment('Flag mitra: penanda peserta pengguna kacamata untuk pemantauan ekstra');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_kacamata');
        });
    }
};