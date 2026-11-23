<?php

namespace Tests\Feature;

use App\Models\EvaluationResultL34;
use App\Models\Participant;
use App\Models\PostEvaluationInterview;
use App\Models\Question;
use App\Models\Training;
use App\Models\User;
use App\Services\PostEvaluationImportService;
use App\Services\PostEvaluationService;
use App\Services\PostEvaluationSurveySummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PostEvaluationImportTest extends TestCase
{
    use RefreshDatabase;

    private function setupData(): array
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'superadmin']);
        $t = Training::create(['nama_pelatihan' => 'PKP Uji Import', 'bidang' => 'Bidang A', 'program_evaluasi' => 'PKP', 'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 10, 'jp' => 10, 'tgl_mulai' => '2024-01-01', 'tgl_selesai' => '2024-01-02', 'created_by' => $admin->id]);
        $p = Participant::create(['training_id' => $t->id, 'name' => 'Alumni Pertama', 'nip_nik' => '198001012000011001', 'registration_status' => 'approved']);
        $questions = [];
        foreach (['mandiri', 'atasan'] as $role) {
            foreach (PostEvaluationService::SURVEY_QUESTIONS as $i => $label) {
                $questions[$role][$i] = Question::create(['bidang' => $t->bidang, 'training_type' => $t->bidang, 'program_evaluasi' => 'PKP', 'category' => 'l34_'.$role, 'sub_category' => 'Dampak Pelatihan', 'metode' => 'semua', 'type' => 'dropdown', 'question_text' => $label, 'options' => ['Tidak Sesuai', 'Kurang Sesuai', 'Sesuai', 'Sangat Sesuai']]);
            }
        }

        return [$admin, $t, $p, $questions];
    }

    private function workbook(string $program = 'PKP', array $cells = [], array $rosterCells = []): UploadedFile
    {
        $book = new Spreadsheet;
        $s = $book->getActiveSheet();
        $s->setTitle('Jawaban');
        $s->setCellValue('B1', 'Alumni')->setCellValue('B3', 'Alumni Pertama | 198001012000011001')->setCellValue('AZ1', 'Atasan Alumni')->setCellValue('AZ2', 'Nama Responden')->setCellValue('AZ3', 'Penilai Uji')->setCellValue('BC2', 'Saya memberikan penilaian pada alumni yang bernama:')->setCellValue('BC3', 'Alumni Pertama');
        foreach (['H', 'AN', 'AR'] as $i => $col) {
            $s->setCellValue($col.'2', ($i + 1).'. '.PostEvaluationService::SURVEY_QUESTIONS[$i]);
            $s->setCellValue($col.'3', 'Sesuai');
        }
        foreach (['CM', 'CN', 'CO'] as $i => $col) {
            $s->setCellValue($col.'2', ($i + 1).'. '.PostEvaluationService::SURVEY_QUESTIONS[$i]);
            $s->setCellValue($col.'3', 'Sangat Sesuai');
        }
        foreach ($cells as $cell => $value) {
            $s->setCellValue($cell, $value);
        }
        $roster = $book->createSheet()->setTitle('Data Peserta');
        $roster->setCellValue('B1', 'Nama Peserta')->setCellValue('B2', 'Alumni Pertama | 198001012000011001');
        foreach ($rosterCells as $cell => $value) {
            $roster->setCellValue($cell, $value);
        }
        $book->createSheet()->setTitle('Informasi Umum')->setCellValue('C2', $program.' Uji Import');
        $path = Storage::disk('local')->path('input-'.Str::uuid().'.xlsx');
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();

        return new UploadedFile($path, 'Rekap Evaluasi.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function preview(User $admin, Training $t, UploadedFile $file): object
    {
        $response = $this->actingAs($admin)->post(route('post-evaluation.import.preview', $t), ['workbook' => $file])->assertSessionHasNoErrors()->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);

        return DB::table('post_evaluation_imports')->where('id', $query['preview'])->first();
    }

    public function test_technical_workbook_maps_alumni_supervisor_and_peer_with_legacy_question_bank(): void
    {
        [$admin, $training, $participant] = $this->setupData();
        $training->update(['program_evaluasi' => 'Teknis']);
        $bank = 'Bidang Pengembangan Kompetensi Manajerial';
        foreach (['mandiri', 'atasan', 'rekan'] as $role) {
            Question::create([
                'bidang' => $bank, 'program_evaluasi' => 'Teknis', 'category' => 'l34_'.$role,
                'metode' => 'semua', 'type' => 'text', 'question_text' => 'Catatan '.$role,
            ]);
        }
        $cells = array_fill_keys(['B1', 'B3', 'H2', 'H3', 'AN2', 'AN3', 'AR2', 'AR3',
            'AZ1', 'AZ2', 'AZ3', 'BC2', 'BC3', 'CM2', 'CM3', 'CN2', 'CN3', 'CO2', 'CO3'], null);
        $file = $this->workbook('Teknis', array_replace($cells, [
            'C1' => 'Alumni', 'C3' => 'Alumni Pertama | '.$participant->nip_nik, 'D1' => 'Alumni PNS',
            'O1' => 'Alumni P3K, Non-ASN', 'O2' => 'Nama / NIP / NIK (Pilihan nama akan hilang)', 'O3' => 'Alumni Pertama',
            'X2' => '1. Catatan mandiri', 'X3' => 'Mandiri uji',
            'AP1' => 'Atasan Alumni', 'AU2' => 'Saya menilai Alumni yang bernama:', 'AU3' => 'Alumni Pertama',
            'AV2' => 'Catatan atasan', 'AV3' => 'Atasan uji',
            'BN1' => 'Rekan Alumni', 'BS2' => 'Saya menilai Alumni yang bernama:', 'BS3' => 'Alumni Pertama',
            'BT2' => 'Catatan rekan', 'BT3' => 'Rekan uji',
        ]));
        $batch = $this->preview($admin, $training, $file);
        $plan = json_decode($batch->report, true);
        $this->assertSame([], $plan['errors']);
        $this->assertSame(['mandiri' => 1, 'atasan' => 1, 'rekan' => 1], $plan['counts']['roles']);
        $this->post(route('post-evaluation.import.store', [$training, $batch->id]), ['confirm_training' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('evaluation_results_l34', ['participant_id' => $participant->id, 'evaluator_role' => 'rekan', 'note' => 'Rekan uji']);
    }

    public function test_preview_and_import_match_nip_and_headers_and_feed_native_results_without_creating_people(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $iv = PostEvaluationInterview::create(['training_id' => $t->id, 'participant_id' => $p->id, 'project' => 'Proper tetap', 'scores' => [80, 81, 82, 83, 84], 'notes' => ['Catatan', '', '', '', '']]);
        $this->actingAs($admin)->get(route('post-evaluation.show', [$t, 'tab' => 'import']))->assertOk()->assertSee('Import Data Evaluasi Historis')->assertSee('Periksa File');
        $batch = $this->preview($admin, $t, $this->workbook());
        $plan = json_decode($batch->report, true);
        $this->assertSame([], $plan['errors']);
        $this->assertSame(6, $plan['counts']['new']);
        $this->assertSame(['mandiri' => 1, 'atasan' => 1], $plan['counts']['roles']);
        $this->assertDatabaseCount('evaluation_results_l34', 0);
        $this->get(route('post-evaluation.show', [$t, 'tab' => 'import', 'preview' => $batch->id]))->assertOk()->assertSee('Alumni Pertama')->assertSee('Simpan Hasil Import');
        $this->post(route('post-evaluation.import.store', [$t, $batch->id]), ['confirm_training' => 1])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseCount('evaluation_results_l34', 6);
        $this->assertDatabaseCount('participants', 1);
        $this->assertDatabaseCount('users', 1);
        $person = app(PostEvaluationService::class)->people($t)[0];
        $this->assertSame([75.0, 75.0, 75.0], $person['survey']);
        $this->assertSame('Proper tetap', $iv->fresh()->project);
        $this->assertSame([80, 81, 82, 83, 84], $iv->fresh()->scores);
        $this->assertDatabaseHas('evaluation_results_l34', ['participant_id' => $p->id, 'evaluator_role' => 'atasan', 'note' => 'Sangat Sesuai', 'evaluator_name' => 'Penilai Uji']);
        $this->post(route('post-evaluation.import.store', [$t, $batch->id]), ['confirm_training' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('evaluation_results_l34', 6);
    }

    public function test_existing_answers_are_preserved_and_second_import_does_not_duplicate(): void
    {
        [$admin,$t,$p,$questions] = $this->setupData();
        EvaluationResultL34::create(['training_id' => $t->id, 'participant_id' => $p->id, 'question_id' => $questions['mandiri'][0]->id, 'evaluator_role' => 'mandiri', 'evaluator_name' => 'Diri Sendiri', 'note' => 'Tidak Sesuai']);
        $batch = $this->preview($admin, $t, $this->workbook());
        $plan = json_decode($batch->report, true);
        $this->assertSame(1, $plan['counts']['conflicts']);
        $this->post(route('post-evaluation.import.store', [$t, $batch->id]), ['confirm_training' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('evaluation_results_l34', 6);
        $this->assertDatabaseHas('evaluation_results_l34', ['question_id' => $questions['mandiri'][0]->id, 'note' => 'Tidak Sesuai']);
        $next = app(PostEvaluationImportService::class)->preview($t, $this->workbook()->getRealPath());
        $this->assertSame(0, $next['counts']['new']);
        $this->assertSame(5, $next['counts']['same']);
    }

    public function test_wrong_program_missing_participant_invalid_answer_and_unknown_question_block_writes(): void
    {
        [$admin,$t,$p] = $this->setupData();
        foreach ([['PKA', []], ['PKP', ['B3' => 'Orang lain | 199001012010011001']], ['PKP', ['H3' => 'Nilai tidak sah']], ['PKP', ['H2' => 'Pertanyaan asing']]] as [$program,$cells]) {
            $batch = $this->preview($admin, $t, $this->workbook($program, $cells));
            $this->assertNotEmpty(json_decode($batch->report, true)['errors']);
            $this->post(route('post-evaluation.import.store', [$t, $batch->id]), ['confirm_training' => 1])->assertSessionHasErrors('workbook');
            $this->assertDatabaseCount('evaluation_results_l34', 0);
        }
        $p->delete();
        $batch = $this->preview($admin, $t, $this->workbook());
        $this->assertSame(0, json_decode($batch->report, true)['counts']['matched']);
        $this->assertDatabaseCount('participants', 0);
    }

    public function test_duplicates_and_stale_preview_are_rejected(): void
    {
        [$admin,$t,$p,$questions] = $this->setupData();
        $batch = $this->preview($admin, $t, $this->workbook('PKP', ['B4' => 'Alumni Pertama | 198001012000011001', 'H4' => 'Sesuai']));
        $this->assertNotEmpty(json_decode($batch->report, true)['errors']);
        $file = $this->workbook();
        $batch = $this->preview($admin, $t, $file);
        EvaluationResultL34::create(['training_id' => $t->id, 'participant_id' => $p->id, 'question_id' => $questions['mandiri'][0]->id, 'evaluator_role' => 'mandiri', 'evaluator_name' => 'Diri Sendiri', 'note' => 'Kurang Sesuai']);
        $this->post(route('post-evaluation.import.store', [$t, $batch->id]), ['confirm_training' => 1])->assertSessionHasErrors('workbook');
        $this->assertDatabaseCount('evaluation_results_l34', 1);
    }

    public function test_import_is_superadmin_only_and_preview_is_bound_to_training_and_uploader(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $batch = $this->preview($admin, $t, $this->workbook());
        $other = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($other)->get(route('post-evaluation.show', [$t, 'tab' => 'import', 'preview' => $batch->id]))->assertNotFound();
        $this->post(route('post-evaluation.import.store', [$t, $batch->id]), ['confirm_training' => 1])->assertNotFound();
        $field = User::factory()->create(['role' => 'admin_bidang']);
        $this->actingAs($field)->get(route('post-evaluation.show', [$t, 'tab' => 'import']))->assertForbidden();
        $this->post(route('post-evaluation.import.preview', $t), ['workbook' => $this->workbook()])->assertForbidden();
        $this->post(route('post-evaluation.import.store', [$t, $batch->id]), ['confirm_training' => 1])->assertForbidden();
        $this->assertDatabaseCount('evaluation_results_l34', 0);
    }

    public function test_supervisor_identity_is_resolved_through_roster_nip_even_when_rows_are_reordered(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $second = Participant::create(['training_id' => $t->id, 'name' => 'Alumni Kedua', 'nip_nik' => '198101012001011001', 'registration_status' => 'approved']);
        $file = $this->workbook('PKP', ['B4' => 'Alumni Kedua | 198101012001011001', 'H4' => 'Sesuai', 'BC3' => 'Alumni Kedua', 'BC4' => 'Alumni Pertama', 'AZ4' => 'Atasan Lain', 'CM4' => 'Kurang Sesuai'], ['B3' => 'Alumni Kedua | 198101012001011001']);
        $batch = $this->preview($admin, $t, $file);
        $this->assertSame([], json_decode($batch->report, true)['errors']);
        $this->post(route('post-evaluation.import.store', [$t, $batch->id]), ['confirm_training' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('evaluation_results_l34', ['participant_id' => $second->id, 'evaluator_role' => 'atasan', 'evaluator_name' => 'Penilai Uji', 'note' => 'Sangat Sesuai']);
        $this->assertDatabaseHas('evaluation_results_l34', ['participant_id' => $p->id, 'evaluator_role' => 'atasan', 'evaluator_name' => 'Atasan Lain', 'note' => 'Kurang Sesuai']);
    }

    public function test_duplicate_question_versions_preserve_existing_response_and_expired_preview_cannot_write(): void
    {
        [$admin,$t,$p,$questions] = $this->setupData();
        $old = $questions['mandiri'][0];
        $duplicate = $old->replicate();
        $duplicate->save();
        EvaluationResultL34::create(['training_id' => $t->id, 'participant_id' => $p->id, 'question_id' => $old->id, 'evaluator_role' => 'mandiri', 'evaluator_name' => 'Diri Sendiri', 'note' => 'Tidak Sesuai']);
        $batch = $this->preview($admin, $t, $this->workbook());
        $plan = json_decode($batch->report, true);
        $this->assertSame(1, $plan['counts']['conflicts']);
        $this->assertSame(5, $plan['counts']['new']);
        DB::table('post_evaluation_imports')->where('id', $batch->id)->update(['created_at' => now()->subDays(2)]);
        $this->post(route('post-evaluation.import.store', [$t, $batch->id]), ['confirm_training' => 1])->assertSessionHasErrors('workbook');
        $this->assertDatabaseCount('evaluation_results_l34', 1);
    }

    public function test_numeric_four_point_answers_and_long_method_description_feed_native_summary(): void
    {
        [$admin,$t,$p,$questions] = $this->setupData();
        Question::create(['bidang' => $t->bidang, 'program_evaluasi' => 'PKP', 'category' => 'l34_mandiri', 'sub_category' => 'Data Diri Alumni', 'metode' => 'semua', 'type' => 'dropdown', 'question_text' => 'Metode Pelaksanaan<h5>Klasikal : tatap muka</h5>', 'options' => ['Klasikal', 'Blended Learning']]);
        $questions['mandiri'][1]->update(['options' => ['1', '2', '3', '4']]);
        $batch = $this->preview($admin, $t, $this->workbook('PKP', ['H3' => 4, 'AN3' => 'Sesuai', 'G2' => 'Metode Pelaksanaan<h5>Keterangan</h5>', 'G3' => 'Blended Learning']));
        $this->assertSame([], json_decode($batch->report, true)['errors']);
        $this->post(route('post-evaluation.import.store', [$t, $batch->id]), ['confirm_training' => 1])->assertSessionHasNoErrors();
        $people = app(PostEvaluationService::class)->people($t);
        $this->assertSame([100.0, 75.0, 75.0], $people[0]['survey']);
        $summary = app(PostEvaluationSurveySummaryService::class)->build($t, $people);
        $this->assertSame(['Blended Learning' => 1], $summary['methods']);
    }

    public function test_technical_slider_zero_is_imported_and_out_of_range_values_are_rejected(): void
    {
        [$admin,$t,$p,$questions] = $this->setupData();
        $t->update(['program_evaluasi' => 'Teknis']);
        foreach ($questions as $group) {
            foreach ($group as $q) {
                $q->update(['program_evaluasi' => 'Teknis', 'type' => 'slider', 'options' => null]);
            }
        }
        $values = ['H3' => 0, 'AN3' => 80, 'AR3' => 100, 'CM3' => 90, 'CN3' => 95, 'CO3' => 100];
        $batch = $this->preview($admin, $t, $this->workbook('TEKNIS', $values));
        $this->assertSame([], json_decode($batch->report, true)['errors']);
        $this->post(route('post-evaluation.import.store', [$t, $batch->id]), ['confirm_training' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('evaluation_results_l34', ['participant_id' => $p->id, 'question_id' => $questions['mandiri'][0]->id, 'score' => 0, 'note' => null]);
        $this->assertSame(0.0, app(PostEvaluationService::class)->people($t)[0]['survey'][0]);
        foreach ([101, -1, 79.5] as $invalid) {
            $bad = $values;
            $bad['H3'] = $invalid;
            $plan = app(PostEvaluationImportService::class)->preview($t, $this->workbook('TEKNIS', $bad)->getRealPath());
            $this->assertNotEmpty($plan['errors']);
        }
    }

    public function test_legacy_nama_instansi_header_matches_alumni_profile_question_without_renaming_bank(): void
    {
        [$admin,$t,$p] = $this->setupData();
        $question = Question::create(['bidang' => $t->bidang, 'program_evaluasi' => 'PKP', 'category' => 'l34_mandiri', 'sub_category' => 'Data Diri Alumni', 'metode' => 'semua', 'type' => 'text', 'question_text' => 'Nama Instansi Alumni']);
        $file = $this->workbook('PKP', ['D2' => 'Nama Instansi', 'D3' => 'Dinas Uji']);
        $batch = $this->preview($admin, $t, $file);
        $plan = json_decode($batch->report, true);
        $this->assertSame([], $plan['errors']);
        $column = collect($plan['mapping'])->firstWhere('column', 'D');
        $this->assertSame($question->id, $column['question_id']);
        $this->post(route('post-evaluation.import.store', [$t, $batch->id]), ['confirm_training' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('evaluation_results_l34', ['participant_id' => $p->id, 'question_id' => $question->id, 'evaluator_role' => 'mandiri', 'note' => 'Dinas Uji']);
        $this->assertSame('Nama Instansi Alumni', $question->fresh()->question_text);
        $repeat = app(PostEvaluationImportService::class)->preview($t, $this->workbook('PKP', ['D2' => 'Nama Instansi', 'D3' => 'Dinas Uji'])->getRealPath());
        $this->assertSame(0, $repeat['counts']['new']);
    }

    public function test_instansi_alias_does_not_match_a_different_role_or_non_profile_question(): void
    {
        [$admin,$t] = $this->setupData();
        Question::create(['bidang' => $t->bidang, 'program_evaluasi' => 'PKP', 'category' => 'l34_atasan', 'sub_category' => 'Data Diri Alumni', 'metode' => 'semua', 'type' => 'text', 'question_text' => 'Nama Instansi Alumni']);
        Question::create(['bidang' => $t->bidang, 'program_evaluasi' => 'PKP', 'category' => 'l34_mandiri', 'sub_category' => 'Dampak Pelatihan', 'metode' => 'semua', 'type' => 'text', 'question_text' => 'Nama Instansi Alumni']);
        $batch = $this->preview($admin, $t, $this->workbook('PKP', ['D2' => 'Nama Instansi', 'D3' => 'Dinas Uji']));
        $this->assertNotEmpty(json_decode($batch->report, true)['errors']);
        $this->assertDatabaseCount('evaluation_results_l34',0);
    }

    public function test_answers_page_renders_values_instead_of_blade_raw_placeholders(): void
    {
        [$admin,$t,$p,$questions]=$this->setupData();
        foreach([['score'=>0,'note'=>null],['score'=>null,'note'=>json_encode(['Bukti A','Bukti B'])],['score'=>null,'note'=>'<script>alert(1)</script>']] as $i=>$value){
            EvaluationResultL34::create($value+['training_id'=>$t->id,'participant_id'=>$p->id,'question_id'=>$questions['mandiri'][$i]->id,'evaluator_role'=>'mandiri','evaluator_name'=>'Diri Sendiri']);
        }
        $response=$this->actingAs($admin)->get(route('post-evaluation.show',[$t,'tab'=>'jawaban']))->assertOk();
        $response->assertSee('Bukti A, Bukti B')->assertSee('<script>alert(1)</script>')->assertDontSee('<script>alert(1)</script>',false);
        $this->assertStringNotContainsString('__raw_block_', $response->getContent());
        $this->assertStringNotContainsString('$answer->score', $response->getContent());
        $this->assertMatchesRegularExpression('/<td>\s*0\s*<\/td>/', $response->getContent());
    }
}
