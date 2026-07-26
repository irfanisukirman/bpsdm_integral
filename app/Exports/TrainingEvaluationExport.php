<?php

namespace App\Exports;

use App\Models\Participant;
use App\Models\EvaluationResultL1;
use App\Models\EvaluationResultL2;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TrainingEvaluationExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $training;

    public function __construct($training)
    {
        $this->training = $training;
    }

    // Penentuan Predikat sesuai Probis Anda
    private function getPredicate($score) {
        if (!$score || $score == 0) return "-";
        if ($score <= 60) return "SANGAT KURANG";
        if ($score <= 70) return "KURANG";
        if ($score <= 80) return "CUKUP";
        if ($score <= 90) return "BAIK";
        return "SANGAT BAIK";
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 25,  // NIP
            'C' => 40,  // Nama
            'D' => 15,  // Skor L1
            'E' => 20,  // Predikat L1
            'F' => 15,  // Skor L2
            'G' => 20,  // Predikat L2
        ];
    }

    public function array(): array
    {
        $t = $this->training;
        $participants = Participant::where('training_id', $t->id)->orderBy('name', 'asc')->get();

        // 1. Judul Dokumen
        $rows = [
            ['HASIL EVALUASI LEVEL 1 & LEVEL 2'],
            [strtoupper($t->nama_pelatihan)],
            ['Penyelenggara: ' . $t->bidang],
            [''], // Baris Kosong
            // 2. Header Tabel (Baris 5)
            ['NO', 'NIP / NIK', 'NAMA LENGKAP', 'SKOR L1', 'PREDIKAT L1', 'SKOR L2', 'PREDIKAT L2']
        ];

        // 3. Isi Data Peserta
        foreach ($participants as $index => $p) {
            // Hitung rata-rata L1 (Reaction)
            $avgL1 = EvaluationResultL1::where('participant_id', $p->id)->avg('score') ?? 0;
            
            // Ambil Post-test L2 (Learning)
            $l2 = EvaluationResultL2::where('participant_id', $p->id)->first();
            $valL2 = $l2 ? $l2->postest : 0;

            $rows[] = [
                $index + 1,
                "'" . $p->nip_nik, // Tanda kutip agar NIP tidak berantakan
                strtoupper($p->name),
                round($avgL1, 1),
                $this->getPredicate($avgL1),
                $valL2,
                $this->getPredicate($valL2),
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Baris 1 & 2 (Judul)
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            
            // Baris 5 (Header Tabel)
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                // Tambahkan Border ke seluruh tabel
                $sheet->getStyle('A5:'.$highestCol.$highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Tengah-kan kolom tertentu
                $sheet->getStyle('A5:B'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D5:G'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Gabungkan sel judul atas
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');
                $sheet->mergeCells('A3:G3');
                $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}