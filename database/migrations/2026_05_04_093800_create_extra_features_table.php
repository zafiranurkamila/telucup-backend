<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel untuk Smart Gallery
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->json('tags')->nullable(); // Menyimpan hasil AI (Nama/Nomor Punggung)
            $table->timestamps();
        });

        // Tabel untuk Chatbot/FAQ
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
        Schema::dropIfExists('faqs');
    }
};
