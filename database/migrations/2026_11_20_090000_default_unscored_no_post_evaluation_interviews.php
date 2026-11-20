<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $interviews = DB::table('post_evaluation_interviews');
        (clone $interviews)->whereNotNull('continuation')->select('id')->chunkById(200, function ($batch) use ($interviews) {
            DB::transaction(function () use ($batch, $interviews) {
                $rows = (clone $interviews)->whereIn('id', $batch->pluck('id'))
                    ->orderBy('id')->lockForUpdate()->get(['id', 'continuation', 'scores']);

                foreach ($rows as $row) {
                    $choices = json_decode($row->continuation ?? 'null', true) ?? [];
                    $scores = json_decode($row->scores ?? 'null', true) ?? array_fill(0, 5, null);
                    $original = $scores;

                    for ($i = 0; $i < 5; $i++) {
                        if (($choices[$i] ?? null) === false && ($scores[$i] ?? null) === null) {
                            $scores[$i] = 25;
                        }
                    }

                    if ($scores !== $original) {
                        (clone $interviews)->where('id', $row->id)->increment('revision', 1, [
                            'scores' => json_encode($scores),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
        });
    }

    public function down(): void
    {
        // Evaluators may have confirmed or changed the backfilled scores.
    }
};
