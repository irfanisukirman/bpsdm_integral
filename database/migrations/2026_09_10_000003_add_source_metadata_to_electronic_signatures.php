<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('electronic_signature_requests', function (Blueprint $table) {
            $table->string('source_type', 40)->default('other_documents')->after('bidang')->index();
            $table->foreignId('training_id')->nullable()->after('source_type')->constrained()->nullOnDelete();
            $table->string('external_reference')->nullable()->after('training_id');
        });
        Schema::table('electronic_signature_documents', function (Blueprint $table) {
            $table->string('external_user_id')->nullable()->after('participant_certificate_id');
        });
    }
    public function down(): void
    {
        Schema::table('electronic_signature_documents', fn (Blueprint $table) => $table->dropColumn('external_user_id'));
        Schema::table('electronic_signature_requests', function (Blueprint $table) {
            $table->dropForeign(['training_id']); $table->dropColumn(['source_type','training_id','external_reference']);
        });
    }
};
