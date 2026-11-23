<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

abstract class LevelOneEvaluationQuestionSeeder extends Seeder
{
    protected function replaceQuestions(string $category, array $questions, int $sliderCount): void
    {
        DB::transaction(function () use ($category, $questions, $sliderCount) {
            $ids = [];
            foreach ($questions as $index => $text) {
                $question = Question::updateOrCreate([
                    'category' => $category,
                    'program_evaluasi' => 'semua',
                    'bidang' => 'Semua Bidang',
                    'training_id' => null,
                    'metode' => 'semua',
                    'question_text' => $text,
                ], [
                    'training_type' => 'Semua Bidang',
                    'type' => $index < $sliderCount ? 'slider' : 'text',
                    'source_order' => $index + 1,
                    'is_active' => true,
                ]);
                $ids[] = $question->id;
            }

            Question::where('category', $category)->where('is_active', true)
                ->whereNotIn('id', $ids)->update(['is_active' => false]);
        });
    }
}
