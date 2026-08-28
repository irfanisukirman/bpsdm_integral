<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengajars', function (Blueprint $table) {
            // Menambahkan field instansi setelah pangkat_golongan
            $table->string('pangkat_golongan')->nullable()->after('bidang_keahlian');
            $table->string('instansi')->nullable()->after('pangkat_golongan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajars', function (Blueprint $table) {
            $table->dropColumn('instansi');
        });
    }
};