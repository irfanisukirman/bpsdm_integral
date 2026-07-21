<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MonitoringIndicatorImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Validasi: Jika kolom indikator kosong, lewati
        if (!isset($row['indikator_pertanyaan'])) {
            return null;
        }

        return new Question([
            'category'      => $row['kategori'], // Contoh: Monitoring Penyelenggara
            'metode'        => strtolower($row['metode']), // klasikal / blended / full learning
            'question_text' => $row['indikator_pertanyaan'],
            'type'          => 'ya_tidak', // Default untuk monitoring
            'training_type' => 'Semua',    // Default
        ]);
    }
}
