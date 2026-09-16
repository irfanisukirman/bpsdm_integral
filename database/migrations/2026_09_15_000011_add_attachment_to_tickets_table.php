<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('tickets', function (Blueprint $t) {
      $t->string('attachment_path')->nullable()->after('message');
    });
  }
  public function down(): void
  {
    Schema::table('tickets', function (Blueprint $t) {
      $t->dropColumn('attachment_path');
    });
  }
};
