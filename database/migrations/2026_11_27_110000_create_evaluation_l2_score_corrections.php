<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_l2_score_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('evaluation_results_l2')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('previous_score', 5, 2);
            $table->decimal('score', 5, 2);
            $table->string('reason', 500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_l2_score_corrections');
    }
};
