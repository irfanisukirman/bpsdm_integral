<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TeacherMonitoringExport implements WithMultipleSheets
{
    public function __construct(
        private Collection $teacherMonitoring,
        private array $summary,
        private int $year,
        private int $month,
        private string $scopeLabel
    ) {}

    public function sheets(): array
    {
        return [
            new TeacherMonitoringSummarySheet(
                $this->teacherMonitoring,
                $this->summary,
                $this->year,
                $this->month,
                $this->scopeLabel
            ),
            new TeacherMonitoringDetailSheet(
                $this->teacherMonitoring,
                $this->year,
                $this->month,
                $this->scopeLabel
            ),
        ];
    }
}

class TeacherMonitoringSummarySheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function __construct(
        private Collection $teacherMonitoring,
        private array $summary,
        private int $year,
        private int $month,
        private string $scopeLabel
    ) {}

    public function title(): string
    {
        return 'Ringkasan Pengajar';
    }

    public function array(): array
    {
        $monthLabel = Carbon::create($this->year, $this->month, 1)->translatedFormat('F');
        $rows = [
            ['MONITORING PENGAJAR'],
            ['Cakupan: '.$this->scopeLabel],
            ['Periode pemantauan: '.$monthLabel.' '.$this->year],
            [
                'No', 'Nama Pengajar', 'NIP/NIK', 'Jabatan', 'Instansi',
                'Total Unit '.$monthLabel, 'JP '.$monthLabel, 'OJ '.$monthLabel, 'Sesi '.$monthLabel,
                'Total Unit Tahun '.$this->year, 'JP Tahun '.$this->year, 'OJ Tahun '.$this->year, 'Pelatihan Tahun '.$this->year,
                'Status 32 JP/OJ', 'Total Unit Seluruh Riwayat', 'JP Seluruh Riwayat', 'OJ Seluruh Riwayat', 'Pelatihan Seluruh Riwayat',
            ],
        ];

        foreach ($this->teacherMonitoring->values() as $index => $item) {
            $teacher = $item['teacher'];
            $rows[] = [
                $index + 1,
                $teacher->name,
                $teacher->nip_nik ? "'".$teacher->nip_nik : '-',
                $teacher->jabatan ?: '-',
                $teacher->instansi ?: $teacher->pengajar?->instansi ?: '-',
                $item['month_units'],
                $item['month_jp'],
                $item['month_oj'],
                $item['month_sessions'],
                $item['year_units'],
                $item['year_jp'],
                $item['year_oj'],
                $item['year_trainings'],
                $item['year_units'] >= 32 ? 'MENCAPAI / MELEBIHI 32 JP/OJ' : 'DI BAWAH 32 JP/OJ',
                $item['lifetime_units'],
                $item['lifetime_jp'],
                $item['lifetime_oj'],
                $item['lifetime_trainings'],
            ];
        }

        $rows[] = [
            '', 'TOTAL', '', '', '', $this->summary['month_units'], $this->summary['month_jp'], $this->summary['month_oj'], '',
            $this->summary['year_units'], $this->summary['year_jp'], $this->summary['year_oj'], '', $this->summary['reached_32'].' pengajar',
            $this->summary['lifetime_units'], $this->teacherMonitoring->sum('lifetime_jp'), $this->teacherMonitoring->sum('lifetime_oj'), '',
        ];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $lastRow = 5 + $this->teacherMonitoring->count();
            foreach (['A1:R1', 'A2:R2', 'A3:R3'] as $range) {
                $event->sheet->mergeCells($range);
            }
            $event->sheet->getStyle('A1:R1')->getFont()->setBold(true)->setSize(15);
            $event->sheet->getStyle('A2:R3')->getFont()->setItalic(true);
            $event->sheet->getStyle('A4:R4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $event->sheet->getStyle('A4:R4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF696CFF');
            $event->sheet->getStyle("A4:R{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle('thin');
            $event->sheet->getStyle("A{$lastRow}:R{$lastRow}")->getFont()->setBold(true);
            foreach ($this->teacherMonitoring->values() as $index => $item) {
                if ($item['year_units'] >= 32) {
                    $row = $index + 5;
                    $event->sheet->getStyle("A{$row}:R{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE1E3');
                    $event->sheet->getStyle("N{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFC62828');
                }
            }
            $event->sheet->freezePane('A5');
            $event->sheet->setAutoFilter("A4:R{$lastRow}");
        }];
    }
}

class TeacherMonitoringDetailSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    private int $detailCount = 0;

    public function __construct(
        private Collection $teacherMonitoring,
        private int $year,
        private int $month,
        private string $scopeLabel
    ) {}

    public function title(): string
    {
        return 'Rincian Sesi';
    }

    public function array(): array
    {
        $monthLabel = Carbon::create($this->year, $this->month, 1)->translatedFormat('F');
        $rows = [
            ['RINCIAN SUMBER JP PENGAJAR'],
            ['Cakupan: '.$this->scopeLabel.' | Periode: '.$monthLabel.' '.$this->year],
            [],
            [
                'No', 'Nama Pengajar', 'NIP/NIK', 'Jabatan', 'Instansi', 'Pelatihan',
                'Bidang', 'Tanggal Mengajar', 'Jam Mengajar', 'Materi/Kegiatan', 'Jumlah', 'Satuan',
                'Termasuk '.$monthLabel,
            ],
        ];

        $number = 1;
        foreach ($this->teacherMonitoring as $item) {
            $teacher = $item['teacher'];
            foreach ($item['training_breakdown'] as $breakdown) {
                foreach ($breakdown['details'] as $schedule) {
                    $scheduleMonth = (int) Carbon::parse($schedule->date)->month;
                    $rows[] = [
                        $number++,
                        $teacher->name,
                        $teacher->nip_nik ? "'".$teacher->nip_nik : '-',
                        $teacher->jabatan ?: '-',
                        $teacher->instansi ?: $teacher->pengajar?->instansi ?: '-',
                        $breakdown['training']?->nama_pelatihan ?: 'Pelatihan telah dihapus',
                        $breakdown['training']?->bidang ?: '-',
                        Carbon::parse($schedule->date)->translatedFormat('d F Y'),
                        substr($schedule->start_time, 0, 5).' - '.substr($schedule->end_time, 0, 5).' WIB',
                        $schedule->activity,
                        (int) $schedule->jp,
                        strtoupper($schedule->duration_unit ?: 'JP'),
                        $scheduleMonth === $this->month ? 'Ya' : 'Tidak',
                    ];
                }
            }
        }

        $this->detailCount = $number - 1;
        $rows[] = ['', 'TOTAL TAHUN '.$this->year, '', '', '', '', '', '', '', '', $this->teacherMonitoring->sum('year_jp'), 'JP (OJ tidak digabung)', ''];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $lastRow = 5 + $this->detailCount;
            $event->sheet->mergeCells('A1:M1');
            $event->sheet->mergeCells('A2:M2');
            $event->sheet->getStyle('A1:M1')->getFont()->setBold(true)->setSize(15);
            $event->sheet->getStyle('A2:M2')->getFont()->setItalic(true);
            $event->sheet->getStyle('A4:M4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $event->sheet->getStyle('A4:M4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF03C3EC');
            $event->sheet->getStyle("A4:M{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle('thin');
            $event->sheet->getStyle("A{$lastRow}:M{$lastRow}")->getFont()->setBold(true);
            $event->sheet->freezePane('A5');
            $event->sheet->setAutoFilter("A4:M{$lastRow}");
        }];
    }
}
