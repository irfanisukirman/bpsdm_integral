<?php

namespace Tests\Unit;

use App\Services\AiActivityReportService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiActivityReportServiceTest extends TestCase
{
    public function test_it_generates_all_report_narratives_with_anonymous_aggregate_data(): void
    {
        config()->set('services.ai.provider', 'gemini');
        config()->set('services.gemini.api_key', 'test-key');
        config()->set('services.gemini.model', 'gemini-test');
        $draft = collect(AiActivityReportService::FIELDS)->mapWithKeys(fn ($field) => [$field => 'Draf '.$field])->all();
        Http::fake(['*generateContent' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($draft)]]]]]])]);

        $result = app(AiActivityReportService::class)->generate(['pelatihan' => ['nama' => 'Pelatihan Uji'], 'statistik' => ['peserta' => 20]]);

        $this->assertSame(AiActivityReportService::FIELDS, array_keys($result));
        Http::assertSent(fn ($request) => str_contains($request['contents'][0]['parts'][0]['text'], 'Pelatihan Uji')
            && ! str_contains($request['contents'][0]['parts'][0]['text'], 'participant_name'));
    }
}
