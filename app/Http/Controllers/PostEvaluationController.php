<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\PostEvaluationInterview;
use App\Models\PostEvaluationSetting;
use App\Models\Training;
use App\Models\User;
use App\Services\PostEvaluationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PostEvaluationController extends Controller
{
    public function index(Request $request, PostEvaluationService $service)
    {
        $user = $request->user();
        abort_if($user->role === 'admin_bidang', 403);
        $trainings = Training::query()->when($user->role !== 'superadmin', function ($q) use ($user) {
            $q->where(function ($scope) use ($user) {
                if ($user->role === 'admin_bidang' && filled($user->bidang)) {
                    $scope->where('bidang', $user->bidang);
                }
                $scope->orWhereIn('id', DB::table('post_evaluation_teams')->join('post_evaluation_team_user', 'team_id', '=', 'post_evaluation_teams.id')->where('user_id', $user->id)->select('training_id'));
            });
        })->when($request->filled('q'), fn ($q) => $q->where('nama_pelatihan', 'like', '%'.mb_substr((string) $request->query('q'), 0, 100).'%'))->latest()->paginate(15)->withQueryString();

        return view('post-evaluation.index', compact('trainings'));
    }

    public function show(Request $request, Training $training, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training);
        $manager = $service->manages($request->user(), $training);
        $tab = $request->query('tab', 'ringkasan');
        abort_unless(in_array($tab, ['ringkasan', 'jawaban', 'peserta', 'wawancara', 'pengaturan'], true), 404);
        abort_if($tab === 'pengaturan' && ! $manager, 403);
        $people = $service->people($training, $request->user());
        $summary = $service->summary($training, $people);
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
        $setting = $service->setting($training);
        $rubric = $service->rubric($training);
        $questions = $manager ? $service->questions($training) : collect();
        $teams = DB::table('post_evaluation_teams')->where('training_id', $training->id)->get();
        $members = DB::table('post_evaluation_team_user')->join('users', 'users.id', '=', 'user_id')->whereIn('team_id', $teams->pluck('id'))->get(['team_id', 'users.name'])->groupBy('team_id');
        $users = $manager ? User::orderBy('name')->get(['id', 'name', 'role']) : collect();
        $evidence = DB::table('post_evaluation_evidence')->whereIn('interview_id', collect($people)->pluck('interview.id')->filter())->get()->groupBy('interview_id');

        return response()->view('post-evaluation.show', compact('training', 'manager', 'tab', 'people', 'summary', 'setting', 'rubric', 'questions', 'teams', 'members', 'users', 'evidence', 'paginator', 'answerRows', 'search'))->header('Cache-Control', 'private, no-store');
    }

    public function settings(Request $request, Training $training, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training, true);
        $data = $request->validate([
            'mappings' => ['nullable', 'array', 'max:30'], 'mappings.*.indicator' => ['required', 'integer', 'between:0,2'],
            'mappings.*.role' => ['required', Rule::in($training->evaluationRoles())],
            'mappings.*.question_id' => ['nullable', 'integer'], 'mappings.*.scale' => ['required', Rule::in(['four', 'hundred'])],
            'rubric' => ['required', 'array', 'size:5'], 'rubric.*.label' => ['required', 'string', 'max:200'],
            'rubric.*.weight' => ['required', 'integer', 'between:0,100'],
            'rubric.*.guides' => ['nullable', 'array', 'size:4'], 'rubric.*.guides.*' => ['required', 'string', 'max:1000'],
        ]);
        if (array_keys($data['rubric']) !== range(0, 4) || array_sum(array_column($data['rubric'], 'weight')) !== 100) {
            throw ValidationException::withMessages(['rubric' => 'Lima bobot harus berjumlah 100%.']);
        }
        $questions = $service->questions($training)->keyBy('id');
        $seen = [];
        $mappings = [];
        foreach ($data['mappings'] ?? [] as $mapping) {
            if (empty($mapping['question_id'])) {
                continue;
            }
            $q = $questions->get($mapping['question_id']);
            if (! $q || $q->category !== 'l34_'.$mapping['role'] || ($mapping['scale'] === 'hundred' && $q->type !== 'slider') || ($mapping['scale'] === 'four' && $q->type !== 'dropdown')) {
                throw ValidationException::withMessages(['mappings' => 'Pertanyaan harus sesuai peran, program dan skala: dropdown empat tingkat atau slider 0?100.']);
            }
            if ($mapping['scale'] === 'four') {
                $valid = ['tidak sesuai', 'kurang sesuai', 'sesuai', 'sangat sesuai', '1', '2', '3', '4'];
                $options = array_map(fn ($v) => mb_strtolower(trim($v)), $q->options ?? []);
                if (count($options) !== 4 || count(array_unique(array_map(fn ($v) => ['tidak sesuai' => '1', 'kurang sesuai' => '2', 'sesuai' => '3', 'sangat sesuai' => '4'][$v] ?? $v, $options))) !== 4 || array_diff($options, $valid)) {
                    throw ValidationException::withMessages(['mappings' => 'Skala empat hanya menerima empat pilihan Tidak Sesuai sampai Sangat Sesuai, atau angka 1?4.']);
                }
            }
            $key = $mapping['indicator'].'|'.$mapping['role'];
            if (isset($seen[$key])) {
                throw ValidationException::withMessages(['mappings' => 'Pilih satu pertanyaan per indikator dan peran.']);
            }
            $seen[$key] = true;
            $mapping['indicator'] = (int) $mapping['indicator'];
            $mapping['question_id'] = (int) $mapping['question_id'];
            $mappings[] = $mapping;
        }
        PostEvaluationSetting::updateOrCreate(['training_id' => $training->id], ['mappings' => $mappings, 'rubric' => array_values($data['rubric'])]);

        return back()->with('success', 'Pemetaan indikator dan rubrik tersimpan. Publikasikan ulang jika hasil publik perlu diperbarui.');
    }

    public function team(Request $request, Training $training, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training, true);
        $data = $request->validate(['name' => 'required|string|max:150', 'users' => 'required|array|min:1', 'users.*' => 'required|integer|distinct|exists:users,id']);
        DB::transaction(function () use ($data, $training) {
            $id = DB::table('post_evaluation_teams')->insertGetId(['training_id' => $training->id, 'name' => $data['name'], 'created_at' => now(), 'updated_at' => now()]);
            foreach ($data['users'] as $user) {
                DB::table('post_evaluation_team_user')->insert(['team_id' => $id, 'user_id' => $user]);
            }
        });

        return back()->with('success', 'Tim wawancara ditambahkan menggunakan akun Integral.');
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

        return response()->view('post-evaluation.interview', compact('training', 'participant', 'interview', 'rubric', 'evidence'))->header('Cache-Control', 'private, no-store');
    }

    public function save(Request $request, Training $training, Participant $participant, PostEvaluationService $service)
    {
        $interview = $this->interviewAccess($request, $training, $participant, $service);
        $data = $request->validate(['revision' => 'required|integer|min:0', 'scores' => 'required|array|size:5', 'scores.*' => 'nullable|numeric|between:0,100', 'notes' => 'required|array|size:5', 'notes.*' => 'nullable|string|max:10000', 'status' => ['required', Rule::in(['draft', 'completed'])], 'project' => 'nullable|string|max:2000', 'medium_milestone' => 'nullable|string|max:10000', 'long_milestone' => 'nullable|string|max:10000', 'current_condition' => 'nullable|string|max:10000']);
        if (array_keys($data['scores']) !== range(0, 4) || array_keys($data['notes']) !== range(0, 4)) {
            throw ValidationException::withMessages(['scores' => 'Urutan lima kriteria tidak valid.']);
        }
        if ($data['status'] === 'completed' && in_array(null, $data['scores'], true)) {
            throw ValidationException::withMessages(['scores' => 'Lengkapi kelima nilai sebelum menandai selesai.']);
        }
        DB::transaction(function () use ($request, $data, $interview, $training, $service) {
            $row = PostEvaluationInterview::lockForUpdate()->findOrFail($interview->id);
            abort_unless($service->manages($request->user(), $training) || $service->teamIds($request->user(), $training)->contains($row->team_id), 403);
            if ($row->revision !== (int) $data['revision']) {
                abort(409, 'Data berubah dari perangkat lain. Salin catatan Anda lalu muat ulang halaman.');
            }
            $data['scores'] = array_map(fn ($v) => $v === null ? null : (float) $v, $data['scores']);
            $row->update(array_merge($data, ['revision' => $row->revision + 1, 'updated_by' => $request->user()->id]));
        });
        if ($request->expectsJson()) {
            return response()->json(['revision' => $interview->fresh()->revision, 'message' => 'Wawancara tersimpan.']);
        }

        return back()->with('success', 'Wawancara tersimpan.');
    }

    public function uploadLink(Request $request, Training $training, Participant $participant, PostEvaluationService $service)
    {
        $service->authorize($request->user(), $training, true);
        $interview = $this->interviewAccess($request, $training, $participant, $service);
        $request->validate(['action' => ['required', Rule::in(['rotate', 'revoke'])]]);
        $interview->update(['upload_token' => $request->input('action') === 'rotate' ? Str::random(48) : null]);

        return back()->with('success', 'Tautan unggah bukti diperbarui.');
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
        $service->authorize($request->user(), $training);
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
        $pdf = $request->routeIs('post-evaluation.pdf', 'post-evaluation.public.pdf');
        if ($pdf) {
            return Pdf::loadView('post-evaluation.presentation', compact('summary', 'public', 'pdf'))->setPaper('a4', 'landscape')->download('Ekspose-Pasca-L34.pdf');
        }

        return response()->view('post-evaluation.presentation', compact('summary', 'public', 'pdf'))->header('Cache-Control', $public ? 'no-store' : 'private, no-store')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function properForm(Training $training)
    {
        $token = null;

        return $this->properResponse($training, $token);
    }

    private function properResponse(Training $training, ?string $token)
    {
        return response()->view('post-evaluation.upload', compact('token', 'training'))
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

    public function publicUpload(string $token)
    {
        $interview = PostEvaluationInterview::where('upload_token', $token)->firstOrFail();

        return $this->properResponse(Training::findOrFail($interview->training_id), $token);
    }

    public function upload(Request $request, string $token)
    {
        $interview = PostEvaluationInterview::where('upload_token', $token)->firstOrFail();
        $participant = Participant::where('training_id', $interview->training_id)->where('registration_status', 'approved')->findOrFail($interview->participant_id);

        return $this->storeProper($request, $participant, $token);
    }

    public function submitProper(Request $request, Training $training)
    {
        return $this->storeProper($request, $this->properParticipant($request, $training));
    }

    private function storeProper(Request $request, Participant $participant, ?string $token = null)
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
            DB::transaction(function () use ($participant, $data, $newFiles, $token, &$oldPaths) {
                Participant::where('training_id', $participant->training_id)->where('registration_status', 'approved')->lockForUpdate()->findOrFail($participant->id);
                $interview = PostEvaluationInterview::firstOrCreate(['training_id' => $participant->training_id, 'participant_id' => $participant->id]);
                $interview = PostEvaluationInterview::lockForUpdate()->findOrFail($interview->id);
                if ($token !== null) {
                    abort_unless($interview->upload_token && hash_equals($interview->upload_token, $token), 404);
                }
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

    public function internalUpload(Request $request, Training $training, Participant $participant, PostEvaluationService $service)
    {
        return $this->storeEvidence($request, $this->interviewAccess($request, $training, $participant, $service));
    }

    private function storeEvidence(Request $request, PostEvaluationInterview $interview)
    {
        $data = $request->validate(['kind' => ['required', Rule::in(['innovation', 'medium', 'long'])], 'file' => 'required|file|mimes:pdf|max:10240']);
        $file = $request->file('file');
        $path = $file->store('post-evaluation/'.$interview->id, 'local');
        abort_unless($path, 500);
        $old = null;
        try {
            DB::transaction(function () use ($interview, $data, $path, $file, &$old) {
                PostEvaluationInterview::lockForUpdate()->findOrFail($interview->id);
                $old = DB::table('post_evaluation_evidence')->where('interview_id', $interview->id)->where('kind', $data['kind'])->first();
                DB::table('post_evaluation_evidence')->updateOrInsert(['interview_id' => $interview->id, 'kind' => $data['kind']], ['path' => $path, 'original_name' => mb_substr($file->getClientOriginalName(), 0, 255), 'mime' => 'application/pdf', 'size' => $file->getSize(), 'created_at' => $old?->created_at ?? now(), 'updated_at' => now()]);
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }
        if ($old) {
            Storage::disk('local')->delete($old->path);
        }

        return back()->with('success', 'Dokumen bukti tersimpan.');
    }

    public function evidence(Request $request, Training $training, int $evidence, PostEvaluationService $service)
    {
        $file = DB::table('post_evaluation_evidence')->find($evidence);
        abort_unless($file, 404);
        $interview = PostEvaluationInterview::where('training_id', $training->id)->findOrFail($file->interview_id);
        $this->interviewAccess($request, $training, Participant::findOrFail($interview->participant_id), $service);
        abort_unless(Storage::disk('local')->exists($file->path), 404);

        return Storage::disk('local')->download($file->path,$file->original_name,['Content-Type' => $file->mime, 'Cache-Control' => 'private, no-store']);
    }
}
