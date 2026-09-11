<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiEvaluationSummaryService
{
    private const EVALUATION_INSTRUCTIONS = 'Anda adalah analis evaluasi pelatihan instansi pemerintah. Susun draf formal, objektif, ringkas, tidak melebih-lebihkan data, tidak menyebut identitas peserta, dan gunakan Bahasa Indonesia baku. Kesimpulan harus mewakili pola seluruh jawaban. Tindak lanjut harus konkret, realistis, dan menanggapi temuan.';

    public function provider(): string { return strtolower((string) config('services.ai.provider', 'openai')); }
    public function model(): string { return (string) config('services.'.$this->provider().'.model'); }
    public function configured(): bool
    {
        return in_array($this->provider(), ['openai', 'gemini'], true)
            && filled(config('services.'.$this->provider().'.api_key')) && filled($this->model());
    }

    public function generate(Collection $responseGroups): array
    {
        $feedback = $responseGroups->map(fn (array $group) => [
            'pertanyaan' => (string) $group['question']->question_text,
            'jawaban' => $group['responses']->pluck('note')->filter()->map(fn ($note) => mb_substr(trim((string) $note), 0, 2000))->values()->all(),
        ])->filter(fn (array $group) => $group['jawaban'] !== [])->values()->all();
        return $this->generateStructured(self::EVALUATION_INSTRUCTIONS, ['masukan_anonim' => $feedback], $this->schemaFor(['conclusion', 'follow_up']));
    }

    public function generateStructured(string $instructions, array $payload, array $schema): array
    {
        if (! $this->configured()) throw new RuntimeException('Provider AI belum dikonfigurasi dengan benar pada file .env.');
        $result = $this->provider() === 'gemini'
            ? $this->generateWithGemini($instructions, $payload, $schema)
            : $this->generateWithOpenAi($instructions, $payload, $schema);
        if (! is_array($result)) throw new RuntimeException('Format jawaban AI tidak sesuai. Silakan coba kembali.');
        return $result;
    }

    public function schemaFor(array $fields): array
    {
        return ['type' => 'object', 'properties' => collect($fields)->mapWithKeys(fn ($field) => [$field => ['type' => 'string']])->all(),
            'required' => array_values($fields), 'additionalProperties' => false];
    }

    private function generateWithOpenAi(string $instructions, array $payload, array $schema): ?array
    {
        $response = Http::asJson()->withToken(config('services.openai.api_key'))->timeout((int) config('services.openai.timeout', 60))
            ->post(rtrim(config('services.openai.base_url'), '/').'/responses', [
                'model' => $this->model(), 'instructions' => $instructions,
                'input' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'text' => ['format' => ['type' => 'json_schema', 'name' => 'draf_laporan_terstruktur', 'strict' => true, 'schema' => $schema]],
            ]);
        if (! $response->successful()) throw new RuntimeException($response->json('error.message') ?: 'Layanan OpenAI tidak dapat memproses permintaan.');
        $text = $response->json('output_text');
        if (! is_string($text) || $text === '') {
            $content = collect($response->json('output', []))->flatMap(fn ($output) => $output['content'] ?? []);
            $text = data_get($content->firstWhere('type', 'output_text'), 'text');
        }
        return is_string($text) ? json_decode($text, true) : null;
    }

    private function generateWithGemini(string $instructions, array $payload, array $schema): ?array
    {
        $model = preg_replace('#^models/#', '', $this->model());
        $response = Http::asJson()->withHeaders(['x-goog-api-key' => config('services.gemini.api_key')])->timeout((int) config('services.gemini.timeout', 60))
            ->post(rtrim(config('services.gemini.base_url'), '/').'/models/'.$model.':generateContent', [
                'contents' => [['role' => 'user', 'parts' => [['text' => $instructions."\n\nData terstruktur:\n".json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]]]],
                'generationConfig' => ['responseMimeType' => 'application/json', 'responseJsonSchema' => $schema],
            ]);
        if (! $response->successful()) throw new RuntimeException($response->json('error.message') ?: 'Layanan Gemini tidak dapat memproses permintaan.');
        $text = $response->json('candidates.0.content.parts.0.text');
        return is_string($text) ? json_decode($text, true) : null;
    }
}