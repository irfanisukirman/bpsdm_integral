<?php

namespace App\Exports;

use App\Models\Participant;
use App\Models\EvaluationResultL34;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;

class L34InformasiSheet implements FromView, WithTitle, ShouldAutoSize
{
    protected $training;
    public function __construct($training) { $this->training = $training; }
    public function title(): string { return 'INFORMASI'; }

    public function view(): View {
        $id = $this->training->id;
        $totalPeserta = Participant::where('training_id', $id)->count();
        $results = EvaluationResultL34::where('training_id', $id)->get();
        
        $stats = [
            'mandiri' => $results->where('evaluator_role', 'mandiri')->unique('participant_id')->count(),
            'atasan'  => $results->where('evaluator_role', 'atasan')->unique('participant_id')->count(),
            'rekan'   => $results->where('evaluator_role', 'rekan')->unique('participant_id')->count(),
        ];

        $instansi = Participant::where('training_id', $id)
            ->select('instansi', DB::raw('count(*) as total'))
            ->groupBy('instansi')->get();

        return view('evaluasi.excel.l34_informasi', compact('totalPeserta', 'stats', 'instansi'), ['training' => $this->training]);
    }
}