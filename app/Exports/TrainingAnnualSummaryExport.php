<?php

namespace App\Exports;

use Carbon\Carbon;
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
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TrainingAnnualSummaryExport extends DefaultValueBinder implements FromQuery, WithColumnFormatting, WithColumnWidths, WithCustomValueBinder, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles
{
    public function __construct(private Builder $trainings, private string $today) {}

    public function query(): Builder
    {
        return $this->trainings;
    }

    public function headings(): array
    {
        return [
            'Nama Pelatihan', 'Tanggal Mulai', 'Tanggal Selesai', 'Metode Pelatihan',
            'Angkatan', 'Total JP', 'Jumlah Peserta', 'Status',
        ];
    }

    public function map($training): array
    {
        $start = Carbon::parse($training->tgl_mulai, 'Asia/Jakarta');
        $end = Carbon::parse($training->tgl_selesai, 'Asia/Jakarta');
        $status = match (true) {
            $this->today < $start->toDateString() => 'Belum selesai',
            $this->today > $end->toDateString() => 'Selesai',
            default => 'Berlangsung',
        };

        return [
            $training->nama_pelatihan,
            Date::dateTimeToExcel($start),
            Date::dateTimeToExcel($end),
            ucfirst($training->metode),
            $training->angkatan,
            (int) $training->jp,
            (int) $training->approved_participants_count,
            $status,
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
        return ['B' => 'dd/mm/yyyy', 'C' => 'dd/mm/yyyy'];
    }

    public function columnWidths(): array
    {
        return ['A' => 55, 'B' => 17, 'C' => 17, 'D' => 23, 'E' => 14, 'F' => 12, 'G' => 18, 'H' => 18];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:H'.$sheet->getHighestRow());

        return [1 => ['font' => ['bold' => true]]];
    }
}
