<?php

namespace Tests\Feature;

use App\Models\EvaluationResultL34;
use App\Models\Participant;
use App\Models\Question;
use App\Models\Training;
use App\Models\User;
use App\Services\ManagerialQuestionBankSyncService;
use App\Services\PostEvaluationImportService;
use App\Services\PostEvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ManagerialQuestionBankSyncTest extends TestCase
{
    use RefreshDatabase;

    private function training(string $program): Training
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        return Training::create(['nama_pelatihan' => 'Uji '.$program, 'bidang' => 'Bidang A', 'program_evaluasi' => $program, 'model' => 'standar', 'metode' => 'klasikal', 'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 10, 'jp' => 10, 'tgl_mulai' => '2024-01-01', 'tgl_selesai' => '2024-01-02', 'created_by' => $admin->id]);
    }

    public function test_sync_matches_each_program_and_preserves_answered_ids_and_archived_history(): void
    {
        Storage::fake('local');
        $t = $this->training('PKP');
        $p = Participant::create(['training_id' => $t->id, 'name' => 'Peserta', 'nip_nik' => '198001012000011001', 'registration_status' => 'approved']);
        $q = Question::create(['bidang' => $t->bidang, 'program_evaluasi' => 'PKP', 'category' => 'l34_mandiri', 'sub_category' => 'Data Diri Alumni', 'metode' => 'semua', 'type' => 'text', 'question_text' => 'Nama Instansi Alumni']);
        $old = $q->replicate();
        $old->question_text = 'Pertanyaan lama tidak ada di Excel';
        $old->save();
        foreach ([$q, $old] as $question) {
            EvaluationResultL34::create(['training_id' => $t->id, 'participant_id' => $p->id, 'question_id' => $question->id, 'evaluator_role' => 'mandiri', 'evaluator_name' => 'Diri Sendiri', 'note' => 'Jawaban lama']);
        }
        $unrelated = $q->replicate();
        $unrelated->program_evaluasi = 'Teknis';
        $unrelated->save();
        $sync = app(ManagerialQuestionBankSyncService::class);
        $before = Question::count();
        $preview = $sync->sync();
        $this->assertSame($before, Question::count());
        $result = $sync->sync(true);
        $this->assertTrue(Storage::disk('local')->exists($result['backup']));
        $this->assertSame('Nama Instansi', $q->fresh()->question_text);
        $this->assertTrue($q->fresh()->is_active);
        $this->assertFalse($old->fresh()->is_active);
        $this->assertDatabaseHas('evaluation_results_l34', ['question_id' => $q->id, 'note' => 'Jawaban lama']);
        $this->assertDatabaseHas('evaluation_results_l34', ['question_id' => $old->id, 'note' => 'Jawaban lama']);
        $this->assertFalse(Question::forTraining($t, 'l34_mandiri')->get()->contains('id', $old->id));
        $this->assertTrue(app(PostEvaluationService::class)->questions($t)->contains('id', $old->id));
        $this->assertSame('Nama Instansi Alumni', $unrelated->fresh()->question_text);
        $this->assertTrue($unrelated->fresh()->is_active);
        foreach (['PKP' => 101, 'PKA' => 99, 'PKN' => 84, 'LATSAR' => 105] as $program => $count) {
            $this->assertSame($count, Question::where('program_evaluasi', $program)->where('is_active', true)->count());
            $this->assertSame(0, Question::where('program_evaluasi', $program)->where('category', 'l34_rekan')->where('is_active', true)->count());
        }
        $repeat = $sync->sync();
        foreach ($repeat['stats'] as $row) {
            $this->assertSame(0, $row['created']);
            $this->assertSame(0, $row['updated']);
            $this->assertSame(0, $row['archived']);
        }
    }

    private function workbook(Training $t, bool $conflict = false): string
    {
        $book = new Spreadsheet;
        $s = $book->getActiveSheet()->setTitle('Jawaban');
        $s->setCellValue('B1', 'Alumni')->setCellValue('B3', 'Peserta | 198001012000011001');
        $definitions = app(ManagerialQuestionBankSyncService::class)->catalog()['programs'][$t->program_evaluasi]['questions'];
        $atasan = collect($definitions)->where('category', 'l34_atasan')->first();
        $s->setCellValue($atasan['source_column'].'1', 'Atasan Alumni');
        foreach ($definitions as $q) {
            $s->setCellValue($q['source_column'].'2', $q['question_text']);
            $value = $q['type'] === 'text' ? 'Isian uji' : ($q['options'][0] === 'Tidak Sesuai' ? 'Sesuai' : $q['options'][0]);
            $s->setCellValue($q['source_column'].'3', $value);
        }
        if ($t->program_evaluasi === 'LATSAR') {
            foreach (['AF', 'CJ'] as $c) {
                $s->setCellValue($c.'2', 'Menggerakkan pemanfaatan berbagai sumber daya untuk tujuan bersama');
                $s->setCellValue($c.'3', $conflict && $c === 'CJ' ? 'Kurang Sesuai' : 'Sesuai');
            }
        }
        $book->createSheet()->setTitle('Data Peserta')->setCellValue('B1', 'Nama Peserta')->setCellValue('B2', 'Peserta | 198001012000011001');
        $book->createSheet()->setTitle('Informasi Umum')->setCellValue('C2', $t->program_evaluasi.' Uji');
        $path = Storage::disk('local')->path(Str::uuid().'.xlsx');
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();

        return $path;
    }

    public function test_all_four_catalogs_match_workbook_headers_and_keep_program_specific_indicators(): void
    {
        Storage::fake('local');
        app(ManagerialQuestionBankSyncService::class)->sync(true);
        $normalizer=app(PostEvaluationImportService::class);
        $this->assertSame($normalizer->normalize('Jika Ya, sebutkan kinerja.'),$normalizer->normalize('Jika <strong>Ya</strong>, sebutkan kinerja.'));
        foreach (['PKP', 'PKA', 'PKN', 'LATSAR'] as $program) {
            $t = $this->training($program);
            Participant::create(['training_id' => $t->id, 'name' => 'Peserta', 'nip_nik' => '198001012000011001', 'registration_status' => 'approved']);
            $plan = app(PostEvaluationImportService::class)->preview($t, $this->workbook($t));
            $this->assertSame([], $plan['errors'], $program.' workbook mismatch');
            $sources = app(PostEvaluationService::class)->automaticSurveySources($t, app(PostEvaluationService::class)->questions($t));
            $this->assertCount(6, $sources);
            $public = Question::forTraining($t, 'l34_mandiri')->get();
            $this->assertNotEmpty($public);
            if ($program === 'PKN') {
                $this->assertTrue($public->contains('sub_category', 'Kemanfaatan Rekomendasi'));
                $this->assertSame(['Ya', 'Tidak'], $public->firstWhere('source_column', 'AU')->options);
            }
            if ($program === 'LATSAR') {
                $this->assertSame(1, $public->where('question_text', 'Menggerakkan pemanfaatan berbagai sumber daya untuk tujuan bersama')->count());
                $this->assertTrue($public->contains('sub_category', 'Faktor Pendukung Aktualisasi'));
            }
        }
    }

    public function test_merged_latsar_columns_are_counted_once_and_conflicts_are_reported(): void
    {
        Storage::fake('local');
        app(ManagerialQuestionBankSyncService::class)->sync(true);
        $t = $this->training('LATSAR');
        Participant::create(['training_id' => $t->id, 'name' => 'Peserta', 'nip_nik' => '198001012000011001', 'registration_status' => 'approved']);
        $service = app(PostEvaluationImportService::class);
        $ok = $service->preview($t, $this->workbook($t));
        $this->assertSame([], $ok['errors']);
        $this->assertCount(2, $ok['warnings']);
        $duplicates = collect($ok['rows'])->filter(fn ($row) => Question::find($row['question_id'])->question_text === 'Menggerakkan pemanfaatan berbagai sumber daya untuk tujuan bersama');
        $this->assertCount(2, $duplicates);
        $bad = $service->preview($t,$this->workbook($t,true));
        $this->assertCount(1,$bad['errors']);
        $this->assertStringContainsString('CJ3',$bad['errors'][0]);
    }
}
