<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_execution_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 200);
            $table->text('note');
            $table->timestamps();
            $table->index(['training_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_execution_notes');
    }
};