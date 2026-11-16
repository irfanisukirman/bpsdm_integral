<?php

namespace Tests\Feature;

use App\Models\AiGeneration;
use App\Models\Participant;
use App\Models\PostEvaluationInterview;
use App\Models\PostEvaluationSetting;
use App\Models\Training;
use App\Models\User;
use App\Services\PostEvaluationExposeService;
use App\Services\PostEvaluationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PostEvaluationAiExposeTest extends TestCase
{
    use RefreshDatabase;

    private function data(): array
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $training = Training::create(['nama_pelatihan' => 'Ekspose PKP Uji', 'bidang' => 'Bidang A', 'program_evaluasi' => 'PKP', 'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 10, 'jp' => 10, 'tgl_mulai' => '2024-01-01', 'tgl_selesai' => '2024-01-02', 'created_by' => $admin->id]);
        $p = Participant::create(['training_id' => $training->id, 'name' => 'NAMA RAHASIA', 'nip_nik' => '111122223333444455', 'registration_status' => 'approved']);
        $iv = PostEvaluationInterview::create(['training_id' => $training->id, 'participant_id' => $p->id, 'scores' => [80, 90, 70, 60, 100], 'notes' => ['CATATAN PRIVAT', '', '', '', ''], 'project' => 'JUDUL PROPER PRIVAT', 'status' => 'completed']);
        config()->set('services.ai.provider', 'gemini');
        config()->set('services.gemini.api_key', 'fake-key');
        config()->set('services.gemini.model', 'test-model');

        return [$admin, $training, $iv];
    }

    private function response(): array
    {
        $content = [];
        foreach (range(0, 4) as $i) {
            $content['discussion_'.$i] = 'Pembahasan AI panel '.$i.'. Data yang belum terisi perlu dilengkapi dan bukti capaian dikonfirmasi.';
        }
        $content['conclusion'] = 'Kesimpulan AI: hasil masih perlu dikonfirmasi dengan memperhatikan kelengkapan data.';
        foreach (range(1, 3) as $i) {
            $content['recommendation_'.$i] = 'Rekomendasi AI '.$i.': penyelenggara dan evaluator perlu melengkapi data serta memantau bukti capaian.';
        }

        return $content;
    }

    public function test_ai_generates_only_from_aggregates_and_is_used_in_expose_and_public_snapshot(): void
    {
        [$admin,$training,$iv] = $this->data();
        $content = $this->response();
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($content)]]]]]])]);
        $this->actingAs($admin)->post(route('post-evaluation.ai.generate', $training))->assertSessionHas('success')->assertRedirect();
        $generation = AiGeneration::where('feature', PostEvaluationExposeService::FEATURE)->firstOrFail();
        $this->assertSame('completed', $generation->status);
        Http::assertSent(function ($request) {
            $payload = $request['contents'][0]['parts'][0]['text'];

            return ! str_contains($payload, 'NAMA RAHASIA') && ! str_contains($payload, '111122223333444455') && ! str_contains($payload, 'CATATAN PRIVAT') && ! str_contains($payload, 'JUDUL PROPER PRIVAT') && str_contains($payload, 'bands');
        });
        $this->get(route('post-evaluation.expose', $training))->assertOk()->assertSee('Pembahasan AI panel 0')->assertSee('Rekomendasi AI 1');
        $this->post(route('post-evaluation.publish', $training), ['action' => 'publish'])->assertRedirect();
        $setting = PostEvaluationSetting::where('training_id', $training->id)->firstOrFail();
        $this->get(route('post-evaluation.public', $setting->public_token))->assertOk()->assertSee('Pembahasan AI panel 0');
        $iv->update(['scores' => [10, 20, 30, 40, 50]]);
        $service = app(PostEvaluationService::class);
        $summary = $service->summary($training, $service->people($training));
        $this->assertSame('stale', $summary['ai_status']);
        $this->get(route('post-evaluation.expose', $training))->assertOk()->assertDontSee('Pembahasan AI panel 0');
        $this->get(route('post-evaluation.public', $setting->public_token))->assertOk()->assertSee('Pembahasan AI panel 0');
    }

    public function test_ai_failure_preserves_previous_generation_and_endpoint_is_superadmin_only(): void
    {
        [$admin,$training] = $this->data();
        $content = $this->response();
        Http::fakeSequence()->push(['candidates' => [['content' => ['parts' => [['text' => json_encode($content)]]]]]])->push(['error' => ['message' => 'Gangguan layanan']], 500);
        $this->actingAs($admin)->post(route('post-evaluation.ai.generate', $training))->assertSessionHas('success');
        $this->post(route('post-evaluation.ai.generate', $training))->assertSessionHas('error');
        $this->assertSame(1, AiGeneration::where('status', 'completed')->count());
        $this->assertSame(1, AiGeneration::where('status', 'failed')->count());
        $this->get(route('post-evaluation.expose', $training))->assertOk()->assertSee('Pembahasan AI panel 0');
        $user = User::factory()->create(['role' => 'evaluator']);
        $this->actingAs($user)->post(route('post-evaluation.ai.generate', $training))->assertForbidden();
    }

    public function test_expose_uses_five_reference_bands_and_nine_pdf_pages(): void
    {
        [$admin,$training] = $this->data();
        $service = app(PostEvaluationService::class);
        $this->assertSame([1, 2, 2, 2, 2], $service->histogram([0, 1, 25, 25.1, 50, 50.1, 75, 75.1, 100, null])['bands']);
        $summary = $service->summary($training, $service->people($training));
        $presenter = app(PostEvaluationExposeService::class);
        $pdf = Pdf::loadView('post-evaluation.presentation', ['summary' => $summary, 'presenter' => $presenter, 'public' => false, 'pdf' => true])->setPaper([0, 0, 960, 540]);
        $pdf->output();
        $this->assertSame(9, $pdf->getDomPDF()->getCanvas()->get_page_count());
        $this->actingAs($admin)->get(route('post-evaluation.expose', $training))->assertOk()->assertSee('Tujuan Evaluasi Pasca')->assertSee('Pembahasan Keseluruhan');
    }

    public function test_ai_requires_numeric_data_and_complete_narrative_fields(): void
    {
        [$admin,$training,$iv] = $this->data();
        $iv->update(['scores' => [null, null, null, null, null]]);
        Http::fake();
        $this->actingAs($admin)->post(route('post-evaluation.ai.generate', $training))->assertSessionHasErrors('ai');
        Http::assertNothingSent();
        $iv->update(['scores' => [80, 90, 70, 60, 100]]);
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => '{}']]]]]])]);
        $this->post(route('post-evaluation.ai.generate', $training))->assertSessionHas('error');
        $this->assertSame(0,AiGeneration::where('status','completed')->count());
    }
}
