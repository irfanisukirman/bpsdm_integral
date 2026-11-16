<?php

namespace App\Services;

use App\Models\Bidang;
use App\Models\EvaluationResultL34;
use App\Models\Question;
use App\Support\EvaluationProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManagerialQuestionBankSyncService
{
    public function __construct(private PostEvaluationImportService $normalizer) {}

    public function catalog(): array
    {
        return json_decode(file_get_contents(database_path('data/managerial_questions_2026_11_15.json')), true, 512, JSON_THROW_ON_ERROR);
    }

    private function key(string $text, string $role): string
    {
        $key = $this->normalizer->normalize($text);
        if ($role === 'l34_mandiri' && $key === 'nama instansi alumni') {
            return 'nama instansi';
        }

        return $key;
    }

    public function sync(bool $apply = false, ?array $catalog = null): array
    {
        $catalog ??= $this->catalog();

        return DB::transaction(function () use ($catalog, $apply) {
            $programs = array_keys($catalog['programs']);
            $aliases = collect($programs)->flatMap(fn ($p) => EvaluationProgram::aliases($p))->unique()->values()->all();
            $query = Question::whereIn(DB::raw('UPPER(program_evaluasi)'), $aliases)->whereIn('category', ['l34_mandiri', 'l34_atasan', 'l34_rekan'])->orderBy('id');
            if ($apply) {
                $query->lockForUpdate();
            }
            $existing = $query->get();
            $references = EvaluationResultL34::whereIn('question_id', $existing->pluck('id'))->select('question_id', DB::raw('count(*) as total'))->groupBy('question_id')->pluck('total', 'question_id');
            $used = [];
            $updates = [];
            $stats = [];
            $bidang = Bidang::resolve(Bidang::DEFAULTS[3]);
            foreach ($catalog['programs'] as $program => $bundle) {
                $stats[$program] = ['active' => count($bundle['questions']), 'created' => 0, 'updated' => 0, 'unchanged' => 0, 'archived' => 0, 'archived_with_answers' => 0];
                foreach ($bundle['questions'] as $definition) {
                    $data = $definition + ['bidang' => $bidang, 'training_type' => $bidang, 'training_id' => null, 'program_evaluasi' => $program, 'metode' => 'semua', 'is_active' => true];
                    $key = $this->key($data['question_text'], $data['category']);
                    $match = $existing->filter(fn ($q) => ! isset($used[$q->id]) && $q->program_evaluasi === $program && $q->category === $data['category'] && $this->key($q->question_text, $q->category) === $key)
                        ->sortByDesc(fn ($q) => [(int) ($references[$q->id] ?? 0), $q->source_column === $data['source_column'] ? 1 : 0, $q->is_active ? 1 : 0, -$q->id])->first();
                    if ($match) {
                        $used[$match->id] = true;
                        $copy = clone $match;
                        $copy->fill($data);
                        $changed = $copy->isDirty();
                        $stats[$program][$changed ? 'updated' : 'unchanged']++;
                    } else {
                        $stats[$program]['created']++;
                    }
                    $updates[] = ['id' => $match?->id, 'data' => $data];
                }
            }
            $archived = $existing->reject(fn ($q) => isset($used[$q->id]));
            foreach ($archived as $q) {
                if ($q->is_active) {
                    $stats[$q->program_evaluasi]['archived']++;
                    if (isset($references[$q->id])) {
                        $stats[$q->program_evaluasi]['archived_with_answers']++;
                    }
                }
            }
            $backup = null;
            if ($apply) {
                $backup = 'question-bank-backups/'.now()->format('Ymd-His').'-'.Str::uuid().'.json';
                $snapshot = ['created_at' => now()->toIso8601String(), 'catalog_version' => $catalog['version'], 'source_hashes' => collect($catalog['programs'])->map(fn ($bundle) => $bundle['sha256'])->all(), 'questions' => $existing->map(fn ($q) => $q->getAttributes())->all(), 'planned_stats' => $stats];
                if (! Storage::disk('local')->put($backup, json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
                    throw new \RuntimeException('Gagal membuat cadangan bank soal; sinkronisasi dibatalkan.');
                }
                Question::whereIn('id', $archived->pluck('id'))->update(['is_active' => false]);
                foreach ($updates as $update) {
                    if ($update['id']) {
                        Question::findOrFail($update['id'])->fill($update['data'])->save();
                    } else {
                        Question::create($update['data']);
                    }
                }
            }

            return ['applied' => $apply, 'stats' => $stats, 'backup' => $backup];
        });
    }
}
