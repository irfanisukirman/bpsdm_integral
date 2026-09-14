<?php

namespace App\Exports;

use App\Models\Participant;
use App\Models\Question;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class L34JawabanSheet implements FromView, WithTitle, ShouldAutoSize
{
    public function __construct(protected $training)
    {
    }

    public function title(): string
    {
        return 'JAWABAN';
    }

    public function view(): View
    {
        $participants = Participant::with(['alumniProfile', 'user', 'evaluationResultsL34.question'])
            ->where('training_id', $this->training->id)
            ->orderBy('name')
            ->get();

        $questions = collect(['mandiri', 'atasan', 'rekan'])
            ->flatMap(fn ($role) => Question::forTraining($this->training, 'l34_'.$role)->orderBy('id')->get())
            ->unique(fn ($question) => $question->sub_category.'|'.$question->question_text)
            ->sortBy(fn ($question) => $this->sectionOrder($question->sub_category).sprintf('%010d', $question->id))
            ->values();

        return view('evaluasi.excel.l34_jawaban', compact('participants', 'questions'));
    }

    private function sectionOrder(?string $section): string
    {
        $order = array_flip(Question::l34SubCategoryOptions());

        return sprintf('%03d|', $order[$section] ?? 999).(string) $section;
    }
}