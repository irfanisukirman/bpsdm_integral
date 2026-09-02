<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class CertificationSummarySheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    public function __construct(private Collection $types, private int $year) {}

    public function title(): string { return 'Ringkasan'; }

    public function array(): array
    {
        $rows = [
            ['REKAPITULASI SERTIFIKASI TAHUN '.$this->year],
            [],
            ['No', 'Jenis Sertifikasi', 'Pelaksanaan', 'Peserta', 'Laki-laki', 'Perempuan', 'Lulus', 'Tidak Lulus', 'Belum Ditentukan', 'Persentase Kelulusan'],
        ];

        foreach ($this->types as $index => $type) {
            $participants = $type->events->flatMap->participants;
            $graduates = $participants->where('result', 'lulus')->count();
            $decided = $participants->whereIn('result', ['lulus', 'tidak_lulus'])->count();
            $rows[] = [
                $index + 1,
                $type->name,
                $type->events->count(),
                $participants->count(),
                $participants->whereIn('gender', ['Laki-laki', 'Laki-Laki', 'L', 'Pria'])->count(),
                $participants->whereIn('gender', ['Perempuan', 'P', 'Wanita'])->count(),
                $graduates,
                $participants->where('result', 'tidak_lulus')->count(),
                $participants->where('result', 'belum_ditentukan')->count(),
                $decided > 0 ? round(($graduates / $decided) * 100, 2).'%' : '0%',
            ];
        }

        $all = $this->types->flatMap(fn ($type) => $type->events->flatMap->participants);
        $graduates = $all->where('result', 'lulus')->count();
        $decided = $all->whereIn('result', ['lulus', 'tidak_lulus'])->count();
        $rows[] = [
            '', 'TOTAL', $this->types->sum(fn ($type) => $type->events->count()), $all->count(),
            $all->whereIn('gender', ['Laki-laki', 'Laki-Laki', 'L', 'Pria'])->count(),
            $all->whereIn('gender', ['Perempuan', 'P', 'Wanita'])->count(), $graduates,
            $all->where('result', 'tidak_lulus')->count(), $all->where('result', 'belum_ditentukan')->count(),
            $decided > 0 ? round(($graduates / $decided) * 100, 2).'%' : '0%',
        ];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $last = 4 + $this->types->count();
            $event->sheet->mergeCells('A1:J1');
            $event->sheet->getStyle('A1:J1')->getFont()->setBold(true)->setSize(14);
            $event->sheet->getStyle('A3:J3')->getFont()->setBold(true);
            $event->sheet->getStyle("A3:J{$last}")->getBorders()->getAllBorders()->setBorderStyle('thin');
            $event->sheet->getStyle("A{$last}:J{$last}")->getFont()->setBold(true);
            $event->sheet->freezePane('A4');
            $event->sheet->setAutoFilter("A3:J{$last}");
        }];
    }
}