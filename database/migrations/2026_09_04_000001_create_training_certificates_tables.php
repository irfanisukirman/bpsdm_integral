<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('training_certificate_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name')->default('Sertifikat Pelatihan');
            $table->string('template_path')->nullable();
            $table->string('number_format');
            $table->unsignedInteger('start_sequence')->default(1);
            $table->date('issued_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('participant_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_certificate_setting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence_number');
            $table->string('certificate_number')->unique();
            $table->string('generated_file_path')->nullable();
            $table->string('final_file_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['training_id', 'participant_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('participant_certificates');
        Schema::dropIfExists('training_certificate_settings');
    }
};
