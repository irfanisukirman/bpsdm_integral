<?php

namespace App\Exports;

use App\Models\AlumniProfile;
use App\Models\EvaluationResultL34;
use App\Models\Question;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithTitle;
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
    protected $questionsPlacement;

    public function __construct($training)
    {
        $this->training = $training;
    }

    public function title(): string
    {
        return 'OLAH DATA';
    }

    public function view(): View
    {
        $id = $this->training->id;
        $profiles = AlumniProfile::where('training_id', $id)->get();
        $results = EvaluationResultL34::with('question')->where('training_id', $id)->get();

        $this->questionsL3 = Question::forTraining($this->training, 'l34_mandiri')
            ->where('sub_category', 'Perubahan Perilaku')
            ->get()
            ->unique('question_text')
            ->values();

        $this->questionsL4 = Question::forTraining($this->training, 'l34_mandiri')
            ->where('sub_category', 'Dampak Pelatihan')
            ->get()
            ->unique('question_text')
            ->values();

        $this->questionsPlacement = Question::forTraining($this->training, 'l34_mandiri')
            ->where('sub_category', 'Penempatan Tugas dan Transfer Learning')
            ->get()
            ->unique('question_text')
            ->values();

        $allQuestions = Question::query()
            ->where(function ($query) {
                $query->where('bidang', $this->training->bidang)
                    ->orWhere('bidang', 'Semua Bidang');
            })
            ->where('category', 'LIKE', 'l34_%')
            ->get();

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
            'questionsPlacement' => $this->questionsPlacement,
            'allQuestions' => $allQuestions,
            'training' => $this->training,
            'totalResponden' => $totalResponden,
        ]);
    }

    public function charts(): array
    {
        $charts = [];
        $sheetName = 'OLAH DATA';

        // Baris 1-11 berisi identitas pelatihan dan ringkasan perubahan data diri.
        $currentRow = 11;
        $chartTopRow = 20;
        $sections = [
            'Penempatan & Transfer Learning' => $this->questionsPlacement ?? collect(),
            'Perubahan Perilaku (L3)' => $this->questionsL3 ?? collect(),
            'Dampak Pelatihan (L4)' => $this->questionsL4 ?? collect(),
        ];

        foreach ($sections as $sectionTitle => $questions) {
            $currentRow++; // Judul bagian.
            if ($questions->isEmpty()) {
                $currentRow++; // Informasi bagian tanpa pertanyaan.
                continue;
            }

            foreach ($questions->values() as $question) {
                $questionRow = $currentRow + 1;
                $headerRow = $questionRow + 1;
                $optionCount = $this->optionCount($question);
                $dataStart = $headerRow + 1;
                $dataEnd = $dataStart + $optionCount - 1;

                if ($optionCount > 0) {
                    $charts[] = $this->generateQuestionBarChart(
                        $sheetName,
                        'Question_'.count($charts),
                        $sectionTitle.': '.Str::limit($question->question_text, 90),
                        $headerRow,
                        $dataStart,
                        $dataEnd,
                        'J'.$chartTopRow,
                        'R'.($chartTopRow + 14)
                    );
                    $chartTopRow += 16;
                }

                // Pertanyaan, header, pilihan jawaban, dan satu baris kosong.
                $currentRow = $headerRow + $optionCount + 1;
            }
        }

        // Bagian ringkasan L4: judul, header, kemudian tiga perspektif.
        $summaryTitleRow = $currentRow + 1;
        $summaryHeaderRow = $summaryTitleRow + 1;
        $summaryDataStart = $summaryHeaderRow + 1;
        $summaryDataEnd = $summaryDataStart + 2;
        array_unshift($charts, $this->generateSummaryBarChart(
            $sheetName,
            $summaryHeaderRow,
            $summaryDataStart,
            $summaryDataEnd,
            'J2',
            'R17'
        ));

        return $charts;
    }

    private function optionCount($question): int
    {
        return match ($question->type) {
            'slider' => 5,
            'text' => 1,
            default => count($question->options ?? []),
        };
    }

    private function generateQuestionBarChart(
        string $sheet,
        string $name,
        string $title,
        int $headerRow,
        int $dataStart,
        int $dataEnd,
        string $posStart,
        string $posEnd
    ): Chart {
        $pointCount = $dataEnd - $dataStart + 1;
        $sheetRef = "'".$sheet."'!";
        $seriesLabels = collect(['C', 'E', 'G'])->map(fn ($column) =>
            new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_STRING,
                $sheetRef.'$'.$column.'$'.$headerRow,
                null,
                1
            )
        )->all();
        $categories = [new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            $sheetRef.'$A$'.$dataStart.':$A$'.$dataEnd,
            null,
            $pointCount
        )];
        $dataValues = collect(['C', 'E', 'G'])->map(fn ($column) =>
            new DataSeriesValues(
                DataSeriesValues::DATASERIES_TYPE_NUMBER,
                $sheetRef.'$'.$column.'$'.$dataStart.':$'.$column.'$'.$dataEnd,
                null,
                $pointCount
            )
        )->all();

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($dataValues) - 1),
            $seriesLabels,
            $categories,
            $dataValues
        );
        $series->setPlotDirection(DataSeries::DIRECTION_BAR);
        $chart = new Chart(
            $name,
            new Title($title),
            new Legend(Legend::POSITION_RIGHT, null, false),
            new PlotArea(null, [$series])
        );
        $chart->setTopLeftPosition($posStart);
        $chart->setBottomRightPosition($posEnd);

        return $chart;
    }

    private function generateSummaryBarChart(
        string $sheet,
        int $headerRow,
        int $dataStart,
        int $dataEnd,
        string $posStart,
        string $posEnd
    ): Chart {
        $sheetRef = "'".$sheet."'!";
        $seriesLabels = [new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            $sheetRef.'$B$'.$headerRow,
            null,
            1
        )];
        $categories = [new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_STRING,
            $sheetRef.'$A$'.$dataStart.':$A$'.$dataEnd,
            null,
            3
        )];
        $dataValues = [new DataSeriesValues(
            DataSeriesValues::DATASERIES_TYPE_NUMBER,
            $sheetRef.'$B$'.$dataStart.':$B$'.$dataEnd,
            null,
            3
        )];

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            [0],
            $seriesLabels,
            $categories,
            $dataValues
        );
        $series->setPlotDirection(DataSeries::DIRECTION_BAR);
        $chart = new Chart(
            'L4_Summary',
            new Title('Ringkasan Skor Dampak (L4)'),
            null,
            new PlotArea(null, [$series])
        );
        $chart->setTopLeftPosition($posStart);
        $chart->setBottomRightPosition($posEnd);

        return $chart;
    }
}
