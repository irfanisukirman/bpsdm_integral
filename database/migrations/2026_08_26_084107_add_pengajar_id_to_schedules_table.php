<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Relasi ke tabel users (yang ber-role pengajar)
            $table->foreignId('pengajar_id')->nullable()->after('pic')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['pengajar_id']);
            $table->dropColumn('pengajar_id');
        });
    }
};