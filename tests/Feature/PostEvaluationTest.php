<?php

namespace Tests\Feature;

use App\Models\EvaluationResultL34;
use App\Models\Participant;
use App\Models\PostEvaluationInterview;
use App\Models\PostEvaluationSetting;
use App\Models\Question;
use App\Models\Training;
use App\Models\User;
use App\Services\PostEvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostEvaluationTest extends TestCase
{
    use RefreshDatabase;

    private function setupData(): array
    {
        $admin = User::factory()->create(['role' => 'superadmin', 'bidang' => 'Bidang A']);
        $training = Training::create(['nama_pelatihan' => 'Uji Pasca PKP', 'bidang' => 'Bidang A', 'program_evaluasi' => 'PKP', 'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 20, 'jp' => 10, 'tgl_mulai' => '2024-01-01', 'tgl_selesai' => '2024-01-02', 'created_by' => $admin->id]);
        $participant = Participant::create(['training_id' => $training->id, 'name' => 'ALUMNI RAHASIA', 'nip_nik' => '198001012000011001', 'instansi' => 'Unit Uji', 'jabatan' => 'Analis', 'registration_status' => 'approved']);

        return [$admin, $training, $participant];
    }

    private function question(Training $t, string $role = 'mandiri', string $program = 'PKP'): Question
    {
        return Question::create(['bidang' => $t->bidang, 'training_type' => $t->bidang, 'program_evaluasi' => $program, 'category' => 'l34_'.$role, 'sub_category' => 'Dampak Pelatihan', 'metode' => 'semua', 'type' => 'dropdown', 'question_text' => 'Indikator tujuan '.$role, 'options' => ['Tidak Sesuai', 'Kurang Sesuai', 'Sesuai', 'Sangat Sesuai']]);
    }

    private function answer(Training $t, Participant $p, Question $q, string $note = 'Sesuai'): void
    {
        EvaluationResultL34::create(['training_id' => $t->id, 'participant_id' => $p->id, 'question_id' => $q->id, 'evaluator_role' => str_replace('l34_', '', $q->category), 'note' => $note, 'evaluator_name' => 'Penilai Uji']);
    }

    private function interviewData(int $revision = 0): array
    {
        return ['revision' => $revision, 'scores' => [80, 90, 70, 60, 100], 'notes' => ['catatan privat', '', '', '', ''], 'status' => 'completed', 'project' => 'Proyek rahasia'];
    }

    public function test_field_admin_cannot_access_results_even_when_assigned_to_team(): void
    {
        [$manager,$training,$participant]=$this->setupData();
        $admin=User::factory()->create(['role'=>'admin_bidang','bidang'=>$training->bidang]);
        $team=DB::table('post_evaluation_teams')->insertGetId(['training_id'=>$training->id,'name'=>'Tim Uji']);
        DB::table('post_evaluation_team_user')->insert(['team_id'=>$team,'user_id'=>$admin->id]);
        $this->actingAs($admin)->get(route('post-evaluation.index'))->assertForbidden();
        $this->get(route('post-evaluation.show',$training))->assertForbidden();
        $this->get(route('post-evaluation.expose',$training))->assertForbidden();
        $this->get(route('post-evaluation.pdf',$training))->assertForbidden();
        $this->post(route('post-evaluation.publish',$training),['action'=>'publish'])->assertForbidden();
        $this->assertStringNotContainsString('Hasil Evaluasi Pasca Level 3 &amp; 4',view('layouts.sidebar')->render());
    }

    public function test_live_survey_preserves_missing_and_roles_without_duplicate_participants(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $q = $this->question($t);
        $other = $this->question($t, 'atasan');
        PostEvaluationSetting::create(['training_id' => $t->id, 'mappings' => [['indicator' => 0, 'question_id' => $q->id, 'role' => 'mandiri', 'scale' => 'four'], ['indicator' => 0, 'question_id' => $other->id, 'role' => 'atasan', 'scale' => 'four']]]);
        $this->answer($t, $p, $q);
        $service = app(PostEvaluationService::class);
        $people = $service->people($t);
        $this->assertCount(1, $people);
        $this->assertSame([75.0, null, null], $people[0]['survey']);
        $this->assertTrue($people[0]['partial']);
        $this->assertSame(['expected' => 2, 'filled' => 1], $people[0]['coverage'][0]);
        $this->answer($t, $p, $other, 'Sangat Sesuai');
        $this->assertSame(87.5, $service->people($t)[0]['survey'][0]);
        $this->assertDatabaseCount('participants', 1);
    }

    public function test_settings_reject_wrong_program_role_and_incompatible_scale(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $q = $this->question($t, 'mandiri', 'PKA');
        $rubric = app(PostEvaluationService::class)->rubric($t);
        $this->actingAs($admin)->post(route('post-evaluation.settings', $t), ['rubric' => $rubric, 'mappings' => [['indicator' => 0, 'question_id' => $q->id, 'role' => 'mandiri', 'scale' => 'four']]])->assertSessionHasErrors('mappings');
        $q = $this->question($t);
        $this->post(route('post-evaluation.settings', $t), ['rubric' => $rubric, 'mappings' => [['indicator' => 0, 'question_id' => $q->id, 'role' => 'mandiri', 'scale' => 'hundred']]])->assertSessionHasErrors('mappings');
        $this->post(route('post-evaluation.settings', $t), ['rubric' => $rubric, 'mappings' => [['indicator' => 0, 'question_id' => $q->id, 'role' => 'mandiri', 'scale' => 'four']]])->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_views_pdf_and_interview_calculation_match_pasca_pattern(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $this->actingAs($admin);
        $this->get(route('post-evaluation.index'))->assertOk()->assertSee('Uji Pasca PKP');
        foreach (['ringkasan', 'jawaban', 'peserta', 'wawancara', 'pengaturan'] as $tab) {
            $this->get(route('post-evaluation.show', [$t, 'tab' => $tab]))->assertOk();
        }
        $this->get(route('post-evaluation.interview', [$t, $p]))->assertOk();
        $this->put(route('post-evaluation.interview.save', [$t, $p]), $this->interviewData())->assertSessionHasNoErrors()->assertRedirect();
        $person = app(PostEvaluationService::class)->people($t)[0];
        $this->assertSame(76.0, $person['weighted']);
        $this->assertSame(73.3333, $person['interview_average']);
        $this->get(route('post-evaluation.expose', $t))->assertOk()->assertSee('Ekspose Evaluasi Level 3');
        $this->get(route('post-evaluation.pdf', $t))->assertOk()->assertDownload();
    }

    public function test_stale_save_conflicts_and_draft_can_be_partial(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $this->actingAs($admin)->get(route('post-evaluation.interview', [$t, $p]))->assertOk();
        $data = $this->interviewData();
        $data['scores'] = [0, null, null, null, null];
        $data['status'] = 'draft';
        $this->put(route('post-evaluation.interview.save', [$t, $p]), $data)->assertRedirect();
        $this->put(route('post-evaluation.interview.save', [$t, $p]), $data)->assertStatus(409);
        $person = app(PostEvaluationService::class)->people($t)[0];
        $this->assertSame(0, $person['scores'][0]);
        $this->assertNull($person['weighted']);
        $data['revision'] = 1;
        $data['status'] = 'completed';
        $this->put(route('post-evaluation.interview.save', [$t, $p]), $data)->assertSessionHasErrors('scores');
    }

    public function test_team_access_is_limited_to_assigned_participants_and_no_settings(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $member = User::factory()->create(['role' => 'pengajar']);
        $outsider = User::factory()->create(['role' => 'admin_bidang', 'bidang' => 'Bidang B']);
        $this->actingAs($admin)->post(route('post-evaluation.team', $t), ['name' => 'Tim Satu', 'users' => [$member->id]])->assertRedirect();
        $team = DB::table('post_evaluation_teams')->first();
        $this->post(route('post-evaluation.assign', $t), ['team_id' => $team->id, 'participant_ids' => [$p->id]])->assertRedirect();
        $other = Participant::create(['training_id' => $t->id, 'name' => 'ALUMNI TIM LAIN', 'nip_nik' => '222', 'instansi' => 'Unit Lain', 'registration_status' => 'approved']);
        $this->actingAs($member)->get(route('post-evaluation.show', [$t, 'tab' => 'peserta']))->assertOk()->assertSee('ALUMNI RAHASIA')->assertDontSee('ALUMNI TIM LAIN');
        $this->get(route('post-evaluation.interview', [$t, $other]))->assertForbidden();
        $this->post(route('post-evaluation.publish', $t), ['action' => 'publish'])->assertForbidden();
        $this->get(route('post-evaluation.show', [$t, 'tab' => 'pengaturan']))->assertForbidden();
        $this->actingAs($outsider)->get(route('post-evaluation.show', $t))->assertForbidden();
    }

    public function test_public_snapshot_is_anonymous_stable_and_revocable(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $q = $this->question($t);
        $this->answer($t, $p, $q);
        $this->actingAs($admin)->post(route('post-evaluation.publish', $t), ['action' => 'publish'])->assertRedirect();
        $setting = PostEvaluationSetting::where('training_id', $t->id)->firstOrFail();
        $token = $setting->public_token;
        $snapshot = $setting->snapshot;
        $this->answer($t, $p, $this->question($t, 'atasan'), 'Sangat Sesuai');
        $this->app['auth']->forgetGuards();
        $this->get(route('post-evaluation.public', $token))->assertOk()->assertDontSee('ALUMNI RAHASIA')->assertDontSee('198001012000011001')->assertDontSee('catatan privat');
        $this->get(route('post-evaluation.public.pdf', $token))->assertOk()->assertDownload();
        $this->assertSame($snapshot, $setting->fresh()->snapshot);
        $this->actingAs($admin)->post(route('post-evaluation.publish', $t), ['action' => 'rotate'])->assertRedirect();
        $this->get(route('post-evaluation.public', $token))->assertNotFound();
        $newToken = $setting->fresh()->public_token;
        $this->post(route('post-evaluation.publish', $t), ['action' => 'revoke'])->assertRedirect();
        $this->get(route('post-evaluation.public', $newToken))->assertNotFound();
    }

    public function test_autosave_returns_revision_and_keeps_interview_when_survey_changes(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $this->actingAs($admin)->get(route('post-evaluation.interview', [$t, $p]))->assertOk();
        $this->putJson(route('post-evaluation.interview.save', [$t, $p]), $this->interviewData())->assertOk()->assertJsonPath('revision', 1);
        $iv = PostEvaluationInterview::firstOrFail();
        $before = $iv->scores;
        $q = $this->question($t);
        $this->answer($t, $p, $q);
        $this->assertSame($before, $iv->fresh()->scores);
        $this->putJson(route('post-evaluation.interview.save', [$t, $p]), $this->interviewData())->assertStatus(409);
    }

    public function test_slider_zero_and_histogram_boundaries_are_not_missing(): void
    {
        $service = app(PostEvaluationService::class);
        $this->assertSame(0.0, $service->score((object) ['score' => 0, 'note' => null], ['scale' => 'hundred']));
        $this->assertSame(25.0, $service->score((object) ['score' => null, 'note' => '1'], ['scale' => 'four']));
        $this->assertSame([2, 2, 2, 2], $service->histogram([0, 60, 60.1, 79, 79.1, 89, 89.1, 100, null])['counts']);
        $this->assertSame(1, $service->histogram([null, 0])['missing']);
    }

    public function test_public_proper_flow_verifies_training_and_updates_details_without_touching_scores(): void
    {
        Storage::fake('local');
        [$admin,$t,$p] = $this->setupData();
        $iv = PostEvaluationInterview::create(['training_id' => $t->id, 'participant_id' => $p->id, 'scores' => [80, 90, 70, 60, 100], 'notes' => ['Catatan tim', '', '', '', ''], 'status' => 'completed', 'revision' => 3]);
        $this->get(route('post-evaluation.proper.public', $t))->assertOk()->assertSee('Judul Inovasi / Proper')->assertSee('NIP/NIK peserta')->assertSee('Kondisi Proper Saat Ini');
        $this->postJson(route('post-evaluation.proper.verify', $t), ['participant_identity' => $p->nip_nik])->assertOk()->assertJsonPath('name', $p->name)->assertJsonPath('has_document', false);
        $this->postJson(route('post-evaluation.proper.verify', $t), ['participant_identity' => '999'])->assertUnprocessable();
        $data = ['participant_identity' => $p->nip_nik, 'proper_title' => 'Aksi Perubahan Uji', 'medium_milestone' => 'Target menengah', 'long_milestone' => 'Target panjang', 'medium_evidence_status' => 'sudah', 'long_evidence_status' => 'belum', 'current_condition' => 'Berjalan', 'document' => UploadedFile::fake()->create('proper.pdf', 20, 'application/pdf')];
        $this->post(route('post-evaluation.proper.store', $t), $data)->assertSessionHasErrors('medium_evidence');
        $data['medium_evidence'] = UploadedFile::fake()->image('bukti.png');
        $this->post(route('post-evaluation.proper.store', $t), $data)->assertSessionHasNoErrors()->assertRedirect();
        $iv->refresh();
        $this->assertSame('Aksi Perubahan Uji', $iv->project);
        $this->assertSame(4, $iv->revision);
        $this->assertSame([80, 90, 70, 60, 100], $iv->scores);
        $this->assertSame('completed', $iv->status);
        $photo = DB::table('post_evaluation_evidence')->where('kind', 'medium')->first();
        Storage::disk('local')->assertExists($photo->path);
        $this->postJson(route('post-evaluation.proper.verify', $t), ['participant_identity' => $p->nip_nik])->assertOk()->assertJsonPath('has_document', true);
        $data['medium_evidence_status'] = 'belum';
        unset($data['medium_evidence']);
        $data['document'] = UploadedFile::fake()->create('proper-baru.pdf', 20, 'application/pdf');
        $this->post(route('post-evaluation.proper.store', $t), $data)->assertSessionHasNoErrors()->assertRedirect();
        Storage::disk('local')->assertMissing($photo->path);
        $this->assertDatabaseMissing('post_evaluation_evidence', ['kind' => 'medium']);
        $this->assertDatabaseCount('participants', 1);
        $this->assertDatabaseCount('post_evaluation_interviews', 1);
        $this->actingAs($admin)->get(route('post-evaluation.interview', [$t, $p]))->assertOk()->assertSee('Aksi Perubahan Uji')->assertSee('Target menengah');
    }

    public function test_evidence_is_private_and_public_upload_link_can_be_revoked(): void
    {
        Storage::fake('local');
        [$admin,$t,$p] = $this->setupData();
        $this->actingAs($admin)->post(route('post-evaluation.upload-link', [$t, $p]), ['action' => 'rotate'])->assertRedirect();
        $iv = PostEvaluationInterview::firstOrFail();
        $token = $iv->upload_token;
        $this->app['auth']->forgetGuards();
        $this->get(route('post-evaluation.upload.public', $token))->assertOk()->assertDontSee('ALUMNI RAHASIA');
        $file = UploadedFile::fake()->create('bukti.pdf', 10, 'application/pdf');
        $this->post(route('post-evaluation.upload.store', $token), ['proper_title' => 'Proper Uji', 'medium_milestone' => 'Target menengah', 'long_milestone' => 'Target panjang', 'medium_evidence_status' => 'belum', 'long_evidence_status' => 'belum', 'current_condition' => 'Sedang diterapkan', 'document' => $file])->assertSessionHasNoErrors()->assertRedirect();
        $evidence = DB::table('post_evaluation_evidence')->first();
        Storage::disk('local')->assertExists($evidence->path);
        $this->actingAs($admin)->get(route('post-evaluation.evidence', [$t, $evidence->id]))->assertOk()->assertDownload();
        $outsider = User::factory()->create(['role' => 'admin_bidang', 'bidang' => 'Bidang B']);
        $this->actingAs($outsider)->get(route('post-evaluation.evidence', [$t, $evidence->id]))->assertForbidden();
        $this->actingAs($admin)->post(route('post-evaluation.upload-link', [$t, $p]), ['action' => 'revoke'])->assertRedirect();
        $this->get(route('post-evaluation.upload.public',$token))->assertNotFound();
    }
}
