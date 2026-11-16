<?php

namespace App\Services;

use App\Models\EvaluationResultL34;
use App\Models\Participant;
use App\Models\PostEvaluationInterview;
use App\Models\PostEvaluationSetting;
use App\Models\Question;
use App\Models\Training;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PostEvaluationService
{
    public const INDICATORS = ['Jangka panjang', 'Ketercapaian tujuan', 'Manfaat hasil pelatihan'];

    public const WEIGHTS = [20, 20, 20, 30, 10];

    public const CRITERIA = ['Target jangka menengah', 'Target jangka panjang', 'Ketercapaian tujuan', 'Manfaat hasil pelatihan', 'Teknik komunikasi'];

    public const SCORE_GUIDES = [
        [
            ['range' => 'nilai ≤60', 'description' => 'Tidak ada target jangka menengah yang tercapai'],
            ['range' => 'nilai >60 sampai 79', 'description' => 'Sebagian kecil target jangka menengah tercapai'],
            ['range' => 'nilai >79 sampai 89', 'description' => 'Sebagian besar target jangka menengah tercapai'],
            ['range' => 'nilai >89 sampai 100', 'description' => 'Seluruh target tercapai'],
        ],
        [
            ['range' => 'nilai ≤60', 'description' => 'Tidak ada target jangka panjang yang tercapai'],
            ['range' => 'nilai >60 sampai 79', 'description' => 'Sebagian kecil target jangka panjang tercapai'],
            ['range' => 'nilai >79 sampai 89', 'description' => 'Sebagian besar target jangka panjang tercapai'],
            ['range' => 'nilai >89 sampai 100', 'description' => 'Seluruh target tercapai'],
        ],
        [
            ['range' => 'nilai ≤60', 'description' => 'Tujuan proper tidak tercapai, proper tidak efektif'],
            ['range' => 'nilai >60 sampai 79', 'description' => 'Sebagian kecil tujuan proper tercapai, proper kurang efektif'],
            ['range' => 'nilai >79 sampai 89', 'description' => 'Sebagian besar tujuan proper tercapai, proper cukup efektif'],
            ['range' => 'nilai >89 sampai 100', 'description' => 'Seluruh tujuan proper tercapai, proper efektif'],
        ],
        [
            ['range' => 'nilai ≤60', 'description' => 'Manfaat hanya dirasakan unit kerja'],
            ['range' => 'nilai >60 sampai 79', 'description' => 'Manfaat dirasakan Perangkat Daerah'],
            ['range' => 'nilai >79 sampai 89', 'description' => 'Manfaat dirasakan beberapa Perangkat Daerah / sebagian masyarakat'],
            ['range' => 'nilai >89 sampai 100', 'description' => 'Manfaat dirasakan sebagian besar Perangkat Daerah / sebagian masyarakat'],
        ],
        [
            ['range' => 'nilai ≤60', 'description' => 'Informasi yang diberikan kurang tepat / kurang lengkap'],
            ['range' => 'nilai >60 sampai 79', 'description' => 'Informasi yang diberikan cukup tepat / kurang lengkap'],
            ['range' => 'nilai >79 sampai 89', 'description' => 'Informasi yang diberikan tepat / lengkap'],
            ['range' => 'nilai >89 sampai 100', 'description' => 'Informasi yang diberikan sangat tepat / sangat lengkap'],
        ],
    ];

    public const CATEGORIES = ['Kurang (<=60)', 'Cukup (>60-79)', 'Baik (>79-89)', 'Sangat Baik (>89-100)'];

    public function manages(User $u, Training $t): bool
    {
        return $u->role === 'superadmin';
    }

    public function teamIds(User $u, Training $t)
    {
        return DB::table('post_evaluation_teams')->join('post_evaluation_team_user', 'team_id', '=', 'post_evaluation_teams.id')->where('training_id', $t->id)->where('user_id', $u->id)->pluck('team_id');
    }

    public function authorize(User $u, Training $t, bool $manager = false): void
    {
        abort_if($u->role === 'admin_bidang', 403);
        abort_unless($this->manages($u, $t) || (! $manager && $this->teamIds($u, $t)->isNotEmpty()), 403);
    }

    public function setting(Training $t): ?PostEvaluationSetting
    {
        return PostEvaluationSetting::where('training_id', $t->id)->first();
    }

    public function questions(Training $t)
    {
        return collect($t->evaluationRoles())->flatMap(fn ($r) => Question::forTraining($t, 'l34_'.$r)->orderBy('id')->get());
    }

    public function rubric(Training $t): array
    {
        return $this->setting($t)?->rubric ?: array_map(fn ($l, $w, $i) => ['label' => $l, 'weight' => $w, 'guides' => array_column(self::SCORE_GUIDES[$i], 'description')], self::CRITERIA, self::WEIGHTS, range(0, 4));
    }

    public function average(array $values): ?float
    {
        $values = array_values(array_filter($values, fn ($v) => $v !== null));

        return $values ? round(array_sum($values) / count($values), 4) : null;
    }

    public function score($result, array $mapping): ?float
    {
        if (! $result) {
            return null;
        }
        if ($mapping['scale'] === 'hundred') {
            return $result->score !== null && is_numeric($result->score) && $result->score >= 0 && $result->score <= 100 ? (float) $result->score : null;
        }
        $raw = mb_strtolower(trim((string) $result->note));
        $v = ['tidak sesuai' => 1, 'kurang sesuai' => 2, 'sesuai' => 3, 'sangat sesuai' => 4][$raw] ?? (is_numeric($raw) ? (float) $raw : null);

        return in_array($v, [1, 2, 3, 4, 1.0, 2.0, 3.0, 4.0], true) ? $v * 25.0 : null;
    }

    public function people(Training $t, ?User $user = null): array
    {
        $questions = $this->questions($t)->keyBy('id');
        $mappings = collect($this->setting($t)?->mappings ?? [])->filter(fn ($m) => $questions->has($m['question_id']) && $questions[$m['question_id']]->category === 'l34_'.$m['role']);
        $participants = Participant::where('training_id', $t->id)->where('registration_status', 'approved')->orderBy('name')->get();
        $interviews = PostEvaluationInterview::where('training_id', $t->id)->get()->keyBy('participant_id');
        if ($user && ! $this->manages($user, $t)) {
            $teams = $this->teamIds($user, $t);
            $participants = $participants->filter(fn ($p) => $interviews->has($p->id) && $teams->contains($interviews[$p->id]->team_id));
        }
        $results = EvaluationResultL34::with('question')->where('training_id', $t->id)->whereIn('participant_id', $participants->pluck('id'))->whereIn('question_id', $questions->keys())->whereIn('evaluator_role', $t->evaluationRoles())->orderBy('id')->get()->groupBy('participant_id');
        $rubric = $this->rubric($t);
        $teams = DB::table('post_evaluation_teams')->where('training_id', $t->id)->pluck('name', 'id');

        return $participants->map(function ($p) use ($t, $results, $interviews, $mappings, $rubric, $teams) {
            $answers = $results->get($p->id, collect());
            $iv = $interviews->get($p->id);
            $survey = [];
            $coverage = [];
            foreach (range(0, 2) as $i) {
                $values = $mappings->where('indicator', $i)->map(fn ($m) => $this->score($answers->where('question_id', $m['question_id'])->where('evaluator_role', $m['role'])->last(), $m))->all();
                $survey[] = $this->average($values);
                $coverage[] = ['expected' => count($values), 'filled' => count(array_filter($values, fn ($v) => $v !== null))];
            }
            $scores = $iv?->scores ?? array_fill(0, 5, null);
            $weighted = in_array(null, $scores, true) ? null : round(array_sum(array_map(fn ($v, $r) => $v * $r['weight'] / 100, $scores, $rubric)), 4);

            return ['participant' => $p, 'interview' => $iv, 'team' => $teams[$iv?->team_id] ?? 'Belum dibagi', 'answers' => $answers,
                'roles' => collect($t->evaluationRoles())->mapWithKeys(fn ($r) => [$r => $answers->where('evaluator_role', $r)->isNotEmpty()])->all(),
                'survey' => $survey, 'coverage' => $coverage, 'survey_average' => $this->average($survey), 'scores' => $scores,
                'interview_average' => $this->average(array_slice($scores, 1, 3)), 'weighted' => $weighted,
                'partial' => count(array_filter($survey, fn ($v) => $v !== null)) < 3 || collect($coverage)->contains(fn ($c) => $c['filled'] < $c['expected'])];
        })->values()->all();
    }

    public function histogram(array $values): array
    {
        $counts = [0, 0, 0, 0];
        $missing = 0;
        foreach ($values as $v) {
            if ($v === null) {
                $missing++;

                continue;
            }$counts[$v <= 60 ? 0 : ($v <= 79 ? 1 : ($v <= 89 ? 2 : 3))]++;
        }

        return ['counts' => $counts, 'missing' => $missing, 'valid' => count($values) - $missing, 'average' => $this->average($values)];
    }

    public function summary(Training $t, array $people): array
    {
        $slides = [['title' => 'Capaian jangka menengah dan panjang', 'series' => [
            ['label' => 'Jangka menengah', 'data' => $this->histogram(array_column(array_column($people, 'scores'), 0))],
            ['label' => 'Jangka panjang', 'data' => $this->histogram(array_column(array_column($people, 'scores'), 1))]]]];
        foreach (self::INDICATORS as $i => $label) {
            $slides[] = ['title' => $label, 'series' => [
                ['label' => 'Survei', 'data' => $this->histogram(array_column(array_column($people, 'survey'), $i))],
                ['label' => 'Wawancara', 'data' => $this->histogram(array_column(array_column($people, 'scores'), $i + 1))]]];
        }
        $survey = $this->histogram(array_column($people, 'survey_average'));
        $interview = $this->histogram(array_column($people, 'interview_average'));
        $slides[] = ['title' => 'Nilai keseluruhan: rata-rata tiga indikator', 'series' => [['label' => 'Survei', 'data' => $survey], ['label' => 'Wawancara', 'data' => $interview]]];
        $completed = count(array_filter($people, fn ($p) => $p['interview']?->status === 'completed'));
        $partial = count(array_filter($people, fn ($p) => $p['partial']));
        $total = count($people);
        $low = $slides[0]['series'][1]['data']['counts'][0];

        return ['training' => $t->nama_pelatihan, 'program' => $t->program_evaluasi, 'total' => $total, 'completed' => $completed, 'partial' => $partial,
            'roles' => collect($t->evaluationRoleLabels())->map(fn ($l, $r) => ['label' => $l, 'filled' => count(array_filter($people, fn ($p) => $p['roles'][$r]))])->values()->all(),
            'survey' => $survey, 'interview' => $interview, 'slides' => $slides, 'categories' => self::CATEGORIES,
            'conclusions' => ["Data mencakup $total alumni; $completed wawancara selesai.", "Nilai survei tersedia untuk {$survey['valid']} alumni dan wawancara untuk {$interview['valid']} alumni. Rerata memakai indikator terisi; $partial hasil survei masih parsial atau belum lengkap.", 'Survei dan wawancara adalah sumber berbeda. Perbandingan ini belum membuktikan perubahan dampak dari waktu ke waktu.'],
            'recommendations' => [$low ? "Konfirmasi bukti capaian $low alumni dengan nilai jangka panjang <=60 dan rencanakan pendampingan." : 'Tinjau bukti capaian sebelum mereplikasi praktik baik.', $completed < $total ? 'Selesaikan wawancara draf dan validasi bukti bersama tim.' : 'Validasi hasil dan tetapkan tindak lanjut bersama unit kerja alumni.', 'Lengkapi indikator yang belum terpetakan; data kosong tidak dihitung sebagai nol.'],
            'generated_at' => now()->toIso8601String()];
    }
}
