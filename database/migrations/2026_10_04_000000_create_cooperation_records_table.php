<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cooperation_records', function (Blueprint $table) {
            $table->id();
            $table->string('partner_name');
            $table->string('subject');
            $table->unsignedSmallInteger('year')->index();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('file_type', 20)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
            $table->index(['year', 'partner_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooperation_records');
    }
};
