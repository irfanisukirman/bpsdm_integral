<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('level_one_recap_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind', 20);
            $table->string('fingerprint', 64);
            $table->string('filename');
            $table->string('source_training')->nullable();
            $table->json('indicators');
            $table->json('entries');
            $table->timestamps();
            $table->unique(['training_id', 'fingerprint']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('level_one_recap_imports');
    }
};
