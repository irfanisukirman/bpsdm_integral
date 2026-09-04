<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('training_certificate_settings', function (Blueprint $table) {
            $table->string('photo_size', 10)->default('3x4')->after('issued_at');
        });
    }
    public function down(): void
    {
        Schema::table('training_certificate_settings', fn (Blueprint $table) => $table->dropColumn('photo_size'));
    }
};
