<?php

namespace App\Services;

use App\Models\EvaluationResultL34;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Collection;

class PostEvaluationSurveySummaryService
{
    private const JOB_BEFORE = ['jabatan saat pelatihan', 'jabatan alumni saat pelatihan', 'jabatan (saat pelatihan)'];
    private const JOB_AFTER = ['jabatan saat ini', 'jabatan alumni saat ini', 'jabatan (saat ini)'];

    public function __construct(private PostEvaluationService $service) {}

    private function profileAnswer($answers, array $labels): ?string
    {
        foreach (['mandiri', 'atasan'] as $role) {
            $result = $answers->where('evaluator_role', $role)->filter(fn ($a) => in_array($this->service->normalizeText($a->question?->question_text ?? ''), $labels, true) && filled($a->note))->sortBy('id')->last();
            if ($result) {
                return trim($result->note);
            }
        }

        return null;
    }

    public function build(Training $training, array $people): array
    {
        return $this->overviewData($training, $people)
            + $this->comparisonData($training, $people, $this->definitions($training))
            + $this->agencyData(collect($people)->pluck('participant.instansi'));
    }

    public function overview(Training $training, User $user): array
    {
        $questions = $this->service->questions($training)->filter(fn ($question) =>
            in_array($this->service->normalizeText($question->question_text), [...self::JOB_BEFORE, ...self::JOB_AFTER], true)
                || $this->isMethodQuestion($question->question_text));

        return $this->overviewData($training, $this->surveyPeople($training, $user, $questions->pluck('id')));
    }

    public function agencies(Training $training, User $user): array
    {
        return $this->agencyData($this->service->visibleParticipants($training, $user)->pluck('instansi'));
    }

    private function surveyPeople(Training $training, User $user, Collection $questionIds): array
    {
        $participants = $this->service->visibleParticipants($training, $user)
            ->with('alumniProfile')
            ->get(['id']);
        $answers = EvaluationResultL34::with('question')->where('training_id', $training->id)
            ->whereIn('participant_id', $participants->pluck('id'))->whereIn('question_id', $questionIds)
            ->whereIn('evaluator_role', $training->evaluationRoles())->orderBy('id')->get()->groupBy('participant_id');

        return $participants->map(fn ($participant) => [
            'participant' => $participant,
            'answers' => $answers->get($participant->id, collect()),
        ])->all();
    }

    private function isMethodQuestion(string $label): bool
    {
        return preg_match('/^metode\s+pelaksanaan(?=\s|:|$)/iu', trim(preg_replace('/<[^>]+>/u', ' ', $label))) === 1;
    }

    private function definitions(Training $training): array
    {
        $questions = $this->service->questions($training);
        $definitions = [];
        foreach ($questions as $question) {
            if (str_starts_with($this->service->normalizeText($question->sub_category ?? ''), 'data diri')) {
                continue;
            }
            $eligible = $this->service->supportsFourPoint($training) ? $this->service->fourPointQuestion($question) : $question->type === 'slider';
            if (! $eligible) {
                continue;
            }
            $section = trim($question->sub_category ?: 'Bagian Lainnya');
            $key = $this->service->normalizeText($section).'|'.$this->service->normalizeText($question->question_text).'|'.($question->source_occurrence ?? 1);
            $definitions[$key] ??= ['label' => $question->question_text, 'section' => $section, 'ids' => []];
            $role = str_replace('l34_', '', $question->category);
            $definitions[$key]['ids'][$role][] = $question->id;
        }

        return $definitions;
    }

    private function overviewData(Training $training, array $people): array
    {
        $job = ['Berubah' => 0, 'Tetap' => 0];
        $methods = [];
        $methodRespondents = 0;
        $multipleMethods = false;
        foreach ($people as $person) {
            $p = $person['participant'];
            $answers = $person['answers'];
            $profile = $p->alumniProfile;
            $before = $profile?->training_id == $training->id ? $profile?->pos_during_training : null;
            $after = $profile?->training_id == $training->id ? $profile?->pos_current : null;
            $before = filled($before) ? $before : $this->profileAnswer($answers, self::JOB_BEFORE);
            $after = filled($after) ? $after : $this->profileAnswer($answers, self::JOB_AFTER);
            if (filled($before) && filled($after)) {
                $job[$this->service->normalizeText($before) === $this->service->normalizeText($after) ? 'Tetap' : 'Berubah']++;
            }
            $method = null;
            foreach (['mandiri', 'atasan'] as $role) {
                $method = $answers->where('evaluator_role', $role)->filter(fn ($a) => $this->isMethodQuestion($a->question?->question_text ?? '') && filled($a->note))->sortBy('id')->last();
                if ($method) {
                    break;
                }
            }
            if ($method) {
                $decoded = json_decode($method->note, true);
                $values = is_array($decoded) ? $decoded : [$method->note];
                $labels = [];
                foreach ($values as $value) {
                    if (! is_scalar($value)) {
                        continue;
                    }
                    $key = $this->service->normalizeText((string) $value);
                    if ($key === '') {
                        continue;
                    }
                    $labels[] = match ($key) {
                        'klasikal','kalsikan','kalsikal','klasikan' => 'Klasikal','blended learning','blended' => 'Blended Learning','full learning','daring','online' => 'Pembelajaran Jarak Jauh',default => 'Metode lainnya'
                    };
                }
                $labels = array_unique($labels);
                if ($labels) {
                    $methodRespondents++;
                    if (count($labels) > 1) {
                        $multipleMethods = true;
                    }
                }
                foreach ($labels as $label) {
                    $methods[$label] = ($methods[$label] ?? 0) + 1;
                }
            }
        }

        return ['total' => count($people), 'job_changes' => $job, 'job_respondents' => array_sum($job), 'methods' => $methods,
            'method_respondents' => $methodRespondents, 'multiple_methods' => $multipleMethods];
    }

    private function comparisonData(Training $training, array $people, array $definitions): array
    {
        $sections = [];
        foreach ($definitions as $definition) {
            $row = ['label' => $definition['label'], 'roles' => []];
            foreach ($training->evaluationRoleLabels() as $role => $label) {
                $values = [];
                foreach ($people as $person) {
                    $answer = $person['answers']->where('evaluator_role', $role)->whereIn('question_id', $definition['ids'][$role] ?? [])->sortBy('id')->last();
                    $score = $this->service->score($answer, ['scale' => $this->service->supportsFourPoint($training) ? 'four' : 'hundred'], $training);
                    if ($score !== null) {
                        $values[] = $this->service->supportsFourPoint($training) ? $score / 25 : $score;
                    }
                }
                $row['roles'][$role] = ['label' => $role === 'mandiri' ? 'Alumni' : $label, 'average' => $this->service->average($values), 'count' => count($values)];
            }
            $sections[$definition['section']][] = $row;
        }
        $order = ['Perubahan Sikap Perilaku', 'Perubahan Perilaku', 'Dampak Pelatihan', 'Faktor Pendukung Aktualisasi', 'Faktor Penghambat Aktualisasi', 'Faktor Pendukung Aksi Perubahan', 'Faktor Penghambat Aksi Perubahan', 'Faktor Pendukung Proyek Perubahan', 'Faktor Penghambat Proyek Perubahan'];
        uksort($sections, fn ($a, $b) => (array_search($a, $order, true) === false ? 999 : array_search($a, $order, true)) <=> (array_search($b, $order, true) === false ? 999 : array_search($b, $order, true)));

        return ['sections' => $sections, 'scale' => $this->service->supportsFourPoint($training) ? 4 : 100];
    }

    private function agencyData(Collection $names): array
    {
        $agencies = $names->map(fn ($name) => trim((string) $name) ?: 'Belum diisi')->countBy()->sortDesc();

        return ['agencies' => $agencies->all(), 'total' => $names->count()];
    }
}
