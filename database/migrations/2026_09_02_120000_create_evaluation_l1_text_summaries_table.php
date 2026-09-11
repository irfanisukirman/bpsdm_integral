<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_l1_text_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->unique()->constrained('trainings')->cascadeOnDelete();
            $table->longText('conclusion');
            $table->longText('follow_up');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_l1_text_summaries');
    }
};
