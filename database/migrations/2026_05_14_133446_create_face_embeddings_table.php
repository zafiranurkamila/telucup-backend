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

        Schema::create('face_embeddings', function (Blueprint $table) use ($hasVector) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            if (!$hasVector) {
                $table->json('embedding')->nullable();
            }
            $table->timestamps();
        });

        if ($hasVector) {
            // Menambahkan kolom vector 512 dimensi (Sesuai output model AdaFace)
            DB::statement('ALTER TABLE face_embeddings ADD COLUMN embedding vector(512)');
            
            // Indeks HNSW di tabel referensi sangat penting untuk mempercepat 
            // proses pencarian (1-to-N) dari sisi AI nantinya
            DB::statement('CREATE INDEX face_embeddings_embedding_hnsw_idx ON face_embeddings USING hnsw (embedding vector_cosine_ops)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('face_embeddings');
    }
};