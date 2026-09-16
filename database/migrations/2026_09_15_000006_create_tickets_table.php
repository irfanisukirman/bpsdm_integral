<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('tickets', function (Blueprint $t) {
      $t->id();
      $t->string('ticket_number')->unique();
      $t->string('tracking_token', 64)->unique();
      $t->enum('user_type', ['PNS', 'PPPK', 'Non-ASN']);
      $t->string('submitter_name');
      $t->string('nip_nik');
      $t->string('perangkat_daerah')->nullable();
      $t->string('email');
      $t->string('phone');
      $t->string('service');
      $t->string('category');
      $t->text('message');
      $t->enum('status', ['BARU', 'DIPROSES', 'MENUNGGU_PENGGUNA', 'RESOLVED', 'CLOSED'])->default('BARU');
      $t->string('bidang');
      $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
      $t->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
      $t->decimal('sla_respond_hours', 8, 2)->nullable();
      $t->decimal('sla_resolve_hours', 8, 2)->nullable();
      $t->timestamp('first_response_at')->nullable();
      $t->timestamp('resolved_at')->nullable();
      $t->timestamp('closed_at')->nullable();
      $t->boolean('is_reopened')->default(false);
      $t->integer('reopen_count')->default(0);
      $t->timestamp('auto_close_at')->nullable();
      $t->timestamps();
      $t->index('status');
      $t->index('bidang');
      $t->index('service');
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('tickets');
  }
};
