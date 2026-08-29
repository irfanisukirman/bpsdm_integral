<?php

namespace App\Exports;

use App\Models\Participant;
use App\Models\Question;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class L34JawabanSheet implements FromView, WithTitle, ShouldAutoSize
{
    protected $training;
    public function __construct($training) { $this->training = $training; }
    public function title(): string { return 'JAWABAN'; }

    public function view(): View {
        // Ambil peserta dan profil alumninya
        $participants = Participant::with(['alumniProfile', 'user', 'evaluationResultsL34.question'])
            ->where('training_id', $this->training->id)->get();
            
        // Ambil semua pertanyaan slider Level 3 & 4
        $questions = Question::forTraining($this->training, 'l34_mandiri')
            ->whereIn('sub_category', ['Penempatan Tugas dan Transfer Learning', 'Perubahan Perilaku', 'Dampak Pelatihan'])
            ->orderBy('sub_category')
            ->orderBy('id')
            ->get();

        return view('evaluasi.excel.l34_jawaban', compact('participants', 'questions'));
    }
}
