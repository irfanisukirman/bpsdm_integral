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
    public function test_word_export_labels_profile_comparisons_without_arrow_placeholders(): void
    {
        $training = $this->training('PKP');
        \App\Models\Participant::create(['training_id' => $training->id, 'name' => 'Alumni Uji', 'nip_nik' => '123456', 'jabatan' => 'Analis', 'registration_status' => 'approved']);
        $response = $this->actingAs(User::factory()->create(['role' => 'superadmin']))->get(route('evall34.export_word', $training));
        $response->assertOk()->assertDownload();
        $path = tempnam(sys_get_temp_dir(), 'l34-word-test-');
        try {
            file_put_contents($path, $response->streamedContent());
            $zip = new \ZipArchive;
            $this->assertTrue($zip->open($path));
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            $document = new \DOMDocument;
            $this->assertTrue($document->loadXML($xml));
            $text = $document->textContent;
            $this->assertStringContainsString('Saat pelatihan: Analis; Saat evaluasi: Belum tersedia', $text);
            $this->assertStringContainsString('Pendidikan: Saat pelatihan: Belum tersedia', $text);
            $this->assertStringNotContainsString('->', $text);
        } finally {
            unlink($path);
        }
    }

    public function test_report_signatories_are_saved_per_training_and_replaced_in_download(): void
    {
        $training = $this->training('PKP');
        $other = $this->training('PKP');
        $admin = User::factory()->create(['role' => 'superadmin']);
        $data = ['head_title' => 'Kepala Bidang Uji & Sertifikasi', 'head_name' => 'Nama Pejabat Baru', 'head_nip' => '198001012000011001', 'monitor_title' => 'Tim Evaluasi Uji', 'monitor_name' => 'Nama Petugas Baru', 'monitor_nip' => '198501012010011001'];
        $this->actingAs($admin)->get(route('evall34.dashboard', $training))->assertOk()->assertSee('Penandatangan Laporan Word')->assertSee('Simpan &amp; Unduh Word', false);
        $this->post(route('evall34.report-signatories', $training), $data + ['download' => '1'])->assertRedirect(route('evall34.export_word', $training));
        $this->assertSame($data, $training->fresh()->l34ReportSignatories());
        $this->assertNull($other->fresh()->l34_report_signatories);
        $response = $this->get(route('evall34.export_word', $training))->assertOk()->assertDownload();
        $path = tempnam(sys_get_temp_dir(), 'l34-sign-test-');
        try {
            file_put_contents($path, $response->streamedContent());
            $zip = new \ZipArchive;
            $this->assertTrue($zip->open($path));
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            $document = new \DOMDocument;
            $this->assertTrue($document->loadXML($xml));
            foreach ($data as $value) $this->assertStringContainsString($value, $document->textContent);
            $this->assertStringNotContainsString('Angga Muchlis', $document->textContent);
            $this->assertStringNotContainsString('Lucky Rizki', $document->textContent);
            $this->assertStringNotContainsString('${l34_sign_', $document->textContent);
        } finally { unlink($path); }
    }

    public function test_report_signatories_validate_nip_and_restrict_unit_access(): void
    {
        $training = $this->training('PKP');
        $url = route('evall34.report-signatories', $training);
        $this->actingAs(User::factory()->create(['role' => 'superadmin']))->post($url, ['head_nip' => 'invalid'])->assertSessionHasErrors(['head_nip','head_name','monitor_name']);
        $this->assertNull($training->fresh()->l34_report_signatories);
        $this->actingAs(User::factory()->create(['role' => 'admin_bidang', 'bidang' => 'Bidang Lain']))->post($url, $training->l34ReportSignatories())->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin_bidang', 'bidang' => $training->bidang]))->post($url, $training->l34ReportSignatories())->assertRedirect(route('evall34.dashboard', $training));
    }

    public function test_monitoring_report_uses_one_team_signatory_in_both_locations_without_signature_images(): void
    {
        $training = $this->training('PKP');
        $admin = User::factory()->create(['role' => 'superadmin']);
        $data = ['head_title' => 'Kepala Bidang Uji & Monitoring', 'head_name' => 'Pejabat Monitoring Baru', 'head_nip' => '198001012000011001', 'monitor_title' => 'Tim Monitoring Baru', 'monitor_name' => 'Petugas Monitoring Tunggal', 'monitor_nip' => '198501012010011001'];
        $this->actingAs($admin)->get(route('monitoring.report-settings', $training))->assertOk()->assertSee('Satu isian Tim Monitoring mengisi seluruh bagian');
        $this->post(route('monitoring.report-settings.save', $training), $data + ['download' => 1])->assertRedirect(route('monitoring.export.laporan', $training));
        $this->assertSame($data, $training->fresh()->monitoringReportSignatories());
        $this->assertNull($training->fresh()->l34_report_signatories);
        $response = $this->get(route('monitoring.export.laporan', $training))->assertOk()->assertDownload();
        $path = tempnam(sys_get_temp_dir(), 'monitoring-word-test-');
        try {
            file_put_contents($path, $response->streamedContent());
            $zip = new \ZipArchive;
            $this->assertTrue($zip->open($path));
            $xml = $zip->getFromName('word/document.xml');
            $this->assertFalse($zip->locateName('word/media/image19.png'));
            $this->assertFalse($zip->locateName('word/media/image20.png'));
            $zip->close();
            $document = new \DOMDocument;
            $this->assertTrue($document->loadXML($xml));
            $text = $document->textContent;
            foreach ($data as $value) $this->assertStringContainsString($value, $text);
            $this->assertSame(2, substr_count($text, $data['monitor_name']));
            $this->assertSame(2, substr_count($text, $data['monitor_nip']));
            $this->assertStringNotContainsString('Tantan Gunawan', $text);
            $this->assertStringNotContainsString('RISGIYANTO', $text);
            $this->assertStringNotContainsString('${monitor_sign_', $text);
        } finally { unlink($path); }
    }

    public function test_monitoring_signatories_are_scoped_and_validate_before_saving(): void
    {
        $training = $this->training('PKP');
        $url = route('monitoring.report-settings.save', $training);
        $this->actingAs(User::factory()->create(['role' => 'admin_bidang', 'bidang' => 'Bidang Lain']))->get(route('monitoring.report-settings', $training))->assertForbidden();
        $this->post($url, $training->monitoringReportSignatories())->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'superadmin']))->post($url, ['head_nip' => 'abc'])->assertSessionHasErrors(['head_nip','head_name','monitor_name']);
        $this->assertNull($training->fresh()->monitoring_report_signatories);
        $this->actingAs(User::factory()->create(['role' => 'admin_bidang', 'bidang' => $training->bidang]))->post($url, $training->monitoringReportSignatories())->assertRedirect(route('monitoring.report-settings', $training));
    }

    public function test_followup_and_checklist_use_shared_monitoring_signatures_and_correct_download_target(): void
    {
        $training = $this->training('PKP');
        $data = array_replace($training->monitoringReportSignatories(), ['head_name' => 'Kepala Bersama Uji', 'monitor_name' => 'Tim Bersama Uji']);
        $this->actingAs(User::factory()->create(['role' => 'superadmin']));
        foreach (['tindaklanjut' => 'monitoring.export.tindaklanjut', 'ceklis' => 'monitoring.export.rekap'] as $type => $route) {
            $this->get(route('monitoring.report-settings', ['id' => $training->id, 'document' => $type]))->assertOk()->assertSee('name="document" value="'.$type.'"', false);
            $this->post(route('monitoring.report-settings.save', $training), $data + ['document' => $type, 'download' => 1])->assertRedirect(route($route, $training));
            $response = $this->get(route($route, $training))->assertOk()->assertDownload();
            $path = tempnam(sys_get_temp_dir(), 'monitoring-shared-test-');
            try {
                file_put_contents($path, $response->streamedContent());
                if ($type === 'ceklis') {
                    $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet();
                    $text = json_encode($sheet->toArray(), JSON_UNESCAPED_UNICODE);
                    foreach ([$data['head_name'],$data['monitor_name'],$data['head_nip'],$data['monitor_nip']] as $value) $this->assertStringContainsString($value, $text);
                } else {
                    $zip = new \ZipArchive;
                    $this->assertTrue($zip->open($path));
                    $xml = $zip->getFromName('word/document.xml');
                    $zip->close();
                    $dom = new \DOMDocument;
                    $this->assertTrue($dom->loadXML($xml));
                    foreach ($data as $value) $this->assertStringContainsString($value, $dom->textContent);
                    $this->assertStringNotContainsString('${monitor_sign_', $dom->textContent);
                    $this->assertStringNotContainsString('r:embed="rId6"', $xml);
                    $this->assertStringNotContainsString('r:embed="rId7"', $xml);
                }
            } finally { unlink($path); }
        }
    }

    public function test_activity_report_combines_evaluations_attendance_agenda_and_teachers(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $training = $this->training('PKP');
        $admin = User::factory()->create(['role' => 'superadmin']);
        $teacher = User::factory()->create(['name' => 'Pengajar Integrasi Uji', 'role' => 'pengajar']);
        $p = \App\Models\Participant::create(['training_id' => $training->id, 'name' => 'Peserta Integrasi Uji', 'nip_nik' => '198001012000011001', 'registration_status' => 'approved']);
        $schedule = \App\Models\Schedule::create(['training_id' => $training->id, 'date' => '2024-01-01', 'start_time' => '08:00', 'end_time' => '10:00', 'activity' => 'Materi Integrasi Uji', 'pic' => 'Panitia', 'schedule_type' => 'learning', 'jp' => 2, 'duration_unit' => 'JP', 'pengajar_id' => $teacher->id]);
        \App\Models\Attendance::create(['schedule_id' => $schedule->id, 'participant_id' => $p->id, 'status' => 'hadir']);
        $q = Question::create(['bidang' => 'Semua Bidang', 'program_evaluasi' => 'PKP', 'category' => 'l1_penyelenggara', 'type' => 'slider', 'metode' => 'semua', 'question_text' => 'Layanan penyelenggara uji']);
        \App\Models\EvaluationResultL1::create(['training_id' => $training->id, 'participant_id' => $p->id, 'question_id' => $q->id, 'score' => 90]);
        \App\Models\EvaluationResultL2::create(['participant_id' => $p->id, 'pretest' => 50, 'postest' => 80]);
        \App\Models\EvaluationL1TextSummary::create(['training_id' => $training->id, 'conclusion' => 'Layanan cukup terstruktur.', 'follow_up' => 'Perbaiki koordinasi jadwal.']);
        $q34 = Question::create(['bidang' => 'Semua Bidang', 'program_evaluasi' => 'PKP', 'category' => 'l34_mandiri', 'sub_category' => 'Perubahan Perilaku', 'type' => 'dropdown', 'metode' => 'semua', 'question_text' => 'Perilaku uji', 'options' => ['Tidak Sesuai','Kurang Sesuai','Sesuai','Sangat Sesuai']]);
        \App\Models\EvaluationResultL34::create(['training_id' => $training->id, 'participant_id' => $p->id, 'question_id' => $q34->id, 'evaluator_name' => 'Diri sendiri', 'evaluator_role' => 'mandiri', 'note' => 'Sesuai']);
        $this->actingAs($admin);
        $response = $this->get(route('training-activity-report.index', $training))->assertOk();
        $data = $response->viewData('data');
        $this->assertSame('75,0', $data['values']['nilai_evaluasi_l3']);
        $this->assertSame('50,0', $data['values']['nilai_pretest_l2']);
        $this->assertSame('30,0', $data['values']['peningkatan_l2']);
        $this->assertSame('100,0%', $data['values']['rata_rata_kehadiran']);
        $this->assertSame('Perbaiki koordinasi jadwal.', $data['values']['tindak_lanjut_evaluasi']);
        $this->assertSame($teacher->name, $data['teachers'][0]['teacher_name']);
        $content = collect(\App\Services\AiActivityReportService::FIELDS)->mapWithKeys(fn ($field) => [$field => 'Draf '.$field])->all();
        config()->set('services.ai.provider','gemini'); config()->set('services.gemini.api_key','test'); config()->set('services.gemini.model','test');
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode($content)]]]]]])]);
        $this->post(route('training-activity-report.ai-draft', $training))->assertSessionHas('activity_report_ai_draft');
        \Illuminate\Support\Facades\Http::assertSent(function ($request) use ($p,$teacher) {
            $text = $request['contents'][0]['parts'][0]['text'];
            return str_contains($text,'Perbaiki koordinasi jadwal.') && str_contains($text,'pretest_level_2') && str_contains($text,'pengajar_agregat') && str_contains($text,'Materi Integrasi Uji') && !str_contains($text,$p->name) && !str_contains($text,$teacher->name);
        });
        $download = $this->post(route('training-activity-report.generate', $training), ['format' => 'docx'])->assertOk()->assertDownload();
        $zip = new \ZipArchive; $this->assertTrue($zip->open($download->baseResponse->getFile()->getPathname()));
        $xml = $zip->getFromName('word/document.xml'); $zip->close();
        $dom = new \DOMDocument; $this->assertTrue($dom->loadXML($xml));
        foreach (['Daftar Pengajar','Rekap Kehadiran','Pengajar Integrasi Uji','Materi Integrasi Uji','Perbaiki koordinasi jadwal.','Pre-test: 50,0','Post-test: 80,0','Level 3: 75,0'] as $text) $this->assertStringContainsString($text,$dom->textContent);
    }

}
