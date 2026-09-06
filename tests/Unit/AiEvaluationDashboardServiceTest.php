<?php

namespace Tests\Unit;

use App\Services\AiEvaluationDashboardService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiEvaluationDashboardServiceTest extends TestCase
{
    public function test_it_generates_executive_dashboard_analysis(): void
    {
        config()->set('services.ai.provider', 'gemini'); config()->set('services.gemini.api_key', 'test-key');
        config()->set('services.gemini.model', 'gemini-test');
        $draft = collect(AiEvaluationDashboardService::FIELDS)->mapWithKeys(fn ($field) => [$field => 'Draf '.$field])->all();
        Http::fake(['*generateContent' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($draft)]]]]]])]);

        $result = app(AiEvaluationDashboardService::class)->generate('Level 1 dan 2', ['peserta' => 20, 'l1_average' => 86]);

        $this->assertSame(AiEvaluationDashboardService::FIELDS, array_keys($result));
        Http::assertSent(fn ($request) => str_contains($request['contents'][0]['parts'][0]['text'], 'l1_average'));
    }
}
