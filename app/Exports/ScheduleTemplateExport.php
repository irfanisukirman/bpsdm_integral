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
            'Tanggal',
            'Jam Mulai',
            'Jam Selesai',
            'Materi / Kegiatan',
            'JP',
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
                '2026-09-01',
                '08:00',
                '09:30',
                'Pengantar Transformasi Digital',
                '2',
                'https://zoom.us/j/123456789',
                'Budi Santoso', // Bisa diisi Nama atau NIP Pengajar yang terdaftar
                'Panitia BPSDM'
            ],
            [
                '2026-09-01',
                '09:45',
                '11:15',
                'Praktik Implementasi SPBE',
                '2',
                '',
                '', // Boleh dikosongkan jika tanpa pengajar khusus
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
