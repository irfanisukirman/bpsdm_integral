<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use Carbon\CarbonPeriod; 


class MonitoringCeklisExport implements FromArray, WithEvents, WithColumnWidths
{
    protected $training;
    protected $categoryRowIndices = []; 
    protected $dynamicColumns = [];

    public function __construct($training)
    {
        $this->training = $training;
    }

    // Mengatur Lebar Kolom secara manual
    public function columnWidths(): array
    {
        return [
            'A' => 8,  // Kolom NO
            'B' => 70, // Kolom BUTIR INDIKATOR (Dibuat lebar agar teks muat)
        ];
    }

    public function array(): array
    {
        $t = $this->training;
        // 1. Tentukan Kolom Dinamis (Harian untuk Standar, Tahapan untuk Blended)
        if ($t->model == 'standar') {
            $period = CarbonPeriod::create($t->tgl_mulai, $t->tgl_selesai);
            foreach ($period as $date) {
                $this->dynamicColumns[] = (object)[
                    'id' => 'std', // Identifier untuk model standar
                    'header_title' => $date->translatedFormat('l'),
                    'header_sub' => $date->format('d/m/Y'),
                    'db_date' => $date->format('Y-m-d')
                ];
            }
        } else {
            foreach ($t->stages as $st) {
                $this->dynamicColumns[] = (object)[
                    'id' => $st->id,
                    'header_title' => strtoupper($st->nama_tahapan),
                    'header_sub' => date('d/m/y', strtotime($st->tgl_mulai)) . ' - ' . date('d/m/y', strtotime($st->tgl_selesai)),
                    'db_date' => null
                ];
            }
        }

        // 1. Judul Atas
        $rows = [
            ['LAPORAN REKAPITULASI MONITORING'],
            ['Nama Pelatihan', ': ' . strtoupper($t->nama_pelatihan)],
            ['Metode Pelatihan', ': ' . strtoupper($t->model)], // BARIS BARU: METODE
            ['LPP / Unit Kerja', ': ' . $t->bidang],
            ['Periode Global', ': ' . date('d/m/Y', strtotime($t->tgl_mulai)) . ' s.d ' . date('d/m/Y', strtotime($t->tgl_selesai))],
            [''], 
        ];

        // 3. Susun Header Tabel
        $header1 = ['NO', 'BUTIR INDIKATOR / INSTRUMEN'];
        $header2 = ['', ''];
        foreach ($this->dynamicColumns as $col) {
            $header1[] = $col->header_title;
            $header2[] = $col->header_sub;
        }
        $rows[] = $header1; // Baris 7
        $rows[] = $header2; // Baris 8

        $currentRow = 8;  

        $categories = \App\Models\Question::where('category', 'LIKE', 'Monitoring%')
                    ->select('category')->distinct()->pluck('category');

        foreach ($categories as $cat) {
            $currentRow++;
            $this->categoryRowIndices[] = $currentRow; 
            $rows[] = ['', strtoupper($cat)]; 
            
            $questions = \App\Models\Question::where('category', $cat)->get();
            $no = 1;
            foreach ($questions as $q) {
                $currentRow++;
                $row = [$no++, $q->question_text];
                
                foreach ($this->dynamicColumns as $col) {
                    // Logic Ceklis
                    $ans = $t->monitoringResults
                        ->where('question_id', $q->id)
                        ->where('training_stage_id', $col->id == 'std' ? null : $col->id)
                        ->first();

                    $row[] = ($ans && $ans->answer == 'ya') ? '✓' : (($ans && $ans->answer == 'tidak') ? 'X' : '-');
                }
                $rows[] = $row;
            }
        }

        // 5. Kesimpulan
        $rows[] = [''];
        $currentRow++;
        $this->categoryRowIndices[] = ++$currentRow;
        $rows[] = ['KESIMPULAN & REKOMENDASI KESELURUHAN'];
        
        foreach ($this->dynamicColumns as $col) {
            $sum = $t->summaries
                ->where('training_stage_id', $col->id == 'std' ? null : $col->id)
                ->where('category', 'STAGE_FINAL_SUMMARY')
                ->first();
            
            // Untuk standar, kita tampilkan kesimpulan di kolom pertama saja atau semua kolom
            $rows[] = ['', '[' . $col->header_title . '] ' . ($sum->conclusion ?? '-')];
        }

        // Tanda Tangan
        $rows[] = [''];
        $rows[] = ['', '', '', '', 'Cimahi, ' . date('d F Y')];
        $rows[] = ['', '', '', '', 'Tim Monitoring BPSDM Jawa Barat'];
        $rows[] = [''];
        $rows[] = [''];
        $rows[] = ['', '', '', '', \Auth::user()->name];

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestCol = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                // 1. FIX JUDUL (BARIS 1)
                $sheet->mergeCells('A1:'.$highestCol.'1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                // Nonaktifkan wrap text khusus judul agar tidak vertikal
                $sheet->getStyle('A2:A6')->getAlignment()->setWrapText(false);

                // 2. FIX INFO HEADER (BARIS 2-4)
                // Kita tidak wrap baris ini agar label "Nama Pelatihan" dll tetap satu baris
                $sheet->getStyle('A3:A5')->getAlignment()->setWrapText(false);
                $sheet->getStyle('A3:A5')->getFont()->setBold(true);

                // 3. STYLE TABLE HEADER (BIRU)
                $sheet->getStyle('A7:'.$highestCol.'8')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER, 
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);
                $sheet->mergeCells('A7:A8');
                $sheet->mergeCells('B7:B8');

                // 4. STYLE BARIS KATEGORI (ABU-ABU)
                foreach ($this->categoryRowIndices as $row) {
                    $sheet->getStyle('A' . $row . ':' . $highestCol . $row)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBEDEF']],
                    ]);
                    $sheet->mergeCells('B' . $row . ':' . $highestCol . $row);
                }

                // 5. SET WRAP TEXT HANYA UNTUK BARIS DATA (AGAR TIDAK MERUSAK HEADER)
                $sheet->getStyle('A9:B'.$highestRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('A9:'.$highestCol.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Atur posisi teks data (Tengah untuk NO dan Ceklis)
                $sheet->getStyle('A9:A'.$highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle('A9:B'.$highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle('C9:'.$highestCol.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C9:'.$highestCol.$highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // 6. STYLE TANDA TANGAN
                $sheet->getStyle('E'.($highestRow-4).':E'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }
}