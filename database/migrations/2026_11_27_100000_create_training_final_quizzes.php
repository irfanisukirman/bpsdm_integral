<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_final_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedTinyInteger('pass_score')->default(70);
            $table->boolean('is_visible')->default(false);
            $table->unsignedInteger('revision')->default(1);
            $table->json('questions');
            $table->timestamps();
        });

        Schema::create('training_final_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('training_final_quizzes')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->unique(['quiz_id', 'participant_id']);
            $table->decimal('score', 5, 2);
            $table->unsignedTinyInteger('pass_score');
            $table->boolean('passed');
            $table->unsignedSmallInteger('correct_count');
            $table->unsignedSmallInteger('questions_count');
            $table->json('answers');
            $table->timestamps();
        });

        Schema::table('evaluation_results_l2', function (Blueprint $table) {
            $table->decimal('pretest', 5, 2)->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_final_quiz_attempts');
        Schema::dropIfExists('training_final_quizzes');
        DB::table('evaluation_results_l2')->whereNull('pretest')->update(['pretest' => 0]);
        Schema::table('evaluation_results_l2', function (Blueprint $table) {
            $table->decimal('pretest', 5, 2)->default(0)->change();
        });
    }
};
