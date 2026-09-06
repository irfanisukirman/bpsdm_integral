<?php

namespace App\Services;

use RuntimeException;

class AiActivityReportService
{
    public const FIELDS = ['background', 'legal_basis', 'objectives', 'implementation', 'achievements', 'constraints', 'follow_up', 'conclusion', 'recommendations'];

    public function __construct(private OpenAiEvaluationSummaryService $ai) {}

    public function provider(): string { return $this->ai->provider(); }
    public function model(): string { return $this->ai->model(); }

    public function generate(array $reportData): array
    {
        $instructions = <<<'PROMPT'
Anda menyusun draf laporan kegiatan pelatihan instansi pemerintah dalam Bahasa Indonesia baku. Gunakan hanya data yang diberikan. Jangan mengarang angka, kejadian, kendala, hasil, peraturan, nomor surat, atau dasar hukum. Setiap bagian harus berupa narasi formal yang siap ditelaah admin, ringkas tetapi komprehensif, dan konsisten antarbab. Jika dasar hukum atau kendala tidak tersedia, tuliskan penanda yang sopan bahwa bagian tersebut perlu dilengkapi admin. Jangan menyebut nama atau identitas peserta. Bedakan kesimpulan (ringkasan hasil) dan rekomendasi (saran perbaikan). Tindak lanjut harus konkret berdasarkan temuan yang tersedia.
PROMPT;
        $result = $this->ai->generateStructured($instructions, $reportData, $this->ai->schemaFor(self::FIELDS));
        foreach (self::FIELDS as $field) {
            if (blank($result[$field] ?? null)) throw new RuntimeException('AI tidak melengkapi seluruh bagian narasi laporan.');
            $result[$field] = mb_substr(trim((string) $result[$field]), 0, 30000);
        }
        return $result;
    }
}
