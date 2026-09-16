<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('ticket_slas', function (Blueprint $t) {
      $t->id();
      $t->foreignId('service_id')->nullable()->constrained('ticket_services')->nullOnDelete();
      $t->foreignId('category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
      $t->foreignId('bidang_id')->nullable()->constrained('ticket_bidang')->nullOnDelete();
      $t->decimal('respond_hours', 8, 2)->default(48);
      $t->decimal('resolve_hours', 8, 2)->default(72);
      $t->time('working_hours_start')->default('08:00');
      $t->time('working_hours_end')->default('17:00');
      $t->boolean('is_active')->default(true);
      $t->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('ticket_slas');
  }
};
