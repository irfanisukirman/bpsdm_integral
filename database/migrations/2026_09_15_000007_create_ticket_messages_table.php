<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('ticket_messages', function (Blueprint $t) {
      $t->id();
      $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
      $t->string('sender_name');
      $t->string('sender_role');
      $t->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
      $t->text('message');
      $t->boolean('is_internal')->default(false);
      $t->string('attachment_path')->nullable();
      $t->timestamps();
      $t->index('ticket_id');
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('ticket_messages');
  }
};
