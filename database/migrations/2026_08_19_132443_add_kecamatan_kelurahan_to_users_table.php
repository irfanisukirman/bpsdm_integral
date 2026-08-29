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
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan field kecamatan dan kelurahan setelah kabupaten_kota
            $table->string('kecamatan')->nullable()->after('kabupaten_kota');
            $table->string('kelurahan')->nullable()->after('kecamatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menghapus field jika di-rollback
            $table->dropColumn(['kecamatan', 'kelurahan']);
        });
    }
};