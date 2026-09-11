<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ScheduleTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Jenis Jadwal',
            'Tanggal',
            'Jam Mulai',
            'Jam Selesai',
            'Materi / Kegiatan',
            'Jumlah',
            'Satuan (JP/OJ)',
            'Link Zoom',
            'Tenaga Pengajar / Fasilitator',
            'Penanggung Jawab (PIC)'
        ];
    }

    public function array(): array
    {
        // Berikan 2 baris contoh pengisian
        return [
            [
                'Pembelajaran',
                '2026-09-01',
                '08:00',
                '09:30',
                'Pengantar Transformasi Digital',
                '2',
                'JP',
                '',
                'Budi Santoso', // Bisa diisi Nama atau NIP Pengajar yang terdaftar
                'Panitia BPSDM'
            ],
            [
                'Istirahat',
                '2026-09-01',
                '09:30',
                '09:45',
                'Coffee Break',
                '',
                '',
                '',
                '',
                ''
            ],
            [
                'Pembelajaran',
                '2026-09-01',
                '09:45',
                '11:45',
                'Praktik Implementasi SPBE',
                '2',
                'OJ',
                '',
                '',
                'Panitia BPSDM'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4F46E5'] // Warna Indigo/Primary
                ]
            ],
        ];
    }
}
