<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('training_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name', 100)->nullable();
            $table->string('tracking_hash', 64)->nullable()->unique();
            $table->string('category', 32);
            $table->string('status', 16)->default('received');
            $table->timestamps();
            $table->index(['user_id', 'updated_at']);
            $table->index(['training_id', 'status']);
        });

        Schema::create('complaint_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('from_staff')->default(false);
            $table->text('body');
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->index(['complaint_ticket_id', 'from_staff', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_messages');
        Schema::dropIfExists('complaint_tickets');
    }
};
