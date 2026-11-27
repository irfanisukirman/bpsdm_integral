<?php

namespace Tests\Feature;

use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Tests\TestCase;

class TrainingFinalQuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_pretest_and_posttest_use_imported_shared_questions_with_separate_scores(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $user = User::factory()->create(['role' => 'participant']);
        $training = Training::create([
            'nama_pelatihan' => 'Kuis Awal dan Akhir', 'bidang' => 'Bidang A', 'model' => 'standar',
            'metode' => 'klasikal', 'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 10,
            'jp' => 10, 'tgl_mulai' => '2026-11-20', 'tgl_selesai' => '2026-11-25', 'created_by' => $admin->id,
        ]);
        $participant = $training->participants()->create([
            'name' => $user->name, 'user_id' => $user->id, 'nip_nik' => '198001012000011001', 'registration_status' => 'approved',
        ]);
        $posttest = $training->finalQuiz()->create([
            'title' => 'Posttest Lama', 'pass_score' => 70, 'is_visible' => false,
            'questions' => [],
        ]);
        $settings = route('evall2.pretest.settings', $training);
        $manage = route('evall2.quiz.manage', $training);
        $import = route('evall2.quiz.questions.import', $training);
        $configuration = [
            'title' => 'Pretest Baru', 'is_visible' => 0,
            'shuffle_questions' => 1, 'shuffle_answers' => 1, 'shuffle_groups' => 1,
        ];
        $pretestManage = route('evall2.quiz.manage', [$training, 'jenis' => 'pretest']);
        $this->actingAs($admin)->put($settings, $configuration)->assertRedirect($pretestManage);
        $this->get($pretestManage)->assertOk()->assertSee('Jenis Kuis')->assertSee('kolom nilai Pretest')->assertDontSee('quizPassScore')
            ->assertSee('Acak Soal')->assertSee('Acak Jawaban')->assertSee('Acak Rumpun')
            ->assertSee('data-bs-target="#quizImportModal"', false)->assertSee('Unduh Template Excel');
        $this->get($manage)->assertOk()->assertSee('kolom nilai Posttest')->assertSee('quizPassScore');
        $template = $this->get(route('evall2.quiz.questions.template', $training))
            ->assertDownload('template-soal-level-2.xlsx')->streamedContent();
        $path = tempnam(sys_get_temp_dir(), 'quiz-template-');
        try {
            file_put_contents($path, $template);
            $book = (new Xlsx())->load($path);
            $sheet = $book->getSheetByName('Soal');
            $this->assertNotNull($book->getSheetByName('Contoh'));
            $sheet->fromArray([
                ['Pilihan Ganda', 'Soal awal pertama', 'Pilihan satu', 'Pilihan dua', 'Pilihan tiga', 'Pilihan empat', 'B', 'Dasar'],
                ['Benar / Salah', 'Soal awal kedua', null, null, null, null, 'X', 'Praktik'],
            ], null, 'A2');
            (new XlsxWriter($book))->save($path);
            $this->from($manage)->post($import, ['question_file' => new UploadedFile($path, 'soal.xlsx', null, null, true)])
                ->assertSessionHasErrors('question_file');
            $this->assertSame([], $training->fresh()->level2Questions());
            $sheet->setCellValue('G3', 'Salah');
            (new XlsxWriter($book))->save($path);
            $this->post($import, ['question_file' => new UploadedFile($path, 'soal.xlsx', null, null, true)])
                ->assertRedirect($manage)->assertSessionHasNoErrors();
            $book->disconnectWorksheets();
        } finally {
            unlink($path);
        }

        $pretest = $training->pretestQuiz()->firstOrFail();
        $this->assertNotSame($posttest->public_token, $pretest->public_token);
        $this->assertSame(['Soal awal pertama', 'Soal awal kedua'], array_column($training->fresh()->level2Questions(), 'prompt'));
        $this->assertSame(['Dasar', 'Praktik'], array_column($training->fresh()->level2Questions(), 'group'));
        $this->put($settings, array_replace($configuration, ['is_visible' => 1]))->assertRedirect($pretestManage);
        $this->put(route('evall2.quiz.settings', $training), ['title' => $posttest->title, 'is_visible' => 1, 'pass_score' => 70])->assertRedirect($manage);
        $pretest = $pretest->fresh();
        $this->actingAs($user)->get(route('participant.training.show', [$training, 'tab' => 'evaluasi']))
            ->assertOk()->assertSee('Mulai Pretest')->assertSee('Mulai Kuis Akhir');
        $pretestPage = route('participant.pretest.show', $training);
        $presented = $this->get($pretestPage)->assertOk()->assertSee('Soal awal pertama')->assertSee('Soal awal kedua')->viewData('questions');
        $this->get($pretestPage)->assertViewHas('questions', $presented);
        $this->assertTrue($pretest->shuffle_questions && $pretest->shuffle_answers && $pretest->shuffle_groups);
        $bank = $training->fresh()->level2Questions();
        $orders = collect(range(1, 20))->map(fn ($id) => $pretest->questionsForParticipant($bank, $id));
        $this->assertGreaterThan(1, $orders->map(fn ($order) => implode('|', array_column($order, 'group')))->unique()->count());
        $optionOrders = $orders->map(fn ($order) => array_keys(collect($order)->firstWhere('id', $bank[0]['id'])['options']));
        $this->assertGreaterThan(1, $optionOrders->unique()->count());
        $this->assertSame($bank[0]['options'][1], collect($presented)->firstWhere('id', $bank[0]['id'])['options'][1]);
        $this->get(route('participant.final-quiz.show', $training))->assertOk()->assertSee('Soal awal pertama')->assertSee('Soal awal kedua');
        [$first, $second] = array_column($training->fresh()->level2Questions(), 'id');
        $this->post(route('participant.pretest.submit', $training), ['revision' => $pretest->revision, 'answers' => [$first => 1, $second => 0]])
            ->assertRedirect(route('participant.pretest.show', $training));
        $this->assertDatabaseHas('evaluation_results_l2', ['participant_id' => $participant->id, 'pretest' => 50, 'postest' => null]);
        $this->actingAs($admin)->get(route('evall12.dashboard', $training))->assertOk()
            ->assertViewHas('preAverage', 50.0)->assertViewHas('postAverage', null)->assertViewHas('gainAverage', null);
        $this->actingAs($user)->post(route('participant.final-quiz.submit', $training), ['revision' => $posttest->fresh()->revision, 'answers' => [$first => 1, $second => 1]])
            ->assertRedirect(route('participant.final-quiz.show', $training));
        $this->assertDatabaseHas('evaluation_results_l2', ['participant_id' => $participant->id, 'pretest' => 50, 'postest' => 100]);
        $this->assertDatabaseCount('training_final_quiz_attempts', 2);

        $token = $pretest->accessTokens()->where('participant_id', $participant->id)->firstOrFail()->token;
        $postToken = $this->actingAs($admin)->postJson(route('evall2.quiz.token.show', [$training, $participant]))->assertOk()->json('token');
        $this->assertNotSame($postToken, $token);
        $index = route('evall2.index', $training);
        $this->get($index)->assertOk()->assertSee('Reset Pengisian Pretest')->assertSee('Reset Pengisian Posttest');
        $attempt = $pretest->attempts()->sole();
        $reset = route('evall2.pretest.attempt.reset', [$training, $participant, $attempt->id]);
        $this->actingAs($user)->delete($reset)->assertForbidden();
        $this->actingAs($admin)->from($index)->delete($reset)->assertRedirect($index);
        $this->assertDatabaseHas('evaluation_results_l2', ['participant_id' => $participant->id, 'pretest' => null, 'postest' => 100]);
        $this->assertDatabaseMissing('training_final_quiz_attempts', ['id' => $attempt->id]);
        $this->assertDatabaseMissing('training_final_quiz_access_tokens', ['quiz_id' => $pretest->id, 'participant_id' => $participant->id]);
        $newToken = $this->postJson(route('evall2.pretest.token.show', [$training, $participant]))->assertOk()->json('token');
        $this->assertNotSame($token, $newToken);
        $this->assertSame($postToken, $posttest->accessTokens()->sole()->token);
        $this->actingAs($user)->post(route('participant.pretest.submit', $training), ['revision' => $pretest->revision, 'answers' => [$first => 1, $second => 1]])
            ->assertRedirect(route('participant.pretest.show', $training));
        $this->actingAs($admin)->delete($reset)->assertNotFound();
        $this->assertDatabaseHas('evaluation_results_l2', ['participant_id' => $participant->id, 'pretest' => 100, 'postest' => 100]);
        $this->from($index)->put(route('evall2.quiz.score', [$training, $participant]), [
            'score' => 90, 'reason' => 'Koreksi nilai sebelum reset',
        ])->assertRedirect($index);
        $this->assertDatabaseCount('evaluation_l2_score_corrections', 1);
        $this->delete(route('evall2.quiz.attempt.reset', [$training, $participant, $posttest->attempts()->sole()->id]))->assertRedirect($index);
        $this->assertDatabaseHas('evaluation_results_l2', ['participant_id' => $participant->id, 'pretest' => 100, 'postest' => null]);
        $this->assertDatabaseCount('evaluation_l2_score_corrections', 0);
        $this->delete(route('evall2.pretest.attempt.reset', [$training, $participant, $pretest->attempts()->sole()->id]))->assertRedirect($index);
        $this->assertDatabaseMissing('evaluation_results_l2', ['participant_id' => $participant->id]);
        $this->assertDatabaseCount('training_final_quiz_attempts', 0);
        $this->assertDatabaseCount('training_final_quiz_access_tokens', 0);
    }

    public function test_public_quiz_requires_an_approved_nip_and_respects_the_schedule(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $training = Training::create([
            'nama_pelatihan' => 'Kuis Publik', 'bidang' => 'Bidang A', 'model' => 'standar',
            'metode' => 'klasikal', 'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 10,
            'jp' => 10, 'tgl_mulai' => '2026-11-20', 'tgl_selesai' => '2026-11-25', 'created_by' => $admin->id,
        ]);
        $user = User::factory()->create(['role' => 'participant']);
        $approved = $training->participants()->create([
            'name' => 'Peserta Terdaftar', 'user_id' => $user->id,
            'nip_nik' => '198001012000011001', 'instansi' => 'BPSDM Jawa Barat', 'registration_status' => 'approved',
        ]);
        $training->participants()->create([
            'name' => 'Peserta Menunggu', 'nip_nik' => '198001012000011002', 'registration_status' => 'pending',
        ]);
        $quiz = $training->finalQuiz()->create([
            'title' => 'Ujian Akhir Publik', 'pass_score' => 70, 'is_visible' => true, 'revision' => 1,
            'questions' => [['id' => 'q1', 'prompt' => 'Pertanyaan uji publik', 'type' => 'boolean',
                'options' => ['Benar', 'Salah'], 'correct' => 0]],
        ]);
        $show = route('public.final-quiz.show', $quiz->public_token);
        $verify = route('public.final-quiz.verify', $quiz->public_token);
        $confirm = route('public.final-quiz.confirm', $quiz->public_token);
        $submit = route('public.final-quiz.submit', $quiz->public_token);
        $settings = route('evall2.quiz.settings', $training);
        $manage = route('evall2.quiz.manage', $training);
        $configuration = [
            'title' => $quiz->title, 'pass_score' => 70, 'is_visible' => 1, 'is_public' => 1,
            'opens_at' => '2026-11-27T09:00', 'closes_at' => '2026-11-27T11:00',
        ];
        $this->get($show)->assertNotFound();
        $this->actingAs($admin)->get(route('evall2.index', $training))->assertOk()
            ->assertSeeInOrder(['NIP/NIK', 'Instansi / Perangkat Daerah', $approved->name, $approved->nip_nik, $approved->instansi]);
        $this->actingAs($admin)->get($manage)->assertOk()->assertSee($show);
        $this->from($manage)->put($settings, array_replace($configuration, ['closes_at' => '2026-11-27T08:00']))
            ->assertSessionHasErrors('closes_at');
        $this->put($settings, $configuration)->assertSessionHasNoErrors();
        $showToken = route('evall2.quiz.token.show', [$training, $approved]);
        $response = $this->postJson($showToken)->assertOk();
        $token = $quiz->accessTokens()->firstOrFail()->token;
        $response->assertExactJson(['token' => $token]);
        $this->actingAs($user)->postJson($showToken)->assertForbidden();
        $this->actingAs($admin);
        $this->assertDatabaseMissing('training_final_quiz_access_tokens', ['token' => $token]);
        $bulk = $this->post(route('evall2.quiz.tokens.download', $training))
            ->assertDownload('token-posttest-'.$training->id.'.xlsx')->streamedContent();
        $path = tempnam(sys_get_temp_dir(), 'quiz-xlsx-');
        try {
            file_put_contents($path, $bulk);
            $workbook = (new Xlsx())->load($path);
            $sheet = $workbook->getActiveSheet();
            $this->assertSame([
                ['Nama Peserta', 'NIP', 'Token Pribadi'],
                [$approved->name, $approved->nip_nik, $token],
            ], $sheet->toArray());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('B2')->getDataType());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('C2')->getDataType());
            $workbook->disconnectWorksheets();
        } finally {
            unlink($path);
        }
        $this->app['auth']->guard()->logout();
        $this->assertGuest();

        $payload = ['revision' => $quiz->fresh()->revision, 'answers' => ['q1' => 0]];
        $this->travelTo(now()->setDate(2026, 11, 27)->setTime(8, 59));
        $this->get($show)->assertOk()->assertSee('09:00 WIB')->assertDontSee('Pertanyaan uji publik');
        $this->post($verify, ['nip' => $approved->nip_nik])->assertForbidden();

        $this->travelTo(now()->setTime(9, 0));
        $this->get($show)->assertOk()->assertSee('Verifikasi Peserta')->assertDontSee($approved->nip_nik);
        $this->post($submit, $payload)->assertForbidden();
        $this->post($verify, ['nip' => $approved->nip_nik])->assertSessionHasErrors('token');
        $this->post($verify, ['nip' => '198001012000011002', 'token' => $token])->assertSessionHasErrors('nip');
        $this->get($show)->assertDontSee($approved->name);
        $credentials = ['nip' => $approved->nip_nik, 'token' => strtolower($token)];
        $pretest = $training->pretestQuiz()->create([
            'title' => 'Pretest Publik', 'pass_score' => 0, 'is_public' => true, 'questions' => [],
        ]);
        $pretestToken = 'A5B7C9D1E3F50628';
        $pretest->accessTokens()->create(['participant_id' => $approved->id, 'token' => $pretestToken]);
        $this->get(route('public.final-quiz.show', $pretest->public_token))->assertRedirect($show);
        $this->post($verify, ['nip' => $approved->nip_nik, 'token' => $pretestToken])->assertRedirect($show);
        $this->get($show)->assertSee('Token ini untuk Pretest.')->assertSee('Konfirmasi Identitas');
        $this->post(route('public.final-quiz.verify', $pretest->public_token), $credentials)->assertRedirect($show);
        $this->get($show)->assertSee('Token ini untuk Posttest.')->assertSee('Konfirmasi Identitas')->assertDontSee('Pertanyaan uji publik');
        $this->assertNull(session('public_final_quiz.'.$pretest->id));
        $this->post($verify, $credentials)->assertRedirect($show);
        $this->get($show)->assertOk()->assertSeeInOrder(['Konfirmasi Identitas', $approved->name, $approved->instansi])
            ->assertDontSee('Pertanyaan uji publik');
        $payload['quiz_access'] = session('public_final_quiz.'.$quiz->id.'.nonce');
        $this->post($submit, $payload)->assertForbidden();
        $this->post($confirm, ['quiz_access' => 'wrong-nonce'])->assertForbidden();
        $this->post($confirm, ['quiz_access' => $payload['quiz_access']])->assertRedirect($show);
        $this->get($show)->assertOk()->assertSee('Pertanyaan uji publik')->assertDontSee('"correct"', false);

        $this->travelTo(now()->setTime(11, 0));
        $this->post($submit, $payload)->assertForbidden();
        $this->assertDatabaseCount('training_final_quiz_attempts', 0);
        $this->travelTo(now()->setTime(10, 59));
        $this->post($submit, $payload)->assertRedirect($show);
        $this->get($show)->assertOk()->assertSee('Jawaban berhasil dikirim')->assertSee('100,00')
            ->assertSee('public-quiz-receipt.js')->assertDontSee('Ganti Peserta');
        $this->get($show)->assertOk()->assertSee('Verifikasi Peserta')->assertDontSee('100,00')->assertDontSee('Ganti Peserta');
        $this->post($submit, $payload)->assertForbidden();
        $this->post($verify, $credentials)->assertRedirect($show);
        $this->get($show)->assertSee('Peserta dengan NIP/NIK dan token ini sudah menyelesaikan kuis.')->assertDontSee('100,00');
        $this->post($confirm, ['quiz_access' => $payload['quiz_access']])->assertForbidden();
        $this->actingAs($admin)->from($manage)->put(route('evall2.quiz.token.rotate', [$training, $approved]))->assertRedirect($manage);
        $newToken = $quiz->accessTokens()->firstOrFail()->token;
        $this->assertNotSame($token, $newToken);
        $this->app['auth']->guard()->logout();
        $this->get($show)->assertSee('Verifikasi Peserta')->assertDontSee($approved->name);
        $this->post($verify, $credentials)->assertSessionHasErrors('nip');
        $this->post($verify, array_replace($credentials, ['token' => $newToken]))->assertRedirect($show);
        $this->get($show)->assertSee('Peserta dengan NIP/NIK dan token ini sudah menyelesaikan kuis.')->assertDontSee('100,00');
        $this->assertDatabaseHas('evaluation_results_l2', ['participant_id' => $approved->id, 'postest' => 100]);
        $approved->evaluationL2()->update(['postest' => 80]);
        $this->travelTo(now()->setTime(11, 0));
        $this->get($show)->assertOk()->assertSee('Verifikasi Peserta')->assertDontSee('80,00');
        $pretest->update(['is_public' => false]);
        $this->get($show)->assertOk()->assertSee('Pengisian kuis sudah ditutup.')
            ->assertDontSee('80,00')->assertDontSee('100,00')->assertDontSee('Lulus')->assertDontSee('Nilai kuis sebelum koreksi');
        $this->actingAs($user)->post(route('participant.final-quiz.submit', $training), $payload)
            ->assertRedirect(route('participant.final-quiz.show', $training));
        $this->get(route('participant.final-quiz.show', $training))->assertOk()
            ->assertSee('Pengisian kuis sudah ditutup.')->assertDontSee('80,00')->assertDontSee('Lulus');
        $this->get(route('participant.training.show', [$training, 'tab' => 'evaluasi']))->assertOk()
            ->assertSee('Pengisian kuis sudah ditutup.')->assertDontSee('80,00')->assertDontSee('Lihat Nilai');
        $this->actingAs($admin)->get(route('evall2.index', $training))->assertOk()->assertSee('80,00');
        $this->assertDatabaseCount('training_final_quiz_attempts', 1);
    }

    public function test_managers_publish_and_grade_final_quizzes_for_approved_participants(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $training = Training::create([
            'nama_pelatihan' => 'Pelatihan Kuis', 'bidang' => 'Bidang A', 'model' => 'standar',
            'metode' => 'klasikal', 'lokasi' => 'BPSDM', 'angkatan' => 'I', 'jumlah_peserta' => 10,
            'jp' => 10, 'tgl_mulai' => '2026-11-20', 'tgl_selesai' => '2026-11-25', 'created_by' => $admin->id,
        ]);
        $approved = User::factory()->create(['role' => 'participant']);
        $second = User::factory()->create(['role' => 'participant']);
        $pending = User::factory()->create(['role' => 'participant']);
        foreach ([$approved, $second, $pending] as $user) {
            $training->participants()->create([
                'name' => $user->name, 'user_id' => $user->id, 'nip_nik' => 'NIP-'.$user->id,
                'registration_status' => $user->is($pending) ? 'pending' : 'approved',
            ]);
        }

        $manage = route('evall2.index', $training);
        $questionSettings = route('evall2.quiz.manage', $training);
        $settings = route('evall2.quiz.settings', $training);
        $questions = route('evall2.quiz.questions.store', $training);
        $quizPage = route('participant.final-quiz.show', $training);
        $submit = route('participant.final-quiz.submit', $training);
        $options = ['title' => 'Ujian Akhir', 'pass_score' => 80, 'is_visible' => 0];

        $this->actingAs($admin)->get($manage)->assertOk()->assertSee('Pengaturan Soal')->assertDontSee('Tambah Soal');
        $this->get($questionSettings)->assertOk()->assertSee('Pengaturan Kuis')->assertSee('Kembali ke Evaluasi Level 2');
        $this->from($questionSettings)->put($settings, array_replace($options, ['is_visible' => 1]))->assertSessionHasErrors('is_visible');
        $this->put($settings, $options)->assertSessionHasNoErrors();
        $this->post($questions, ['prompt' => 'Pilih contoh yang tepat', 'type' => 'multiple',
            'options' => ['Satu', 'Dua', 'Tiga', 'Empat'], 'correct' => 2])->assertSessionHasNoErrors();
        $this->post($questions, ['prompt' => 'Pernyataan pertama benar', 'type' => 'boolean', 'correct' => 0])
            ->assertSessionHasNoErrors();
        $this->get($questionSettings)->assertOk()
            ->assertSee('data-bs-target="#quizQuestionModal"', false)->assertSee('id="quizQuestionModal"', false)
            ->assertSeeInOrder(['Daftar Soal', 'Kunci Jawaban', 'Pilih contoh yang tepat', 'Tiga', 'Pernyataan pertama benar'])
            ->assertDontSee('<summary class="fw-semibold">Tambah Soal</summary>', false);
        $this->actingAs($approved)->get($quizPage)->assertNotFound();
        $detail = route('participant.training.show', [$training, 'tab' => 'evaluasi']);
        $this->get($detail)->assertOk()->assertDontSee('Mulai Kuis Akhir');
        $this->actingAs($admin)->put($settings, array_replace($options, ['is_visible' => 1]))->assertSessionHasNoErrors();
        $quiz = $training->finalQuiz()->firstOrFail();
        [$multiple, $boolean] = array_column($quiz->questions, 'id');

        $this->actingAs($pending)->get($quizPage)->assertForbidden();
        $this->actingAs($approved)->get($quizPage)->assertOk()->assertSee('Pilih contoh yang tepat')
            ->assertSee('Pernyataan pertama benar')->assertDontSee('"correct"', false)->assertSee('Token otomatis terisi dari akun Anda.');
        $this->assertDatabaseHas('training_final_quiz_access_tokens', [
            'quiz_id' => $quiz->id, 'participant_id' => $training->participants()->where('user_id', $approved->id)->value('id'),
        ]);
        $this->get($detail)->assertOk()->assertSee('Mulai Kuis Akhir')->assertSee('Kuis Wajib')
            ->assertSee('Kuis Akhir wajib diselesaikan untuk melengkapi evaluasi pelatihan.');
        $this->from($quizPage)->post($submit, ['revision' => $quiz->revision, 'answers' => [$multiple => 2]])
            ->assertSessionHasErrors('answers');
        $this->post($submit, ['revision' => $quiz->revision, 'answers' => [$multiple => 2, $boolean => 0]])
            ->assertRedirect($quizPage);
        $this->get($quizPage)->assertOk()->assertSee('Lulus')->assertSee('100,00');
        $this->get($detail)->assertOk()->assertSee('Sudah dikerjakan')->assertDontSee('Kuis Wajib');
        $this->post($submit, ['revision' => $quiz->revision, 'answers' => [$multiple => 0, $boolean => 1]])->assertRedirect($quizPage);
        $this->assertDatabaseCount('training_final_quiz_attempts', 1);

        $this->actingAs($second)->post($submit, ['revision' => $quiz->revision, 'answers' => [$multiple => 2, $boolean => 1]])
            ->assertRedirect($quizPage);
        $this->get($quizPage)->assertOk()->assertSee('Belum Lulus')->assertSee('50,00');
        $this->assertDatabaseHas('evaluation_results_l2', [
            'participant_id' => $training->participants()->where('user_id', $second->id)->value('id'),
            'pretest' => null, 'postest' => 50,
        ]);
        $this->actingAs($admin)->get($manage)->assertOk()->assertSee('Nilai Peserta')->assertSee('50,00');
        $this->get(route('evall12.dashboard', $training))->assertOk()
            ->assertViewHas('postAverage', 75.0)->assertViewHas('preAverage', null)->assertViewHas('gainAverage', null);
        $participant = $training->participants()->where('user_id', $second->id)->firstOrFail();
        $correctScore = route('evall2.quiz.score', [$training, $participant]);
        $this->actingAs(User::factory()->create(['role' => 'admin_bidang', 'bidang' => 'Bidang A']))
            ->put($correctScore, ['score' => 85, 'reason' => 'Verifikasi ulang'])->assertForbidden();
        $this->get($manage)->assertOk()->assertDontSee('Koreksi Nilai');
        $this->actingAs($admin)->from($manage)->put($correctScore, ['score' => 85.5, 'reason' => 'Verifikasi ulang'])
            ->assertSessionHasNoErrors()->assertRedirect($manage);
        $this->assertDatabaseHas('evaluation_l2_score_corrections', [
            'result_id' => $participant->evaluationL2->id, 'previous_score' => 50, 'score' => 85.5,
            'reason' => 'Verifikasi ulang', 'changed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('training_final_quiz_attempts', ['participant_id' => $participant->id, 'score' => 50]);
        $this->get($manage)->assertOk()->assertSee('85,50')->assertSee('Verifikasi ulang');
        $this->get(route('evall12.dashboard', $training))->assertOk()->assertViewHas('postAverage', 92.8);
        $this->actingAs($second)->get($quizPage)->assertOk()->assertSee('85,50')->assertDontSee('Belum Lulus')
            ->assertSee('Nilai kuis sebelum koreksi');
        $this->actingAs($admin)->from($questionSettings)->put($settings, array_replace($options, ['pass_score' => 70]))->assertSessionHasErrors('pass_score');
        $this->actingAs(User::factory()->create(['role' => 'admin_bidang', 'bidang' => 'Bidang B']))
            ->put($settings, $options)->assertForbidden();
    }
}
