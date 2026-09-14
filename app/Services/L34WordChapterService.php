<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class L34WordChapterService
{
    public function build(Training $training, $results, $profiles): Table
    {
        $table = new Table([
            'borderSize' => 6, 'borderColor' => 'B8C2CC', 'cellMargin' => 90,
            'width' => 100 * 50, 'unit' => TblWidth::PERCENT,
        ]);
        $title = ['bold' => true, 'color' => 'FFFFFF', 'size' => 12];
        $section = ['bold' => true, 'color' => 'FFFFFF', 'size' => 11];
        $header = ['bold' => true, 'color' => '1F2937', 'size' => 9];
        $body = ['size' => 8.5, 'color' => '263238'];
        $center = ['alignment' => 'center'];

        $table->addRow();
        $table->addCell(null, ['gridSpan' => 6, 'bgColor' => '2F5597'])->addText(
            'HASIL EVALUASI PASCA PELATIHAN - PROGRAM '.strtoupper((string) ($training->program_evaluasi ?: 'PKTI/PKTU')),
            $title, $center
        );
        $summary = [
            'Pelatihan' => $training->nama_pelatihan,
            'Program Evaluasi' => $training->program_evaluasi ?: 'PKTI/PKTU',
            'Periode Pelatihan' => Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y').' s.d. '.Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y'),
            'Cakupan Responden' => collect(['mandiri' => 'Mandiri', 'atasan' => 'Atasan', 'rekan' => 'Rekan'])
                ->map(fn ($label, $role) => $label.': '.$results->where('evaluator_role', $role)->unique('participant_id')->count())
                ->implode('; '),
        ];
        foreach ($summary as $label => $value) {
            $table->addRow();
            $table->addCell(1700, ['bgColor' => 'D9EAF7'])->addText($label, $header);
            $table->addCell(null, ['gridSpan' => 5])->addText((string) $value, $body);
        }

        $this->addSectionTitle($table, 'A. DATA DIRI ALUMNI', $section);
        $table->addRow();
        foreach (['No.', 'Alumni / NIP', 'Pendidikan dan Pangkat', 'Jabatan', 'Unit / Instansi', 'Status 360'] as $heading) {
            $table->addCell(null, ['bgColor' => 'D9EAF7'])->addText($heading, $header, $center);
        }
        foreach ($training->participants->sortBy('name')->values() as $index => $participant) {
            $profile = $profiles->firstWhere('participant_id', $participant->id);
            $roles = $results->where('participant_id', $participant->id)->pluck('evaluator_role')->unique();
            $table->addRow();
            $table->addCell(500)->addText((string) ($index + 1), $body, $center);
            $table->addCell(2200)->addText($participant->name."\n".($participant->nip_nik ?: '-'), $body);
            $table->addCell(1800)->addText('Pendidikan: '.($profile?->edu_during_training ?: '-').' -> '.($profile?->edu_current ?: '-')."\nPangkat: ".($profile?->rank_during_training ?: '-').' -> '.($profile?->rank_current ?: '-'), $body);
            $table->addCell(1800)->addText(($profile?->pos_during_training ?: $participant->jabatan ?: '-').' -> '.($profile?->pos_current ?: '-'), $body);
            $table->addCell(1900)->addText(($profile?->unit_current ?: '-')."\n".($profile?->dept_current ?: $participant->instansi ?: '-'), $body);
            $table->addCell(1500)->addText(collect(['mandiri' => 'Mandiri', 'atasan' => 'Atasan', 'rekan' => 'Rekan'])->map(fn ($label, $role) => $label.': '.($roles->contains($role) ? 'Sudah' : 'Belum'))->implode("\n"), $body);
        }
        if ($training->participants->isEmpty()) {
            $table->addRow();
            $table->addCell(null, ['gridSpan' => 6])->addText('Belum tersedia data alumni.', $body);
        }

        $allQuestions = collect(['mandiri', 'atasan', 'rekan'])
            ->flatMap(fn ($role) => Question::forTraining($training, 'l34_'.$role)->orderBy('id')->get())
            ->values();
        $order = array_flip(Question::l34SubCategoryOptions());
        $sections = $allQuestions
            ->unique(fn ($question) => ($question->sub_category ?: 'Bagian Lainnya').'|'.$question->question_text)
            ->groupBy(fn ($question) => $question->sub_category ?: 'Bagian Lainnya')
            ->sortKeysUsing(fn ($a, $b) => ($order[$a] ?? 999) <=> ($order[$b] ?? 999));

        foreach ($sections as $sectionName => $questions) {
            $letter = chr(66 + min(24, (int) $sections->keys()->search($sectionName)));
            $this->addSectionTitle($table, $letter.'. '.strtoupper((string) $sectionName), $section);
            $table->addRow();
            foreach (['No.', 'Pertanyaan', 'Tipe', 'Mandiri', 'Atasan', 'Rekan Kerja'] as $heading) {
                $table->addCell(null, ['bgColor' => 'D9EAF7'])->addText($heading, $header, $center);
            }
            foreach ($questions->values() as $index => $question) {
                $table->addRow();
                $table->addCell(500)->addText((string) ($index + 1), $body, $center);
                $table->addCell(3000)->addText($question->question_text, $body);
                $table->addCell(900)->addText(ucfirst($question->type), $body, $center);
                foreach (['mandiri', 'atasan', 'rekan'] as $role) {
                    $table->addCell(1900)->addText($this->summarize($question, $role, $allQuestions, $results), $body);
                }
            }
        }
        if ($sections->isEmpty()) {
            $table->addRow();
            $table->addCell(null, ['gridSpan' => 6, 'bgColor' => 'FFF2CC'])->addText('Bank pertanyaan untuk program ini belum tersedia.', $header);
        }

        return $table;
    }


    /**
     * Menjaga seluruh bagian XML DOCX tetap valid ketika complex block berisi
     * karakter ampersand dari pertanyaan, profil, atau jawaban responden.
     */
    public function repairDocxXml(string $path): void
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('File laporan DOCX tidak dapat diperiksa.');
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);
            if (! preg_match('/\.(?:xml|rels)$/i', $name)) {
                continue;
            }

            $xml = $zip->getFromIndex($index);
            if ($xml === false) {
                continue;
            }

            $repaired = preg_replace(
                '/&(?!amp;|lt;|gt;|quot;|apos;|#[0-9]+;|#x[0-9A-Fa-f]+;)/u',
                '&amp;',
                $xml
            );

            if ($repaired !== null && $repaired !== $xml) {
                $zip->addFromString($name, $repaired);
            }
        }

        $zip->close();
    }
    private function addSectionTitle(Table $table, string $text, array $style): void
    {
        $table->addRow();
        $table->addCell(null, ['gridSpan' => 6, 'bgColor' => '4472C4'])->addText($text, $style);
    }

    private function summarize($question, string $role, $allQuestions, $results): string
    {
        $ids = $allQuestions->where('category', 'l34_'.$role)
            ->where('sub_category', $question->sub_category)
            ->where('question_text', $question->question_text)->pluck('id');
        $items = $results->where('evaluator_role', $role)->whereIn('question_id', $ids);
        if ($items->isEmpty()) return 'Belum ada jawaban';
        $scores = $items->whereNotNull('score')->pluck('score')->filter(fn ($score) => is_numeric($score));
        if ($scores->isNotEmpty()) return 'Rata-rata '.number_format($scores->avg(), 1, ',', '.').'/100 (n='.$scores->count().')';

        $answers = $items->flatMap(function ($item) {
            $note = trim((string) $item->note);
            if ($note === '') return [];
            $decoded = json_decode($note, true);
            return is_array($decoded) ? $decoded : [$note];
        })->filter(fn ($answer) => trim((string) $answer) !== '');
        if ($answers->isEmpty()) return 'Belum ada jawaban';

        return $answers->countBy()->sortDesc()
            ->map(fn ($count, $answer) => Str::limit((string) $answer, 180).' ('.$count.')')
            ->implode('; ');
    }
}
