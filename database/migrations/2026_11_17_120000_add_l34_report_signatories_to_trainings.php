<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainings', fn (Blueprint $table) => $table->json('l34_report_signatories')->nullable());
    }

    public function down(): void
    {
        Schema::table('trainings', fn (Blueprint $table) => $table->dropColumn('l34_report_signatories'));
    }
};
