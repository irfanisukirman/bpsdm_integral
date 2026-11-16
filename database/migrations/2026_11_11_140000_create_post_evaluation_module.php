<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_evaluation_settings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('training_id')->unique()->constrained()->cascadeOnDelete();
            $t->json('mappings')->nullable();
            $t->json('rubric')->nullable();
            $t->string('public_token', 64)->nullable()->unique();
            $t->json('snapshot')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
        });
        Schema::create('post_evaluation_teams', function (Blueprint $t) {
            $t->id();
            $t->foreignId('training_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->timestamps();
        });
        Schema::create('post_evaluation_team_user', function (Blueprint $t) {
            $t->id();
            $t->foreignId('team_id')->constrained('post_evaluation_teams')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unique(['team_id', 'user_id']);
        });
        Schema::create('post_evaluation_interviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('training_id')->constrained()->cascadeOnDelete();
            $t->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $t->foreignId('team_id')->nullable()->constrained('post_evaluation_teams')->nullOnDelete();
            $t->json('scores')->nullable();
            $t->json('notes')->nullable();
            $t->string('status')->default('draft');
            $t->unsignedInteger('revision')->default(0);
            $t->text('project')->nullable();
            $t->text('medium_milestone')->nullable();
            $t->text('long_milestone')->nullable();
            $t->text('current_condition')->nullable();
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('upload_token', 64)->nullable()->unique();
            $t->timestamps();
            $t->unique(['training_id', 'participant_id']);
        });
        Schema::create('post_evaluation_evidence', function (Blueprint $t) {
            $t->id();
            $t->foreignId('interview_id')->constrained('post_evaluation_interviews')->cascadeOnDelete();
            $t->string('kind');
            $t->string('path');
            $t->string('original_name');
            $t->string('mime');
            $t->unsignedBigInteger('size');
            $t->timestamps();
            $t->unique(['interview_id', 'kind']);
        });
    }

    public function down(): void
    {
        foreach (['post_evaluation_evidence', 'post_evaluation_interviews', 'post_evaluation_team_user', 'post_evaluation_teams', 'post_evaluation_settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
