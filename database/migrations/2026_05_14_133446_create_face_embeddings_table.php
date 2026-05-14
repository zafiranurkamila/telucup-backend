<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan ekstensi pgvector aktif di database
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector;');

        Schema::create('face_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->timestamps();
        });

        // Menambahkan kolom vector 512 dimensi (Sesuai output model AdaFace)
        DB::statement('ALTER TABLE face_embeddings ADD COLUMN embedding vector(512)');
        
        // Indeks HNSW di tabel referensi sangat penting untuk mempercepat 
        // proses pencarian (1-to-N) dari sisi AI nantinya
        DB::statement('CREATE INDEX face_embeddings_embedding_hnsw_idx ON face_embeddings USING hnsw (embedding vector_cosine_ops)');
    }

    public function down(): void
    {
        Schema::dropIfExists('face_embeddings');
    }
};