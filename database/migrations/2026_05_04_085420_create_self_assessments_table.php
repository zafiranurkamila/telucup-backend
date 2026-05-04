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
    Schema::create('self_assessments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('player_id')->constrained()->onDelete('cascade');
        $table->text('injury_history')->nullable(); 
        $table->string('injury_location')->nullable();
        $table->text('current_condition')->nullable();
        $table->integer('pain_score')->default(0); // Bagian C (0-10)
        $table->json('form_responses')->nullable(); 
        $table->float('confidence_score')->default(0); // Skor 0-100%
        $table->text('medical_notes')->nullable();     // Catatan Dokter/Fisio
        $table->boolean('is_allowed_to_play')->default(false); // Toggle Izin
        $table->timestamp('reviewed_at')->nullable();  // Waktu Review
        $table->enum('risk_label', ['low', 'moderate', 'high'])->default('low');
        $table->text('recommendation')->nullable(); // FR-01.5
        $table->boolean('pic_confirmed')->default(false); // FR-01.6
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('self_assessments');
    }
};
