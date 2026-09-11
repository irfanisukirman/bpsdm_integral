<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electronic_signature_documents', function (Blueprint $table) {
            $table->foreignId('participant_certificate_id')->nullable()->after('electronic_signature_request_id');
            $table->foreign('participant_certificate_id', 'es_doc_participant_cert_fk')
                ->references('id')->on('participant_certificates')->nullOnDelete();
            $table->index('participant_certificate_id', 'es_doc_participant_cert_idx');
        });
    }

    public function down(): void
    {
        Schema::table('electronic_signature_documents', function (Blueprint $table) {
            $table->dropForeign('es_doc_participant_cert_fk');
            $table->dropIndex('es_doc_participant_cert_idx');
            $table->dropColumn('participant_certificate_id');
        });
    }
};
