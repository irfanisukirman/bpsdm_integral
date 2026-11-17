<?php

namespace App\Services;

class PostEvaluationNarrativeService
{
    /** Notes remain private; only their fingerprint belongs in a public summary. */
    public function notes(array $people): array
    {
        $identities = [];
        foreach ($people as $person) {
            foreach (['name', 'nip_nik', 'email'] as $field) {
                $value = trim((string) ($person['participant']->{$field} ?? ''));
                if (mb_strlen($value) >= 3) $identities[] = $value;
            }
        }
        usort($identities, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        $notes = [];
        foreach (array_values($people) as $index => $person) {
            foreach (PostEvaluationService::CRITERIA as $criterion => $label) {
                $text = trim(strip_tags((string) ($person['interview']?->notes[$criterion] ?? '')));
                if ($text === '') continue;
                $text = str_ireplace($identities, '[identitas disamarkan]', $text);
                $text = preg_replace('/[\w.+-]+@[\w.-]+\.[a-z]{2,}|\b\d{10,}\b/iu', '[identitas disamarkan]', $text);
                $notes[] = ['peserta' => 'Peserta '.($index + 1), 'kriteria' => $label,
                    'status_wawancara' => $person['interview']?->status ?? 'draft', 'catatan' => $text];
            }
        }
        return $notes;
    }

    public function metadata(array $people): array
    {
        $notes = $this->notes($people);
        return ['narrative_version' => 2, 'evaluator_note_count' => count($notes),
            'evaluator_notes_hash' => hash('sha256', json_encode($notes, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)),
            'interview_coverage' => ['without_scores' => count(array_filter($people, fn ($p) => count(array_filter($p['scores'], fn ($v) => $v !== null)) === 0)),
                'partial_scores' => count(array_filter($people, fn ($p) => ($n = count(array_filter($p['scores'], fn ($v) => $v !== null))) > 0 && $n < 5))]];
    }
}
