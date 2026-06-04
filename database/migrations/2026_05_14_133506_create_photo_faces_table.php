<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_faces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_photo_id')->constrained('event_photos')->cascadeOnDelete();
            $table->foreignId('matched_player_id')->nullable()->constrained('players')->nullOnDelete();
            
            // Logika status validasi (pending by AI, accepted/rejected by User)
            $table->enum('validation_status', ['pending', 'accepted', 'rejected'])->default('pending');
            
            $table->float('similarity_score')->nullable(); 
            $table->json('bounding_box')->nullable(); // Menyimpan koordinat wajah [x, y, w, h] untuk UI bounding box
            $table->json('face_encoding')->nullable(); // Menggunakan JSON untuk kompatibilitas MySQL versi lama
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_faces');
    }
};