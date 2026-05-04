<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Cabang Olahraga (Gambar 3 & 4)
        Schema::create('sports', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Basket, Futsal, Catur
            $table->json('categories')->nullable(); // ["Putra", "Putri"]
            $table->string('icon_path')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Pivot Registrasi & Pemain (Gambar 5)
        // Satu registrasi kontingen memiliki banyak pemain
        Schema::create('registration_player', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_player');
        Schema::dropIfExists('sports');
    }
};
