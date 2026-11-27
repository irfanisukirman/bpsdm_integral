<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionImport implements ToModel, WithHeadingRow
{
    public const HEADINGS = ['bidang', 'metode', 'program_evaluasi', 'level_peran', 'sub_kategori', 'tipe_jawaban', 'pertanyaan', 'pilihan_jawaban'];

    public function __construct(private readonly ?string $defaultBidang, private readonly array $allowedBidang = [])
    {
    }

    public function model(array $row)
    {
        $questionText = trim((string) ($row['pertanyaan'] ?? ''));
        if ($questionText === '') {
            return null;
        }
        $bidang = $this->defaultBidang ?? trim((string) ($row['bidang'] ?? ''));
        if ($this->defaultBidang === null && !in_array($bidang, $this->allowedBidang, true)) {
            throw new \InvalidArgumentException('Bidang pada file Excel tidak tersedia: '.($bidang ?: '(kosong)').'.');
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
        if (in_array($type, ['dropdown', 'checkbox'], true)) {
            $rawOptions = $row['pilihan_jawaban'] ?? null;
            if (blank($rawOptions)) {
                $rawOptions = $row['jawaban'] ?? '';
            }
            $decoded = json_decode((string) $rawOptions, true);
            $options = is_array($decoded) && array_is_list($decoded) && collect($decoded)->every(fn ($option) => is_string($option))
                ? $decoded
                : array_values(array_filter(
                    array_map('trim', preg_split('/[|,;\r\n]+/u', (string) $rawOptions)),
                    fn ($option) => $option !== '' && $option !== '-'
                ));
            if ($options === []) {
                throw new \InvalidArgumentException('Pertanyaan "'.mb_substr($questionText, 0, 80).'" bertipe '.$type.' wajib memiliki pilihan pada kolom pilihan_jawaban atau jawaban.');
            }
        }

        $programInput = strtoupper(trim((string) ($row['program_evaluasi'] ?? 'Teknis')));
        $program = \App\Support\EvaluationProgram::normalize($programInput);
        if (!in_array($program, ['semua', 'LATSAR', 'PKP', 'PKA', 'PKN', 'Teknis', 'Manajerial'], true)) {
            throw new \InvalidArgumentException('Program evaluasi wajib semua, LATSAR, PKP, PKA, PKN, Manajerial, atau Teknis.');
        }

        if ($category === 'l34_rekan' && \App\Support\EvaluationProgram::isManagerial($program)) {
            throw new \InvalidArgumentException('LATSAR, PKP, PKA, dan PKN hanya menggunakan penilaian mandiri dan atasan.');
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
            'training_type' => $bidang,
            'bidang' => $bidang,
            'program_evaluasi' => $program,
            'metode' => $metode,
            'category' => $category,
            'sub_category' => $subCategory,
            'type' => $type,
            'question_text' => $questionText,
            'options' => $options,
        ]);
    }
}
