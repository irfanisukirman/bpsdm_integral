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

    public function test_ai_generates_from_aggregates_and_anonymized_notes_and_is_used_in_public_snapshot(): void
    {
        [$admin,$training,$iv] = $this->data();
        $content = $this->response();
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($content)]]]]]])]);
        $this->actingAs($admin)->post(route('post-evaluation.ai.generate', $training))->assertSessionHas('success')->assertRedirect();
        $generation = AiGeneration::where('feature', PostEvaluationExposeService::FEATURE)->firstOrFail();
        $this->assertSame('completed', $generation->status);
        Http::assertSent(function ($request) {
            $payload = $request['contents'][0]['parts'][0]['text'];

            return ! str_contains($payload, 'NAMA RAHASIA') && ! str_contains($payload, '111122223333444455') && str_contains($payload, 'CATATAN PRIVAT') && ! str_contains($payload, 'JUDUL PROPER PRIVAT') && str_contains($payload, 'bands');
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

    public function test_expose_uses_four_reference_bands_and_nine_pdf_pages(): void
    {
        [$admin,$training] = $this->data();
        $service = app(PostEvaluationService::class);
        $this->assertSame([3, 2, 2, 2], $service->histogram([0, 1, 25, 25.1, 50, 50.1, 75, 75.1, 100, null])['bands']);
        $summary = $service->summary($training, $service->people($training));
        $this->assertSame(['Jangka panjang', 'Jangka menengah'], array_column($summary['slides'][0]['series'], 'label'));
        $this->assertSame(['Wawancara', 'Survei'], array_column($summary['slides'][1]['series'], 'label'));
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
    public function test_automatic_generation_reuses_current_result_and_note_changes_invalidate_it(): void
    {
        [$admin, $training, $iv] = $this->data();
        $iv->update(['notes' => ['NAMA RAHASIA 111122223333444455 email uji@example.com: target tertunda', '', '', '', '']]);
        $content = $this->response();
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($content)]]]]]])]);
        $url = route('post-evaluation.ai.generate', $training);
        $this->actingAs($admin)->postJson($url, ['automatic' => true])->assertOk();
        $this->postJson($url, ['automatic' => true])->assertOk()->assertJson(['cached' => true]);
        Http::assertSentCount(1);
        Http::assertSent(function ($request) {
            $text = $request['contents'][0]['parts'][0]['text'];
            return str_contains($text, 'target tertunda') && !str_contains($text, 'NAMA RAHASIA') && !str_contains($text, '111122223333444455') && !str_contains($text, 'uji@example.com');
        });
        $service = app(PostEvaluationService::class);
        $summary = $service->summary($training, $service->people($training));
        $this->assertSame('current', $summary['ai_status']);
        $this->assertArrayNotHasKey('evaluator_notes', $summary);
        $this->assertSame(1, $summary['evaluator_note_count']);
        $iv->update(['notes' => ['Catatan terbaru tanpa perubahan angka', '', '', '', '']]);
        $this->assertSame('stale', $service->summary($training, $service->people($training))['ai_status']);
        $this->postJson($url, ['automatic' => true])->assertOk();
        Http::assertSentCount(2);
        $this->post(route('post-evaluation.publish', $training), ['action' => 'publish'])->assertRedirect();
        $snapshot = PostEvaluationSetting::where('training_id', $training->id)->firstOrFail()->snapshot;
        $this->assertStringNotContainsString('Catatan terbaru', json_encode($snapshot));
        $this->assertArrayNotHasKey('evaluator_notes', $snapshot);
    }

    public function test_ui_does_not_generate_automatically_and_failed_explicit_requests_do_not_loop(): void
    {
        [$admin, $training] = $this->data();
        $this->actingAs($admin)->get(route('post-evaluation.show', $training))->assertOk()->assertSee('pasca-narrative-form')->assertSee('Prompt ChatGPT')->assertDontSee("data.set('automatic', '1')", false);
        Http::fake(['*' => Http::response(['error' => ['message' => 'Unavailable']], 500)]);
        $this->postJson(route('post-evaluation.ai.generate', $training), ['automatic' => true])->assertStatus(502);
        $this->postJson(route('post-evaluation.ai.generate', $training), ['automatic' => true])->assertStatus(429);
        Http::assertSentCount(1);
    }

    public function test_prompt_is_private_local_and_contains_anonymized_data_without_api_requests(): void
    {
        [$admin,$training,$iv]=$this->data();
        $iv->update(['notes'=>['NAMA RAHASIA 111122223333444455 uji@example.test target belum tercapai','','','','']]);
        Http::fake();
        $response=$this->actingAs($admin)->get(route('post-evaluation.narrative.edit',$training))
            ->assertOk()->assertHeader('Cache-Control','no-store, private')->assertSee('Salin Prompt + Data')->assertSee('rekapitulasi_keberlanjutan');
        $response->assertSee('target belum tercapai')->assertDontSee('uji@example.test')->assertDontSee('NAMA RAHASIA')->assertDontSee('111122223333444455');
        $this->get(route('post-evaluation.show',$training))->assertOk()->assertDontSee("data.set('automatic', '1')",false);
        Http::assertNothingSent();
        $this->actingAs(User::factory()->create(['role'=>'evaluator']))->get(route('post-evaluation.narrative.edit',$training))->assertForbidden();
    }

    public function test_manual_narrative_save_is_used_by_expose_pdf_and_reset_restores_defaults(): void
    {
        [$admin,$training,$iv]=$this->data();
        $service=app(PostEvaluationService::class);
        $hash=app(\App\Services\PostEvaluationNarrativeService::class)->fingerprint($service->summary($training,$service->people($training),false));
        $data=['action'=>'save','source_hash'=>$hash,'notes'=>'Konteks privat tambahan',
            'discussion'=>[4=>'Pembahasan manual slide empat.',5=>'',6=>'',7=>'',8=>''],
            'conclusions'=>'Kesimpulan hasil tinjauan.', 'recommendations'=>"Tindak lanjut pertama.\nTindak lanjut kedua.\nTindak lanjut ketiga."];
        Http::fake();
        $url=route('post-evaluation.narrative.update',$training);
        $this->actingAs($admin)->put($url,$data)->assertSessionHasNoErrors()->assertRedirect(route('post-evaluation.expose',$training));
        $this->get(route('post-evaluation.expose',$training))->assertOk()->assertSee('Pembahasan manual slide empat.')->assertSee('Kesimpulan hasil tinjauan.')->assertDontSee('Konteks privat tambahan');
        $this->get(route('post-evaluation.pdf',$training))->assertOk()->assertDownload();
        $this->get(route('post-evaluation.narrative.edit',$training))->assertOk()->assertSee('Pembahasan manual slide empat.')->assertSee('Konteks privat tambahan');
        Http::assertNothingSent();
        $iv->update(['notes'=>['Bukti terbaru','','','','']]);
        $this->get(route('post-evaluation.expose',$training))->assertOk()->assertSee('Data berubah: tinjau kembali narasi tersimpan');
        $this->put($url,$data)->assertSessionHasErrors('source_hash');
        $data['action']='reset';
        $this->put($url,$data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertNull(PostEvaluationSetting::where('training_id',$training->id)->first()->report_narrative);
        $this->get(route('post-evaluation.expose',$training))->assertOk()->assertDontSee('Pembahasan manual slide empat.');
    }

    public function test_manual_narrative_validation_authorization_and_training_isolation(): void
    {
        [$admin,$training]=$this->data();
        $url=route('post-evaluation.narrative.update',$training);
        $this->actingAs(User::factory()->create(['role'=>'admin_bidang']))->put($url,[])->assertForbidden();
        $service=app(PostEvaluationService::class);
        $hash=app(\App\Services\PostEvaluationNarrativeService::class)->fingerprint($service->summary($training,$service->people($training),false));
        $this->actingAs($admin)->put($url,['action'=>'save','source_hash'=>$hash,'discussion'=>[4=>str_repeat('a',1601)]])->assertSessionHasErrors('discussion.4');
        $this->put($url,['action'=>'save','source_hash'=>str_repeat('0',64),'discussion'=>array_fill_keys(range(4,8),'')])->assertSessionHasErrors('source_hash');
        $this->assertNull(PostEvaluationSetting::where('training_id',$training->id)->first());
    }

}
