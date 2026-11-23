<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LevelOneEvaluationScores
{
    public function calculate(Collection $results, Collection $imports): array
    {
        $numeric = $results->whereNotNull('score');
        $categories = [];
        $indicators = collect();
        $usesImport = false;
        foreach (['penyelenggara', 'narasumber'] as $kind) {
            $batches = $imports->where('kind', $kind);
            $entries = $batches->flatMap(fn ($batch) => $batch->entries);
            $importedAverage = $entries->pluck('average')->avg();
            if ($importedAverage !== null) {
                $categories[$kind] = $importedAverage;
                $usesImport = true;
                $byLabel = [];
                foreach ($batches as $batch) {
                    foreach ($batch->indicators as $index => $indicator) {
                        if ($indicator['kind'] !== 'score') continue;
                        $label = $indicator['label'];
                        $key = Str::lower(Str::squish($label));
                        $scores = collect($batch->entries)->map(fn ($entry) => $entry['values'][$index] ?? null)
                            ->filter(fn ($value) => $value !== null);
                        if ($scores->isEmpty()) continue;
                        $byLabel[$key] ??= ['label' => $label, 'sum' => 0, 'rows' => 0];
                        $byLabel[$key]['sum'] += $scores->sum();
                        $byLabel[$key]['rows'] += $scores->count();
                    }
                }
                foreach ($byLabel as $indicator) {
                    $indicators->push([
                        'label' => $indicator['label'], 'average' => round($indicator['sum'] / $indicator['rows'], 1),
                        'responses' => $kind === 'penyelenggara' ? $indicator['rows'] : null,
                        'source' => 'impor', 'kind' => $kind,
                    ]);
                }
                continue;
            }

            $scores = $kind === 'penyelenggara' ? $numeric->whereNull('schedule_id') : $numeric->whereNotNull('schedule_id');
            $categories[$kind] = $scores->avg('score');
            foreach ($scores->filter(fn ($result) => $result->question)->groupBy('question_id') as $group) {
                $indicators->push([
                    'label' => $group->first()->question->question_text, 'average' => round($group->avg('score'), 1),
                    'responses' => $group->count(), 'source' => 'form', 'kind' => $kind,
                ]);
            }
        }

        $average = $usesImport ? collect($categories)->avg() : $numeric->avg('score');
        $organizerEntries = $imports->where('kind', 'penyelenggara')->flatMap(fn ($batch) => $batch->entries);
        $respondentIds = $results->pluck('participant_id')->merge($organizerEntries->pluck('linked_id'))
            ->filter()->map(fn ($id) => (int) $id)->unique();

        return [
            'average' => $average === null ? null : round($average, 1),
            'organizer' => $categories['penyelenggara'] === null ? null : round($categories['penyelenggara'], 1),
            'speaker' => $categories['narasumber'] === null ? null : round($categories['narasumber'], 1),
            'indicators' => $indicators->sortBy('average')->values(),
            'respondentIds' => $respondentIds,
            'usesImport' => $usesImport,
        ];
    }
}
