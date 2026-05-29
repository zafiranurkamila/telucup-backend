<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL tidak bisa langsung menambah nilai ke enum,
        // jadi kita hapus constraint lama dan buat yang baru.
        DB::statement("ALTER TABLE registrations DROP CONSTRAINT IF EXISTS registrations_status_check");
        DB::statement("ALTER TABLE registrations ADD CONSTRAINT registrations_status_check CHECK (status IN ('draft', 'submitted', 'verified', 'rejected'))");

        // Migrasi data: 'pending' (alur lama) → 'submitted' agar konsisten dengan alur baru
        DB::statement("UPDATE registrations SET status = 'submitted' WHERE status = 'pending'");
    }

    public function down(): void
    {
        // Kembalikan ke submitted dulu agar semua nilai valid sebelum ubah constraint
        DB::statement("UPDATE registrations SET status = 'pending' WHERE status IN ('submitted', 'draft')");
        DB::statement("ALTER TABLE registrations DROP CONSTRAINT IF EXISTS registrations_status_check");
        DB::statement("ALTER TABLE registrations ADD CONSTRAINT registrations_status_check CHECK (status IN ('pending', 'verified', 'rejected'))");
    }
};
