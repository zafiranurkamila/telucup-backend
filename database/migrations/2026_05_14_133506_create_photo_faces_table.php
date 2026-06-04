<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Periksa secara aman apakah pgvector tersedia di PostgreSQL
        $hasVector = false;
        try {
            $connection = DB::connection()->getDriverName();
            if ($connection === 'pgsql') {
                $available = DB::select("SELECT 1 FROM pg_available_extensions WHERE name = 'vector'");
                if (!empty($available)) {
                    DB::statement('CREATE EXTENSION IF NOT EXISTS vector;');
                    $hasVector = true;
                }
            }
        } catch (\Exception $e) {
            // Abaikan error koneksi atau metadata
        }

        Schema::create('photo_faces', function (Blueprint $table) use ($hasVector) {
            $table->id();
            $table->foreignId('event_photo_id')->constrained('event_photos')->cascadeOnDelete();
            $table->foreignId('matched_player_id')->nullable()->constrained('players')->nullOnDelete();
            
            // Logika status validasi (pending by AI, accepted/rejected by User)
            $table->enum('validation_status', ['pending', 'accepted', 'rejected'])->default('pending');
            
            $table->float('similarity_score')->nullable(); 
            $table->json('bounding_box')->nullable(); // Menyimpan koordinat wajah [x, y, w, h] untuk UI bounding box
            if (!$hasVector) {
                $table->json('face_encoding')->nullable();
            }
            $table->timestamps();
        });

        if ($hasVector) {
            // Menyimpan vektor ekstraksi dari foto lapangan
            DB::statement('ALTER TABLE photo_faces ADD COLUMN face_encoding vector(512)');
            
            // Indeks HNSW seperti yang Anda minta
            DB::statement('CREATE INDEX photo_faces_encoding_hnsw_idx ON photo_faces USING hnsw (face_encoding vector_cosine_ops)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_faces');
    }
};