<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('internship_programs', function (Blueprint $table) {
            $table->string('certificate_delivery_mode', 20)->default('tte')->after('certificate_reviewer_ids');
        });
    }

    public function down(): void
    {
        Schema::table('internship_programs', fn (Blueprint $table) => $table->dropColumn('certificate_delivery_mode'));
    }
};
