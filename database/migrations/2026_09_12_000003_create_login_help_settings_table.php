<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_help_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Hubungi Admin via WhatsApp');
            $table->text('description')->nullable();
            $table->json('contacts')->nullable();
            $table->text('message_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_help_settings');
    }
};
