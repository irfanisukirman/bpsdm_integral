<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_final_quizzes', function (Blueprint $table) {
            $table->string('kind', 12)->default('posttest');
            $table->unique(['training_id', 'kind'], 'training_quizzes_training_kind_unique');
        });
        Schema::table('training_final_quizzes', function (Blueprint $table) {
            $table->dropUnique(['training_id']);
        });
        Schema::table('evaluation_results_l2', function (Blueprint $table) {
            $table->decimal('postest', 5, 2)->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        DB::table('training_final_quizzes')->where('kind', 'pretest')->delete();
        Schema::table('training_final_quizzes', function (Blueprint $table) {
            $table->unique('training_id');
        });
        Schema::table('training_final_quizzes', function (Blueprint $table) {
            $table->dropUnique('training_quizzes_training_kind_unique');
            $table->dropColumn('kind');
        });
        DB::table('evaluation_results_l2')->whereNull('postest')->update(['postest' => 0]);
        Schema::table('evaluation_results_l2', function (Blueprint $table) {
            $table->decimal('postest', 5, 2)->default(0)->change();
        });
    }
};
