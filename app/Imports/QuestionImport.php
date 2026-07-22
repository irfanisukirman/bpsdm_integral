<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Mapping Kategori agar user bisa ngetik "Mandiri" saja di Excel
        $categoryMap = [
            'mandiri' => 'l34_mandiri',
            'rekan'   => 'l34_rekan',
            'atasan'  => 'l34_atasan',
            'penyelenggara' => 'l1_penyelenggara',
            'narasumber'    => 'l1_narasumber',
        ];

        $rawCategory = strtolower($row['level_peran']);
        $category = $categoryMap[$rawCategory] ?? $rawCategory;

        // Proses pilihan jawaban (jika ada)
        $options = null;
        if (!empty($row['pilihan_jawaban'])) {
            // Pecah string koma menjadi array
            $options = array_map('trim', explode(',', $row['pilihan_jawaban']));
        }

        return new Question([
            'training_type' => $row['jenis_pelatihan'], // PKTI, CPNS, dll
            'category'      => $category,
            'type'          => strtolower($row['tipe_jawaban']), // slider, dropdown, text
            'question_text' => $row['pertanyaan'],
            'options'       => $options,
        ]);
    }
}
