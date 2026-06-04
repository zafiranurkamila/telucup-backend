<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
        Schema::create('face_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->json('embedding')->nullable(); // Diubah ke JSON untuk kompatibilitas MySQL
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_embeddings');
    }
};