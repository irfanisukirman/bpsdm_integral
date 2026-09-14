<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InternshipProgramRecapExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly Collection $participants,
        private readonly string $from,
        private readonly string $to
    ) {}

    public function headings(): array
    {
        return ['Nama','NIS/NIM','Sekolah/Kampus','Penempatan','Periode Rekap','Total Kehadiran','Hari Terlambat','Terlambat (Menit)','Izin','Sakit','Belum Pulang'];
    }

    public function collection(): Collection
    {
        return $this->participants->map(fn($participant)=>[
            $participant->name,
            $participant->student_number,
            $participant->institution,
            $participant->placement_unit,
            date('d/m/Y',strtotime($this->from)).' s.d. '.date('d/m/Y',strtotime($this->to)),
            $participant->recap_present_count,
            $participant->recap_late_count,
            (int)$participant->recap_late_minutes,
            $participant->recap_permission_count,
            $participant->recap_sick_count,
            $participant->recap_missing_checkout_count,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:K1');
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('DCEEF7');
        return [];
    }
}
