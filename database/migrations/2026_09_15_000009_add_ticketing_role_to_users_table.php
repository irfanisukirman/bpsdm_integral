<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  public function up(): void
  {
    if (DB::getDriverName() === 'mysql') {
      DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin','admin_bidang','participant','pengajar','admin_aset','mitra','penandatangan','intern','pengelola_magang','resepsionis') NOT NULL");
    }
  }
  public function down(): void
  {
    if (DB::getDriverName() === 'mysql') {
      DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin','admin_bidang','participant','pengajar','admin_aset','mitra','penandatangan','intern','pengelola_magang','resepsionis') NOT NULL");
    }
  }
};
