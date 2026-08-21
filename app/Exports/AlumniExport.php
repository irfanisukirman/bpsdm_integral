<?php

namespace App\Exports;

use App\Models\Participant;
use App\Models\AlumniProfile;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AlumniExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $bidang;
    protected $isSuperadmin;

    public function __construct($bidang, $isSuperadmin)
    {
        $this->bidang = $bidang;
        $this->isSuperadmin = $isSuperadmin;
    }

    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 15, 'C' => 20];
    }

    public function array(): array
    {
        $query = Participant::query();
        if (!$this->isSuperadmin) {
            $query->whereHas('training', fn($q) => $q->where('bidang', $this->bidang));
        }
        $alumni = $query->get();
        $total = $alumni->count();

        // Logika Wilayah 3T (Contoh List)
        $list3T = ['Kepulauan Meranti', 'Nias', 'Sumba Timur', 'Donggala', 'Nabire', 'Asmat', 'Merauke'];

        $rows = [
            ['LAPORAN REKAPITULASI DATA ALUMNI PELATIHAN'],
            ['Unit Kerja/Bidang', ': ' . ($this->isSuperadmin ? 'SEMUA BIDANG' : $this->bidang)],
            ['Tanggal Cetak', ': ' . date('d/m/Y H:i')],
            [''],

            ['STATISTIK UTAMA', 'JUMLAH', 'PERSENTASE'],
            ['Total Alumni', $total, '100%'],
            [''],

            ['KOMPOSISI GENDER'],
            ['Laki-Laki', $alumni->where('gender', 'Laki-Laki')->count(), $this->perc($alumni->where('gender', 'Laki-Laki')->count(), $total)],
            ['Perempuan', $alumni->where('gender', 'Perempuan')->count(), $this->perc($alumni->where('gender', 'Perempuan')->count(), $total)],
            [''],

            ['KLASIFIKASI WILAYAH (Ref: Jarak Cimahi)'],
            ['Wilayah 3T', $alumni->filter(fn($a) => in_array($a->kabupaten_kota, $list3T))->count(), $this->perc($alumni->filter(fn($a) => in_array($a->kabupaten_kota, $list3T))->count(), $total)],
            ['Non-3T', $alumni->filter(fn($a) => !in_array($a->kabupaten_kota, $list3T))->count(), $this->perc($alumni->filter(fn($a) => !in_array($a->kabupaten_kota, $list3T))->count(), $total)],
            [''],

            ['STATUS KEPEGAWAIAN'],
        ];

        foreach ($alumni->groupBy('status_kepegawaian') as $status => $group) {
            $rows[] = [$status ?? 'Lainnya', $group->count(), $this->perc($group->count(), $total)];
        }

        $rows[] = [''];
        $rows[] = ['TINGKAT PENDIDIKAN (Data Evaluasi Pasca)'];
        
        $eduStats = AlumniProfile::whereIn('participant_id', $alumni->pluck('id'))
                    ->select('edu_current', \DB::raw('count(*) as total'))
                    ->groupBy('edu_current')->get();

        foreach ($eduStats as $edu) {
            $rows[] = [$edu->edu_current, $edu->total, $this->perc($edu->total, $total)];
        }

        return $rows;
    }

    private function perc($part, $total) {
        return $total > 0 ? round(($part / $total) * 100, 1) . '%' : '0%';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            5 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:C1');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Bold semua sub-judul
                $rows = [8, 12, 16, 20]; 
                foreach($rows as $r) {
                    $sheet->getStyle('A'.$r)->getFont()->setBold(true);
                    $sheet->getStyle('A'.$r.':C'.$r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EBEDEF');
                }
            },
        ];
    }
}