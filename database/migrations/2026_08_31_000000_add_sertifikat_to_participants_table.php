<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->foreignId('sertifikat_file_id')->nullable()->after('pas_foto_file_id')->constrained('files')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropForeign(['sertifikat_file_id']);
            $table->dropColumn('sertifikat_file_id');
        });
    }
};
