<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('ticket_services', function (Blueprint $t) {
      $t->id();
      $t->string('name');
      $t->string('slug')->unique();
      $t->boolean('is_active')->default(true);
      $t->integer('sort_order')->default(0);
      $t->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('ticket_services');
  }
};
