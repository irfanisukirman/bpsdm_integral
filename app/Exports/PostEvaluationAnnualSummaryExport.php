<?php

namespace App\Exports;

use App\Models\User;
use App\Services\PostEvaluationService;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PostEvaluationAnnualSummaryExport extends DefaultValueBinder implements FromQuery, WithColumnFormatting, WithColumnWidths, WithCustomValueBinder, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles
{
    public function __construct(
        private Builder $trainings,
        private PostEvaluationService $service,
        private User $user,
    ) {}

    public function query(): Builder
    {
        return $this->trainings;
    }

    public function headings(): array
    {
        return [
            'Nama Pelatihan', 'Program', 'Bidang', 'Angkatan', 'Jumlah Alumni',
            'Rerata Survei Alumni', 'Alumni dengan Nilai Survei',
            'Rerata Survei Atasan', 'Alumni dengan Nilai Survei Atasan',
            'Rerata Wawancara', 'Alumni dengan Nilai Wawancara', 'Wawancara Selesai',
        ];
    }

    public function map($training): array
    {
        $row = $this->service->annualSummaryRow($training, $this->user);
        $survey = $row['survey'];
        $supervisorSurvey = $row['supervisor_survey'];
        $interview = $row['interview'];
        $hasInterview = $interview['average'] !== null;

        return [
            $training->nama_pelatihan, $training->program_evaluasi, $training->bidang, $training->angkatan,
            $row['total'], $survey['average'] !== null ? $survey['average'] / 100 : null, $survey['valid'],
            $supervisorSurvey['average'] !== null ? $supervisorSurvey['average'] / 100 : null, $supervisorSurvey['valid'],
            $hasInterview ? $interview['average'] / 100 : null,
            $hasInterview ? $interview['valid'] : null,
            $hasInterview ? $row['completed'] : null,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return ['F' => '0.00%', 'H' => '0.00%', 'J' => '0.00%'];
    }

    public function columnWidths(): array
    {
        return ['A' => 52, 'B' => 15, 'C' => 42, 'D' => 13, 'E' => 18, 'F' => 23, 'G' => 29,
            'H' => 23, 'I' => 36, 'J' => 22, 'K' => 33, 'L' => 22];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:L'.$sheet->getHighestRow());

        return [1 => ['font' => ['bold' => true]]];
    }
}
