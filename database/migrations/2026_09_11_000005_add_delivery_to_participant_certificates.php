<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_certificates', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable()->after('uploaded_at');
            $table->foreignId('sent_by')->nullable()->after('sent_at')->constrained('users')->nullOnDelete();
            $table->index(['training_id', 'sent_at'], 'participant_certificates_training_sent_idx');
        });

        // Sertifikat final lama sudah dipublikasikan sebelum alur persetujuan distribusi tersedia.
        DB::table('participant_certificates')
            ->whereNotNull('final_file_path')
            ->whereNull('sent_at')
            ->update(['sent_at' => DB::raw('COALESCE(uploaded_at, updated_at)')]);
    }

    public function down(): void
    {
        Schema::table('participant_certificates', function (Blueprint $table) {
            $table->dropIndex('participant_certificates_training_sent_idx');
            $table->dropConstrainedForeignId('sent_by');
            $table->dropColumn('sent_at');
        });
    }
};