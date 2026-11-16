<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\Training;
use App\Models\User;
use App\Imports\QuestionImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EvaluationProgramTest extends TestCase
{
    use RefreshDatabase;

    private function training(string $program): Training
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        return Training::create([
            'nama_pelatihan' => 'Pelatihan Uji', 'bidang' => 'Semua Bidang',
            'program_evaluasi' => $program, 'model' => 'standar', 'metode' => 'klasikal',
            'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 10, 'jp' => 10,
            'tgl_mulai' => '2024-01-01', 'tgl_selesai' => '2024-01-02', 'created_by' => $user->id,
        ]);
    }

    private function questions(): void
    {
        foreach (['CPNS', 'PKP', 'PKA', 'PKN', 'LATSAR', 'PKTI/PKTU'] as $program) {
            foreach (['mandiri', 'atasan', 'rekan'] as $role) {
                DB::table('evaluation_questions')->insert([
                    'bidang' => 'Semua Bidang', 'training_type' => 'Semua Bidang',
                    'program_evaluasi' => $program, 'category' => 'l34_'.$role,
                    'sub_category' => 'Dampak Pelatihan', 'metode' => 'semua',
                    'type' => 'slider', 'question_text' => $program.' '.$role,
                ]);
            }
        }
    }

    public function test_legacy_programs_are_read_as_new_groups_with_correct_roles_and_dates(): void
    {
        foreach (['CPNS', 'PKP', 'PKA', 'PKN', 'LATSAR'] as $program) {
            $training = new Training;
            $training->setRawAttributes(['program_evaluasi' => $program, 'tgl_selesai' => '2024-01-02']);
            $this->assertSame($program === 'CPNS' ? 'LATSAR' : $program, $training->program_evaluasi);
            $this->assertSame(['mandiri', 'atasan'], $training->evaluationRoles());
            $this->assertSame('2025-01-02', $training->tgl_sebar_l34->toDateString());
        }
        $training = new Training(['program_evaluasi' => 'PKTI/PKTU', 'tgl_selesai' => '2024-01-02']);
        $this->assertSame('Teknis', $training->program_evaluasi);
        $this->assertCount(3, $training->evaluationRoles());
        $this->assertSame('2024-05-02', $training->tgl_sebar_l34->toDateString());
    }

    public function test_new_groups_use_legacy_questions_and_exclude_managerial_peer_questions(): void
    {
        $this->questions();
        foreach (['LATSAR', 'PKP', 'PKA', 'PKN'] as $program) {
            $managerial = $this->training($program);
            $questions = Question::forTraining($managerial, 'l34_mandiri')->get();
            $this->assertCount($program === 'LATSAR' ? 2 : 1, $questions);
            $this->assertSame(0, Question::forTraining($managerial, 'l34_rekan')->count());
            if ($program !== 'LATSAR') {
                $this->assertSame([$program.' mandiri'], $questions->pluck('question_text')->all());
            }
        }
        $technical = $this->training('Teknis');
        $this->assertSame(1, Question::forTraining($technical, 'l34_rekan')->count());
    }

    public function test_public_managerial_peer_links_and_submissions_are_rejected(): void
    {
        $this->questions();
        $training = $this->training('PKP');
        $this->get(route('public.l34.gateway', $training))->assertOk()->assertDontSee('REKAN KERJA');
        $this->get(route('public.l34.form', [$training, 'rekan']))->assertNotFound();
        $this->post(route('public.l34.store', [$training, 'rekan']), [])->assertNotFound();
        $technical = $this->training('Teknis');
        $this->get(route('public.l34.gateway', $technical))->assertOk()->assertSee('REKAN KERJA');
    }

    public function test_question_form_rejects_managerial_peer_category(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($user)->post(route('questions.store'), [
            'bidang' => 'Semua Bidang', 'category' => 'l34_rekan',
            'program_evaluasi' => 'PKP', 'sub_category' => 'Dampak Pelatihan',
            'metode' => 'semua', 'type' => 'slider', 'question_text' => 'Uji',
        ])->assertSessionHasErrors('category');
    }

    public function test_managerial_monitoring_dashboard_and_excel_use_two_roles(): void
    {
        $this->questions();
        $training = $this->training('PKP');
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($user)->get(route('evall34.index', $training))
            ->assertOk()->assertSee('Target 2 Penilai')->assertDontSee('Link Rekan Kerja');
        $this->get(route('evall34.dashboard', $training))->assertOk();
        $this->get(route('evall34.export', $training))->assertOk()->assertDownload();
        $this->get(route('evall34.export_word', $training))->assertOk()->assertDownload();
        $this->get(route('evall34.export_invitation', $training))->assertOk()->assertDownload();
        $view = (new \App\Exports\L34JawabanSheet($training))->view()->render();
        $this->assertStringNotContainsString('<th>Rekan', $view);
        $this->assertStringContainsString('colspan="2"', $view);
        $this->get(route('questions.index', ['program' => 'PKP']))
            ->assertOk()->assertSee('PKP mandiri')->assertDontSee('PKP rekan')->assertDontSee('PKA mandiri')->assertDontSee('CPNS mandiri');
    }

    public function test_level_one_also_preserves_and_filters_each_program(): void
    {
        $import = new QuestionImport('Semua Bidang');
        foreach (['LATSAR', 'PKP', 'PKA', 'PKN', 'Teknis'] as $program) {
            $question = $import->model([
                'pertanyaan' => 'Penyelenggaraan '.$program, 'level_peran' => 'Penyelenggara',
                'program_evaluasi' => $program, 'metode' => 'klasikal', 'tipe_jawaban' => 'slider',
            ]);
            $question->save();
            $this->assertSame($program, $question->fresh()->program_evaluasi);
        }
        foreach (['LATSAR', 'PKP', 'PKA', 'PKN', 'Teknis'] as $program) {
            $training = $this->training($program);
            $this->assertSame(['Penyelenggaraan '.$program], Question::forTraining($training, 'l1_penyelenggara')->pluck('question_text')->all());
        }
    }

    public function test_public_supervisor_form_shows_method_dropdown_and_explanation(): void
    {
        $training = $this->training('PKP');
        \App\Models\Participant::create([
            'training_id' => $training->id, 'name' => 'Alumni Uji', 'nip_nik' => '1234567890',
            'jabatan' => 'Analis', 'instansi' => 'BPSDM', 'registration_status' => 'approved',
        ]);
        $question = Question::create([
            'bidang' => 'Semua Bidang', 'program_evaluasi' => 'PKP', 'category' => 'l34_atasan',
            'sub_category' => 'Data Diri Atasan', 'metode' => 'semua', 'type' => 'dropdown',
            'question_text' => "Metode Pelaksanaan\n\nKlasikal : Pembelajaran tatap muka.\nBlended Learning : Pembelajaran kombinasi.",
            'options' => ['Klasikal', 'Blended Learning'],
        ]);
        $this->get(route('public.l34.form', [$training, 'atasan']))
            ->assertOk()->assertSee('Metode Pelaksanaan</label>', false)
            ->assertSee('question-description-'.$question->id)
            ->assertSee('Klasikal : Pembelajaran tatap muka.')
            ->assertSee('<option value="Klasikal"', false)
            ->assertSee('<option value="Blended Learning"', false);
    }

    public function test_import_accepts_legacy_names_but_rejects_managerial_peer_questions(): void
    {
        $import = new QuestionImport('Semua Bidang');
        $row = ['pertanyaan' => 'Uji', 'level_peran' => 'Mandiri', 'program_evaluasi' => 'LATSAR', 'sub_kategori' => 'Dampak Pelatihan'];
        $this->assertSame('LATSAR', $import->model($row)->program_evaluasi);
        $this->expectException(\InvalidArgumentException::class);
        $import->model(array_replace($row, ['level_peran' => 'Rekan']));
    }
}
