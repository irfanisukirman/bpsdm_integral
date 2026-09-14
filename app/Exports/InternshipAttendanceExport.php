<?php

namespace App\Exports;

use App\Models\InternshipParticipant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InternshipAttendanceExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly InternshipParticipant $participant,
        private readonly Collection $attendances
    ) {}

    public function headings(): array
    {
        return [
            'Tanggal', 'Hari', 'Status', 'Jam Masuk', 'Jam Pulang',
            'Terlambat (Menit)', 'Status Verifikasi', 'Keterangan',
        ];
    }

    public function collection(): Collection
    {
        return $this->attendances->map(fn ($attendance) => [
            $attendance->attendance_date->format('d/m/Y'),
            $attendance->attendance_date->translatedFormat('l'),
            match ($attendance->status) {
                'present' => 'Hadir',
                'permission' => 'Izin',
                'sick' => 'Sakit',
                default => 'Tidak Hadir',
            },
            $attendance->check_in_at?->format('H:i') ?? '-',
            $attendance->check_out_at?->format('H:i') ?? '-',
            $attendance->late_minutes,
            match ($attendance->review_status) {
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'pending' => 'Menunggu',
                default => '-',
            },
            $attendance->note ?? '-',
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->setTitle('Rekap Presensi');
        $sheet->freezePane('A2');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('DCEEF7');
        $sheet->setAutoFilter('A1:H1');

        return [];
    }
}
