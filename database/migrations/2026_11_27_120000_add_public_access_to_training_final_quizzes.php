<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_final_quizzes', function (Blueprint $table) {
            $table->uuid('public_token')->nullable()->unique();
            $table->boolean('is_public')->default(false);
            $table->dateTime('opens_at')->nullable();
            $table->dateTime('closes_at')->nullable();
        });

        DB::table('training_final_quizzes')->select('id')->chunkById(200, function ($quizzes) {
            foreach ($quizzes as $quiz) {
                DB::table('training_final_quizzes')->where('id', $quiz->id)->update(['public_token' => (string) Str::uuid()]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('training_final_quizzes', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn(['public_token', 'is_public', 'opens_at', 'closes_at']);
        });
    }
};
