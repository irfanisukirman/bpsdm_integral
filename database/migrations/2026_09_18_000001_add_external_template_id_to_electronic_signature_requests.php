<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('electronic_signature_requests', function (Blueprint $table) {
            $table->string('external_template_id')->nullable()->after('external_reference');
        });
    }
    public function down(): void
    {
        Schema::table('electronic_signature_requests', function (Blueprint $table) {
            $table->dropColumn('external_template_id');
        });
    }
};