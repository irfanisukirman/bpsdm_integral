<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('post_evaluation_interviews', 'continuation')) {
            Schema::table('post_evaluation_interviews', fn (Blueprint $table) => $table->json('continuation')->nullable());
        }
        if (!Schema::hasTable('post_evaluation_score_backups')) {
            Schema::create('post_evaluation_score_backups', function (Blueprint $table) {
                $table->unsignedBigInteger('interview_id')->primary();
                $table->json('original_scores');
                $table->json('converted_scores');
                $table->unsignedInteger('original_revision');
                $table->dateTime('original_updated_at')->nullable();
                $table->dateTime('converted_at');
            });
        }
        DB::transaction(function () {
            DB::table('post_evaluation_interviews')->orderBy('id')->lockForUpdate()->get()->each(function ($row) {
                if (DB::table('post_evaluation_score_backups')->where('interview_id', $row->id)->exists()) return;
                $scores = json_decode($row->scores ?? 'null', true) ?? array_fill(0, 5, null);
                // Same legacy rubric conversion as Pasca: <=60, <=79, <=89, >89.
                $converted = array_map(fn ($value) => $value === null ? null : ($value <= 60 ? 25 : ($value <= 79 ? 50 : ($value <= 89 ? 75 : 100))), $scores);
                $choices = array_map(fn ($value) => $value === null ? null : $value > 25, $converted);
                $changed = $scores !== $converted || count(array_filter($scores, fn ($value) => $value !== null)) > 0;
                DB::table('post_evaluation_score_backups')->insert([
                    'interview_id'=>$row->id, 'original_scores'=>json_encode($scores), 'converted_scores'=>json_encode($converted),
                    'original_revision'=>$row->revision, 'original_updated_at'=>$row->updated_at, 'converted_at'=>now(),
                ]);
                DB::table('post_evaluation_interviews')->where('id', $row->id)->update([
                    'scores'=>json_encode($converted), 'continuation'=>json_encode($choices),
                    'revision'=>$row->revision + ($changed ? 1 : 0),
                ]);
            });
        });
    }

    public function down(): void
    {
        // Keep backups and confirmed answers: silently reverting assessed results would lose edits.
    }
};
