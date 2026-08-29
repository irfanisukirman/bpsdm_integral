<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_results', function (Blueprint $table) {
            $table->date('monitoring_date')->nullable()->after('training_stage_id');
            $table->text('recommendation')->nullable()->after('notes');
            $table->string('priority', 20)->default('sedang')->after('follow_up_target');
            $table->date('due_date')->nullable()->after('priority');
            $table->string('workflow_status', 30)->default('open')->after('due_date');
            $table->foreignId('submitted_by')->nullable()->after('evidence_file')->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->after('submitted_by');
            $table->foreignId('verified_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('verification_notes')->nullable()->after('verified_at');
        });

        DB::table('monitoring_results')->where('answer', 'tidak')->update([
            'workflow_status' => DB::raw("CASE WHEN is_resolved = 1 THEN 'verified' ELSE 'open' END"),
        ]);
    }

    public function down(): void
    {
        Schema::table('monitoring_results', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'monitoring_date', 'recommendation', 'priority', 'due_date', 'workflow_status',
                'submitted_by', 'submitted_at', 'verified_by', 'verified_at', 'verification_notes',
            ]);
        });
    }
};
