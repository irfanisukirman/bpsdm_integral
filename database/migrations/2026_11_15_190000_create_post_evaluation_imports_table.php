<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_evaluation_imports', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignId('training_id')->constrained()->cascadeOnDelete();
            $t->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $t->string('filename');
            $t->string('path');
            $t->string('fingerprint', 64);
            $t->string('status', 20)->default('preview');
            $t->json('report');
            $t->timestamp('imported_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_evaluation_imports');
    }
};
