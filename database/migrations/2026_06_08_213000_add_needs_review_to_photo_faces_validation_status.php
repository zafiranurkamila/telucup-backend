<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE photo_faces MODIFY COLUMN validation_status ENUM('pending', 'needs_review', 'accepted', 'rejected') DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE photo_faces SET validation_status = 'pending' WHERE validation_status = 'needs_review'");
        DB::statement("ALTER TABLE photo_faces MODIFY COLUMN validation_status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending'");
    }
};
