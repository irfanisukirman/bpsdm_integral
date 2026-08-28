<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajar_schedule_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('bahan_ajar_path')->nullable();
            $table->string('rbpmp_rp_path')->nullable();
            $table->string('bukti_mengajar_path')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('pengajar_schedule_documents');
    }
};
