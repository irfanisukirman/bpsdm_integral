<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_activity_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('report_number')->nullable();
            foreach (['background', 'legal_basis', 'objectives', 'implementation', 'achievements', 'constraints', 'follow_up', 'conclusion', 'recommendations'] as $column) {
                $table->longText($column)->nullable();
            }
            $table->string('signatory_name')->nullable();
            $table->string('signatory_nip')->nullable();
            $table->string('signatory_position')->nullable();
            $table->date('approval_date')->nullable();
            $table->string('template_path')->nullable();
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('training_activity_documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('caption')->nullable();
            $table->string('category')->default('lainnya');
            $table->date('taken_at')->nullable();
            $table->string('file_path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('include_in_report')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('training_activity_report_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_activity_report_id')->constrained('training_activity_reports', 'id', 'tar_versions_report_fk')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('docx_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->json('snapshot')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users', 'id', 'tar_versions_user_fk')->nullOnDelete();
            $table->timestamps();
            $table->unique(['training_activity_report_id', 'version'], 'tar_versions_report_version_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_activity_report_versions');
        Schema::dropIfExists('training_activity_documentations');
        Schema::dropIfExists('training_activity_reports');
    }
};
