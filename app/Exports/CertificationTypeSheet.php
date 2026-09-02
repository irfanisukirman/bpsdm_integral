<?php

namespace App\Exports;

use App\Models\CertificationType;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class CertificationTypeSheet implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    private int $headerRow = 9;

    public function __construct(private CertificationType $type, private int $year) {}

    public function title(): string
    {
$name = preg_replace('~[\\/?*\[\]:]~', '-', $this->type->name);
        return Str::limit($this->type->id.' '.$name, 31, '');
    }

    public function array(): array
    {
        $participants = $this->type->events->flatMap->participants;
        $rows = [
            ['DATA PESERTA SERTIFIKASI'],
            ['Tahun', $this->year],
            ['Jenis Sertifikasi', $this->type->name],
            ['Jumlah Pelaksanaan', $this->type->events->count()],
            ['Jumlah Peserta', $participants->count()],
            ['Jumlah Lulusan', $participants->where('result', 'lulus')->count()],
            ['Jumlah Tidak Lulus', $participants->where('result', 'tidak_lulus')->count()],
            ['Belum Ditentukan', $participants->where('result', 'belum_ditentukan')->count()],
            ['No', 'Nama Pelaksanaan', 'Tanggal Mulai', 'Tanggal Selesai', 'Lokasi', 'NIP/NIK', 'Nama Peserta', 'Jenis Kelamin', 'Jabatan', 'Instansi', 'Provinsi', 'Kabupaten/Kota', 'Nomor HP', 'Email', 'Status', 'Catatan'],
        ];

        $number = 1;
        foreach ($this->type->events as $event) {
            foreach ($event->participants as $participant) {
                $rows[] = [
                    $number++, $event->title, $event->start_date?->format('d-m-Y'), $event->end_date?->format('d-m-Y'),
                    $event->location, "'".$participant->nip_nik, $participant->name, $participant->gender, $participant->position,
                    $participant->institution, $participant->province, $participant->city, $participant->phone,
                    $participant->email, match ($participant->result) {
                        'lulus' => 'Lulus', 'tidak_lulus' => 'Tidak Lulus', default => 'Belum Ditentukan'
                    }, $participant->notes,
                ];
            }
        }

        if ($number === 1) $rows[] = ['', 'Belum ada data peserta pada tahun '.$this->year];
        return $rows;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $last = max($this->headerRow + 1, $this->headerRow + $this->type->events->sum(fn ($item) => $item->participants->count()));
            $event->sheet->mergeCells('A1:P1');
            $event->sheet->getStyle('A1:P1')->getFont()->setBold(true)->setSize(14);
            $event->sheet->getStyle('A2:B8')->getBorders()->getAllBorders()->setBorderStyle('thin');
            $event->sheet->getStyle('A2:A8')->getFont()->setBold(true);
            $event->sheet->getStyle("A{$this->headerRow}:P{$this->headerRow}")->getFont()->setBold(true);
            $event->sheet->getStyle("A{$this->headerRow}:P{$last}")->getBorders()->getAllBorders()->setBorderStyle('thin');
            $event->sheet->freezePane('A10');
            $event->sheet->setAutoFilter("A{$this->headerRow}:P{$last}");
        }];
    }
}