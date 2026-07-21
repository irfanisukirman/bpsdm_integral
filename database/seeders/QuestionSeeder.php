<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // MONITORING (Modul 05 & 10)
            ['category' => 'monitoring', 'type' => 'ya_tidak', 'metode' => 'klasikal', 'question_text' => 'Apakah ruang kelas dalam kondisi bersih dan nyaman?'],
            ['category' => 'monitoring', 'type' => 'ya_tidak', 'metode' => 'blended', 'question_text' => 'Apakah LMS dapat diakses dengan lancar oleh peserta?'],

            // LEVEL 1: NARASUMBER (Modul 08)
            ['category' => 'l1_narasumber', 'type' => 'slider', 'metode' => 'semua', 'question_text' => 'Penguasaan materi oleh narasumber'],
            ['category' => 'l1_narasumber', 'type' => 'slider', 'metode' => 'semua', 'question_text' => 'Kemampuan narasumber dalam menjawab pertanyaan'],

            // LEVEL 1: PENYELENGGARA (Modul 08)
            ['category' => 'l1_penyelenggara', 'type' => 'slider', 'metode' => 'semua', 'question_text' => 'Kualitas konsumsi yang disediakan'],
            ['category' => 'l1_penyelenggara', 'type' => 'slider', 'metode' => 'semua', 'question_text' => 'Keramahan panitia penyelenggara'],

            // LEVEL 3 & 4: 360 (Modul 11)
            ['category' => 'l3_mandiri', 'type' => 'slider', 'metode' => 'semua', 'question_text' => 'Sejauh mana Anda menerapkan ilmu pelatihan dalam pekerjaan sehari-hari?'],
            ['category' => 'l3_atasan_rekan', 'type' => 'slider', 'metode' => 'semua', 'question_text' => 'Apakah terdapat perubahan perilaku kerja alumni setelah mengikuti pelatihan?'],
        ];

        foreach ($data as $item) {
            Question::create($item);
        }
    }
}
