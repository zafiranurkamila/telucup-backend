<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('players', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('nim_nip')->unique();
        $table->string('sport_branch'); // Cabang Olahraga
        $table->string('contingent');    // Nama Kontingen/Fakultas
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
