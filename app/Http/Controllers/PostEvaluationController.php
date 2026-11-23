<?php

namespace App\Http\Controllers;

use App\Exports\PostEvaluationAnnualSummaryExport;
use App\Models\AiGeneration;
use App\Models\Participant;
use App\Models\PostEvaluationInterview;
use App\Models\PostEvaluationSetting;
use App\Models\Training;
use App\Models\User;
use App\Services\PostEvaluationAiService;
use App\Services\PostEvaluationExposeService;
use App\Services\PostEvaluationService;
use App\Services\PostEvaluationSurveySummaryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class PostEvaluationController extends Controller
{
    public function index(Request $request, PostEvaluationService $service)
    {
        $user = $request->user();
        abort_if($user->role === 'admin_bidang', 403);
        $query = Training::query()->when($user->role !== 'superadmin', function ($query) use ($user) {
            $query->whereExists(function ($assignment) use ($user) {
                $assignment->selectRaw('1')->from('post_evaluation_teams as evaluator_team')
                    ->join('post_evaluation_team_user as evaluator_member', 'evaluator_member.team_id', '=', 'evaluator_team.id')
                    ->whereColumn('evaluator_team.training_id', 'trainings.id')
                    ->where('evaluator_member.user_id', $user->id);
            });
        });
        $annual = $request->query('tab') === 'ringkasan-tahunan';
        $search = $request->query('q');
        $search = is_string($search) ? mb_substr(trim($search), 0, 100) : '';
        $years = collect();
        $year = null;
        if ($annual) {
            $years = (clone $query)->selectRaw('SUBSTR(tgl_mulai, 1, 4) as year')
                ->distinct()->orderByDesc('year')->pluck('year')->map(fn ($year) => (int) $year);
            $selectedYear = $request->integer('year');
            $year = $years->contains($selectedYear) ? $selectedYear : $years->first();
            if ($year !== null) {
                $query->whereYear('tgl_mulai', $year);
            }
        }

        $query->when($search !== '', fn ($q) => $q->where('nama_pelatihan', 'like', '%'.$search.'%'));
        $query = $annual ? $query->orderByDesc('tgl_mulai')->orderByDesc('id') : $query->latest();

        if ($annual && $request->query('format') === 'xlsx') {
            $filename = $year === null ? 'ringkasan-evaluasi-pasca.xlsx' : "ringkasan-evaluasi-pasca-{$year}.xlsx";

            return Excel::download(new PostEvaluationAnnualSummaryExport($query, $service, $user), $filename, null, [
                'Cache-Control' => 'private, no-store',
            ]);
        }

        $trainings = $query->paginate($annual ? 10 : 15)->withQueryString();
        if ($annual) {
            $trainings->through(fn (Training $training) => $service->annualSummaryRow($training, $user));
        }

        return response()->view('post-evaluation.index', compact('trainings', 'annual', 'years', 'year', 'search'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function show(Request $request, Training $training, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training);
        $manager = $service->manages($request->user(), $training);
        $tab = $request->query('tab', 'ringkasan');
        // Keep existing participant links opening the combined workspace.
        if ($tab === 'peserta') $tab = 'wawancara';
        if (in_array($tab, ['keberlanjutan', 'perbandingan', 'instansi'], true)) $tab = 'ringkasan';
        $tabs = [
            'ringkasan' => 'Ringkasan',
            'jawaban' => 'Jawaban',
            'wawancara' => 'Peserta & Wawancara',
            'pengaturan' => 'Pengaturan',
            'import' => 'Import Data',
        ];
        abort_unless(is_string($tab) && isset($tabs[$tab]), 404);
        if (! $manager) {
            $tabs = array_diff_key($tabs, array_flip(['jawaban', 'pengaturan', 'import']));
        }
        abort_unless(isset($tabs[$tab]), 403);

        $user = $request->user();
        $survey = app(PostEvaluationSurveySummaryService::class);
        $surveySummary = $tab === 'ringkasan'
            ? $survey->overview($training, $user) + $survey->agencies($training, $user)
            : null;
        $continuationRecap = $tab === 'ringkasan' ? $service->trainingContinuationRecap($training, $user) : [];
        $showNarrative = $manager && $tab === 'ringkasan';
        $manualNarrative = $showNarrative && filled($service->setting($training)?->report_narrative);
        $savedGemini = $showNarrative && ! $manualNarrative && AiGeneration::where('training_id', $training->id)
            ->where('feature', PostEvaluationExposeService::FEATURE)->where('status', 'completed')->exists();
        $people = in_array($tab, ['jawaban', 'wawancara', 'pengaturan'], true) ? $service->people($training, $user) : [];
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $paginator = null;
        $answerRows = collect();
        if ($tab === 'jawaban') {
            $rows = collect($people)->flatMap(function ($person) {
                return $person['answers']->map(fn ($answer) => ['person' => $person, 'answer' => $answer]);
            });
            if ($search !== '') {
                $rows = $rows->filter(fn ($row) => mb_stripos($row['person']['participant']->name.' '.$row['answer']->question?->question_text.' '.$row['answer']->note, $search) !== false);
            }
        } else {
            $rows = collect($people);
            if ($search !== '') {
                $rows = $rows->filter(fn ($person) => mb_stripos($person['participant']->name.' '.$person['participant']->nip_nik.' '.$person['participant']->instansi.' '.$person['team'], $search) !== false);
            }
        }
        if (in_array($tab, ['jawaban', 'peserta', 'wawancara'], true)) {
            $page = LengthAwarePaginator::resolveCurrentPage();
            $paginator = new LengthAwarePaginator($rows->values()->forPage($page, 25)->values(), $rows->count(), 25, $page, ['path' => $request->url(), 'query' => $request->query()]);
            if ($tab === 'jawaban') {
                $answerRows = $paginator;
            } else {
                $people = $paginator->items();
            }
        }
        $importPreview = null;
        $importHistory = collect();
        if ($tab === 'import') {
            if ($request->filled('preview')) {
                $importPreview = DB::table('post_evaluation_imports')->where('id', $request->query('preview'))->where('training_id', $training->id)->where('created_by', $request->user()->id)->first();
                abort_unless($importPreview, 404);
                $importPreview->report = json_decode($importPreview->report, true, 512, JSON_THROW_ON_ERROR);
            }
            $importHistory = DB::table('post_evaluation_imports')->where('training_id',$training->id)->where('status','imported')->latest('imported_at')->limit(10)->get();
        }
        $teams = $manager && in_array($tab, ['wawancara', 'pengaturan'], true)
            ? DB::table('post_evaluation_teams')->where('training_id', $training->id)->get() : collect();
        $members = $tab === 'pengaturan' ? DB::table('post_evaluation_team_user')->join('users', 'users.id', '=', 'user_id')
            ->whereIn('team_id', $teams->pluck('id'))->get(['team_id', 'users.id as user_id', 'users.name'])->groupBy('team_id') : collect();
        $users = $tab === 'pengaturan' ? User::where('role', '!=', 'admin_bidang')->orderBy('name')
            ->get(['id', 'name', 'username', 'role']) : collect();

        return response()->view('post-evaluation.show', compact('training', 'manager', 'tabs', 'tab', 'people',
            'teams', 'members', 'users', 'paginator', 'answerRows', 'search', 'surveySummary', 'continuationRecap',
            'manualNarrative', 'savedGemini', 'importPreview', 'importHistory'))->header('Cache-Control', 'private, no-store');
    }

    public function team(Request $request, Training $training, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training, true);
        $data = $request->validate(['name' => 'required|string|max:150', 'users' => 'sometimes|array|min:1', 'users.*' => ['required', 'integer', 'distinct', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', '!=', 'admin_bidang'))]]);
        DB::transaction(function () use ($data, $training) {
            $id = DB::table('post_evaluation_teams')->insertGetId(['training_id' => $training->id, 'name' => $data['name'], 'created_at' => now(), 'updated_at' => now()]);
            foreach ($data['users'] ?? [] as $user) {
                DB::table('post_evaluation_team_user')->insert(['team_id' => $id, 'user_id' => $user]);
            }
        });

        return back()->with('success', 'Nama tim berhasil dibuat. Selanjutnya tambahkan evaluator ke tim.');
    }

    public function addEvaluators(Request $request, Training $training, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training, true);
        $data = $request->validate([
            'team_id' => ['required', 'integer', Rule::exists('post_evaluation_teams', 'id')->where('training_id', $training->id)],
            'users' => ['required', 'array', 'min:1'],
            'users.*' => ['required', 'integer', 'distinct', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', '!=', 'admin_bidang'))],
        ]);
        DB::transaction(function () use ($data, $training) {
            DB::table('post_evaluation_teams')->where('training_id', $training->id)->where('id', $data['team_id'])->lockForUpdate()->firstOrFail();
            foreach ($data['users'] as $user) {
                DB::table('post_evaluation_team_user')->insertOrIgnore(['team_id' => $data['team_id'], 'user_id' => $user]);
            }
        });

        return back()->with('success', 'Evaluator dari akun yang sudah ada berhasil ditambahkan ke tim.');
    }

    public function moveEvaluator(Request $request, Training $training, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training, true);
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'from_team_id' => ['required', 'integer', Rule::exists('post_evaluation_teams', 'id')->where('training_id', $training->id)],
            'to_team_id' => ['nullable', 'integer', 'different:from_team_id', Rule::exists('post_evaluation_teams', 'id')->where('training_id', $training->id)],
        ]);
        DB::transaction(function () use ($data, $training) {
            Training::whereKey($training->id)->lockForUpdate()->firstOrFail();
            $membership = DB::table('post_evaluation_team_user')->where('team_id', $data['from_team_id'])->where('user_id', $data['user_id'])->lockForUpdate()->first();
            abort_unless($membership, 404);
            if (! empty($data['to_team_id'])) {
                abort_if(User::findOrFail($data['user_id'])->role === 'admin_bidang', 422, 'Admin bidang tidak dapat ditugaskan sebagai evaluator.');
                DB::table('post_evaluation_team_user')->insertOrIgnore(['team_id' => $data['to_team_id'], 'user_id' => $data['user_id']]);
            }
            DB::table('post_evaluation_team_user')->where('id', $membership->id)->delete();
        });

        return back()->with('success', empty($data['to_team_id']) ? 'Evaluator dikeluarkan dari tim. Akun dan hasil wawancara tetap tersimpan.' : 'Evaluator berhasil dipindahkan ke tim tujuan. Peserta dan nilai wawancara tetap pada tim masing-masing.');
    }

    public function createEvaluator(Request $request, Training $training, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training, true);
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'username')],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
            'team_id' => ['nullable', 'integer', Rule::exists('post_evaluation_teams', 'id')->where('training_id', $training->id)],
        ]);
        if (User::whereRaw('LOWER(username) = ?', [$data['email']])->exists()) {
            throw ValidationException::withMessages(['email' => 'Email sudah dipakai akun lain. Pilih akun tersebut dari daftar evaluator yang tersedia.']);
        }
        DB::transaction(function () use ($data) {
            $user = User::create(['name' => $data['name'], 'username' => $data['email'], 'password' => $data['password'], 'role' => 'evaluator', 'must_complete_profile' => false, 'must_change_password' => false]);
            if (! empty($data['team_id'])) {
                DB::table('post_evaluation_team_user')->insert(['team_id' => $data['team_id'], 'user_id' => $user->id]);
            }
        });

        return back()->with('success', 'Akun evaluator berhasil dibuat. Evaluator dapat login menggunakan email dan password yang Anda tetapkan.');
    }

    public function deleteTeam(Request $request, Training $training, int $team, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training, true);
        DB::transaction(function () use ($training, $team) {
            abort_unless(DB::table('post_evaluation_teams')->where('training_id', $training->id)->where('id', $team)->exists(), 404);
            PostEvaluationInterview::where('training_id', $training->id)->where('team_id', $team)->update(['team_id' => null, 'revision' => DB::raw('revision + 1')]);
            DB::table('post_evaluation_teams')->where('id', $team)->delete();
        });

        return back()->with('success', 'Tim dihapus; nilai wawancara tetap tersimpan.');
    }

    public function assign(Request $request, Training $training, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training, true);
        $data = $request->validate(['participant_ids' => 'required|array|min:1', 'participant_ids.*' => 'required|integer|distinct', 'team_id' => ['nullable', 'integer', Rule::exists('post_evaluation_teams', 'id')->where('training_id', $training->id)]]);
        $ids = Participant::where('training_id', $training->id)->where('registration_status', 'approved')->whereIn('id', $data['participant_ids'])->pluck('id');
        abort_unless($ids->count() === count($data['participant_ids']), 422);
        DB::transaction(function () use ($ids, $data, $training) {
            foreach ($ids as $id) {
                PostEvaluationInterview::firstOrCreate(['training_id' => $training->id, 'participant_id' => $id]);
                PostEvaluationInterview::where('training_id', $training->id)->where('participant_id', $id)->update(['team_id' => $data['team_id'] ?? null, 'revision' => DB::raw('revision + 1')]);
            }
        });

        return back()->with('success', 'Pembagian peserta tersimpan.');
    }

    public function autoAssign(Request $request, Training $training, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training, true);
        $assigned = DB::transaction(function () use ($training) {
            Training::whereKey($training->id)->lockForUpdate()->firstOrFail();
            $teams = DB::table('post_evaluation_teams')->where('training_id', $training->id)
                ->whereExists(function ($q) {
                    $q->selectRaw('1')->from('post_evaluation_team_user')->join('users', 'users.id', '=', 'user_id')
                        ->whereColumn('team_id', 'post_evaluation_teams.id')->where('users.role', '!=', 'admin_bidang');
                })->orderBy('id')->lockForUpdate()->pluck('id');
            if ($teams->isEmpty()) {
                throw ValidationException::withMessages(['team' => 'Buat tim dan tambahkan evaluator terlebih dahulu sebelum membagi peserta otomatis.']);
            }
            $participants = Participant::where('training_id', $training->id)->where('registration_status', 'approved')->orderBy('id')->lockForUpdate()->get();
            $interviews = PostEvaluationInterview::where('training_id', $training->id)->lockForUpdate()->get()->keyBy('participant_id');
            $loads = array_fill_keys($teams->all(), 0);
            foreach ($participants as $participant) {
                $teamId = $interviews->get($participant->id)?->team_id;
                if (isset($loads[$teamId])) {
                    $loads[$teamId]++;
                }
            }
            $count = 0;
            foreach ($participants as $participant) {
                $interview = $interviews->get($participant->id);
                if ($interview?->team_id !== null) {
                    continue;
                }
                $teamId = array_search(min($loads), $loads, true);
                $interview ??= PostEvaluationInterview::firstOrCreate(['training_id' => $training->id, 'participant_id' => $participant->id]);
                $interview->update(['team_id' => $teamId, 'revision' => $interview->revision + 1]);
                $loads[$teamId]++;
                $count++;
            }

            return $count;
        });

        return back()->with('success', $assigned ? $assigned.' peserta berhasil dibagi otomatis secara merata. Pembagian yang sudah ada tetap dipertahankan.' : 'Semua peserta yang disetujui sudah memiliki tim. Tidak ada pembagian yang diubah.');
    }

    private function interviewAccess(Request $request, Training $training, Participant $participant, PostEvaluationService $service): PostEvaluationInterview
    {
        abort_unless($participant->training_id == $training->id && $participant->registration_status === 'approved', 404);
        $service->authorize($request->user(), $training);
        $interview = PostEvaluationInterview::where('training_id', $training->id)->where('participant_id', $participant->id)->first();
        if (! $interview && $service->manages($request->user(), $training)) {
            $interview = PostEvaluationInterview::create(['training_id' => $training->id, 'participant_id' => $participant->id]);
        }
        abort_unless($interview && ($service->manages($request->user(), $training) || $service->teamIds($request->user(), $training)->contains($interview->team_id)), 403);

        return $interview;
    }

    public function edit(Request $request, Training $training, Participant $participant, PostEvaluationService $service)
    {
        $interview = $this->interviewAccess($request, $training, $participant, $service);
        $rubric = $service->rubric($training);
        $evidence = DB::table('post_evaluation_evidence')->where('interview_id', $interview->id)->get();

        $surveyAnswers = $service->surveyAnswers($training, $participant);

        return response()->view('post-evaluation.interview', compact('training', 'participant', 'interview', 'rubric', 'evidence', 'surveyAnswers'))->header('Cache-Control', 'private, no-store');
    }

    public function save(Request $request, Training $training, Participant $participant, PostEvaluationService $service)
    {
        $interview = $this->interviewAccess($request, $training, $participant, $service);
        $data = $request->validate(['revision' => 'required|integer|min:0', 'continuation' => 'sometimes|array|size:5', 'continuation.*' => 'nullable|in:yes,no', 'scores' => 'required|array|size:5', 'scores.*' => 'nullable|numeric|between:0,100', 'notes' => 'required|array|size:5', 'notes.*' => 'nullable|string|max:10000', 'status' => ['required', Rule::in(['draft', 'completed'])]]);
        if (array_keys($data['scores']) !== range(0, 4) || array_keys($data['notes']) !== range(0, 4)) {
            throw ValidationException::withMessages(['scores' => 'Urutan lima kriteria tidak valid.']);
        }
        $saved = DB::transaction(function () use ($request, $data, $interview, $training, $service) {
            $row = PostEvaluationInterview::lockForUpdate()->findOrFail($interview->id);
            abort_unless($service->manages($request->user(), $training) || $service->teamIds($request->user(), $training)->contains($row->team_id), 403);
            if ($row->revision !== (int) $data['revision']) {
                abort(409, 'Data berubah dari perangkat lain. Salin catatan Anda lalu muat ulang halaman.');
            }
            $data['scores'] = array_map(fn ($v) => $v === null ? null : (float) $v, $data['scores']);
            if (isset($data['continuation'])) {
                if (array_keys($data['continuation']) !== range(0, 4)) {
                    throw ValidationException::withMessages(['continuation'=>'Urutan konfirmasi tidak valid.']);
                }
                $choices = [];
                foreach ($data['continuation'] as $i => $answer) {
                    $choices[$i] = $answer === null ? null : $answer === 'yes';
                    if ($answer === 'no' && $data['scores'][$i] === null) {
                        $data['scores'][$i] = $row->scores[$i] ?? PostEvaluationInterview::DEFAULT_NO_SCORE;
                    } elseif ($answer === null) {
                        $data['scores'][$i] = null;
                    }
                }
                $data['continuation'] = $choices;
                if ($data['status'] === 'completed' && in_array(null, $choices, true)) {
                    throw ValidationException::withMessages(['continuation'=>'Pilih YA atau TIDAK pada kelima kriteria sebelum menandai selesai.']);
                }
            } else {
                // Compatibility for older open forms: infer confirmation from submitted percentages.
                $data['continuation'] = array_map(fn ($v) => $v === null ? null : $v > 25, $data['scores']);
            }
            if ($data['status'] === 'completed' && in_array(null, $data['scores'], true)) {
                throw ValidationException::withMessages(['scores'=>'Lengkapi kelima nilai sebelum menandai selesai.']);
            }
            $row->update(array_merge($data, ['revision' => $row->revision + 1, 'updated_by' => $request->user()->id]));

            return $row;
        });
        if ($request->expectsJson()) {
            return response()->json(['revision' => $saved->revision, 'scores' => $saved->scores, 'message' => 'Wawancara tersimpan.']);
        }

        return back()->with('success', 'Wawancara tersimpan.');
    }

    public function editNarrative(Request $request, Training $training, PostEvaluationService $service, \App\Services\PostEvaluationNarrativeService $narrative)
    {
        $service->authorize($request->user(), $training, true);
        $people = $service->people($training);
        $summary = $service->summary($training, $people, false);
        $saved = $service->setting($training)?->report_narrative ?? [];
        $sourceHash = $narrative->fingerprint($summary);
        $stale = $saved && ($saved['source_hash'] ?? '') !== $sourceHash;
        $notes = $narrative->notes($people);
        $evaluatorNotes = $narrative->notesText($notes);
        $noteCount = count($notes);
        $prompt = $narrative->prompt($summary, $notes);
        return response()->view('post-evaluation.narrative', compact('training','summary','saved','sourceHash','stale','evaluatorNotes','noteCount','prompt'))->header('Cache-Control','private, no-store');
    }

    public function saveNarrative(Request $request, Training $training, PostEvaluationService $service, \App\Services\PostEvaluationNarrativeService $narrative)
    {
        $service->authorize($request->user(), $training, true);
        $data = $request->validate([
            'action'=>'required|in:save,reset', 'source_hash'=>'required|string|size:64',
            'notes'=>'nullable|string|max:10000', 'discussion'=>'required|array:4,5,6,7,8',
            'discussion.*'=>'nullable|string|max:1600', 'conclusions'=>'nullable|string|max:1600',
            'recommendations'=>'nullable|string|max:1600',
        ]);
        if ($data['action'] === 'save') {
            $summary = $service->summary($training, $service->people($training), false);
            if (!hash_equals($narrative->fingerprint($summary), $data['source_hash'])) {
                throw ValidationException::withMessages(['source_hash'=>'Data evaluasi berubah. Salin dahulu narasi Anda, muat ulang prompt terbaru, lalu tinjau kembali sebelum menyimpan.']);
            }
        }
        PostEvaluationSetting::firstOrCreate(['training_id'=>$training->id])->update([
            'report_narrative'=>$data['action'] === 'reset' ? null : array_merge($data,['saved_at'=>now()->toIso8601String(),'saved_by'=>$request->user()->id]),
        ]);
        return redirect()->route('post-evaluation.expose', [$training] + $request->only('from'))->with('success', $data['action']==='reset' ? 'Narasi kembali ke ringkasan dasar atau hasil Gemini yang tersimpan.' : 'Narasi disimpan dan diterapkan pada ekspose serta PDF.');
    }

    public function generateExposeAi(Request $request, Training $training, PostEvaluationService $service, PostEvaluationAiService $ai, PostEvaluationExposeService $expose)
    {
        $service->authorize($request->user(), $training, true);
        $people = $service->people($training);
        $summary = $service->summary($training, $people);
        $automatic = $request->boolean('automatic');
        if (! $ai->configured()) {
            throw ValidationException::withMessages(['ai' => 'Layanan AI belum dikonfigurasi. Lengkapi pengaturan provider AI aplikasi terlebih dahulu.']);
        }
        if ($summary['survey']['valid'] === 0 && $summary['interview']['valid'] === 0) {
            throw ValidationException::withMessages(['ai' => 'Belum ada nilai survei atau wawancara untuk dianalisis. Lengkapi data terlebih dahulu.']);
        }
        $hash = $expose->fingerprint($summary);
        $lock = \Illuminate\Support\Facades\Cache::lock('post-evaluation-ai:'.$training->id, 300);
        if (! $lock->get()) return response()->json(['message' => 'Narasi sedang dibuat di sesi lain. Muat ulang setelah selesai.'], 409);
        try {
            if ($automatic && AiGeneration::where('training_id', $training->id)->where('feature', PostEvaluationExposeService::FEATURE)->where('source_hash', $hash)->where('status', 'completed')->exists()) {
                return response()->json(['message' => 'Narasi sudah sesuai data terkini.', 'cached' => true]);
            }
            if ($automatic && AiGeneration::where('training_id', $training->id)->where('feature', PostEvaluationExposeService::FEATURE)->where('source_hash', $hash)->where('status', 'failed')->where('created_at', '>', now()->subMinutes(5))->exists()) {
                return response()->json(['message' => 'Gemini belum berhasil membuat narasi. Gunakan tombol Buat ulang untuk mencoba kembali.'], 429);
            }
            $summary['evaluator_notes'] = app(\App\Services\PostEvaluationNarrativeService::class)->notes($people);
            $generation = AiGeneration::create(['training_id' => $training->id, 'user_id' => $request->user()->id,
                'feature' => PostEvaluationExposeService::FEATURE, 'model' => $ai->model(), 'source_hash' => $hash,
                'status' => 'processing', 'input_summary' => ['aggregate_only' => false, 'participant_identities_sent' => false,
                    'anonymized_evaluator_notes_sent' => true, 'data' => $expose->payload($summary)]]);
            try {
                $content = $ai->generate($summary);
                $generation->update(['status' => 'completed', 'generated_content' => $content, 'generated_at' => now()]);
            } catch (\Throwable $error) {
                report($error);
                $generation->update(['status' => 'failed', 'error_message' => mb_substr($error->getMessage(), 0, 2000)]);
                $message = 'Narasi AI belum berhasil dibuat. Silakan coba kembali; hasil sebelumnya tetap tersimpan.';
                return $request->expectsJson() ? response()->json(['message' => $message], 502) : back()->with('error', $message);
            }
            $message = 'Pembahasan, kesimpulan dan rekomendasi AI tersimpan. Perbarui publikasi untuk menampilkan narasi terbaru pada link publik.';
            return $request->expectsJson() ? response()->json(['message' => $message]) : back()->with('success', $message);
        } finally {
            $lock->release();
        }
    }

    public function publish(Request $request, Training $training, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training, true);
        $data = $request->validate(['action' => ['required', Rule::in(['publish', 'revoke', 'rotate'])]]);
        $setting = PostEvaluationSetting::firstOrCreate(['training_id' => $training->id]);
        if ($data['action'] === 'revoke') {
            $setting->update(['public_token' => null, 'snapshot' => null, 'published_at' => null]);
        } else {
            $summary = $service->summary($training, $service->people($training));
            $setting->update(['public_token' => $data['action'] === 'rotate' || ! $setting->public_token ? Str::random(48) : $setting->public_token, 'snapshot' => $summary, 'published_at' => now()]);
        }

        return back()->with('success', 'Pengaturan publikasi hasil diperbarui.');
    }

    public function expose(Request $request, Training $training, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training, true);
        $summary = $service->summary($training, $service->people($training, $request->user()));

        return $this->presentation($request, $summary, false);
    }

    public function publicResult(Request $request, string $token)
    {
        $setting = PostEvaluationSetting::where('public_token', $token)->whereNotNull('published_at')->firstOrFail();
        abort_unless($setting->snapshot, 404);

        return $this->presentation($request, $setting->snapshot, true);
    }

    private function presentation(Request $request, array $summary, bool $public)
    {
        $presenter = app(PostEvaluationExposeService::class);
        $summary = $presenter->forView($summary);
        $pdf = $request->routeIs('post-evaluation.pdf', 'post-evaluation.public.pdf');
        if ($pdf) {
            return Pdf::loadView('post-evaluation.presentation', compact('summary', 'public', 'pdf', 'presenter'))->setPaper([0, 0, 960, 540])->download('Ekspose-Pasca-L34.pdf');
        }

        return response()->view('post-evaluation.presentation', compact('summary', 'public', 'pdf', 'presenter'))->header('Cache-Control', $public ? 'no-store' : 'private, no-store')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function properForm(Training $training)
    {
        return $this->properResponse($training);
    }

    private function properResponse(Training $training)
    {
        return response()->view('post-evaluation.upload', compact('training'))
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function properParticipant(Request $request, Training $training): Participant
    {
        $data = $request->validate(['participant_identity' => 'required|string|max:80']);
        $identity = trim(ltrim($data['participant_identity'], "'"));
        $participant = Participant::where('training_id', $training->id)->where('registration_status', 'approved')->where('nip_nik', $identity)->first();
        if (! $participant) {
            throw ValidationException::withMessages(['participant_identity' => 'NIP/NIK tidak ditemukan pada peserta yang disetujui di pelatihan ini.']);
        }

        return $participant;
    }

    public function verifyProper(Request $request, Training $training)
    {
        $participant = $this->properParticipant($request, $training);
        $interview = PostEvaluationInterview::where('training_id', $training->id)->where('participant_id', $participant->id)->first();
        $document = $interview ? DB::table('post_evaluation_evidence')->where('interview_id', $interview->id)->where('kind', 'innovation')->first() : null;

        return response()->json(['found' => true, 'name' => $participant->name, 'has_document' => (bool) $document,
            'document' => $document ? ['name' => $document->original_name, 'size' => $document->size, 'uploaded_at' => Carbon::parse($document->updated_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y H:i').' WIB'] : null])->header('Cache-Control', 'no-store');
    }

    public function submitProper(Request $request, Training $training)
    {
        return $this->storeProper($request, $this->properParticipant($request, $training));
    }

    private function storeProper(Request $request, Participant $participant)
    {
        $data = $request->validate([
            'proper_title' => 'required|string|max:2000',
            'medium_milestone' => 'required|string|max:10000',
            'medium_evidence_status' => ['required', Rule::in(['sudah', 'belum'])],
            'medium_evidence' => 'nullable|required_if:medium_evidence_status,sudah|image|mimes:jpg,jpeg,png,webp|max:10240',
            'long_milestone' => 'required|string|max:10000',
            'long_evidence_status' => ['required', Rule::in(['sudah', 'belum'])],
            'long_evidence' => 'nullable|required_if:long_evidence_status,sudah|image|mimes:jpg,jpeg,png,webp|max:10240',
            'current_condition' => 'required|string|max:10000',
            'document' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar|max:102400',
        ], [
            'medium_evidence.required_if' => 'Unggah foto evidence jangka menengah jika sudah dilaksanakan.',
            'long_evidence.required_if' => 'Unggah foto evidence jangka panjang jika sudah dilaksanakan.',
            'document.max' => 'Dokumen Proper maksimal 100 MB.',
        ]);
        $newFiles = [];
        $oldPaths = [];
        try {
            foreach (['innovation' => 'document', 'medium' => 'medium_evidence', 'long' => 'long_evidence'] as $kind => $field) {
                if ($kind !== 'innovation' && $data[$kind.'_evidence_status'] !== 'sudah') {
                    continue;
                }
                $file = $request->file($field);
                if (! $file) {
                    continue;
                }
                $path = $file->store('post-evaluation/proper/'.$participant->id, 'local');
                if (! $path) {
                    throw new \RuntimeException('Dokumen gagal disimpan.');
                }
                $newFiles[$kind] = ['path' => $path, 'original_name' => mb_substr($file->getClientOriginalName(), 0, 255), 'mime' => $file->getMimeType() ?: 'application/octet-stream', 'size' => $file->getSize()];
            }
            DB::transaction(function () use ($participant, $data, $newFiles, &$oldPaths) {
                Participant::where('training_id', $participant->training_id)->where('registration_status', 'approved')->lockForUpdate()->findOrFail($participant->id);
                $interview = PostEvaluationInterview::firstOrCreate(['training_id' => $participant->training_id, 'participant_id' => $participant->id]);
                $interview = PostEvaluationInterview::lockForUpdate()->findOrFail($interview->id);

                $interview->update(['project' => $data['proper_title'], 'medium_milestone' => $data['medium_milestone'], 'long_milestone' => $data['long_milestone'],
                    'medium_evidence_status' => $data['medium_evidence_status'], 'long_evidence_status' => $data['long_evidence_status'],
                    'current_condition' => $data['current_condition'], 'revision' => $interview->revision + 1]);
                foreach (['innovation', 'medium', 'long'] as $kind) {
                    $old = DB::table('post_evaluation_evidence')->where('interview_id', $interview->id)->where('kind', $kind)->first();
                    if ($old) {
                        $oldPaths[] = $old->path;
                    }
                    if (isset($newFiles[$kind])) {
                        DB::table('post_evaluation_evidence')->updateOrInsert(['interview_id' => $interview->id, 'kind' => $kind], array_merge($newFiles[$kind], ['created_at' => $old?->created_at ?? now(), 'updated_at' => now()]));
                    } elseif ($kind !== 'innovation' && $data[$kind.'_evidence_status'] === 'belum') {
                        DB::table('post_evaluation_evidence')->where('interview_id', $interview->id)->where('kind', $kind)->delete();
                    }
                }
            });
        } catch (\Throwable $error) {
            Storage::disk('local')->delete(array_column($newFiles, 'path'));
            throw $error;
        }
        Storage::disk('local')->delete($oldPaths);

        return back()->with('success', 'Data inovasi/Proper, target, kondisi terkini, dan dokumen berhasil disimpan.');
    }

    public function evidence(Request $request, Training $training, int $evidence, PostEvaluationService $service)
    {
        $file = DB::table('post_evaluation_evidence')->find($evidence);
        abort_unless($file, 404);
        $interview = PostEvaluationInterview::where('training_id', $training->id)->findOrFail($file->interview_id);
        $this->interviewAccess($request, $training, Participant::findOrFail($interview->participant_id), $service);
        abort_unless(Storage::disk('local')->exists($file->path), 404);

        if ($request->boolean('preview') && in_array($file->mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true)) {
            return Storage::disk('local')->response($file->path, $file->original_name, ['Content-Type' => $file->mime, 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'], 'inline');
        }

        return Storage::disk('local')->download($file->path, $file->original_name, ['Content-Type' => $file->mime, 'Cache-Control' => 'private, no-store']);
    }
}
