<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('training_identity_card_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('logo_path')->nullable();
            $table->string('background_path')->nullable();
            $table->string('primary_color', 7)->default('#3157A4');
            $table->string('accent_color', 7)->default('#F4B740');
            $table->string('text_color', 7)->default('#FFFFFF');
            $table->unsignedTinyInteger('background_opacity')->default(100);
            $table->boolean('enabled')->default(false);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('participant_identity_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('photo_path')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_identity_cards');
        Schema::dropIfExists('training_identity_card_settings');
    }
};