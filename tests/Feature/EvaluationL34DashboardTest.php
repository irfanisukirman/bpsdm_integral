<?php

namespace Tests\Feature;

use App\Models\EvaluationResultL34;
use App\Models\Participant;
use App\Models\Question;
use App\Models\Training;
use App\Models\User;
use App\Services\AiEvaluationDashboardService;
use App\Services\EvaluationL34DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class EvaluationL34DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function setupData(string $program = 'PKP'): array
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $t = Training::create(['nama_pelatihan' => 'Uji Dashboard', 'bidang' => 'Bidang A', 'program_evaluasi' => $program, 'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 10, 'jp' => 10, 'tgl_mulai' => '2024-01-01', 'tgl_selesai' => '2024-01-02', 'created_by' => $admin->id]);
        $people = [];
        foreach (['Rahasia Satu', 'Rahasia Dua'] as $i => $name) {
            $people[] = Participant::create(['training_id' => $t->id, 'name' => $name, 'nip_nik' => '19800101200001100'.($i + 1), 'registration_status' => 'approved']);
        }
        $questions = [];
        foreach ($t->evaluationRoles() as $role) {
            foreach (['Perubahan Sikap Perilaku', 'Dampak Pelatihan'] as $i => $section) {
                $questions[$role][$i] = $this->question($t, $role, $section, 'Indikator '.($i + 1), $program === 'Teknis' ? 'slider' : 'dropdown');
            }
        }

        return [$admin, $t, $people, $questions];
    }

    private function question(Training $t, string $role, string $section, string $label, string $type = 'dropdown', ?array $options = null): Question
    {
        return Question::create(['bidang' => $t->bidang, 'training_type' => $t->bidang, 'program_evaluasi' => $t->program_evaluasi, 'metode' => 'semua', 'category' => 'l34_'.$role, 'sub_category' => $section, 'question_text' => $label, 'type' => $type, 'options' => $options ?? ($type === 'dropdown' ? ['Tidak Sesuai', 'Kurang Sesuai', 'Sesuai', 'Sangat Sesuai'] : null)]);
    }

    private function answer(Training $t, Participant $p, Question $q, ?string $note, ?int $score = null, ?string $role = null): EvaluationResultL34
    {
        return EvaluationResultL34::create(['training_id' => $t->id, 'participant_id' => $p->id, 'question_id' => $q->id, 'evaluator_role' => $role ?? str_replace('l34_', '', $q->category), 'evaluator_name' => 'Penilai', 'note' => $note, 'score' => $score]);
    }

    public function test_four_point_scores_progress_and_partial_answers_are_counted_without_profile_or_yes_no_scores(): void
    {
        [$admin,$t,$p,$q] = $this->setupData();
        $this->answer($t, $p[0], $q['mandiri'][0], 'Sesuai');
        $this->answer($t, $p[0], $q['mandiri'][1], 'Sangat Sesuai');
        $this->answer($t, $p[0], $q['atasan'][0], 'Tidak Sesuai');
        $this->answer($t, $p[0], $q['atasan'][1], 'Kurang Sesuai');
        $this->answer($t, $p[1], $q['mandiri'][0], 'Sesuai');
        $text = $this->question($t, 'mandiri', 'Faktor Pendukung Aksi Perubahan', 'Catatan', 'text');
        $this->answer($t, $p[0], $text, 'Sangat Sesuai', 100);
        $yn = $this->question($t, 'mandiri', 'Dampak Pelatihan', 'Kebijakan digunakan?', 'dropdown', ['Ya', 'Tidak']);
        $this->answer($t, $p[0], $yn, 'Ya');
        $pending = Participant::create(['training_id' => $t->id, 'name' => 'Belum Disetujui', 'nip_nik' => '111', 'registration_status' => 'pending']);
        $this->answer($t, $pending, $q['mandiri'][0], 'Sangat Sesuai');
        $this->answer($t, $p[1], $q['mandiri'][0], 'Sangat Sesuai', null, 'atasan');
        $d = app(EvaluationL34DashboardService::class)->build($t);
        $this->assertSame(2, $d['participantTotal']);
        $this->assertSame(1, $d['excludedParticipants']);
        $this->assertSame(1, $d['fullyAssessed']);
        $this->assertSame(75.0, $d['overallCoverage']);
        $this->assertSame(50.0, $d['completionCoverage']);
        $this->assertEqualsWithDelta(58.3333, $d['l3Average'], .0001);
        $this->assertSame(75.0, $d['l4Average']);
        $this->assertSame(1, $d['progress']['mandiri']['partial']);
        $this->assertSame(1, $d['progress']['atasan']['not_started']);
        $this->assertSame(2, $d['progress']['mandiri']['expected_questions']);
        $this->assertSame(0, $d['impactDistribution']['Sangat Baik']);
        $this->assertSame(1, $d['impactDistribution']['Cukup']);
        $this->assertSame(1, $d['impactDistribution']['Belum dinilai']);
        $this->assertNull($d['dashboardSections']->firstWhere('name', 'Faktor Pendukung Aksi Perubahan')['average']);
        $this->actingAs($admin)->get(route('evall34.dashboard', $t))->assertOk()->assertHeader('X-Integral-Dashboard','l34-local-charts-20261115')->assertSee('background-color:#eef3ff',false)->assertSee('Progres pengisian per penilai')->assertSee('Kelengkapan per alumni')->assertSee('assets/vendor/libs/apex-charts/apexcharts.js', false)->assertDontSee('cdn.jsdelivr.net/npm/chart.js', false)->assertSee('58,3')->assertDontSee('__raw_block_', false);
    }

    public function test_latest_duplicate_question_response_is_used_and_numeric_four_point_choice_is_converted(): void
    {
        [$admin,$t,$p,$q] = $this->setupData();
        $copy = $q['mandiri'][0]->replicate();
        $copy->save();
        $this->answer($t, $p[0], $q['mandiri'][0], 'Tidak Sesuai');
        $this->answer($t, $p[0], $copy, 'Sangat Sesuai');
        $this->answer($t, $p[0], $q['mandiri'][1], null, 3);
        $d = app(EvaluationL34DashboardService::class)->build($t);
        $this->assertSame(100.0, $d['l3Average']);
        $this->assertSame(75.0, $d['l4Average']);
        $this->assertSame(2, $d['progress']['mandiri']['expected_questions']);
        $this->assertSame(1, $d['l3Indicators']->first()['responses']);
        $this->assertSame(1, $d['progress']['mandiri']['completed']);
    }

    public function test_missing_values_remain_missing_slider_zero_counts_and_invalid_scores_are_excluded(): void
    {
        [$admin,$t,$p,$q] = $this->setupData('Teknis');
        $this->answer($t, $p[0], $q['mandiri'][0], null, 0);
        $this->answer($t, $p[0], $q['mandiri'][1], null, 150);
        $d = app(EvaluationL34DashboardService::class)->build($t);
        $this->assertSame(0.0, $d['l3Average']);
        $this->assertNull($d['l4Average']);
        $this->assertSame(1, $d['progress']['mandiri']['answered_questions']);
        $this->assertSame(0, $d['fullyAssessed']);
        $this->assertSame(2, $d['impactDistribution']['Belum dinilai']);
        $this->assertSame(3, $d['activeRoles']->count());
    }

    public function test_no_data_or_missing_instruments_never_produces_a_positive_conclusion(): void
    {
        [$admin,$t,$p,$q] = $this->setupData();
        Question::where('category', 'l34_atasan')->delete();
        $d = app(EvaluationL34DashboardService::class)->build($t);
        $this->assertCount(2, $d['activeRoles']);
        $this->assertFalse($d['progress']['atasan']['instrument_ready']);
        $this->assertNull($d['l3Average']);
        $this->assertNull($d['l4Average']);
        $this->assertSame(0, $d['fullyAssessed']);
        $this->assertFalse($d['recommendations']->contains('title', 'Dokumentasikan praktik baik'));
        Participant::where('training_id', $t->id)->delete();
        $d = app(EvaluationL34DashboardService::class)->build($t);
        $this->assertSame(0, $d['participantTotal']);
        $this->assertSame(0, $d['overallCoverage']);
        $this->actingAs($admin)->get(route('evall34.dashboard', $t))->assertOk()->assertSee('Belum ada peserta disetujui');
    }

    public function test_dashboard_and_ai_are_scoped_to_superadmin_or_the_matching_field_admin(): void
    {
        [$admin,$t] = $this->setupData();
        foreach ([['participant', 'Bidang A'], ['admin_bidang', 'Bidang B'], ['resepsionis', 'Bidang A']] as [$role,$field]) {
            $user = User::factory()->create(['role' => $role, 'bidang' => $field]);
            $this->actingAs($user)->get(route('evall34.dashboard', $t))->assertForbidden();
            $this->post(route('evall34.dashboard.ai', $t))->assertForbidden();
        }
        $fieldAdmin = User::factory()->create(['role' => 'admin_bidang', 'bidang' => 'Bidang A']);
        $this->actingAs($fieldAdmin)->get(route('evall34.dashboard', $t))->assertOk();
    }

    public function test_ai_uses_the_same_anonymous_aggregates_and_old_analysis_is_hidden_when_answers_change(): void
    {
        [$admin,$t,$p,$q] = $this->setupData();
        $answer = $this->answer($t, $p[0], $q['mandiri'][0], 'Sesuai');
        $this->answer($t, $p[0], $q['mandiri'][1], 'Sangat Sesuai');
        $mock = Mockery::mock(AiEvaluationDashboardService::class);
        $mock->shouldReceive('provider')->once()->andReturn('fake');
        $mock->shouldReceive('model')->once()->andReturn('test');
        $mock->shouldReceive('generate')->once()->with('Level 3 dan 4', Mockery::on(function ($payload) {
            $json = json_encode($payload);

            return $payload['rerata_bagian']['Perubahan Sikap Perilaku'] === 75.0 && ! str_contains($json, 'Rahasia Satu') && ! str_contains($json, '198001012000011001');
        }))->andReturn(['executive_summary' => 'Analisis agregat yang diuji', 'key_findings' => 'Temuan', 'priority_actions' => 'Tindakan', 'data_caution' => 'Catatan']);
        $this->app->instance(AiEvaluationDashboardService::class,$mock);
        $this->actingAs($admin)->post(route('evall34.dashboard.ai',$t))->assertSessionHas('success');
        $this->get(route('evall34.dashboard',$t))->assertOk()->assertSee('Analisis agregat yang diuji');
        $answer->update(['note' => 'Kurang Sesuai']);
        $this->get(route('evall34.dashboard',$t))->assertOk()->assertDontSee('Analisis agregat yang diuji')->assertSee('Analisis AI sebelumnya memakai data yang berbeda');
    }
}
