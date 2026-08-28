<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengajars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('npwp')->nullable();           // Untuk urusan honorarium
            $table->string('nama_bank')->nullable();      // Bank pencairan
            $table->string('nomor_rekening')->nullable(); // Rekening
            $table->text('bidang_keahlian')->nullable();  // Spesialisasi materi
            $table->string('cv_path')->nullable();        // Opsional jika ingin upload CV
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengajars');
    }
};