<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionImport implements ToModel, WithHeadingRow
{
    public function __construct(private readonly string $defaultBidang)
    {
    }

    public function model(array $row)
    {
        $questionText = trim((string) ($row['pertanyaan'] ?? ''));
        if ($questionText === '') {
            return null;
        }

        $categoryMap = [
            'mandiri' => 'l34_mandiri',
            'alumni' => 'l34_mandiri',
            'peserta' => 'l34_mandiri',
            'rekan' => 'l34_rekan',
            'rekan kerja' => 'l34_rekan',
            'atasan' => 'l34_atasan',
            'atasan langsung' => 'l34_atasan',
            'penyelenggara' => 'l1_penyelenggara',
            'narasumber' => 'l1_narasumber',
        ];
        $rawCategory = mb_strtolower(trim((string) ($row['level_peran'] ?? '')));
        if (!isset($categoryMap[$rawCategory])) {
            throw new \InvalidArgumentException('Level/peran wajib Penyelenggara, Narasumber, Mandiri/Alumni, Atasan, atau Rekan.');
        }
        $category = $categoryMap[$rawCategory];
        $isLevel34 = str_starts_with($category, 'l34_');

        $type = mb_strtolower(trim((string) ($row['tipe_jawaban'] ?? 'slider')));
        if (!in_array($type, ['slider', 'dropdown', 'checkbox', 'text'], true)) {
            throw new \InvalidArgumentException('Tipe jawaban wajib slider, dropdown, checkbox, atau text.');
        }

        $options = null;
        if (filled($row['pilihan_jawaban'] ?? null)) {
            $options = array_values(array_filter(array_map('trim', explode(',', (string) $row['pilihan_jawaban']))));
        }

        $programInput = strtoupper(trim((string) ($row['program_evaluasi'] ?? 'PKTI/PKTU')));
        $program = $programInput === 'SEMUA' ? 'semua' : $programInput;
        if (!in_array($program, ['semua', 'CPNS', 'PKP', 'PKA', 'PKN', 'PKTI/PKTU'], true)) {
            throw new \InvalidArgumentException('Program evaluasi wajib semua, CPNS, PKP, PKA, PKN, atau PKTI/PKTU.');
        }

        $metode = mb_strtolower(trim((string) ($row['metode'] ?? 'semua')));
        if (!$isLevel34 && !in_array($metode, ['semua', 'klasikal', 'full learning', 'blended'], true)) {
            throw new \InvalidArgumentException('Metode Level 1 wajib semua, klasikal, full learning, atau blended.');
        }
        if ($isLevel34) {
            $metode = 'semua';
        }

        $subCategory = null;
        if ($isLevel34) {
            $subCategory = trim((string) ($row['sub_kategori'] ?? ''));
            $aliases = [
                'Perubahan Sikap Prilaku' => 'Perubahan Sikap Perilaku',
                'Data Diri Peserta' => 'Data Diri Alumni',
            ];
            $subCategory = $aliases[$subCategory] ?? $subCategory;
            if (!in_array($subCategory, Question::l34SubCategoryOptions(), true)) {
                throw new \InvalidArgumentException('Sub kategori L3/L4 tidak dikenali: '.($subCategory ?: '(kosong)').'. Gunakan pilihan pada template terbaru.');
            }
        }

        return new Question([
            'training_type' => $this->defaultBidang,
            'bidang' => $this->defaultBidang,
            'program_evaluasi' => $isLevel34 ? $program : 'PKTI/PKTU',
            'metode' => $metode,
            'category' => $category,
            'sub_category' => $subCategory,
            'type' => $type,
            'question_text' => $questionText,
            'options' => $options,
        ]);
    }
}