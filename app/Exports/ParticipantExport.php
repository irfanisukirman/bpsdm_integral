<?php

namespace App\Exports;

use App\Models\Participant;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $trainingId;

    public function __construct($trainingId) {
        $this->trainingId = $trainingId;
    }

    /**
     * Header tabel Excel
     */
    public function headings(): array {
        return [
            'NIP / NIK', 
            'NAMA LENGKAP', 
            'NOMOR WA', 
            'GENDER', 
            'STATUS KEPEGAWAIAN', 
            'JABATAN', 
            'INSTANSI', 
            'PROVINSI', 
            'KABUPATEN / KOTA', 
            'KECAMATAN', 
            'KELURAHAN'
        ];
    }

    /**
     * Transformasi data dari database ke array Excel
     */
    public function array(): array {
        return Participant::where('training_id', $this->trainingId)
            ->get()
            ->map(function($p) {
                return [
                    "'" . $p->nip_nik, // Gunakan tanda kutip agar NIP tidak menjadi format saintifik (E+)
                    strtoupper($p->name),
                    $p->phone ?? '-',
                    $p->gender ?? '-',
                    strtoupper($p->status_kepegawaian ?? 'NON-ASN'),
                    $p->jabatan ?? '-',
                    $p->instansi ?? '-',
                    $p->provinsi ?? '-',
                    $p->kota ?? '-',      // Kolom Kota (Sesuai Database)
                    $p->kecamatan ?? '-', // Kolom Kecamatan
                    $p->kelurahan ?? '-'  // Kolom Kelurahan
                ];
            })->toArray();
    }

    /**
     * Styling Header Excel agar lebih rapi
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Baris 1 (Header) dibuat Bold
            1 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ],
        ];
    }
}