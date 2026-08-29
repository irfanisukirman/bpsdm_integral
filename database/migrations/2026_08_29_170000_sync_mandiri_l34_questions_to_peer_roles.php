<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sharedSections = [
            'Penempatan Tugas dan Transfer Learning',
            'Perubahan Perilaku',
            'Dampak Pelatihan',
        ];

        $sources = DB::table('evaluation_questions')
            ->where('category', 'l34_mandiri')
            ->whereIn('sub_category', $sharedSections)
            ->orderBy('id')
            ->get();

        foreach (['l34_rekan', 'l34_atasan'] as $targetCategory) {
            foreach ($sources as $source) {
                $exists = DB::table('evaluation_questions')
                    ->where('bidang', $source->bidang)
                    ->where('category', $targetCategory)
                    ->where('sub_category', $source->sub_category)
                    ->where('metode', $source->metode)
                    ->where('type', $source->type)
                    ->where('question_text', $source->question_text)
                    ->where('options', $source->options)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('evaluation_questions')->insert([
                    'training_type' => $source->bidang,
                    'bidang' => $source->bidang,
                    'training_id' => null,
                    'category' => $targetCategory,
                    'metode' => $source->metode,
                    'sub_category' => $source->sub_category,
                    'question_text' => $source->question_text,
                    'type' => $source->type,
                    'options' => $source->options,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Tidak menghapus pertanyaan agar jawaban evaluasi yang mungkin sudah masuk tetap aman.
    }
};
