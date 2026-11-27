<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_final_quiz_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('training_final_quizzes')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->text('token');
            $table->unique(['quiz_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_final_quiz_access_tokens');
    }
};
