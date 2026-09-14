<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin','admin_bidang','participant','pengajar','admin_aset','mitra','penandatangan','intern','pengelola_magang') NOT NULL");
        }
        Schema::table('internship_programs', function (Blueprint $table) {
            $table->foreignId('manager_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('internship_programs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_id');
        });
        DB::table('users')->where('role', 'pengelola_magang')->update(['role' => 'superadmin']);
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin','admin_bidang','participant','pengajar','admin_aset','mitra','penandatangan','intern') NOT NULL");
        }
    }
};