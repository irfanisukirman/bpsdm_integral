<?php


namespace App\Exports;

use App\Models\EvaluationFormL1;
use App\Models\EvaluationResultL1;
use App\Models\Question;
use App\Models\Participant;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EvaluationL1Export implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $form;

    public function __construct($form)
    {
        $this->form = $form;
    }

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
        return ['A' => 5, 'B' => 20, 'C' => 35];
    }

    public function array(): array
    {
        $form = $this->form;
        $training = $form->training;
        $questions = Question::where('category', 'l1_' . $form->type)->get();
        $participants = Participant::where('training_id', $training->id)->orderBy('name', 'asc')->get();

        // 1. HEADER INFORMASI
        if ($form->type == 'penyelenggara') {
            $rows = [
                ['REKAPITULASI EVALUASI PENYELENGGARA'],
                ['Nama Pelatihan', ': ' . strtoupper($training->nama_pelatihan)],
                ['Instansi Penyelenggara', ': ' . $form->target_name],
                ['Periode', ': ' . date('d/m/Y', strtotime($training->tgl_mulai)) . ' - ' . date('d/m/Y', strtotime($training->tgl_selesai))],
            ];
        } else {
            $rows = [
                ['REKAPITULASI NILAI PENGAJAR / NARASUMBER'],
                ['Nama Pengajar', ': ' . $form->target_name],
                ['Materi', ': ' . $form->materi],
                ['Pelatihan', ': ' . strtoupper($training->nama_pelatihan)],
                ['Tanggal Mengajar', ': ' . date('d/m/Y', strtotime($form->schedule->date ?? $training->tgl_mulai))],
            ];
        }

        $rows[] = ['']; // Spasi

        // 2. HEADER TABEL DINAMIS
        $headerTable = ['NO', 'NIP / NIK', 'NAMA LENGKAP'];
        foreach ($questions as $q) {
            $headerTable[] = strtoupper($q->question_text);
        }
        $rows[] = $headerTable;

        // 3. ISI DATA PESERTA
        $totalScoresAll = 0;
        $countFiller = 0;

        foreach ($participants as $index => $p) {
            $row = [$index + 1, "'" . $p->nip_nik, strtoupper($p->name)];
            $pScoreTotal = 0;
            $pQCount = 0;

            foreach ($questions as $q) {
                $res = EvaluationResultL1::where('participant_id', $p->id)
                    ->where('question_id', $q->id)
                    ->where('schedule_id', $form->schedule_id)
                    ->first();

                if ($q->type == 'slider') {
                    $val = $res ? $res->score : 0;
                    $row[] = $val;
                    $pScoreTotal += $val;
                    $pQCount++;
                } else {
                    $row[] = $res ? $res->note : '-';
                }
            }
            
            if ($pScoreTotal > 0) {
                $totalScoresAll += ($pScoreTotal / $pQCount);
                $countFiller++;
            }
            
            $rows[] = $row;
        }

        // 4. FOOTER STATISTIK
        $avgFinal = $countFiller > 0 ? ($totalScoresAll / $countFiller) : 0;
        $rows[] = [''];
        $rows[] = ['', 'RATA-RATA NILAI KESELURUHAN', round($avgFinal, 2)];
        $rows[] = ['', 'PREDIKAT', $this->getPredicate($avgFinal)];
        $rows[] = ['', 'KESIMPULAN', 'Pelaksanaan evaluasi berjalan dengan kategori ' . $this->getPredicate($avgFinal)];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            'A7:Z7' => [ // Asumsi baris ke 7 adalah header tabel
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']],
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

                $sheet->getStyle('A7:'.$highestCol.$highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A7:'.$highestCol.'7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1:A5')->getFont()->setBold(true);
            },
        ];
    }
}