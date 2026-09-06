<?php

namespace Tests\Unit;

use App\Services\OpenAiEvaluationSummaryService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiEvaluationSummaryServiceTest extends TestCase
{
    public function test_it_generates_anonymous_summary_with_gemini(): void
    {
        config()->set('services.ai.provider', 'gemini');
        config()->set('services.gemini.api_key', 'test-gemini-key');
        config()->set('services.gemini.model', 'gemini-test');
        Http::fake(['*generateContent' => Http::response(['candidates' => [[
            'content' => ['parts' => [['text' => json_encode([
                'conclusion' => 'Kesimpulan Gemini.', 'follow_up' => 'Tindak lanjut Gemini.',
            ])]]],
        ]]])]);
        $groups = collect([[
            'question' => (object) ['question_text' => 'Apa saran Anda?'],
            'responses' => collect([(object) ['note' => 'Perbaiki ketepatan waktu.']]),
        ]]);

        $result = app(OpenAiEvaluationSummaryService::class)->generate($groups);

        $this->assertSame('Kesimpulan Gemini.', $result['conclusion']);
        $this->assertSame('Tindak lanjut Gemini.', $result['follow_up']);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/models/gemini-test:generateContent')
            && $request->hasHeader('x-goog-api-key', 'test-gemini-key')
            && str_contains($request['contents'][0]['parts'][0]['text'], 'Perbaiki ketepatan waktu.')
            && ! str_contains($request['contents'][0]['parts'][0]['text'], 'nip_nik'));
    }
}
