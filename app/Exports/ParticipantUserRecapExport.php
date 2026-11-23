<?php

namespace App\Exports;

use Generator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantUserRecapExport extends StringValueBinder implements FromGenerator, WithColumnWidths, WithCustomValueBinder, WithHeadings, WithStyles
{
    public function __construct(
        private Collection $participants,
        private bool $byMunicipality,
    ) {}

    public function generator(): Generator
    {
        foreach ($this->participants as $participant) {
            yield [
                $participant['name'],
                $participant['nip'] ?: '—',
                $participant['nik'] ?: '—',
                ...($this->byMunicipality ? [$participant['municipality'] ?: '—'] : []),
                $participant['institution'] ?: '—',
                $participant['trainings']->isEmpty()
                    ? 'Belum ada aktivitas tercatat'
                    : $participant['trainings']->pluck('nama_pelatihan')->implode("\n"),
            ];
        }
    }

    public function headings(): array
    {
        return [
            'Nama', 'NIP', 'NIK',
            ...($this->byMunicipality ? ['Kabupaten/Kota'] : []),
            'Nama Instansi', 'Pelatihan yang Diikuti',
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 32, 'B' => 23, 'C' => 21] + ($this->byMunicipality
            ? ['D' => 27, 'E' => 38, 'F' => 60]
            : ['D' => 38, 'E' => 60]);
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $this->byMunicipality ? 'F' : 'E';
        $lastRow = $sheet->getHighestRow();
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}{$lastRow}");
        if ($lastRow > 1) {
            $sheet->getStyle("{$lastColumn}2:{$lastColumn}{$lastRow}")->getAlignment()->setWrapText(true);
        }

        return [1 => ['font' => ['bold' => true]]];
    }
}
