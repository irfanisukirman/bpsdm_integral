<?php

namespace App\Services;

use App\Models\EvaluationResultL34;
use App\Models\Participant;
use App\Models\Question;
use App\Models\Training;

class EvaluationL34DashboardService
{
    public function __construct(private PostEvaluationService $scores) {}

    public function category(?float $score): string
    {
        if ($score === null) {
            return 'Belum dapat dinilai';
        }

        return $score <= 60 ? 'Kurang' : ($score <= 79 ? 'Cukup' : ($score <= 89 ? 'Baik' : 'Sangat Baik'));
    }

    private function key(Question $q): string
    {
        return trim((string) $q->sub_category).'|'.$this->scores->normalizeText($q->question_text).'|'.($q->source_occurrence ?? 1);
    }

    private function quantitative(Question $q, Training $t): bool
    {
        return $q->type === 'slider' || ($this->scores->supportsFourPoint($t) && $this->scores->fourPointQuestion($q));
    }

    private function score($answer, Question $question, Training $t): ?float
    {
        if (! $answer || ! $this->quantitative($question, $t)) {
            return null;
        }
        if ($question->type === 'slider') {
            return $this->scores->score($answer, ['scale' => 'hundred'], $t);
        }
        $score = $this->scores->score($answer, ['scale' => 'four'], $t);
        if ($score !== null) {
            return $score;
        }
        // Older public forms stored numeric 1-4 choices in the numeric column.
        if (blank($answer->note) && is_numeric($answer->score)) {
            $value = (float) $answer->score;
            if (in_array($value, [1.0, 2.0, 3.0, 4.0], true)) {
                return $value * 25;
            }
            if (in_array($value, [25.0, 50.0, 75.0, 100.0], true)) {
                return $value;
            }
        }

        return null;
    }

    public function build(Training $t): array
    {
        $participants = Participant::where('training_id', $t->id)->where('registration_status', 'approved')->orderBy('name')->get();
        $participantTotal = $participants->count();
        $excludedParticipants = Participant::where('training_id', $t->id)->where('registration_status', '!=', 'approved')->count();
        $roleLabels = collect($t->evaluationRoleLabels());
        $activeRoles = $roleLabels;
        $roleQuestions = $roleLabels->mapWithKeys(fn ($label, $role) => [$role => Question::forTraining($t, 'l34_'.$role)->get()]);
        $questionMap = $roleQuestions->flatten()->keyBy('id');
        $results = EvaluationResultL34::where('training_id', $t->id)->whereIn('participant_id', $participants->pluck('id'))->whereIn('question_id', $questionMap->keys())->whereIn('evaluator_role', $roleLabels->keys())->orderBy('id')->get()
            ->filter(fn ($r) => $questionMap[$r->question_id]->category === 'l34_'.$r->evaluator_role);
        $answers = [];
        foreach ($results as $answer) {
            $q = $questionMap[$answer->question_id];
            $answers[$answer->participant_id][$answer->evaluator_role][$this->key($q)] = $answer;
        }
        $definitions = [];
        $scaled = [];
        $sectionNames = [];
        foreach ($roleQuestions as $role => $questions) {
            foreach ($questions as $q) {
                $key = $this->key($q);
                $definitions[$role][$key] = $q;
                if (! str_starts_with((string) $q->sub_category, 'Data Diri')) {
                    $sectionNames[(string) ($q->sub_category ?: 'Bagian Lainnya')] = true;
                }
                if (! str_starts_with((string) $q->sub_category, 'Data Diri') && $this->quantitative($q, $t)) {
                    $scaled[$role][$key] = $q;
                }
            }
        }
        $progress = [];
        $participantProgress = [];
        $completedPairs = 0;
        $startedPairs = 0;
        $fullyAssessed = 0;
        $numeric = [];
        $latestAnswerAt = null;
        foreach ($roleLabels as $role => $label) {
            $progress[$role] = ['label' => $label, 'started' => 0, 'completed' => 0, 'partial' => 0, 'not_started' => 0, 'expected_questions' => count($scaled[$role] ?? []), 'answered_questions' => 0, 'instrument_ready' => ! empty($scaled[$role])];
        }
        foreach ($participants as $participant) {
            $row = ['participant' => $participant, 'roles' => [], 'completed' => 0, 'started' => 0];
            foreach ($roleLabels as $role => $label) {
                $personAnswers = $answers[$participant->id][$role] ?? [];
                $started = false;
                $valid = 0;
                foreach ($personAnswers as $answer) {
                    if ($answer->score !== null || filled($answer->note)) {
                        $started = true;
                    }
                    if ($answer->updated_at && (! $latestAnswerAt || $answer->updated_at->gt($latestAnswerAt))) {
                        $latestAnswerAt = $answer->updated_at;
                    }
                }
                foreach ($scaled[$role] ?? [] as $key => $q) {
                    $value = $this->score($personAnswers[$key] ?? null, $q, $t);
                    if ($value !== null) {
                        $valid++;
                        $numeric[$role][$key][$participant->id] = $value;
                    }
                }
                $expected = count($scaled[$role] ?? []);
                $complete = $expected > 0 && $valid === $expected;
                $row['roles'][$role] = ['started' => $started, 'completed' => $complete, 'valid' => $valid, 'expected' => $expected];
                $progress[$role]['started'] += (int) $started;
                $progress[$role]['completed'] += (int) $complete;
                $progress[$role]['answered_questions'] += $valid;
                $row['started'] += (int) $started;
                $row['completed'] += (int) $complete;
                $startedPairs += (int) $started;
                $completedPairs += (int) $complete;
            }
            $row['status'] = $row['completed'] === $roleLabels->count() ? 'complete' : ($row['started'] ? 'partial' : 'empty');
            if ($row['status'] === 'complete') {
                $fullyAssessed++;
            }
            $participantProgress[] = $row;
        }
        foreach ($progress as $role => &$p) {
            $p['partial'] = $p['started'] - $p['completed'];
            $p['not_started'] = $participantTotal - $p['started'];
            $p['coverage'] = $participantTotal ? round($p['started'] / $participantTotal * 100, 1) : 0;
            $p['completion'] = $participantTotal ? round($p['completed'] / $participantTotal * 100, 1) : 0;
        }unset($p);
        $targetPairs = $participantTotal * $roleLabels->count();
        $overallCoverage = $targetPairs ? round($startedPairs / $targetPairs * 100, 1) : 0;
        $completionCoverage = $targetPairs ? round($completedPairs / $targetPairs * 100, 1) : 0;
        $respondentsByRole = collect($progress)->map(fn ($p) => $p['started']);
        $coverageByRole = collect($progress)->map(fn ($p) => $p['coverage']);
        $indicatorDefinitions = [];
        foreach ($scaled as $role => $questions) {
            foreach ($questions as $key => $q) {
                $indicatorDefinitions[$key] ??= ['label' => $q->question_text, 'section' => $q->sub_category];
            }
        }
        $indicators = collect($indicatorDefinitions)->map(function ($definition, $key) use ($roleLabels, $numeric) {
            $means = [];
            $counts = [];
            $values = [];
            foreach ($roleLabels as $role => $label) {
                $items = array_values($numeric[$role][$key] ?? []);
                $means[$role] = $this->scores->average($items);
                $counts[$role] = count($items);
                $values = array_merge($values, $items);
            }

            return $definition + ['average' => $this->scores->average($values), 'responses' => count($values), 'roles' => collect($means), 'role_counts' => $counts];
        })->values();
        $l3Category = isset($sectionNames['Perubahan Sikap Perilaku']) ? 'Perubahan Sikap Perilaku' : 'Perubahan Perilaku';
        $l4Category = 'Dampak Pelatihan';
        $l3Indicators = $indicators->where('section', $l3Category)->values();
        $l4Indicators = $indicators->where('section', $l4Category)->values();
        $l3Average = $this->scores->average($l3Indicators->pluck('average')->all());
        $l4Average = $this->scores->average($l4Indicators->pluck('average')->all());
        $evaluationSections = collect(Question::l34SubCategoryOptions())->filter(fn ($s) => isset($sectionNames[$s]) && ! str_starts_with($s, 'Data Diri'))->merge(array_diff(array_keys($sectionNames), Question::l34SubCategoryOptions()))->values();
        $dashboardSections = $evaluationSections->map(function ($name) use ($indicators, $roleLabels, $definitions, $answers, $participants) {
            $items = $indicators->where('section', $name);
            $qualitative = 0;
            $respondents = [];
            foreach ($participants as $p) {
                foreach ($roleLabels as $role => $label) {
                    foreach ($definitions[$role] ?? [] as $key => $q) {
                        if ($q->sub_category !== $name) {
                            continue;
                        }$a = $answers[$p->id][$role][$key] ?? null;
                        if (! $a || ($a->score === null && blank($a->note))) {
                            continue;
                        }
                        $respondents[$p->id] = true;
                        if ($q->type === 'text' && filled($a->note)) {
                            $qualitative++;
                        }
                    }
                }
            }

            return ['name' => $name, 'average' => $this->scores->average($items->pluck('average')->all()), 'responses' => $items->sum('responses'), 'respondents' => count($respondents), 'qualitative' => $qualitative, 'indicators' => $items->count(), 'is_qualitative' => $items->isEmpty(), 'is_barrier' => str_contains($name, 'Penghambat')];
        });
        $averagesByRole = $roleLabels->mapWithKeys(fn ($label, $role) => [$role => $evaluationSections->mapWithKeys(fn ($section) => [$section => $this->scores->average($indicators->where('section', $section)->map(fn ($i) => $i['roles'][$role] ?? null)->all())])]);
        $chartSections = $dashboardSections->where('indicators', '>', 0)->pluck('name')->values();
        $roleChartSeries = $roleLabels->map(fn ($label, $role) => ['label' => $label, 'data' => $chartSections->map(fn ($name) => $averagesByRole[$role][$name] ?? null)->values()])->values();
        $impactDistribution = collect(['Sangat Baik' => 0, 'Baik' => 0, 'Cukup' => 0, 'Kurang' => 0, 'Belum dinilai' => 0]);
        foreach ($participants as $p) {
            $values = [];
            foreach ($scaled as $role => $questions) {
                foreach ($questions as $key => $q) {
                    if ($q->sub_category === $l4Category && isset($numeric[$role][$key][$p->id])) {
                        $values[] = $numeric[$role][$key][$p->id];
                    }
                }
            }$average = $this->scores->average($values);
            $category = $average === null ? 'Belum dinilai' : $this->category($average);
            $impactDistribution->put($category, $impactDistribution->get($category) + 1);
        }
        $lowestL3 = $l3Indicators->whereNotNull('average')->sortBy('average')->first();
        $lowestL4 = $l4Indicators->whereNotNull('average')->sortBy('average')->first();
        $priorities = $l3Indicators->merge($l4Indicators)->whereNotNull('average')->sortBy('average')->take(5)->values();
        $recommendations = collect();
        if (! $participantTotal) {
            $recommendations->push(['title' => 'Siapkan peserta evaluasi', 'detail' => 'Belum ada peserta berstatus disetujui pada pelatihan ini. Daftarkan dan setujui peserta sebelum memantau respons.', 'icon' => 'bx-group', 'color' => 'info']);
        }
        foreach ($progress as $role => $p) {
            if (! $p['instrument_ready']) {
                $recommendations->push(['title' => 'Lengkapi instrumen '.$p['label'], 'detail' => 'Belum ada pertanyaan berskala yang dapat dihitung untuk peran ini. Periksa bank soal dan program pelatihan.', 'icon' => 'bx-list-check', 'color' => 'warning']);
            } elseif ($participantTotal && $p['completed'] < $participantTotal) {
                $recommendations->push(['title' => 'Tindak lanjuti penilaian '.$p['label'], 'detail' => $p['not_started'].' belum mulai, '.$p['partial'].' masih parsial, '.$p['completed'].' lengkap dari '.$participantTotal.' alumni.', 'icon' => 'bx-user-check', 'color' => 'warning']);
            }
        }
        foreach ([[$lowestL3, 'Level 3'], [$lowestL4, 'Level 4']] as [$item,$level]) {
            if ($item && $item['average'] < 80) {
                $recommendations->push(['title' => 'Periksa indikator '.$level, 'detail' => $item['label'].' memperoleh '.$this->format($item['average']).'/100 dari '.$item['responses'].' jawaban valid. Tinjau bukti penerapan dan kebutuhan pendampingan.', 'icon' => 'bx-target-lock', 'color' => 'warning']);
            }
        }
        if ($l3Average === null || $l4Average === null) {
            $recommendations->push(['title' => 'Lengkapi dasar penilaian hasil', 'detail' => 'Indeks Level 3 atau Level 4 belum tersedia. Respons profil dan jawaban teks tidak dianggap sebagai nilai skala.', 'icon' => 'bx-info-circle', 'color' => 'info']);
        }
        if ($recommendations->isEmpty()) {
            $recommendations->push(['title' => 'Dokumentasikan praktik baik', 'detail' => 'Jawaban skala seluruh peran telah lengkap dan indikator utama mencapai acuan pemantauan. Cocokkan dengan bukti kinerja sebelum menyusun tindak lanjut.', 'icon' => 'bx-badge-check', 'color' => 'success']);
        }
        $executiveNarrative = $l3Average !== null && $l4Average !== null ? 'Rerata indikator perubahan perilaku '.$this->format($l3Average).'/100 dan dampak pelatihan '.$this->format($l4Average).'/100. '.$fullyAssessed.' dari '.$participantTotal.' alumni memiliki jawaban skala lengkap pada seluruh peran.' : 'Hasil Level 3 atau Level 4 belum cukup untuk dirangkum. Lihat progres per peran dan lengkapi jawaban skala yang belum tersedia.';
        $scoreCategory = fn (?float $score) => $this->category($score);
        $payload = ['pelatihan' => ['nama' => $t->nama_pelatihan, 'angkatan' => $t->angkatan, 'bidang' => $t->bidang, 'program' => $t->program_evaluasi],
            'cakupan' => ['jumlah_alumni' => $participantTotal, 'perspektif_aktif' => $roleLabels->keys()->all(), 'pasangan_penilaian_terisi' => $startedPairs, 'target_pasangan' => $targetPairs, 'pasangan_skala_lengkap' => $completedPairs, 'per_perspektif' => $respondentsByRole->all()],
            'rerata_bagian' => $dashboardSections->mapWithKeys(fn ($s) => [$s['name'] => $s['average']])->all(),
            'indikator' => $indicators->map(fn ($i) => ['bagian' => $i['section'], 'label' => $i['label'], 'rerata' => $i['average'], 'respons' => $i['responses']])->all(),
            'metode' => ['skala_empat' => 'Tidak Sesuai=25, Kurang Sesuai=50, Sesuai=75, Sangat Sesuai=100', 'indeks' => 'Rerata indikator terisi; setiap indikator adalah rerata jawaban valid, termasuk data parsial.', 'kelengkapan' => 'Seluruh pertanyaan skala aktif per peran; isian profil dan teks tidak membentuk skor.', 'batas_kategori' => ['Kurang <=60', 'Cukup >60 sampai 79', 'Baik >79 sampai 89', 'Sangat Baik >89 sampai 100'], 'hambatan' => 'Skor tinggi menunjukkan hambatan lebih kuat; tidak dimasukkan ke indeks Level 3/4.']];

        return compact('roleLabels', 'activeRoles', 'roleQuestions', 'participantTotal', 'excludedParticipants', 'progress', 'participantProgress', 'respondentsByRole', 'coverageByRole', 'overallCoverage', 'completionCoverage', 'fullyAssessed', 'targetPairs', 'startedPairs', 'completedPairs', 'l3Average', 'l4Average', 'scoreCategory', 'averagesByRole', 'l3Indicators', 'l4Indicators', 'l3Category', 'l4Category', 'evaluationSections', 'dashboardSections', 'roleChartSeries', 'chartSections', 'impactDistribution', 'lowestL3', 'lowestL4', 'priorities', 'recommendations', 'executiveNarrative', 'latestAnswerAt', 'payload');
    }

    private function format(float $value): string
    {
        return number_format($value, 1, ',', '.');
    }
}
