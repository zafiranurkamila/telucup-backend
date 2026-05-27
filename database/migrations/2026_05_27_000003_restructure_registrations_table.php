<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Hapus kolom lama yang kini digantikan oleh FK
            $table->dropColumn(['sport_branch', 'contingent', 'pic_name', 'pic_email', 'pic_whatsapp']);

            // Tambah FK ke entitas yang sudah terstruktur
            $table->foreignId('contingent_id')->after('id')->constrained('contingents')->onDelete('cascade');
            $table->foreignId('sport_id')->after('contingent_id')->constrained('sports')->onDelete('cascade');
            $table->foreignId('sport_category_id')->nullable()->after('sport_id')->constrained('sport_categories')->onDelete('cascade');

            // Satu kontingen hanya boleh mendaftar satu kali per sport+kategori
            $table->unique(['contingent_id', 'sport_id', 'sport_category_id'], 'unique_team_per_sport');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropUnique('unique_team_per_sport');
            $table->dropForeign(['sport_category_id']);
            $table->dropForeign(['sport_id']);
            $table->dropForeign(['contingent_id']);
            $table->dropColumn(['contingent_id', 'sport_id', 'sport_category_id']);

            $table->string('sport_branch');
            $table->string('contingent');
            $table->string('pic_name');
            $table->string('pic_email');
            $table->string('pic_whatsapp');
        });
    }
};
