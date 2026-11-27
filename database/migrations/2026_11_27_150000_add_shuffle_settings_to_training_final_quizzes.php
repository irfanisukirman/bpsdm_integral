<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_final_quizzes', function (Blueprint $table) {
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_answers')->default(false);
            $table->boolean('shuffle_groups')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('training_final_quizzes', function (Blueprint $table) {
            $table->dropColumn(['shuffle_questions', 'shuffle_answers', 'shuffle_groups']);
        });
    }
};
