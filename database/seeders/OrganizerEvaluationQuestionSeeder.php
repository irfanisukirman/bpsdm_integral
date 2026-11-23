<?php

namespace Database\Seeders;

class OrganizerEvaluationQuestionSeeder extends LevelOneEvaluationQuestionSeeder
{
    public function run(): void
    {
        $this->replaceQuestions('l1_penyelenggara', [
            'Bagaimana ketercapaian harapan Anda (ekspektasi) terhadap pelatihan ini?',
            'Bagaimana penyelenggaraan dan waktu yang disediakan untuk pelaksanaan pelatihan ini?',
            'Bagaimana tentang penyelenggaraan pelatihan ini secara keseluruhan?',
            'Bagaimana tentang sajian materi dalam pelatihan (membantu tugas-tugas)?',
            'Bagaimana kepuasan yang dirasakan terhadap manfaat materi pelatihan yang sudah diberikan?',
            'Sebutkan materi pelatihan yang sangat mendukung tugas Anda',
            '(Materi yang dibahas) Sebutkan materi pelatihan yang kurang mendukung tugas Anda',
            'Menurut pendapat Anda materi apa saja yang perlu ditambahkan dalam pelatihan ini?',
            '(Materi yang dibahas) Menurut pendapat Anda materi apa saja yang perlu dihapuskan dalam pelatihan ini?',
            'Saran-saran untuk peningkatan pelatihan yang akan datang?',
        ], 5);
    }
}
