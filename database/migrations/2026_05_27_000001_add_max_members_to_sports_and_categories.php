<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Untuk sport tanpa sub-kategori (misal: Futsal, Basket)
        Schema::table('sports', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_members')->nullable()->after('icon_path');
        });

        // Untuk sport dengan sub-kategori (misal: Badminton Tunggal=1, Ganda=2)
        Schema::table('sport_categories', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_members')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('sport_categories', function (Blueprint $table) {
            $table->dropColumn('max_members');
        });

        Schema::table('sports', function (Blueprint $table) {
            $table->dropColumn('max_members');
        });
    }
};
