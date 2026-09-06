<?php

namespace Tests\Unit;

use App\Services\OpenAiEvaluationSummaryService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAiEvaluationSummaryServiceTest extends TestCase
{
    public function test_it_generates_structured_anonymous_summary(): void
    {
        config()->set('services.ai.provider', 'openai');
        config()->set('services.openai.api_key', 'test-key');
        config()->set('services.openai.model', 'test-model');
        Http::fake(['*/responses' => Http::response([
            'output_text' => json_encode(['conclusion' => 'Kesimpulan uji.', 'follow_up' => 'Tindak lanjut uji.']),
        ])]);
        $groups = collect([[
            'question' => (object) ['question_text' => 'Apa saran Anda?'],
            'responses' => collect([(object) ['note' => 'Tambahkan studi kasus.']]),
        ]]);

        $result = app(OpenAiEvaluationSummaryService::class)->generate($groups);

        $this->assertSame('Kesimpulan uji.', $result['conclusion']);
        $this->assertSame('Tindak lanjut uji.', $result['follow_up']);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.openai.com/v1/responses'
            && $request['model'] === 'test-model'
            && str_contains($request['input'], 'Tambahkan studi kasus.')
            && ! str_contains($request['input'], 'nip_nik'));
    }
}
