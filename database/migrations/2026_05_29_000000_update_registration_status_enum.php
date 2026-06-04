<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: modify enum
        DB::statement("ALTER TABLE registrations MODIFY COLUMN status ENUM('draft', 'submitted', 'verified', 'rejected')");

        // Migrasi data: 'pending' (alur lama) → 'submitted' agar konsisten dengan alur baru
        DB::statement("UPDATE registrations SET status = 'submitted' WHERE status = 'pending'");
    }

    public function down(): void
    {
        // Kembalikan ke submitted dulu agar semua nilai valid sebelum ubah constraint
        DB::statement("UPDATE registrations SET status = 'pending' WHERE status IN ('submitted', 'draft')");
        DB::statement("ALTER TABLE registrations MODIFY COLUMN status ENUM('pending', 'verified', 'rejected')");
    }
};
