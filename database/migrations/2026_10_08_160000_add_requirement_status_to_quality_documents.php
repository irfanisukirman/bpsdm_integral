<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quality_document_records', function (Blueprint $table) {
            $table->boolean('is_required')->default(true)->after('requirement_key')->index();
            $table->foreignId('requirement_updated_by')->nullable()->after('uploaded_by')->constrained('users')->nullOnDelete();
            $table->timestamp('requirement_updated_at')->nullable()->after('requirement_updated_by');
        });
    }

    public function down(): void
    {
        Schema::table('quality_document_records', function (Blueprint $table) {
            $table->dropForeign(['requirement_updated_by']);
            $table->dropColumn(['is_required', 'requirement_updated_by', 'requirement_updated_at']);
        });
    }
};
