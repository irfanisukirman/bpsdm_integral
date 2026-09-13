<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_attendance_forms', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_token')->unique();
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->string('bidang')->nullable()->index();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->string('location')->nullable();
            $table->text('confirmation_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('activity_attendance_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_attendance_form_id');
            $table->foreign('activity_attendance_form_id','aa_questions_form_fk')->references('id')->on('activity_attendance_forms')->cascadeOnDelete();
            $table->text('label');
            $table->text('help_text')->nullable();
            $table->string('type', 30);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('max_file_size_kb')->nullable();
            $table->string('allowed_extensions')->nullable();
            $table->timestamps();
            $table->index(['activity_attendance_form_id', 'sort_order'], 'activity_questions_form_sort_idx');
        });

        Schema::create('activity_attendance_responses', function (Blueprint $table) {
            $table->id();
            $table->uuid('response_token')->unique();
            $table->foreignId('activity_attendance_form_id');
            $table->foreign('activity_attendance_form_id','aa_responses_form_fk')->references('id')->on('activity_attendance_forms')->cascadeOnDelete();
            $table->timestamp('submitted_at');
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            $table->index(['activity_attendance_form_id', 'submitted_at'], 'activity_responses_form_date_idx');
        });

        Schema::create('activity_attendance_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_attendance_response_id');
            $table->foreign('activity_attendance_response_id','aa_answers_response_fk')->references('id')->on('activity_attendance_responses')->cascadeOnDelete();
            $table->foreignId('activity_attendance_question_id');
            $table->foreign('activity_attendance_question_id','aa_answers_question_fk')->references('id')->on('activity_attendance_questions')->cascadeOnDelete();
            $table->longText('value_text')->nullable();
            $table->json('value_json')->nullable();
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
            $table->unique(['activity_attendance_response_id', 'activity_attendance_question_id'], 'activity_answers_response_question_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_attendance_answers');
        Schema::dropIfExists('activity_attendance_responses');
        Schema::dropIfExists('activity_attendance_questions');
        Schema::dropIfExists('activity_attendance_forms');
    }
};
