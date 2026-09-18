<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotline_availability_settings', function (Blueprint $table) {
            $table->id();
            $table->string('mode')->default('auto');
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->json('workdays')->nullable();
            $table->string('note')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotline_availability_settings');
    }
};
