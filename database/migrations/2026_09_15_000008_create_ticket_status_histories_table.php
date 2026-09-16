<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('ticket_status_histories', function (Blueprint $t) {
      $t->id();
      $t->foreignId('ticket_id')->constrained()->cascadeOnDelete();
      $t->string('from_status')->nullable();
      $t->string('to_status');
      $t->string('changed_by_name');
      $t->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
      $t->text('note')->nullable();
      $t->timestamps();
      $t->index('ticket_id');
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('ticket_status_histories');
  }
};
