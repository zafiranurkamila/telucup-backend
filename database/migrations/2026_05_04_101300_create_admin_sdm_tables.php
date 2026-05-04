<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Template (Gambar 1)
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('year'); // 2025, 2026
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 2. Update Tabel Players (Gambar 3)
        Schema::table('players', function (Blueprint $table) {
            $table->string('photo_path')->nullable();
            $table->string('employee_status')->nullable(); // TPA PEGAWAI TETAP
            $table->string('work_location')->nullable();   // URUSAN PENCATATAN...
        });

        // 3. Tabel Registrasi (Gambar 2 & 3)
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('sport_branch');
            $table->string('contingent');
            $table->string('pic_name');
            $table->string('pic_email');
            $table->string('pic_whatsapp');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // 4. Update Tabel Games (Gambar 4 & 5)
        Schema::table('games', function (Blueprint $table) {
            $table->string('location')->nullable(); // Sport Center
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
        Schema::dropIfExists('registrations');
    }
};
