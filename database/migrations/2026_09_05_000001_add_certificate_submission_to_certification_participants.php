<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certification_participants', function (Blueprint $table) {
            $table->string('certificate_number')->nullable()->after('biodata_submitted_at');
            $table->foreignId('certificate_file_id')->nullable()->after('certificate_number')->constrained('files')->nullOnDelete();
            $table->unsignedTinyInteger('certification_rating')->nullable()->after('certificate_file_id');
            $table->text('certification_feedback')->nullable()->after('certification_rating');
            $table->timestamp('certificate_submitted_at')->nullable()->after('certification_feedback');
        });
    }

    public function down(): void
    {
        Schema::table('certification_participants', function (Blueprint $table) {
            $table->dropForeign(['certificate_file_id']);
            $table->dropColumn(['certificate_number', 'certificate_file_id', 'certification_rating', 'certification_feedback', 'certificate_submitted_at']);
        });
    }
};
