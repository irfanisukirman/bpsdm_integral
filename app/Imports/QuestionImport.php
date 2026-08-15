<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // 1. VALIDASI: Jika baris 'pertanyaan' kosong, abaikan baris ini (skip)
        // Ini mencegah error jika ada baris kosong di bawah data Excel Anda
        if (!isset($row['pertanyaan']) || empty(trim($row['pertanyaan']))) {
            return null;
        }

        // 2. Mapping Kategori (Level & Peran)
        // Pastikan di Excel kolom 'level_peran' diisi: Mandiri, Atasan, atau Rekan
        $categoryMap = [
            'mandiri'       => 'l34_mandiri',
            'alumni'        => 'l34_mandiri',
            'rekan'         => 'l34_rekan',
            'atasan'        => 'l34_atasan',
            'penyelenggara' => 'l1_penyelenggara',
            'narasumber'    => 'l1_narasumber',
        ];

        $rawCategory = isset($row['level_peran']) ? strtolower(trim($row['level_peran'])) : '';
        $category = $categoryMap[$rawCategory] ?? 'l34_mandiri'; // Default ke mandiri jika tidak cocok

        // 3. Mapping Tipe Jawaban
        // Pastikan di Excel kolom 'tipe_jawaban' diisi: slider, dropdown, atau text
        $type = isset($row['tipe_jawaban']) ? strtolower(trim($row['tipe_jawaban'])) : 'slider';

        // 4. Proses pilihan jawaban (jika tipe dropdown)
        $options = null;
        if (!empty($row['pilihan_jawaban'])) {
            $options = array_map('trim', explode(',', $row['pilihan_jawaban']));
        }

        // 5. Simpan ke Database
        return new Question([
            'training_type' => $row['jenis_pelatihan'] ?? 'Semua',
            'category'      => $category,
            'sub_category'  => $row['sub_kategori'] ?? 'Perubahan Perilaku',
            'type'          => $type,
            'question_text' => trim($row['pertanyaan']),
            'options'       => $options,
        ]);
    }
}