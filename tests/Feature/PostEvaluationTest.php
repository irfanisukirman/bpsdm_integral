<?php

namespace Tests\Feature;

use App\Models\AlumniProfile;
use App\Models\EvaluationResultL34;
use App\Models\Participant;
use App\Models\PostEvaluationInterview;
use App\Models\PostEvaluationSetting;
use App\Models\Question;
use App\Models\Training;
use App\Models\User;
use App\Services\PostEvaluationService;
use App\Services\PostEvaluationSurveySummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
        return Question::create(['bidang' => $t->bidang, 'training_type' => $t->bidang, 'program_evaluasi' => $program, 'category' => 'l34_'.$role, 'sub_category' => 'Dampak Pelatihan', 'metode' => 'semua', 'type' => 'dropdown', 'question_text' => PostEvaluationService::SURVEY_QUESTIONS[0], 'options' => ['Tidak Sesuai', 'Kurang Sesuai', 'Sesuai', 'Sangat Sesuai']]);
    }

    private function answer(Training $t, Participant $p, Question $q, string $note = 'Sesuai'): void
    {
        EvaluationResultL34::create(['training_id' => $t->id, 'participant_id' => $p->id, 'question_id' => $q->id, 'evaluator_role' => str_replace('l34_', '', $q->category), 'note' => $note, 'evaluator_name' => 'Penilai Uji']);
    }

    private function interviewData(int $revision = 0): array
    {
        return ['revision' => $revision, 'scores' => [80, 90, 70, 60, 100], 'notes' => ['catatan privat', '', '', '', ''], 'status' => 'completed', 'project' => 'Proyek rahasia'];
    }

    public function test_annual_summary_filters_by_training_year_and_shows_interview_only_when_scored(): void
    {
        [$admin, $training, $participant] = $this->setupData();
        $otherYear = $training->replicate(['proper_uid', 'invitation_code']);
        $otherYear->forceFill(['nama_pelatihan' => 'Pelatihan Tahun Lain', 'tgl_mulai' => '2025-01-01', 'tgl_selesai' => '2025-01-02'])->save();
        $otherTraining = $training->replicate(['proper_uid', 'invitation_code']);
        $otherTraining->forceFill(['nama_pelatihan' => 'Pelatihan lain di tahun yang sama'])->save();
        $this->answer($training, $participant, $this->question($training), 'Sesuai');
        $this->answer($training, $participant, $this->question($training, 'atasan'), 'Sangat Sesuai');
        $filters = ['tab' => 'ringkasan-tahunan', 'year' => 2024, 'q' => 'Uji Pasca'];
        $report = route('post-evaluation.index', $filters);
        $export = route('post-evaluation.index', $filters + ['format' => 'xlsx']);

        $this->actingAs($admin)->get($report)->assertOk()
            ->assertSee('Ekspor Excel')->assertSee($export)
            ->assertSee($training->nama_pelatihan)->assertDontSee($otherYear->nama_pelatihan)->assertDontSee($otherTraining->nama_pelatihan)
            ->assertSee('Rerata Survei Alumni')->assertSee('75,00%')->assertDontSee('Rerata Wawancara');
        $response = $this->get($export)->assertDownload('ringkasan-evaluasi-pasca-2024.xlsx');
        Cell::setValueBinder(new DefaultValueBinder());
        $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();
        $this->assertSame(2, $sheet->getHighestDataRow());
        $this->assertSame($training->nama_pelatihan, $sheet->getCell('A2')->getValue());
        $this->assertSame('75.00%', $sheet->getCell('F2')->getFormattedValue());
        $this->assertSame('Rerata Survei Atasan', $sheet->getCell('H1')->getValue());
        $this->assertSame('100.00%', $sheet->getCell('H2')->getFormattedValue());
        $this->assertEquals(1, $sheet->getCell('I2')->getValue());
        $this->assertNull($sheet->getCell('J2')->getValue());

        $evaluator = User::factory()->create(['role' => 'evaluator']);
        $teamId = DB::table('post_evaluation_teams')->insertGetId(['training_id' => $training->id, 'name' => 'Tim Tahunan']);
        DB::table('post_evaluation_team_user')->insert(['team_id' => $teamId, 'user_id' => $evaluator->id]);
        $this->actingAs($evaluator)->get($report)->assertOk()
            ->assertSee('Belum ada nilai survei')->assertDontSee('Rerata Wawancara');

        PostEvaluationInterview::create([
            'training_id' => $training->id, 'participant_id' => $participant->id, 'team_id' => $teamId,
            'scores' => [80, 90, 80, 70, 100], 'status' => 'completed',
        ]);
        $this->get($report)->assertOk()->assertSee('75,00%')->assertSee('Rerata Wawancara')
            ->assertSee('80,00%')->assertSee('1 wawancara selesai');
        $response = $this->get($export)->assertDownload('ringkasan-evaluasi-pasca-2024.xlsx');
        Cell::setValueBinder(new DefaultValueBinder());
        $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet();
        $this->assertSame('100.00%', $sheet->getCell('H2')->getFormattedValue());
        $this->assertSame('80.00%', $sheet->getCell('J2')->getFormattedValue());
        $this->assertEquals(1, $sheet->getCell('L2')->getValue());
    }

    public function test_result_back_link_follows_entry_point_across_tabs(): void
    {
        [$admin, $training] = $this->setupData();
        $manage = route('trainings.manage', $training);
        $index = route('post-evaluation.index');
        $origin = ['from' => 'kelola-pelatihan'];
        $fromManage = route('post-evaluation.show', [$training] + $origin);

        $this->actingAs($admin)->get($index)->assertOk()->assertSee(route('post-evaluation.show', $training));
        $this->get(route('post-evaluation.show', [$training, 'tab' => 'ringkasan']))->assertOk()
            ->assertSee('href="'.$index.'" class="btn btn-outline-secondary btn-sm"', false)
            ->assertSee('Kembali ke Hasil Evaluasi Pasca');
        $this->get($manage)->assertOk()->assertSee($fromManage);
        $tab = route('post-evaluation.show', [$training, 'tab' => 'jawaban'] + $origin);
        $this->get($fromManage)->assertOk()->assertSee($tab)
            ->assertSee('href="'.$manage.'" class="btn btn-outline-secondary btn-sm"', false);
        $this->get($tab)->assertOk()->assertSee('name="from" value="kelola-pelatihan"', false)
            ->assertSee('href="'.$manage.'" class="btn btn-outline-secondary btn-sm"', false);
    }

    public function test_field_admin_cannot_access_results_even_when_assigned_to_team(): void
    {
        [$manager,$training,$participant] = $this->setupData();
        $admin = User::factory()->create(['role' => 'admin_bidang', 'bidang' => $training->bidang]);
        $team = DB::table('post_evaluation_teams')->insertGetId(['training_id' => $training->id, 'name' => 'Tim Uji']);
        DB::table('post_evaluation_team_user')->insert(['team_id' => $team, 'user_id' => $admin->id]);
        $this->actingAs($admin)->get(route('post-evaluation.index'))->assertForbidden();
        $this->get(route('post-evaluation.show', $training))->assertForbidden();
        $this->get(route('post-evaluation.expose', $training))->assertForbidden();
        $this->get(route('post-evaluation.pdf', $training))->assertForbidden();
        $this->post(route('post-evaluation.publish', $training), ['action' => 'publish'])->assertForbidden();
        $this->assertStringNotContainsString('Hasil Evaluasi Pasca Level 3 &amp; 4', view('layouts.sidebar')->render());
    }

    public function test_settings_only_manages_teams_and_can_register_evaluator_with_email_login(): void
    {
        [$admin,$training,$participant] = $this->setupData();
        $this->actingAs($admin)->get(route('post-evaluation.show', [$training, 'tab' => 'pengaturan']))->assertOk()->assertSee('Tambah evaluator baru')->assertSee('Bagi Peserta Otomatis')->assertDontSee('Pemetaan indikator')->assertDontSee('Bobot (%)')->assertDontSee('Publikasi hasil');
        $team = DB::table('post_evaluation_teams')->insertGetId(['training_id' => $training->id, 'name' => 'Tim Evaluasi']);
        $password = 'EvaluatorUji123!';
        $this->post(route('post-evaluation.evaluator.store', $training), ['name' => 'Evaluator Baru', 'email' => 'evaluator@example.test', 'password' => $password, 'password_confirmation' => $password, 'team_id' => $team])->assertSessionHasNoErrors()->assertRedirect();
        $evaluator = User::where('username', 'evaluator@example.test')->firstOrFail();
        $this->assertSame('evaluator', $evaluator->role);
        $this->assertTrue(Hash::check($password, $evaluator->password));
        $this->assertDatabaseHas('post_evaluation_team_user', ['team_id' => $team, 'user_id' => $evaluator->id]);
        $this->post(route('post-evaluation.assign', $training), ['team_id' => $team, 'participant_ids' => [$participant->id]])->assertRedirect();
        $this->post('/logout')->assertRedirect();
        $this->post('/login', ['username' => 'evaluator@example.test', 'password' => $password])->assertRedirect(route('post-evaluation.index'));
        $this->assertAuthenticatedAs($evaluator);
        $this->get(route('dashboard'))->assertRedirect(route('post-evaluation.index'));
        $this->get(route('post-evaluation.index'))->assertOk()->assertSee('Uji Pasca PKP');
        $this->get(route('post-evaluation.interview', [$training, $participant]))->assertOk();
        $this->post(route('post-evaluation.evaluator.store', $training), ['name' => 'Tidak boleh'])->assertForbidden();
    }

    public function test_existing_evaluators_are_reused_and_duplicate_email_is_rejected(): void
    {
        [$admin,$training] = $this->setupData();
        $existing = User::factory()->create(['role' => 'evaluator', 'username' => 'existing@example.test']);
        $before = $existing->password;
        $this->actingAs($admin)->post(route('post-evaluation.team', $training), ['name' => 'Tim Lama', 'users' => [$existing->id]])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($before, $existing->fresh()->password);
        $count = User::count();
        $this->post(route('post-evaluation.evaluator.store', $training), ['name' => 'Duplikat', 'email' => 'existing@example.test', 'password' => 'ContohPass123', 'password_confirmation' => 'ContohPass123'])->assertSessionHasErrors('email');
        $this->assertSame($count, User::count());
        $blocked = User::factory()->create(['role' => 'admin_bidang']);
        $this->post(route('post-evaluation.team', $training), ['name' => 'Tim Tidak Valid', 'users' => [$blocked->id]])->assertSessionHasErrors('users.0');
    }

    public function test_interview_shows_original_survey_and_proper_evidence_in_each_criterion(): void
    {
        [$admin,$training,$participant] = $this->setupData();
        $mapping = [];
        foreach (PostEvaluationService::SURVEY_QUESTIONS as $i => $label) {
            $question = $this->question($training);
            $question->update(['question_text' => $label]);
            $this->answer($training, $participant, $question, (string) ($i === 0 ? 3 : 4));
            $mapping[] = ['indicator' => $i, 'question_id' => $question->id, 'role' => 'mandiri', 'scale' => 'four'];
        }
        PostEvaluationSetting::create(['training_id' => $training->id, 'mappings' => $mapping]);
        $iv = PostEvaluationInterview::create(['training_id' => $training->id, 'participant_id' => $participant->id, 'project' => 'Judul Proper dari alumni', 'current_condition' => 'Kondisi terkini dari alumni', 'medium_milestone' => 'Milestone menengah asli', 'long_milestone' => 'Milestone panjang asli', 'medium_evidence_status' => 'sudah', 'long_evidence_status' => 'belum']);
        DB::table('post_evaluation_evidence')->insert(['interview_id' => $iv->id, 'kind' => 'medium', 'path' => 'private/foto.png', 'original_name' => 'foto-menengah.png', 'mime' => 'image/png', 'size' => 50, 'created_at' => now(), 'updated_at' => now()]);
        $rows = app(PostEvaluationService::class)->surveyAnswers($training, $participant);
        $this->assertEquals([3, 4, 4], array_column($rows, 'four'));
        $this->assertEquals([75, 100, 100], array_column($rows, 'converted'));
        $this->actingAs($admin)->get(route('post-evaluation.interview', [$training, $participant]))->assertOk()
            ->assertSeeInOrder(['Informasi wawancara', 'Informasi Proper dari peserta', 'Kondisi terkini dari alumni', 'Jawaban survei', 'Target Aksi Perubahan dalam jangka panjang tercapai', '1. Target jangka menengah', 'Data Jangka Menengah dari peserta', 'Milestone menengah asli', 'Lihat foto evidence', '2. Target jangka panjang', 'Data Jangka Panjang dari peserta', 'Milestone panjang asli', 'Belum Dilaksanakan', '3. Ketercapaian tujuan'])
            ->assertSee('>3</strong>', false)->assertSee('75')->assertDontSee('type="file"', false);
    }

    public function test_survey_original_view_handles_unmapped_answers_and_does_not_invent_four_point_slider_scores(): void
    {
        [$admin,$training,$participant] = $this->setupData();
        $question = $this->question($training);
        $question->update(['question_text' => PostEvaluationService::SURVEY_QUESTIONS[0]]);
        $this->answer($training, $participant, $question, 'Sangat Sesuai');
        $service = app(PostEvaluationService::class);
        $this->assertEquals(4, $service->surveyAnswers($training, $participant)[0]['four']);
        $this->assertSame('Sangat Sesuai', $service->surveyAnswers($training, $participant)[0]['answer']);
        $slider = $this->question($training);
        $slider->update(['type' => 'slider']);
        EvaluationResultL34::create(['training_id' => $training->id, 'participant_id' => $participant->id, 'question_id' => $slider->id, 'evaluator_role' => 'mandiri', 'evaluator_name' => 'Sendiri', 'score' => 80]);
        PostEvaluationSetting::create(['training_id' => $training->id, 'mappings' => [['indicator' => 0, 'question_id' => $slider->id, 'role' => 'mandiri', 'scale' => 'hundred']]]);
        $rows = $service->surveyAnswers($training, $participant);
        $this->assertEquals(4, $rows[0]['four']);
        $this->assertEquals(100, $rows[0]['converted']);
    }

    public function test_interview_scores_are_limited_to_one_hundred_on_server(): void
    {
        [$admin,$training,$participant] = $this->setupData();
        $this->actingAs($admin)->get(route('post-evaluation.interview', [$training, $participant]))->assertOk()->assertSee('interview-score-comment')->assertSee('interview-score-input');
        $data = $this->interviewData();
        $data['scores'][0] = 101;
        $this->putJson(route('post-evaluation.interview.save', [$training, $participant]), $data)->assertUnprocessable()->assertJsonValidationErrors('scores.0');
        $data['scores'][0] = -1;
        $this->putJson(route('post-evaluation.interview.save', [$training, $participant]), $data)->assertUnprocessable()->assertJsonValidationErrors('scores.0');
        $data['scores'][0] = 100;
        $this->putJson(route('post-evaluation.interview.save', [$training, $participant]), $data)->assertOk();
        $this->assertEquals(100, PostEvaluationInterview::where('participant_id', $participant->id)->firstOrFail()->scores[0]);
    }

    public function test_interview_survey_only_shows_three_indicators_and_excludes_other_questions(): void
    {
        [$admin,$training,$participant] = $this->setupData();
        $question = $this->question($training);
        $question->update(['question_text' => PostEvaluationService::SURVEY_QUESTIONS[0]]);
        $this->answer($training, $participant, $question, 'Sesuai');
        $unrelated = $this->question($training);
        $unrelated->update(['question_text' => 'Pertanyaan kepuasan lainnya']);
        $this->answer($training, $participant, $unrelated, 'Sangat Sesuai');
        $rows = app(PostEvaluationService::class)->surveyAnswers($training, $participant);
        $this->assertCount(1, $rows);
        $response = $this->actingAs($admin)->get(route('post-evaluation.interview', [$training, $participant]))->assertOk()->assertDontSee('Pertanyaan kepuasan lainnya');
        foreach (PostEvaluationService::SURVEY_QUESTIONS as $label) {
            $response->assertSee($label);
        }
        $this->assertSame(3, substr_count($response->getContent(), 'data-survey-indicator='));
    }

    public function test_four_point_conversion_is_only_for_four_managerial_programs(): void
    {
        [$admin,$training] = $this->setupData();
        $service = app(PostEvaluationService::class);
        foreach (['PKP', 'PKA', 'PKN', 'LATSAR'] as $program) {
            $training->program_evaluasi = $program;
            foreach (['Tidak Sesuai' => 25, 'Kurang Sesuai' => 50, 'Sesuai' => 75, 'Sangat Sesuai' => 100] as $label => $converted) {
                $this->assertEquals($converted, $service->score((object) ['note' => $label, 'score' => null], ['scale' => 'four'], $training));
            }
        }
        $training->program_evaluasi = 'Teknis';
        $training->save();
        $this->assertNull($service->score((object) ['note' => 'Sesuai', 'score' => null], ['scale' => 'four'], $training));
        $this->assertSame(80.0, $service->score((object) ['note' => null, 'score' => 80], ['scale' => 'hundred'], $training));
        $question = $this->question($training, 'mandiri', 'Teknis');
        $this->assertSame([], $service->automaticSurveySources($training, $service->questions($training)));
    }

    public function test_team_is_created_first_then_existing_users_are_added_from_popup(): void
    {
        [$admin,$training,$participant] = $this->setupData();
        $evaluator = User::factory()->create(['role' => 'evaluator']);
        $this->actingAs($admin)->get(route('post-evaluation.show', [$training, 'tab' => 'pengaturan']))->assertOk()->assertSee('Buat Nama Tim')->assertSee('data-bs-target="#createTeamModal"', false)->assertSee('data-bs-target="#addEvaluatorModal"', false);
        $this->post(route('post-evaluation.team', $training), ['name' => 'Tim Baru', '_settings_form' => 'create_team'])->assertSessionHasNoErrors()->assertRedirect();
        $team = DB::table('post_evaluation_teams')->where('training_id', $training->id)->first();
        $this->assertDatabaseMissing('post_evaluation_team_user', ['team_id' => $team->id]);
        $payload = ['team_id' => $team->id, 'users' => [$evaluator->id], '_settings_form' => 'add_members'];
        $this->post(route('post-evaluation.evaluator.add', $training), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('post-evaluation.evaluator.add', $training), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(1, DB::table('post_evaluation_team_user')->where('team_id', $team->id)->where('user_id', $evaluator->id)->count());
        $other = $this->setupData()[1];
        $otherTeam = DB::table('post_evaluation_teams')->insertGetId(['training_id' => $other->id, 'name' => 'Tim pelatihan lain']);
        $this->post(route('post-evaluation.evaluator.add', $training), ['team_id' => $otherTeam, 'users' => [$evaluator->id]])->assertSessionHasErrors('team_id');
        $url = route('post-evaluation.show', [$training, 'tab' => 'pengaturan']);
        $this->from($url)->post(route('post-evaluation.evaluator.add', $training), ['team_id' => $team->id, '_settings_form' => 'add_members'])->assertSessionHasErrors('users');
        $this->get($url)->assertOk()->assertSee('addEvaluatorModal');
        $this->actingAs($evaluator)->post(route('post-evaluation.evaluator.add', $training), $payload)->assertForbidden();
    }

    public function test_automatic_assignment_balances_eligible_teams_without_changing_existing_results(): void
    {
        [$admin,$training,$participant] = $this->setupData();
        $evaluator = User::factory()->create(['role' => 'evaluator']);
        $teams = [];
        foreach (['Tim 1', 'Tim 2'] as $name) {
            $id = DB::table('post_evaluation_teams')->insertGetId(['training_id' => $training->id, 'name' => $name]);
            DB::table('post_evaluation_team_user')->insert(['team_id' => $id, 'user_id' => $evaluator->id]);
            $teams[] = $id;
        }
        $emptyTeam = DB::table('post_evaluation_teams')->insertGetId(['training_id' => $training->id, 'name' => 'Tanpa evaluator']);
        $existing = PostEvaluationInterview::create(['training_id' => $training->id, 'participant_id' => $participant->id, 'team_id' => $teams[0], 'scores' => [80, 90, 70, 60, 100], 'notes' => ['Tetap tersimpan', '', '', '', ''], 'project' => 'Proper tetap', 'status' => 'completed', 'revision' => 5]);
        foreach (range(1, 6) as $i) {
            Participant::create(['training_id' => $training->id, 'name' => 'Alumni '.$i, 'nip_nik' => 'UJI'.$i, 'instansi' => 'Unit', 'registration_status' => 'approved']);
        }
        $pending = Participant::create(['training_id' => $training->id, 'name' => 'Belum disetujui', 'nip_nik' => 'PENDING', 'registration_status' => 'pending']);
        $this->actingAs($admin)->post(route('post-evaluation.assign.auto', $training))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(7, PostEvaluationInterview::where('training_id', $training->id)->count());
        $counts = PostEvaluationInterview::whereIn('team_id', $teams)->selectRaw('team_id, COUNT(*) as total')->groupBy('team_id')->pluck('total')->all();
        $this->assertLessThanOrEqual(1, max($counts) - min($counts));
        $this->assertDatabaseMissing('post_evaluation_interviews', ['participant_id' => $pending->id]);
        $this->assertDatabaseMissing('post_evaluation_interviews', ['team_id' => $emptyTeam]);
        $this->assertSame([80, 90, 70, 60, 100], $existing->fresh()->scores);
        $this->assertSame(5, $existing->fresh()->revision);
        $this->assertSame('Proper tetap', $existing->fresh()->project);
        $before = PostEvaluationInterview::where('training_id', $training->id)->get()->toArray();
        $this->post(route('post-evaluation.assign.auto', $training))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($before, PostEvaluationInterview::where('training_id', $training->id)->get()->toArray());
        $this->actingAs($evaluator)->post(route('post-evaluation.assign.auto', $training))->assertForbidden();
    }

    public function test_automatic_assignment_requires_team_with_evaluator(): void
    {
        [$admin,$training] = $this->setupData();
        DB::table('post_evaluation_teams')->insert(['training_id' => $training->id, 'name' => 'Tim kosong']);
        $this->actingAs($admin)->post(route('post-evaluation.assign.auto', $training))->assertSessionHasErrors('team');
        $this->assertDatabaseCount('post_evaluation_interviews', 0);
    }

    public function test_evaluator_can_move_or_leave_team_without_moving_participants_or_scores(): void
    {
        [$admin,$training,$participant] = $this->setupData();
        $evaluator = User::factory()->create(['role' => 'evaluator']);
        $ids = [];
        foreach (['Tim Asal', 'Tim Tujuan'] as $name) {
            $ids[] = DB::table('post_evaluation_teams')->insertGetId(['training_id' => $training->id, 'name' => $name]);
        }
        DB::table('post_evaluation_team_user')->insert(['team_id' => $ids[0], 'user_id' => $evaluator->id]);
        $interview = PostEvaluationInterview::create(['training_id' => $training->id, 'participant_id' => $participant->id, 'team_id' => $ids[0], 'scores' => [80, 90, 70, 60, 100], 'notes' => ['Catatan tetap', '', '', '', ''], 'status' => 'completed']);
        $other = Participant::create(['training_id' => $training->id, 'name' => 'Peserta tujuan', 'nip_nik' => 'TUJUAN', 'registration_status' => 'approved']);
        PostEvaluationInterview::create(['training_id' => $training->id, 'participant_id' => $other->id, 'team_id' => $ids[1]]);
        $this->actingAs($admin)->get(route('post-evaluation.show', [$training, 'tab' => 'pengaturan']))->assertOk()->assertSee('Kelola Evaluator');
        $payload = ['user_id' => $evaluator->id, 'from_team_id' => $ids[0], 'to_team_id' => $ids[1]];
        $this->post(route('post-evaluation.evaluator.move', $training), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseMissing('post_evaluation_team_user', ['team_id' => $ids[0], 'user_id' => $evaluator->id]);
        $this->assertDatabaseHas('post_evaluation_team_user', ['team_id' => $ids[1], 'user_id' => $evaluator->id]);
        $this->assertSame($ids[0], $interview->fresh()->team_id);
        $this->assertSame([80, 90, 70, 60, 100], $interview->fresh()->scores);
        $this->actingAs($evaluator)->get(route('post-evaluation.interview', [$training, $participant]))->assertForbidden();
        $this->get(route('post-evaluation.interview', [$training, $other]))->assertOk();
        $this->post(route('post-evaluation.evaluator.move', $training), ['user_id' => $evaluator->id, 'from_team_id' => $ids[1]])->assertForbidden();
        $this->actingAs($admin)->post(route('post-evaluation.evaluator.move', $training), ['user_id' => $evaluator->id, 'from_team_id' => $ids[1]])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertModelExists($evaluator);
        $this->assertDatabaseMissing('post_evaluation_team_user', ['user_id' => $evaluator->id]);
        $this->assertSame('Catatan tetap', $interview->fresh()->notes[0]);
    }

    public function test_evaluator_cannot_be_moved_to_another_training_team(): void
    {
        [$admin,$training] = $this->setupData();
        $other = $this->setupData()[1];
        $evaluator = User::factory()->create(['role' => 'evaluator']);
        $source = DB::table('post_evaluation_teams')->insertGetId(['training_id' => $training->id, 'name' => 'Asal']);
        $target = DB::table('post_evaluation_teams')->insertGetId(['training_id' => $other->id, 'name' => 'Lain pelatihan']);
        DB::table('post_evaluation_team_user')->insert(['team_id' => $source, 'user_id' => $evaluator->id]);
        $this->actingAs($admin)->post(route('post-evaluation.evaluator.move', $training), ['user_id' => $evaluator->id, 'from_team_id' => $source, 'to_team_id' => $target])->assertSessionHasErrors('to_team_id');
        $this->assertDatabaseHas('post_evaluation_team_user', ['team_id' => $source, 'user_id' => $evaluator->id]);
    }

    public function test_summary_matches_pasca_order_with_real_profiles_methods_and_separate_role_averages(): void
    {
        [$admin,$training,$participant] = $this->setupData();
        AlumniProfile::create(['training_id' => $training->id, 'participant_id' => $participant->id, 'pos_during_training' => 'Analis', 'pos_current' => 'Kepala Seksi', 'edu_during_training' => 'S1', 'edu_current' => 'S1', 'rank_during_training' => 'III/a', 'rank_current' => 'III/a', 'unit_during_training' => 'Unit', 'unit_current' => 'Unit', 'dept_during_training' => 'Dinas', 'dept_current' => 'Dinas']);
        $other = Participant::create(['training_id' => $training->id, 'name' => 'Alumni belum merespons', 'nip_nik' => 'SECOND', 'registration_status' => 'approved', 'instansi' => 'Instansi lain']);
        $q = $this->question($training);
        $atasan = $this->question($training, 'atasan');
        $this->answer($training, $participant, $q, 'Sesuai');
        $this->answer($training, $participant, $atasan, 'Sangat Sesuai');
        $method = $this->question($training);
        $method->update(['question_text' => 'Metode Pelaksanaan', 'sub_category' => 'Data Diri Alumni', 'options' => ['Klasikal', 'Blended Learning']]);
        $this->answer($training, $participant, $method, 'Klasikal');
        $supervisorMethod = $this->question($training, 'atasan');
        $supervisorMethod->update(['question_text' => 'Metode Pelaksanaan', 'sub_category' => 'Data Diri Atasan', 'options' => ['Klasikal', 'Blended Learning']]);
        $this->answer($training, $participant, $supervisorMethod, 'Blended Learning');
        $service = app(PostEvaluationService::class);
        $summary = app(PostEvaluationSurveySummaryService::class)->build($training, $service->people($training));
        $this->assertSame(['Berubah' => 1, 'Tetap' => 0], $summary['job_changes']);
        $this->assertSame(1, $summary['job_respondents']);
        $this->assertSame(['Klasikal' => 1], $summary['methods']);
        $this->assertSame(1, $summary['method_respondents']);
        $this->assertSame(3.0, $summary['sections']['Dampak Pelatihan'][0]['roles']['mandiri']['average']);
        $this->assertSame(4.0, $summary['sections']['Dampak Pelatihan'][0]['roles']['atasan']['average']);
        $this->assertSame(1, $summary['sections']['Dampak Pelatihan'][0]['roles']['mandiri']['count']);
        $this->assertSame(2, $summary['total']);
        $this->actingAs($admin)->get(route('post-evaluation.show', [$training, 'tab' => 'ringkasan']))->assertOk()
            ->assertSeeInOrder(['Rekap keberlanjutan hasil wawancara', 'Perubahan jabatan', 'Metode pelatihan', 'Klasikal', 'Sebaran asal instansi', 'Instansi lain'])
            ->assertDontSee('Perbandingan hasil alumni dan atasan')->assertDontSee('tab=perbandingan', false);
    }

    public function test_automatic_three_indicator_scores_and_summary_do_not_double_count_question_versions(): void
    {
        [$admin,$training,$participant] = $this->setupData();
        $first = $this->question($training);
        $second = $this->question($training);
        $this->answer($training, $participant, $first, 'Sangat Sesuai');
        $this->answer($training, $participant, $second, 'Kurang Sesuai');
        $service = app(PostEvaluationService::class);
        $people = $service->people($training);
        $this->assertSame(50.0, $people[0]['survey'][0]);
        $this->assertCount(1, $service->surveyAnswers($training, $participant));
        $summary = app(PostEvaluationSurveySummaryService::class)->build($training, $people);
        $this->assertCount(1, $summary['sections']['Dampak Pelatihan']);
        $this->assertSame(2.0, $summary['sections']['Dampak Pelatihan'][0]['roles']['mandiri']['average']);
        $this->assertNull($summary['sections']['Dampak Pelatihan'][0]['roles']['atasan']['average']);
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
        $this->assertSame(['expected' => 1, 'filled' => 1], $people[0]['coverage'][0]);
        $this->answer($t, $p, $other, 'Sangat Sesuai');
        $this->assertSame(75.0, $service->people($t)[0]['survey'][0]);
        $this->assertDatabaseCount('participants', 1);
    }

    public function test_survey_sources_are_automatic_and_ignore_manual_or_foreign_program_mappings(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $foreign = $this->question($t, 'mandiri', 'PKA');
        $q = $this->question($t);
        $this->answer($t, $p, $foreign, 'Sangat Sesuai');
        $this->answer($t, $p, $q, 'Sesuai');
        PostEvaluationSetting::create(['training_id' => $t->id, 'mappings' => [['indicator' => 0, 'question_id' => $foreign->id, 'role' => 'mandiri', 'scale' => 'four']]]);
        $service = app(PostEvaluationService::class);
        $this->assertSame(75.0, $service->people($t)[0]['survey'][0]);
        $this->actingAs($admin)->get(route('post-evaluation.show', [$t, 'tab' => 'ringkasan']))->assertOk()->assertDontSee('Pemetaan indikator survei')->assertDontSee('Atur pemetaan');
        $this->post('/hasil-evaluasi-pasca/'.$t->id.'/pengaturan', [])->assertNotFound();
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
        $this->get(route('post-evaluation.expose', $t))->assertOk()->assertSee('Mekanisme Konfirmasi Jawaban dan Ekspose');
        $this->get(route('post-evaluation.pdf', $t))->assertOk()->assertDownload();
    }

    public function test_explicit_continuation_updates_scores_and_scoped_recap(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $this->actingAs($admin)->get(route('post-evaluation.interview', [$t,$p]))->assertOk()->assertSee('data-continuation', false);
        $iv = PostEvaluationInterview::where('participant_id',$p->id)->firstOrFail();
        $iv->update(['scores'=>[0,null,null,null,null]]);
        $data=$this->interviewData();
        $data['continuation']=['no','no','yes','yes','yes'];
        $data['scores'][0] = null;
        $data['scores'][1] = null;
        $this->putJson(route('post-evaluation.interview.save',[$t,$p]),$data)
            ->assertOk()->assertJsonPath('scores.0', 0)->assertJsonPath('scores.1', 25);
        $iv->refresh();
        $this->assertEquals([0,25,70,60,100],$iv->scores);
        $this->assertSame([false,false,true,true,true],$iv->continuation);
        $summary=app(PostEvaluationService::class)->summary($t,app(PostEvaluationService::class)->people($t));
        $this->assertSame(1,$summary['continuation_recap'][0]['no']);
        $this->assertNull($summary['continuation_recap'][0]['average_yes']);
        $this->assertSame(70.0,$summary['continuation_recap'][2]['average_yes']);
        $data['revision']=1;
        $data['scores'][0] = 42.5;
        $this->putJson(route('post-evaluation.interview.save',[$t,$p]),$data)
            ->assertOk()->assertJsonPath('scores.0', 42.5);
        $this->assertEquals(42.5, $iv->fresh()->scores[0]);
        $this->assertFalse($iv->fresh()->continuation[0]);
        $data['revision']=2;
        $data['continuation'][0]=null;
        $this->putJson(route('post-evaluation.interview.save',[$t,$p]),$data)->assertUnprocessable()->assertJsonValidationErrors('continuation');
        $data['continuation'][0]='yes'; $data['scores'][0]=null;
        $this->putJson(route('post-evaluation.interview.save',[$t,$p]),$data)->assertUnprocessable()->assertJsonValidationErrors('scores');
        $data['status']='draft';
        $this->putJson(route('post-evaluation.interview.save',[$t,$p]),$data)->assertOk();
        $this->assertNull($iv->fresh()->scores[0]);
        $this->get(route('post-evaluation.show', $t))->assertOk()
            ->assertSee('Rekap keberlanjutan hasil wawancara')->assertSee('Sebaran asal instansi');
    }

    public function test_defaults_existing_unscored_no_without_overwriting_other_scores(): void
    {
        [$admin, $training, $participant] = $this->setupData();
        $interview = PostEvaluationInterview::create([
            'training_id' => $training->id,
            'participant_id' => $participant->id,
            'continuation' => [false, false, true, null, false],
            'scores' => [null, 0, null, null, 65],
            'notes' => ['Catatan tetap', '', '', '', ''],
            'status' => 'draft',
            'revision' => 2,
        ]);
        $migration = require database_path('migrations/2026_11_20_090000_default_unscored_no_post_evaluation_interviews.php');
        $migration->up();
        $migration->up();

        $interview->refresh();
        $this->assertSame([25, 0, null, null, 65], $interview->scores);
        $this->assertSame(3, $interview->revision);
        $this->assertSame('Catatan tetap', $interview->notes[0]);
    }

    public function test_legacy_percentage_migration_backs_up_scores_and_preserves_notes(): void
    {
        [$admin,$t,$p]=$this->setupData();
        $iv=PostEvaluationInterview::create(['training_id'=>$t->id,'participant_id'=>$p->id,'scores'=>[60,79,89,100,null],
            'notes'=>['Bukti lama','','','',''],'status'=>'completed','revision'=>7]);
        $migration=require database_path('migrations/2026_11_19_090000_align_post_evaluation_interview_percentages.php');
        $migration->up();
        $this->assertEquals([25,50,75,100,null],$iv->fresh()->scores);
        $this->assertSame([false,true,true,true,null],$iv->fresh()->continuation);
        $this->assertSame('completed',$iv->fresh()->status);
        $this->assertSame('Bukti lama',$iv->fresh()->notes[0]);
        $this->assertSame(8,$iv->fresh()->revision);
        $this->assertEquals([60,79,89,100,null],json_decode(DB::table('post_evaluation_score_backups')->where('interview_id',$iv->id)->value('original_scores'),true));
        $migration->up();
        $this->assertSame(8,$iv->fresh()->revision);
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
        $this->get(route('post-evaluation.show', $t))->assertOk()
            ->assertSee('Ringkasan')->assertSee('Peserta &amp; Wawancara', false)
            ->assertDontSee('Link Pengisian Proper')->assertDontSee('Link publik pengisian inovasi')
            ->assertDontSee('Unduh PDF')->assertDontSee('Excel Survei')->assertDontSee('>Ekspose</a>', false)
            ->assertDontSee('Buat ulang narasi AI')->assertDontSee('pasca-narrative-form');
        $this->get(route('post-evaluation.show', [$t, 'tab' => 'jawaban']))->assertForbidden();
        $this->get(route('post-evaluation.expose', $t))->assertForbidden();
        $this->get(route('post-evaluation.pdf', $t))->assertForbidden();
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
        [$admin,$training] = $this->setupData();
        $service = app(PostEvaluationService::class);
        $this->assertSame(0.0, $service->score((object) ['score' => 0, 'note' => null], ['scale' => 'hundred'], $training));
        $this->assertSame(25.0, $service->score((object) ['score' => null, 'note' => '1'], ['scale' => 'four'], $training));
        $this->assertSame([2, 2, 2, 2], $service->histogram([0, 25, 25.1, 50, 50.1, 75, 75.1, 100, null])['counts']);
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
        $this->get(route('post-evaluation.proper.public', $t))->assertOk()->assertDontSee('id="properSuccessDialog"', false);
        $data['medium_evidence'] = UploadedFile::fake()->image('bukti.png');
        $this->post(route('post-evaluation.proper.store', $t), $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->get(route('post-evaluation.proper.public', $t))->assertOk()
            ->assertSee('id="properSuccessDialog"', false)->assertSee('Proper Berhasil Dikirim');
        $this->get(route('post-evaluation.proper.public', $t))->assertOk()->assertDontSee('id="properSuccessDialog"', false);
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

    public function test_proper_uses_one_uid_link_and_old_upload_routes_are_unavailable(): void
    {
        Storage::fake('local');
        [$admin,$t,$p] = $this->setupData();
        $this->assertTrue(Str::isUuid($t->proper_uid));
        $uid = $t->proper_uid;
        $t->update(['lokasi' => 'Lokasi baru']);
        $this->assertSame($uid, $t->fresh()->proper_uid);
        $this->get('/isi-proper/'.$t->id)->assertNotFound();
        $this->post('/isi-proper/'.$t->id, [])->assertNotFound();
        $this->postJson('/isi-proper/'.$t->id.'/verifikasi', [])->assertNotFound();
        $this->get(route('post-evaluation.proper.public', $t->proper_uid))->assertOk()->assertSee('NIP/NIK peserta');
        $file = UploadedFile::fake()->create('bukti.pdf', 10, 'application/pdf');
        $this->post(route('post-evaluation.proper.store', $t->proper_uid), ['participant_identity' => $p->nip_nik, 'proper_title' => 'Proper Uji', 'medium_milestone' => 'Target menengah', 'long_milestone' => 'Target panjang', 'medium_evidence_status' => 'belum', 'long_evidence_status' => 'belum', 'current_condition' => 'Berjalan', 'document' => $file])->assertSessionHasNoErrors()->assertRedirect();
        $evidence = DB::table('post_evaluation_evidence')->first();
        Storage::disk('local')->assertExists($evidence->path);
        $this->actingAs($admin)->get(route('post-evaluation.show', [$t, 'tab' => 'wawancara']))->assertOk()->assertSee('/isi-proper/'.$uid)->assertDontSee('Buat Link Proper Alumni')->assertDontSee('link-bukti');
        $this->get(route('post-evaluation.interview', [$t, $p]))->assertOk()->assertDontSee('type="file"', false)->assertSee('bukti.pdf');
        $this->get(route('post-evaluation.evidence', [$t, $evidence->id]))->assertOk()->assertDownload();
        $iv = PostEvaluationInterview::where('participant_id', $p->id)->firstOrFail();
        $data = $this->interviewData($iv->revision);
        $data['project'] = 'Tidak boleh menimpa Proper';
        $this->put(route('post-evaluation.interview.save', [$t, $p]), $data)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Proper Uji', $iv->fresh()->project);
        $evaluator = User::factory()->create(['role' => 'pengajar']);
        $this->post(route('post-evaluation.team', $t), ['name' => 'Tim Evaluator', 'users' => [$evaluator->id]])->assertRedirect();
        $team = DB::table('post_evaluation_teams')->where('training_id', $t->id)->first();
        $this->post(route('post-evaluation.assign', $t), ['team_id' => $team->id, 'participant_ids' => [$p->id]])->assertRedirect();
        $this->actingAs($evaluator)->get(route('post-evaluation.interview', [$t, $p]))->assertOk()->assertSee('bukti.pdf')->assertSee('Lihat')->assertDontSee('type="file"', false);
        $this->get(route('post-evaluation.evidence', [$t, $evidence->id, 'preview' => 1]))->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('Content-Disposition', 'inline; filename=bukti.pdf');
        $this->actingAs($admin);

        $this->post('/hasil-evaluasi-pasca/'.$t->id.'/wawancara/'.$p->id.'/link-bukti', [])->assertNotFound();
        $this->post('/hasil-evaluasi-pasca/'.$t->id.'/wawancara/'.$p->id.'/bukti', [])->assertNotFound();
        $this->get('/bukti-pasca/tautan-lama')->assertNotFound();
        $outsider = User::factory()->create(['role' => 'admin_bidang', 'bidang' => 'Bidang B']);
        $this->actingAs($outsider)->get(route('post-evaluation.evidence', [$t, $evidence->id]))->assertForbidden();
    }
    public function test_interview_live_summary_shows_weighted_and_partial_values_after_survey(): void
    {
        [$admin, $training, $participant] = $this->setupData();
        $interview = PostEvaluationInterview::firstOrCreate(['training_id' => $training->id, 'participant_id' => $participant->id]);
        $interview->update(['scores' => [80, 90, 90, 86, 100], 'status' => 'completed']);
        $response = $this->actingAs($admin)->get(route('post-evaluation.interview', [$training, $participant]));
        $response->assertOk()->assertSeeInOrder(['Jawaban survei', 'PENGISIAN LANGSUNG', 'Nilai akhir berbobot', '87,80', '5/5 nilai terisi', 'Wawancara ekspose', '88,67']);
        $interview->update(['scores' => [0, null, null, null, null], 'status' => 'draft']);
        $this->get(route('post-evaluation.interview', [$training, $participant]))->assertOk()->assertSee('1/5 nilai terisi')->assertSee('id="liveWeightedScore" aria-live="polite">-</div>', false)->assertSee('id="liveInterviewStatus">Draf</strong>', false);
    }

    public function test_evaluator_training_list_only_contains_explicit_team_assignments_and_revokes_access(): void
    {
        [$admin, $assigned] = $this->setupData();
        $assigned->update(['nama_pelatihan' => 'Pelatihan Ditugaskan']);
        $other = $assigned->replicate(['proper_uid']); $other->nama_pelatihan = 'Pelatihan Tim Orang Lain'; $other->save();
        $empty = $assigned->replicate(['proper_uid']); $empty->nama_pelatihan = 'Pelatihan Tanpa Penugasan'; $empty->save();
        $evaluator = User::factory()->create(['role' => 'evaluator', 'bidang' => $assigned->bidang]);
        $colleague = User::factory()->create(['role' => 'evaluator']);
        $this->actingAs($evaluator)->get(route('post-evaluation.index'))->assertOk()->assertDontSee('Pelatihan Ditugaskan')->assertDontSee('Pelatihan Tanpa Penugasan');
        $this->actingAs($admin)->post(route('post-evaluation.team', $assigned), ['name' => 'Tim Satu', 'users' => [$evaluator->id]])->assertRedirect();
        $this->post(route('post-evaluation.team', $assigned), ['name' => 'Tim Dua', 'users' => [$evaluator->id]])->assertRedirect();
        $this->post(route('post-evaluation.team', $other), ['name' => 'Tim Orang Lain', 'users' => [$colleague->id]])->assertRedirect();
        $response = $this->actingAs($evaluator)->get(route('post-evaluation.index'))->assertOk()->assertSee('Pelatihan Ditugaskan')->assertDontSee('Pelatihan Tim Orang Lain')->assertDontSee('Pelatihan Tanpa Penugasan');
        $this->assertSame(1, $response->viewData('trainings')->total());
        $this->get(route('post-evaluation.index', ['q' => 'Pelatihan']))->assertOk()->assertDontSee('Pelatihan Tim Orang Lain');
        $this->get(route('post-evaluation.show', $other))->assertForbidden();
        DB::table('post_evaluation_team_user')->where('user_id', $evaluator->id)->delete();
        $this->get(route('post-evaluation.index'))->assertOk()->assertDontSee('Pelatihan Ditugaskan');
        $this->get(route('post-evaluation.show', $assigned))->assertForbidden();
        $this->actingAs($admin)->get(route('post-evaluation.index'))->assertOk()->assertSee('Pelatihan Ditugaskan')->assertSee('Pelatihan Tim Orang Lain')->assertSee('Pelatihan Tanpa Penugasan');
    }

}
