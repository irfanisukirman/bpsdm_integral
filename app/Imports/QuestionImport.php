<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. Mapping Kategori (Level & Peran)
        $categoryMap = [
            'mandiri'       => 'l34_mandiri',
            'alumni'        => 'l34_mandiri',
            'rekan'         => 'l34_rekan',
            'atasan'        => 'l34_atasan',
            'penyelenggara' => 'l1_penyelenggara',
            'narasumber'    => 'l1_narasumber',
        ];

        $rawCategory = strtolower($row['level_peran']);
        $category = $categoryMap[$rawCategory] ?? $rawCategory;

        // 2. Mapping Sub Kategori (Grup Pertanyaan)
        // Jika di excel kosong, beri default 'Perubahan Perilaku'
        $subCategory = $row['sub_kategori'] ?? 'Perubahan Perilaku';

        // 3. Proses pilihan jawaban jika tipe dropdown
        $options = null;
        if (!empty($row['pilihan_jawaban'])) {
            $options = array_map('trim', explode(',', $row['pilihan_jawaban']));
        }

        return new Question([
            'training_type' => $row['jenis_pelatihan'] ?? 'Semua',
            'category'      => $category,
            'sub_category'  => $subCategory, // KOLOM BARU: Untuk grouping di form
            'type'          => strtolower($row['tipe_jawaban']), // slider, dropdown, text
            'question_text' => $row['pertanyaan'],
            'options'       => $options,
        ]);
    }
}
