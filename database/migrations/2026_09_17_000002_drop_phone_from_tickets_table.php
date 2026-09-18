<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('tickets', function (Blueprint $t) {
      if (Schema::hasColumn('tickets', 'phone')) {
        $t->dropColumn('phone');
      }
    });
  }
  public function down(): void
  {
    Schema::table('tickets', function (Blueprint $t) {
      if (!Schema::hasColumn('tickets', 'phone')) {
        $t->string('phone')->nullable();
      }
    });
  }
};