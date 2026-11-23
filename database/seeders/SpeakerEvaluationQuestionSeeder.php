<?php

namespace Database\Seeders;

class SpeakerEvaluationQuestionSeeder extends LevelOneEvaluationQuestionSeeder
{
    public function run(): void
    {
        $this->replaceQuestions('l1_narasumber', [
            'Sistematika Penyajian?',
            'Kemampuan Menyajikan',
            'Ketepatan Waktu & Kehadiran',
            'Penggunaan Metode & Sarana Diklat',
            'Sikap & Perilaku',
            'Cara Menjawab Pertanyaan dari Peserta',
            'Penggunaan Bahasa',
            'Pemberian Motivasi kepada Peserta',
            'Kerapian Berpakaian',
            'Kerja Sama antar Widyaiswara (dalam Tim)',
            'Catatan dan Saran Peserta',
        ], 10);
    }
}
