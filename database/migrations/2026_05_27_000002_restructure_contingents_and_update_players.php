<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Restrukturisasi tabel contingents:
        //    - Hapus external_id dan faculty_name (tidak diperlukan)
        //    - Tambah pic_user_id sebagai referensi ke User yang menjadi PIC
        if (Schema::hasColumn('contingents', 'external_id')) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS contingents_external_id_unique');
            } else {
                Schema::table('contingents', function (Blueprint $table) {
                    $table->dropUnique('contingents_external_id_unique');
                });
            }
        }

        Schema::table('contingents', function (Blueprint $table) {
            $table->dropColumn(['external_id', 'faculty_name']);
            $table->foreignId('pic_user_id')
                  ->nullable()
                  ->after('name')
                  ->constrained('users')
                  ->onDelete('set null');
        });

        // 2. Update players: ganti kolom contingent (string) menjadi contingent_id (FK)
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('contingent');
            $table->foreignId('contingent_id')
                  ->nullable()
                  ->after('sport_category_id')
                  ->constrained('contingents')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign(['contingent_id']);
            $table->dropColumn('contingent_id');
            $table->string('contingent')->nullable();
        });

        Schema::table('contingents', function (Blueprint $table) {
            $table->dropForeign(['pic_user_id']);
            $table->dropColumn('pic_user_id');
            $table->string('external_id')->unique()->nullable();
            $table->string('faculty_name')->nullable();
        });
    }
};
