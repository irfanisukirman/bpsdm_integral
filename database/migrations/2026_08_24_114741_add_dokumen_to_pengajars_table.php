<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajars', function (Blueprint $table) {
            // Kolom untuk menyimpan path file dokumen
            if (!Schema::hasColumn('pengajars', 'cv_path')) {
                $table->string('cv_path')->nullable();
            }
            $table->string('sertifikat_path')->nullable()->after('cv_path');
            $table->string('surat_tugas_path')->nullable()->after('sertifikat_path');
        });
    }

    public function down(): void
    {
        Schema::table('pengajars', function (Blueprint $table) {
            $table->dropColumn(['cv_path', 'sertifikat_path', 'surat_tugas_path']);
        });
    }
};