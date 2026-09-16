<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('ticket_routing_rules', function (Blueprint $t) {
      $t->id();
      $t->foreignId('service_id')->constrained('ticket_services')->cascadeOnDelete();
      $t->foreignId('category_id')->constrained('ticket_categories')->cascadeOnDelete();
      $t->foreignId('bidang_id')->constrained('ticket_bidang')->cascadeOnDelete();
      $t->foreignId('default_pic_user_id')->nullable()->constrained('users')->nullOnDelete();
      $t->decimal('sla_respond_hours', 8, 2)->nullable();
      $t->decimal('sla_resolve_hours', 8, 2)->nullable();
      $t->boolean('is_active')->default(true);
      $t->timestamps();
      $t->unique(['service_id', 'category_id'], 'ticket_routing_unique');
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('ticket_routing_rules');
  }
};
