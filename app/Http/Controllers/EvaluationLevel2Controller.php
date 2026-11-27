<?php

namespace App\Http\Controllers;

use App\Models\EvaluationResultL2;
use App\Models\Participant;
use App\Models\Training;
use App\Models\TrainingFinalQuiz;
use App\Models\TrainingFinalQuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EvaluationLevel2Controller extends Controller
{
    private const QUESTION_HEADERS = ['Jenis Soal', 'Pertanyaan', 'Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D', 'Kunci Jawaban'];

    public function index(Request $request, Training $training)
    {
        $this->authorizeManager($request, $training);
        $quiz = $training->finalQuiz;
        $pretest = $training->pretestQuiz;
        $attempts = $quiz?->attempts()->get(['id', 'participant_id', 'pass_score'])->keyBy('participant_id') ?? collect();
        $pretestAttempts = $pretest?->attempts()->pluck('id', 'participant_id') ?? collect();
        $canCorrect = $request->user()->role === 'superadmin';
        $participants = $training->participants()->with([
            $canCorrect ? 'evaluationL2.latestScoreCorrection' : 'evaluationL2',
            'user:id,nip,nip_nik,instansi',
        ])
            ->where('registration_status', 'approved')->orderBy('name')->paginate(20);

        return view('evaluasi.l2_index', compact('training', 'quiz', 'pretest', 'participants', 'attempts', 'pretestAttempts', 'canCorrect'));
    }

    public function questionSettings(Request $request, Training $training)
    {
        $this->authorizeManager($request, $training);
        $posttest = $training->finalQuiz;
        $pretest = $training->pretestQuiz;
        $questions = $training->level2Questions();
        $posttestHasAttempts = $posttest?->attempts()->exists() ?? false;
        $pretestHasAttempts = $pretest?->attempts()->exists() ?? false;
        $hasAttempts = $posttestHasAttempts || $pretestHasAttempts;
        $editable = !$hasAttempts && !$posttest?->is_visible && !$posttest?->is_public
            && !$pretest?->is_visible && !$pretest?->is_public;
        $isPretest = $request->query('jenis', $this->quizKind($request)) === 'pretest';
        $quiz = $isPretest ? $pretest : $posttest;
        $hasQuizAttempts = $isPretest ? $pretestHasAttempts : $posttestHasAttempts;

        return view('evaluasi.l2_quiz_settings', compact('training', 'quiz', 'isPretest', 'hasQuizAttempts', 'questions', 'hasAttempts', 'editable'));
    }

    public function showParticipantToken(Request $request, Training $training, Participant $participant)
    {
        $this->authorizeManager($request, $training);
        abort_unless($participant->training_id === $training->id && $participant->registration_status === 'approved', 404);
        $quiz = $training->quizFor($this->quizKind($request))->firstOrFail();
        return response()->json(['token' => $this->participantToken($quiz, $participant)])
            ->header('Cache-Control', 'private, no-store');
    }

    private function participantToken(TrainingFinalQuiz $quiz, Participant $participant): string
    {
        return $quiz->accessTokens()->firstOrCreate(
            ['participant_id' => $participant->id],
            ['token' => strtoupper(bin2hex(random_bytes(8)))],
        )->token;
    }

    public function downloadAllParticipantTokens(Request $request, Training $training)
    {
        $this->authorizeManager($request, $training);
        $quiz = $training->quizFor($this->quizKind($request))->firstOrFail();
        return response()->streamDownload(function () use ($training, $quiz) {
            $workbook = new Spreadsheet();
            try {
                $sheet = $workbook->getActiveSheet();
                $sheet->setTitle('Token Peserta');
                $sheet->fromArray(['Nama Peserta', 'NIP', 'Token Pribadi'], null, 'A1');
                $sheet->getStyle('A1:C1')->getFont()->setBold(true);
                $sheet->freezePane('A2');
                $sheet->getColumnDimension('A')->setWidth(38);
                $sheet->getColumnDimension('B')->setWidth(24);
                $sheet->getColumnDimension('C')->setWidth(22);

                $row = 2;
                $participants = $training->participants()->with('user:id,nip,nip_nik')
                    ->where('registration_status', 'approved')->lazyById();
                foreach ($participants as $participant) {
                    $nip = $participant->user?->nip ?: $participant->nip_nik ?: $participant->user?->nip_nik ?: '';
                    $sheet->setCellValueExplicit('A'.$row, $participant->name, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('B'.$row, $nip, DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit('C'.$row, $this->participantToken($quiz, $participant), DataType::TYPE_STRING);
                    $row++;
                }

                (new Xlsx($workbook))->save('php://output');
            } finally {
                $workbook->disconnectWorksheets();
            }
        }, 'token-'.($quiz->isPretest() ? 'pretest' : 'posttest').'-'.$training->id.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function rotateParticipantToken(Request $request, Training $training, Participant $participant)
    {
        $this->authorizeManager($request, $training);
        abort_unless($participant->training_id === $training->id && $participant->registration_status === 'approved', 404);
        $kind = $this->quizKind($request);
        DB::transaction(function () use ($training, $participant, $kind) {
            $quiz = Training::lockForUpdate()->findOrFail($training->id)->quizFor($kind)->firstOrFail();
            $quiz->accessTokens()->updateOrCreate(
                ['participant_id' => $participant->id],
                ['token' => strtoupper(bin2hex(random_bytes(8)))],
            );
        });

        return back()->with('success', 'Token baru dibuat. Klik Lihat Token untuk menyalinnya.');
    }

    public function correctScore(Request $request, Training $training, Participant $participant)
    {
        abort_unless($request->user()->role === 'superadmin', 403);
        abort_unless($participant->training_id === $training->id, 404);
        $data = $request->validate([
            'score' => ['required', 'numeric', 'decimal:0,2', 'between:0,100'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $changed = DB::transaction(function () use ($request, $training, $participant, $data) {
            Training::lockForUpdate()->findOrFail($training->id);
            $result = $participant->evaluationL2()->lockForUpdate()->firstOrFail();
            abort_if($result->postest === null, 404);
            $score = (float) $data['score'];
            if ((float) $result->postest === $score) return false;

            $result->scoreCorrections()->create([
                'changed_by' => $request->user()->id, 'previous_score' => $result->postest,
                'score' => $score, 'reason' => $data['reason'],
            ]);
            $result->update(['postest' => $score]);

            return true;
        });

        return back()->with('success', $changed ? 'Koreksi nilai berhasil disimpan.' : 'Nilai sudah sesuai.');
    }

    public function resetAttempt(Request $request, Training $training, Participant $participant, int $attempt)
    {
        abort_unless($request->user()->role === 'superadmin', 403);
        abort_unless($participant->training_id === $training->id && $participant->registration_status === 'approved', 404);
        $kind = $this->quizKind($request);

        DB::transaction(function () use ($training, $participant, $kind, $attempt) {
            $quiz = Training::lockForUpdate()->findOrFail($training->id)->quizFor($kind)->firstOrFail();
            $submission = $quiz->attempts()->where('participant_id', $participant->id)->findOrFail($attempt);
            $result = $participant->evaluationL2()->lockForUpdate()->first();
            if ($result) {
                $result->{$kind === 'pretest' ? 'pretest' : 'postest'} = null;
                if ($result->pretest === null && $result->postest === null) {
                    $result->delete();
                } else {
                    if ($kind === 'posttest') {
                        $result->scoreCorrections()->delete();
                    }
                    $result->save();
                }
            }
            $quiz->accessTokens()->where('participant_id', $participant->id)->delete();
            $submission->delete();
        });

        return back()->with('success', 'Pengisian '.ucfirst($kind).' direset. Peserta bisa mengerjakan ulang sesuai jadwal. Klik Lihat Token untuk mendapatkan token baru bagi akses publik.');
    }

    public function settings(Request $request, Training $training)
    {
        $this->authorizeManager($request, $training);
        $kind = $this->quizKind($request);
        if ($kind === 'pretest') {
            $request->merge(['pass_score' => 0]);
        }
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'pass_score' => ['required', 'integer', 'between:0,100'],
            'is_visible' => ['required', 'boolean'],
            'is_public' => ['sometimes', 'boolean'],
            'shuffle_questions' => ['sometimes', 'boolean'],
            'shuffle_answers' => ['sometimes', 'boolean'],
            'shuffle_groups' => ['sometimes', 'boolean'],
            'opens_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'closes_at' => ['nullable', 'date_format:Y-m-d\TH:i', Rule::when($request->filled('opens_at'), 'after:opens_at')],
        ], [
            'closes_at.after' => 'Waktu tutup harus setelah waktu buka.',
        ]);

        DB::transaction(function () use ($training, $data, $kind) {
            $locked = Training::lockForUpdate()->findOrFail($training->id);
            $sharedQuestions = $locked->level2Questions();
            $quiz = $locked->quizFor($kind)->firstOrNew([], ['questions' => $kind === 'posttest' ? $sharedQuestions : [], 'revision' => 0]);
            if ($quiz->exists && $quiz->attempts()->exists() && (int) $quiz->pass_score !== (int) $data['pass_score']) {
                throw ValidationException::withMessages(['pass_score' => 'Standar kelulusan terkunci setelah peserta mengirim jawaban.']);
            }
            if (($data['is_visible'] || ($data['is_public'] ?? $quiz->is_public)) && !$sharedQuestions) {
                throw ValidationException::withMessages(['is_visible' => 'Tambahkan minimal satu soal sebelum menampilkan kuis.']);
            }
            $quiz->fill($data);
            if (!$quiz->exists || $quiz->isDirty()) {
                if (!$quiz->exists || $quiz->isDirty(['pass_score', 'shuffle_questions', 'shuffle_answers', 'shuffle_groups'])) {
                    $quiz->revision++;
                }
                $quiz->save();
            }
        });

        $parameters = $kind === 'pretest' ? [$training, 'jenis' => 'pretest'] : [$training];
        return to_route('evall2.quiz.manage', $parameters)->with('success', 'Pengaturan kuis berhasil disimpan.');
    }

    public function saveQuestion(Request $request, Training $training, ?string $question = null)
    {
        $this->authorizeManager($request, $training);
        $item = $this->questionItem($request->all(), $question);

        DB::transaction(function () use ($training, $question, $item) {
            $quiz = $this->editableQuiz($training);
            $questions = collect($quiz->questions)->keyBy('id');
            abort_if($question && !$questions->has($question), 404);
            if (!$question && $questions->count() >= TrainingFinalQuiz::MAX_QUESTIONS) {
                throw ValidationException::withMessages(['prompt' => 'Maksimal 100 soal per kuis.']);
            }
            $this->saveQuestionBank($quiz, $questions->put($item['id'], $item)->values()->all());
        });

        return $this->backToSettings($training, 'Soal berhasil disimpan.');
    }

    private function questionItem(array $input, ?string $id = null): array
    {
        $multiple = ($input['type'] ?? null) === 'multiple';
        $data = Validator::make($input, [
            'prompt' => ['required', 'string', 'max:2000'],
            'group' => ['nullable', 'string', 'max:100'],
            'type' => ['required', Rule::in(['multiple', 'boolean'])],
            'options' => ['exclude_unless:type,multiple', 'required', 'array', 'list', 'size:4'],
            'options.*' => ['required', 'string', 'max:500', 'distinct:ignore_case'],
            'correct' => ['required', 'integer', 'between:0,'.($multiple ? '3' : '1')],
        ])->validate();

        return [
            'id' => $id ?? (string) Str::uuid(),
            'prompt' => $data['prompt'],
            'group' => $data['group'] ?? null,
            'type' => $data['type'],
            'options' => $multiple ? $data['options'] : ['Benar', 'Salah'],
            'correct' => (int) $data['correct'],
        ];

    }

    public function questionTemplate(Request $request, Training $training)
    {
        $this->authorizeManager($request, $training);
        return response()->streamDownload(function () {
            $book = new Spreadsheet();
            $sheet = $book->getActiveSheet();
            $sheet->setTitle('Soal');
            $sheet->fromArray([...self::QUESTION_HEADERS, 'Rumpun']);
            $sheet->getStyle('A1:H1')->getFont()->setBold(true);
            $sheet->freezePane('A2');
            $sheet->getColumnDimension('B')->setWidth(50);
            foreach (['A', 'C', 'D', 'E', 'F', 'G', 'H'] as $column) {
                $sheet->getColumnDimension($column)->setWidth(20);
            }
            $instructions = $book->createSheet();
            $instructions->setTitle('Contoh');
            $instructions->fromArray([
                [...self::QUESTION_HEADERS, 'Rumpun'],
                ['Pilihan Ganda', 'Ibu kota Jawa Barat adalah?', 'Bandung', 'Bogor', 'Bekasi', 'Depok', 'A', 'Geografi'],
                ['Benar / Salah', 'Bandung berada di Jawa Barat.', null, null, null, null, 'Benar', 'Geografi'],
            ]);
            $instructions->getColumnDimension('B')->setWidth(50);
            $book->setActiveSheetIndex(0);
            (new Xlsx($book))->save('php://output');
            $book->disconnectWorksheets();
        }, 'template-soal-level-2.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importQuestions(Request $request, Training $training)
    {
        $this->authorizeManager($request, $training);
        $request->validate(['question_file' => ['required', 'file', 'mimes:xlsx', 'max:2048']]);
        $path = $request->file('question_file')->getPathname();

        try {
            $reader = new XlsxReader();
            $sheets = $reader->listWorksheetInfo($path);
            $info = collect($sheets)->firstWhere('worksheetName', 'Soal');
            if (!$info || $info['totalRows'] > TrainingFinalQuiz::MAX_QUESTIONS + 1 || $info['totalColumns'] > 8) {
                throw ValidationException::withMessages(['question_file' => 'Gunakan lembar Soal dari template, dengan maksimal 100 baris soal dan 8 kolom.']);
            }
            $book = $reader->setLoadSheetsOnly('Soal')->load($path);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $exception) {
            throw ValidationException::withMessages(['question_file' => 'File Excel tidak bisa dibaca. Gunakan template soal .xlsx.']);
        }

        try {
            $sheet = $book->getSheet(0);
            $rows = $sheet->rangeToArray('A1:H'.max(1, $info['totalRows']), null, false, false);
            $headers = array_shift($rows);
            if (array_slice($headers, 0, 7) !== self::QUESTION_HEADERS
                || !in_array($headers[7], [null, 'Rumpun'], true)) {
                throw ValidationException::withMessages(['question_file' => 'Judul kolom harus sama dengan template.']);
            }
            $items = [];
            foreach ($rows as $index => $row) {
                $row = array_map(fn ($value) => trim((string) $value), $row);
                if (!array_filter($row, fn ($value) => $value !== '')) {
                    continue;
                }
                foreach (range('A', 'H') as $column) {
                    if ($sheet->getCell($column.($index + 2))->isFormula()) {
                        throw ValidationException::withMessages(['question_file' => 'Baris '.($index + 2).' berisi rumus. Gunakan nilai teks biasa.']);
                    }
                }
                [$type, $prompt, $a, $b, $c, $d, $key, $group] = $row;
                $type = match (strtolower($type)) {
                    'pilihan ganda' => 'multiple',
                    'benar / salah', 'benar/salah', 'benar salah' => 'boolean',
                    default => '',
                };
                $answers = $type === 'multiple' ? ['a', 'b', 'c', 'd'] : ['benar', 'salah'];
                $correct = array_search(strtolower($key), $answers, true);
                if (!$type || $correct === false) {
                    throw ValidationException::withMessages(['question_file' => 'Jenis soal atau kunci jawaban tidak sesuai pada baris '.($index + 2).'.']);
                }
                try {
                    $items[] = $this->questionItem(['type' => $type, 'prompt' => $prompt, 'group' => $group === '' ? null : $group, 'options' => [$a, $b, $c, $d], 'correct' => $correct]);
                } catch (ValidationException $exception) {
                    throw ValidationException::withMessages(['question_file' => 'Baris '.($index + 2).' tidak valid. Isi pertanyaan dan empat pilihan berbeda untuk Pilihan Ganda, sesuai batas panjang soal.']);
                }
            }
        } finally {
            $book->disconnectWorksheets();
        }

        if (!$items) {
            throw ValidationException::withMessages(['question_file' => 'Lembar Soal belum berisi pertanyaan.']);
        }
        DB::transaction(function () use ($training, $items) {
            $quiz = $this->editableQuiz($training);
            $questions = array_merge($quiz->questions, $items);
            if (count($questions) > TrainingFinalQuiz::MAX_QUESTIONS) {
                throw ValidationException::withMessages(['question_file' => 'Jumlah soal setelah impor melebihi batas 100 soal.']);
            }
            $this->saveQuestionBank($quiz, $questions);
        });

        return $this->backToSettings($training, count($items).' soal berhasil diimpor.');
    }

    public function deleteQuestion(Request $request, Training $training, string $question)
    {
        $this->authorizeManager($request, $training);
        DB::transaction(function () use ($training, $question) {
            $quiz = $this->editableQuiz($training);
            $questions = collect($quiz->questions)->keyBy('id');
            abort_unless($questions->has($question), 404);
            $this->saveQuestionBank($quiz, $questions->forget($question)->values()->all());
        });

        return $this->backToSettings($training, 'Soal berhasil dihapus.');
    }

    public function quiz(Request $request, Training $training)
    {
        $participant = $this->approvedParticipant($request, $training);
        $quiz = $training->quizFor($this->quizKind($request))->firstOrFail();
        $attempt = $quiz->attempts()->where('participant_id', $participant->id)->first();
        abort_unless($attempt || ($quiz->is_visible && $training->level2Questions()), 404);

        return $this->quizResponse($quiz, $training, $participant, false, $attempt);
    }

    public function submit(Request $request, Training $training)
    {
        $kind = $this->quizKind($request);
        $this->saveAttempt($request, $training, $kind);

        return to_route($kind === 'pretest' ? 'participant.pretest.show' : 'participant.final-quiz.show', $training);
    }

    public function publicQuiz(Request $request, TrainingFinalQuiz $quiz)
    {
        $training = $quiz->training;
        $publicQuizzes = $this->publicQuizzes($training);
        if ($quiz->public_token !== $training->level2PublicToken()) {
            return to_route('public.final-quiz.show', $training->level2PublicToken());
        }
        $activeKey = 'public_final_quiz_active.'.$training->id;
        $active = $publicQuizzes->get($request->session()->get($activeKey));
        $available = $publicQuizzes->first(fn ($candidate) => $candidate->scheduleMessage() === null) ?? $publicQuizzes->first();
        $quiz = $active ?? $available;
        $receiptId = $request->session()->pull('public_quiz_receipt.'.$quiz->id);
        $attempt = $receiptId ? $quiz->attempts()->find($receiptId) : null;
        $participant = $attempt ? null : $this->publicParticipant($request, $quiz);
        if (!$participant) {
            $request->session()->forget($activeKey);
            if (!$attempt) {
                $quiz = $available;
            }
        }
        if ($participant && $quiz->attempts()->where('participant_id', $participant->id)->exists()) {
            $request->session()->forget([$activeKey, 'public_final_quiz.'.$quiz->id]);

            return $this->alreadyCompleted($quiz);
        }

        return $this->quizResponse($quiz, $training, $participant, true, $attempt);
    }

    public function verifyPublicQuiz(Request $request, TrainingFinalQuiz $quiz)
    {
        $training = $quiz->training;
        $publicQuizzes = $this->publicQuizzes($training);
        $publicToken = $training->level2PublicToken();
        abort_unless($publicQuizzes->contains(fn ($candidate) => $candidate->scheduleMessage() === null),
            403, $publicQuizzes->first()->scheduleMessage());
        $validator = Validator::make($request->only(['nip', 'token']), [
            'nip' => ['required', 'string', 'regex:/\A(?:[0-9]{18}|[0-9]{16})\z/'],
            'token' => ['required', 'string', 'regex:/\A[0-9a-fA-F]{16}\z/'],
        ], ['nip.regex' => 'Masukkan 18 digit NIP atau 16 digit NIK.', 'token.regex' => 'Token terdiri dari 16 karakter.']);
        if ($validator->fails()) {
            return to_route('public.final-quiz.show', $publicToken)->withErrors($validator);
        }

        $data = $validator->validated();
        $matches = $training->participants()->where('registration_status', 'approved')
            ->where(function ($query) use ($data) {
                $query->where('nip_nik', $data['nip'])->orWhereHas('user', function ($user) use ($data) {
                    $user->where('nip', $data['nip'])->orWhere('nik', $data['nip'])->orWhere('nip_nik', $data['nip']);
                });
            })->pluck('id');
        $token = strtoupper($data['token']);
        $access = $publicQuizzes->flatMap(fn ($candidate) => $candidate->accessTokens()
            ->whereIn('participant_id', $matches)->get()
            ->filter(fn ($entry) => hash_equals($entry->token, $token))->values());
        if ($access->count() !== 1) {
            return to_route('public.final-quiz.show', $publicToken)->withErrors([
                'nip' => 'NIP/NIK dan token tidak cocok. Periksa kembali atau hubungi penyelenggara.',
            ]);
        }

        $entry = $access->sole();
        $quiz = $publicQuizzes->get($entry->quiz_id);
        $label = $quiz->isPretest() ? 'Pretest' : 'Posttest';
        $activeKey = 'public_final_quiz_active.'.$training->id;
        $previousQuizId = $request->session()->pull($activeKey);
        if ($previousQuizId) {
            $request->session()->forget('public_final_quiz.'.$previousQuizId);
        }
        if ($quiz->attempts()->where('participant_id', $entry->participant_id)->exists()) {
            return $this->alreadyCompleted($quiz);
        }
        if ($message = $quiz->scheduleMessage()) {
            return to_route('public.final-quiz.show', $publicToken)->withErrors([
                'token' => 'Token ini untuk '.$label.'. '.$message,
            ]);
        }

        $request->session()->regenerate(true);
        $request->session()->put($activeKey, $quiz->id);
        $request->session()->put('public_final_quiz.'.$quiz->id, [
            'participant_id' => $entry->participant_id,
            'nonce' => Str::random(32),
            'token_hash' => hash('sha256', $entry->token),
            'confirmed' => false,
        ]);

        return to_route('public.final-quiz.show', $publicToken)->with('quiz_notice', 'Token ini untuk '.$label.'. Silakan konfirmasi identitas untuk melanjutkan.');
    }

    public function confirmPublicParticipant(Request $request, TrainingFinalQuiz $quiz)
    {
        $quiz = $this->activePublicQuiz($request, $quiz->training);
        $scheduleMessage = $quiz->scheduleMessage();
        abort_if($scheduleMessage !== null, 403, $scheduleMessage);
        $key = 'public_final_quiz.'.$quiz->id;
        abort_unless($this->publicParticipant($request, $quiz)
            && $request->session()->get($key.'.nonce') === $request->input('quiz_access'), 403);
        $request->session()->put($key.'.confirmed', true);

        return to_route('public.final-quiz.show', $quiz->training->level2PublicToken());
    }

    public function submitPublicQuiz(Request $request, TrainingFinalQuiz $quiz)
    {
        $quiz = $this->activePublicQuiz($request, $quiz->training);
        $attempt = $this->saveAttempt($request, $quiz->training, $quiz->kind, $quiz->id);
        $request->session()->forget('public_final_quiz.'.$quiz->id);
        $request->session()->regenerate(true);

        return $attempt
            ? to_route('public.final-quiz.show', $quiz->training->level2PublicToken())->with('public_quiz_receipt.'.$quiz->id, $attempt->id)
            : $this->alreadyCompleted($quiz);
    }

    private function alreadyCompleted(TrainingFinalQuiz $quiz)
    {
        return to_route('public.final-quiz.show', $quiz->training->level2PublicToken())
            ->with('quiz_completed', 'Peserta dengan NIP/NIK dan token ini sudah menyelesaikan kuis.');
    }

    private function publicQuizzes(Training $training)
    {
        $quizzes = collect([$training->pretestQuiz, $training->finalQuiz])
            ->filter(fn ($quiz) => $quiz?->is_public)->keyBy('id');
        abort_unless($quizzes->isNotEmpty() && $training->level2Questions(), 404);

        return $quizzes;
    }

    private function activePublicQuiz(Request $request, Training $training): TrainingFinalQuiz
    {
        $quiz = $this->publicQuizzes($training)->get($request->session()->get('public_final_quiz_active.'.$training->id));
        abort_unless($quiz, 403, 'Verifikasi NIP/NIK dan token sebelum melanjutkan.');

        return $quiz;
    }

    private function publicParticipant(Request $request, TrainingFinalQuiz $quiz): ?Participant
    {
        $access = $request->session()->get('public_final_quiz.'.$quiz->id);
        $participantId = $access['participant_id'] ?? null;
        if (!$participantId) {
            return null;
        }

        $token = $quiz->accessTokens()->where('participant_id', $participantId)->first()?->token;
        if ($token === null || !hash_equals(hash('sha256', $token), $access['token_hash'] ?? '')) {
            return null;
        }

        return $quiz->training->participants()->where('registration_status', 'approved')->find($participantId);
    }

    private function quizResponse(TrainingFinalQuiz $quiz, Training $training, ?Participant $participant, bool $public, ?TrainingFinalQuizAttempt $attempt)
    {
        $scheduleMessage = $quiz->scheduleMessage();
        $scoreField = $quiz->isPretest() ? 'pretest' : 'postest';
        $questions = $training->level2Questions();
        if ($participant && !$attempt && $scheduleMessage === null) {
            $questions = $quiz->questionsForParticipant($questions, $participant->id);
        }
        $finalScore = !$public && $attempt && $scheduleMessage === null
            ? (float) ($participant->evaluationL2?->{$scoreField} ?? $attempt->score)
            : null;
        $accountToken = !$public && !$attempt && $scheduleMessage === null ? $this->participantToken($quiz, $participant) : null;
        $publicAccess = $public ? request()->session()->get('public_final_quiz.'.$quiz->id) : null;
        $submitUrl = $public ? route('public.final-quiz.submit', $training->level2PublicToken())
            : route($quiz->isPretest() ? 'participant.pretest.submit' : 'participant.final-quiz.submit', $training);

        return response()->view($public ? 'evaluasi.l2_public_quiz' : 'participant.final_quiz',
            compact('training', 'quiz', 'attempt', 'finalScore', 'scheduleMessage', 'submitUrl', 'participant', 'publicAccess', 'accountToken', 'questions'))
            ->header('Cache-Control', 'private, no-store')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function saveAttempt(Request $request, Training $training, string $kind, ?int $publicQuizId = null): ?TrainingFinalQuizAttempt
    {
        return DB::transaction(function () use ($request, $training, $kind, $publicQuizId) {
            $locked = Training::lockForUpdate()->findOrFail($training->id);
            $quiz = $locked->quizFor($kind)->firstOrFail();
            if ($publicQuizId !== null) {
                abort_unless($quiz->id === $publicQuizId && $quiz->is_public, 404);
                $participant = $this->publicParticipant($request, $quiz);
                $access = $request->session()->get('public_final_quiz.'.$quiz->id);
                abort_unless($participant && ($access['confirmed'] ?? false) && $access['nonce'] === $request->input('quiz_access'),
                    403, 'Verifikasi dan konfirmasi identitas sebelum mengirim jawaban.');
            } else {
                $participant = $this->approvedParticipant($request, $locked);
            }
            if ($quiz->attempts()->where('participant_id', $participant->id)->exists()) {
                return null;
            }
            $questions = $locked->level2Questions();
            abort_unless(($publicQuizId !== null || $quiz->is_visible) && $questions, 404);
            $scheduleMessage = $quiz->scheduleMessage();
            abort_if($scheduleMessage !== null, 403, $scheduleMessage);

            $rules = ['revision' => ['required', 'integer'], 'answers' => ['required', 'array', 'size:'.count($questions)]];
            foreach ($questions as $item) {
                $rules['answers.'.$item['id']] = ['required', 'integer', 'between:0,'.(count($item['options']) - 1)];
            }
            $data = $request->validate($rules);
            if ((int) $data['revision'] !== $quiz->revision) {
                throw ValidationException::withMessages(['revision' => 'Kuis diperbarui. Muat ulang halaman sebelum mengirim jawaban.']);
            }
            $answers = array_map('intval', $data['answers']);
            $correct = count(array_filter($questions, fn ($item) => $answers[$item['id']] === $item['correct']));
            $total = count($questions);
            $score = round($correct / $total * 100, 2);
            $attempt = $quiz->attempts()->create([
                'participant_id' => $participant->id, 'score' => $score, 'pass_score' => $quiz->pass_score,
                'passed' => $score >= $quiz->pass_score, 'correct_count' => $correct, 'questions_count' => $total,
                'answers' => $answers,
            ]);

            $result = EvaluationResultL2::firstOrNew(['participant_id' => $participant->id], ['pretest' => null, 'postest' => null]);
            $result->{$quiz->isPretest() ? 'pretest' : 'postest'} = $score;
            $result->save();

            return $attempt;
        });
    }

    public function indexAll(Request $request)
    {
        $user = $request->user();
        abort_unless(in_array($user->role, ['superadmin', 'admin_bidang'], true), 403);
        $trainings = Training::when($user->role === 'admin_bidang', fn ($query) => $query->where('bidang', $user->bidang))->latest()->get();
        return view('evaluasi.l2_all', compact('trainings'));
    }

    private function authorizeManager(Request $request, Training $training): void
    {
        $user = $request->user();
        abort_unless($user->role === 'superadmin' || ($user->role === 'admin_bidang' && $user->bidang === $training->bidang), 403);
    }

    private function quizKind(Request $request): string
    {
        return $request->routeIs('evall2.pretest.*', 'participant.pretest.*') ? 'pretest' : 'posttest';
    }

    private function backToSettings(Training $training, string $message)
    {
        return to_route('evall2.quiz.manage', $training)->with('success', $message);
    }

    private function editableQuiz(Training $training): TrainingFinalQuiz
    {
        $locked = Training::lockForUpdate()->findOrFail($training->id);
        foreach (array_filter([$locked->finalQuiz, $locked->pretestQuiz]) as $quiz) {
            if ($quiz->is_visible || $quiz->is_public || $quiz->attempts()->exists()) {
                throw ValidationException::withMessages(['prompt' => 'Soal hanya dapat diubah saat Pretest dan Posttest disembunyikan dan keduanya belum menerima jawaban.']);
            }
        }

        return $locked->finalQuiz ?? $locked->finalQuiz()->create([
            'title' => 'Posttest Pelatihan', 'pass_score' => 70, 'questions' => $locked->level2Questions(), 'revision' => 0,
        ]);
    }

    private function saveQuestionBank(TrainingFinalQuiz $quiz, array $questions): void
    {
        $quiz->update(['questions' => $questions, 'revision' => $quiz->revision + 1]);
        $quiz->training->pretestQuiz()->increment('revision');
    }

    private function approvedParticipant(Request $request, Training $training): Participant
    {
        $user = $request->user();
        abort_unless($user->role === 'participant', 403);
        $participants = $training->participants()->where('registration_status', 'approved');
        $participant = (clone $participants)->where('user_id', $user->id)->first();
        if (!$participant && filled($user->nip_nik)) {
            $matches = (clone $participants)->whereNull('user_id')->where('nip_nik', $user->nip_nik)->limit(2)->get();
            if ($matches->count() === 1) $participant = $matches->first();
        }
        abort_unless($participant, 403, 'Kuis hanya tersedia bagi peserta yang disetujui.');

        return $participant;
    }
}
