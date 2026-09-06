<?php

namespace App\Services;

use RuntimeException;

class AiEvaluationDashboardService
{
    public const FIELDS = ['executive_summary', 'key_findings', 'priority_actions', 'data_caution'];

    public function __construct(private OpenAiEvaluationSummaryService $ai) {}
    public function provider(): string { return $this->ai->provider(); }
    public function model(): string { return $this->ai->model(); }

    public function generate(string $level, array $aggregates): array
    {
        $instructions = 'Anda adalah analis evaluasi pelatihan instansi pemerintah untuk bahan pimpinan. Analisis dashboard '.$level.' hanya berdasarkan statistik agregat yang diberikan. Jangan mengarang angka, sebab-akibat, atau identitas. Gunakan Bahasa Indonesia baku, ringkas, jelas, dan berorientasi keputusan. Jika cakupan data rendah, nyatakan keterbatasannya secara tegas. key_findings dan priority_actions ditulis sebagai butir bernomor dalam satu string. Bedakan temuan dari tindakan. data_caution menjelaskan tingkat keterwakilan dan batas interpretasi.';
        $result = $this->ai->generateStructured($instructions, ['level' => $level, 'data_agregat' => $aggregates], $this->ai->schemaFor(self::FIELDS));
        foreach (self::FIELDS as $field) {
            if (blank($result[$field] ?? null)) throw new RuntimeException('AI tidak melengkapi seluruh bagian analisis dashboard.');
            $result[$field] = mb_substr(trim((string) $result[$field]), 0, 12000);
        }
        return $result;
    }
}
