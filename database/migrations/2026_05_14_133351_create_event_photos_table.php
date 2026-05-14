<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_photos', function (Blueprint $table) {
            $table->id();
            $table->string('cloudinary_public_id')->unique();
            $table->string('image_url');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete(); // Panitia yang mengunggah
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_photos');
    }
};