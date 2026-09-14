<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('internship_programs', function (Blueprint $table) {
            $table->string('certificate_template_path')->nullable()->after('status');
            $table->string('certificate_number_format')->nullable()->after('certificate_template_path');
            $table->unsignedInteger('certificate_start_sequence')->default(1)->after('certificate_number_format');
            $table->date('certificate_issued_at')->nullable()->after('certificate_start_sequence');
        });
        Schema::table('internship_participants', function (Blueprint $table) {
            $table->string('certificate_number')->nullable()->after('final_grade');
            $table->string('certificate_file_path')->nullable()->after('certificate_number');
            $table->timestamp('certificate_generated_at')->nullable()->after('certificate_file_path');
            $table->timestamp('certificate_downloaded_at')->nullable()->after('certificate_generated_at');
        });
    }
    public function down(): void {
        Schema::table('internship_participants', fn(Blueprint $table)=>$table->dropColumn(['certificate_number','certificate_file_path','certificate_generated_at','certificate_downloaded_at']));
        Schema::table('internship_programs', fn(Blueprint $table)=>$table->dropColumn(['certificate_template_path','certificate_number_format','certificate_start_sequence','certificate_issued_at']));
    }
};
