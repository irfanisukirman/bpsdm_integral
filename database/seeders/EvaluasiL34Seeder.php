<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EvaluasiL34Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categories = ['l34_mandiri', 'l34_atasan', 'l34_rekan'];
        foreach ($categories as $cat) {
            // Seksi: Perubahan Perilaku
            $questions = [
                ['text' => 'Ybs memahami bahwa sumber daya yang diperlukan untuk implementasi materi pembelajaran di lingkungan kerja tersedia secara memadai', 'type' => 'slider', 'sub' => 'Perubahan Perilaku'],
                ['text' => 'Materi pelatihan sangat bermanfaat dalam menunjang pekerjaan Ybs', 'type' => 'slider', 'sub' => 'Perubahan Perilaku'],
                ['text' => 'Keberhasilan pelaksanaan pekerjaan Ybs meningkat setelah mengikuti pelatihan', 'type' => 'slider', 'sub' => 'Perubahan Perilaku'],
                ['text' => 'Kualitas dan kecepatan penyelesaian pekerjaan Ybs meningkat setelah mengikuti pelatihan', 'type' => 'slider', 'sub' => 'Perubahan Perilaku'],
                ['text' => 'Ybs berkomitmen untuk implementasi materi pelatihan di lingkungan kerja saat ini', 'type' => 'slider', 'sub' => 'Perubahan Perilaku'],
                
                // Seksi: Dampak Pelatihan
                ['text' => 'Dampak pelatihan terhadap unit kerja', 'type' => 'slider', 'sub' => 'Dampak Pelatihan'],
                ['text' => 'Dampak pelatihan terhadap pengetahuan teoritis atau konsep Ybs', 'type' => 'slider', 'sub' => 'Dampak Pelatihan'],
                ['text' => 'Dampak pelatihan terhadap peningkatan produktivitas', 'type' => 'slider', 'sub' => 'Dampak Pelatihan'],
                ['text' => 'Dampak pelatihan terhadap perbaikan kualitas hasil kerja', 'type' => 'slider', 'sub' => 'Dampak Pelatihan'],
                ['text' => 'Dampak pelatihan terhadap peningkatan kepuasan pelanggan', 'type' => 'slider', 'sub' => 'Dampak Pelatihan'],
                ['text' => 'Dampak pelatihan terhadap penguatan hubungan antara rekan-rekan kerja', 'type' => 'slider', 'sub' => 'Dampak Pelatihan'],
            ];

            foreach ($questions as $q) {
                \App\Models\Question::updateOrCreate(
                    ['question_text' => $q['text'], 'category' => $cat],
                    ['type' => $q['type'], 'training_type' => 'Semua', 'sub_category' => $q['sub']]
                );
            }
        }
    }
}
