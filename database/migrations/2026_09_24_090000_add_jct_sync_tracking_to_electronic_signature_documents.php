<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('electronic_signature_documents', function (Blueprint $table) {
            $table->string('jct_sync_status', 20)->nullable()->after('completed_at');
            $table->timestamp('jct_synced_at')->nullable()->after('jct_sync_status');
            $table->text('jct_sync_error')->nullable()->after('jct_synced_at');
            $table->unsignedInteger('jct_sync_attempts')->default(0)->after('jct_sync_error');
        });
    }

    public function down(): void
    {
        Schema::table('electronic_signature_documents', function (Blueprint $table) {
            $table->dropColumn(['jct_sync_status', 'jct_synced_at', 'jct_sync_error', 'jct_sync_attempts']);
        });
    }
};