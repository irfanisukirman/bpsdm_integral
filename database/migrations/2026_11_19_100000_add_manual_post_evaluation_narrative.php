<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('post_evaluation_settings', fn (Blueprint $table) => $table->json('report_narrative')->nullable()); }
    public function down(): void { Schema::table('post_evaluation_settings', fn (Blueprint $table) => $table->dropColumn('report_narrative')); }
};
