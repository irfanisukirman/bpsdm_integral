<?php

namespace App\Exports;

use App\Models\AlumniProfile;
use App\Models\EvaluationResultL34;
use App\Models\Question;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class L34OlahDataSheet implements FromView, WithTitle, WithCharts, ShouldAutoSize
{
    protected $training;
    protected $questionsL3;
    protected $questionsL4;

    public function __construct($training) { 
        $this->training = $training; 
    }

    public function title(): string { return 'OLAH DATA'; }

    public function view(): View
    {
        $id = $this->training->id;
        $profiles = AlumniProfile::where('training_id', $id)->get();
        $results = EvaluationResultL34::where('training_id', $id)->get();

        // Ambil Pertanyaan L3 & L4
        $this->questionsL3 = Question::where('category', 'LIKE', 'l34%')
                            ->where('sub_category', 'Perubahan Perilaku')
                            ->get()->unique('question_text');

        $this->questionsL4 = Question::where('category', 'LIKE', 'l34%')
                            ->where('sub_category', 'Dampak Pelatihan')
                            ->get()->unique('question_text');

        $totalResponden = [
            'mandiri' => $results->where('evaluator_role', 'mandiri')->unique('participant_id')->count(),
            'atasan' => $results->where('evaluator_role', 'atasan')->unique('participant_id')->count(),
            'rekan' => $results->where('evaluator_role', 'rekan')->unique('participant_id')->count(),
        ];

        return view('evaluasi.excel.l34_olah_data', [
            'profiles' => $profiles,
            'results' => $results,
            'questionsL3' => $this->questionsL3,
            'questionsL4' => $this->questionsL4,
            'totalResponden' => $totalResponden
        ]);
    }

    public function charts(): array
    {
        $charts = [];
        $sheetName = 'OLAH DATA';

        // 1. Chart Pendidikan (Baris 4-8)
        $charts[] = $this->generateBarChart($sheetName, 'Pendidikan', 'Statistik Pendidikan', '$B$3:$C$3', '$A$4:$A$8', ['$B$4:$B$8', '$C$4:$C$8'], 'E3', 'L13');

        // 2. Chart Golongan (Baris 17-20)
        $charts[] = $this->generateBarChart($sheetName, 'Golongan', 'Statistik Golongan', '$B$16:$C$16', '$A$17:$A$20', ['$B$17:$B$20', '$C$17:$C$20'], 'E16', 'L26');

        // 3. Chart Jabatan (Baris 29-30)
        $charts[] = $this->generateBarChart($sheetName, 'Jabatan', 'Perubahan Jabatan', '$B$28', '$A$29:$A$30', ['$B$29:$B$30'], 'E28', 'L35');

        // 4. Chart Unit Kerja (Baris 38-39)
        $charts[] = $this->generateBarChart($sheetName, 'UnitKerja', 'Perubahan Unit Kerja', '$B$37', '$A$38:$A$39', ['$B$38:$B$39'], 'E37', 'L44');

        // 5. Chart Penugasan (Urutan Baris Mulai dari 51)
        // Kita loop untuk pertanyaan No 6 sampai 10
        for ($i = 1; $i <= 5; $i++) {
            $startRow = 51 + (($i - 1) * 11);
            $headerRow = $startRow - 1;
            $dataStart = $startRow;
            $dataEnd = $startRow + 1;

            $charts[] = $this->generateBarChart($sheetName, "Task_$i", "Penugasan $i", "\$B$headerRow:\$D$headerRow", "\$A$dataStart:\$A$dataEnd", ["\$E$dataStart:\$E$dataEnd", "\$F$dataStart:\$F$dataEnd", "\$G$dataStart:\$G$dataEnd"], 'I'.$headerRow, 'P'.($dataEnd+5));
        }

        // 6. Chart Perubahan Perilaku (L3) - Dinamis
        $currentRow = 110; // Baris awal seksi L3 di Blade
        foreach ($this->questionsL3 as $idx => $q) {
            $headerRow = $currentRow + 1;
            $dataStart = $currentRow + 2;
            $dataEnd = $currentRow + 6;

            $charts[] = $this->generateBarChart($sheetName, "L3_$idx", "L3: ".$q->question_text, "\$B$headerRow:\$D$headerRow", "\$A$dataStart:\$A$dataEnd", ["\$E$dataStart:\$E$dataEnd", "\$F$dataStart:\$F$dataEnd", "\$G$dataStart:\$G$dataEnd"], 'I'.$currentRow, 'P'.($dataEnd+2));
            
            $currentRow += 13; // Jarak antar pertanyaan di Blade
        }

        // 7. Chart Dampak Pelatihan (L4) - Dinamis
        $currentRowL4 = 110 + (count($this->questionsL3) * 13) + 5;
        foreach ($this->questionsL4 as $idx => $q) {
            $headerRow = $currentRowL4 + 1;
            $dataStart = $currentRowL4 + 2;
            $dataEnd = $currentRowL4 + 6;

            $charts[] = $this->generateBarChart($sheetName, "L4_$idx", "L4: ".$q->question_text, "\$B$headerRow:\$D$headerRow", "\$A$dataStart:\$A$dataEnd", ["\$E$dataStart:\$E$dataEnd", "\$F$dataStart:\$F$dataEnd", "\$G$dataStart:\$G$dataEnd"], 'I'.$currentRowL4, 'P'.($dataEnd+2));
            
            $currentRowL4 += 13;
        }

        return $charts;
    }

    private function generateBarChart($sheet, $name, $title, $legend, $xAxis, $dataSeries, $posStart, $posEnd)
    {
        $categories = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'$sheet'!$legend", null, count($dataSeries))];
        $xAxisTickValues = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'$sheet'!$xAxis", null, 5)];
        $dataValues = [];
        foreach ($dataSeries as $ds) {
            $dataValues[] = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'$sheet'!$ds", null, 5);
        }

        $series = new DataSeries(DataSeries::TYPE_BARCHART, DataSeries::GROUPING_CLUSTERED, range(0, count($dataValues) - 1), [], $xAxisTickValues, $dataValues);
        $series->setPlotDirection(DataSeries::DIRECTION_BAR);
        $plot = new PlotArea(null, [$series]);
        $chart = new Chart($name, new Title($title), new Legend(Legend::POSITION_RIGHT, null, false), $plot);
        $chart->setTopLeftPosition($posStart);
        $chart->setBottomRightPosition($posEnd);
        return $chart;
    }
}