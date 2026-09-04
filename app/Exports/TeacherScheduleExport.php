<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TeacherScheduleExport implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function __construct(private Collection $schedules) {}

    public function title(): string
    {
        return 'Jadwal Pengajar';
    }

    public function array(): array
    {
        $rows = [[
            'No', 'Nama WI / Pengajar', 'NIP/NIK', 'Tanggal', 'Jam Mulai', 'Jam Selesai',
            'Materi Ajar', 'Pelatihan', 'Bidang Penyelenggara', 'Lokasi / Media', 'Jumlah', 'Satuan', 'Status', 'Bentrok'
        ]];

        foreach ($this->schedules->values() as $index => $schedule) {
            $location = $schedule->external_place ?: ($schedule->venue_type ?: ($schedule->link_zoom ? 'Daring / Zoom' : '-'));
            $rows[] = [
                $index + 1,
                $schedule->pengajar?->name ?: '-',
                $schedule->pengajar?->nip_nik ? "'".$schedule->pengajar->nip_nik : '-',
                \Carbon\Carbon::parse($schedule->date)->format('d/m/Y'),
                substr((string) $schedule->start_time, 0, 5),
                substr((string) $schedule->end_time, 0, 5),
                $schedule->activity ?: '-',
                $schedule->training?->nama_pelatihan ?: '-',
                $schedule->training?->bidang ?: '-',
                $location,
                $schedule->jp ?? 0,
                strtoupper($schedule->duration_unit ?: 'JP'),
                match ($schedule->monitoring_status) { 'ongoing' => 'Sedang Berlangsung', 'finished' => 'Selesai', default => 'Akan Datang' },
                $schedule->has_conflict ? 'YA' : 'TIDAK',
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $lastRow = max(1, $this->schedules->count() + 1);
            $event->sheet->getStyle('A1:N1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $event->sheet->getStyle('A1:N1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF696CFF');
            $event->sheet->getStyle("A1:N{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle('thin');
            $event->sheet->freezePane('A2');
            $event->sheet->setAutoFilter("A1:N{$lastRow}");
            foreach ($this->schedules->values() as $index => $schedule) {
                if ($schedule->has_conflict) {
                    $row = $index + 2;
                    $event->sheet->getStyle("A{$row}:N{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE1E3');
                }
            }
        }];
    }
}
