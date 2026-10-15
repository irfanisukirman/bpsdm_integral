<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin','admin_bidang','participant','pengajar','admin_aset','mitra','penandatangan','intern','pengelola_magang','resepsionis','pengelola_keuangan','manajemen_mutu') NOT NULL");
        }
        Schema::create('quality_document_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->string('requirement_key', 120);
            $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->string('source', 20)->default('uploaded');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['training_id', 'requirement_key'], 'quality_training_requirement_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_document_records');
        DB::table('users')->where('role', 'manajemen_mutu')->update(['role' => 'admin_bidang']);
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('superadmin','admin_bidang','participant','pengajar','admin_aset','mitra','penandatangan','intern','pengelola_magang','resepsionis','pengelola_keuangan') NOT NULL");
        }
    }
};
