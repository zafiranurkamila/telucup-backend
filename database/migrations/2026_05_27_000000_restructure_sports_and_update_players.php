<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel sport_categories untuk sub-cabang olahraga
        Schema::create('sport_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')->constrained('sports')->onDelete('cascade');
            $table->string('name'); // "Putra", "Putri", dst.
            $table->timestamps();
        });

        // 2. Hapus kolom categories (JSON) dari sports
        Schema::table('sports', function (Blueprint $table) {
            $table->dropColumn('categories');
        });

        // 3. Ubah players: ganti sport_branch (string) menjadi sport_id & sport_category_id (FK)
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('sport_branch');
            $table->foreignId('sport_id')->nullable()->constrained('sports')->onDelete('set null');
            $table->foreignId('sport_category_id')->nullable()->constrained('sport_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign(['sport_category_id']);
            $table->dropForeign(['sport_id']);
            $table->dropColumn(['sport_id', 'sport_category_id']);
            $table->string('sport_branch')->nullable();
        });

        Schema::table('sports', function (Blueprint $table) {
            $table->json('categories')->nullable();
        });

        Schema::dropIfExists('sport_categories');
    }
};
