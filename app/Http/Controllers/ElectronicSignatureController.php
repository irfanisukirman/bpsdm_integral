<?php

namespace App\Http\Controllers;

use App\Models\ElectronicSignatureAction;
use App\Models\ElectronicSignatureAttempt;
use App\Models\ElectronicSignatureDocument;
use App\Models\ElectronicSignatureRequest as SignatureRequest;
use App\Models\File as DocumentFile;
use App\Models\Folder;
use App\Models\ParticipantCertificate;
use App\Models\Training;
use App\Models\User;
use App\Services\ElectronicSignature\BsreClient;
use App\Services\ElectronicSignature\JctClient;
use App\Services\ElectronicSignature\JctSyncService;
use App\Services\ElectronicSignature\PdfLegalNoticeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use ZipArchive;

class ElectronicSignatureController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $visible = SignatureRequest::query()->where(function ($query) use ($user) {
                if ($user->role === 'superadmin') return $query;
                $query->where('created_by', $user->id)
                    ->orWhereHas('actors', fn ($actor) => $actor->where('user_id', $user->id));
                if ($user->role === 'admin_bidang' && filled($user->bidang)) $query->orWhere('bidang', $user->bidang);
            });
        $sourceCounts = (clone $visible)->selectRaw('source_type, COUNT(*) as total')->groupBy('source_type')->pluck('total', 'source_type');
        $documentStats = ElectronicSignatureDocument::query()
            ->join('electronic_signature_requests as request', 'request.id', '=', 'electronic_signature_documents.electronic_signature_request_id')
            ->whereIn('request.id', (clone $visible)->select('id'))
            ->selectRaw("request.source_type, COUNT(*) as total, SUM(electronic_signature_documents.status = 'completed') as signed")
            ->groupBy('request.source_type')->get()->keyBy('source_type');
        $requests = (clone $visible)->with('creator')->withCount(['documents', 'documents as completed_documents_count' => fn ($query) => $query->where('status', 'completed')])
            ->when(in_array($request->source, ['training_certificates','jct_certificates','other_documents'], true), fn ($query) => $query->where('source_type', $request->source))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->q, fn ($query, $term) => $query->where('title', 'like', '%'.$term.'%'))
            ->latest()->paginate(15)->withQueryString();
        $myPending = ElectronicSignatureAction::where('status', 'pending')
            ->whereHas('actor', fn ($actor) => $actor->where('user_id', $user->id))->count();
        $certificateYear = (int) now()->year;
        $certificateRows = ElectronicSignatureDocument::query()
            ->join('electronic_signature_requests as request', 'request.id', '=', 'electronic_signature_documents.electronic_signature_request_id')
            ->whereIn('request.id', (clone $visible)->select('id'))
            ->whereIn('request.source_type', ['training_certificates', 'jct_certificates', 'other_documents'])
            ->where('electronic_signature_documents.status', 'completed')
            ->whereYear('electronic_signature_documents.completed_at', $certificateYear)
            ->get(['electronic_signature_documents.completed_at', 'request.source_type']);
        $certificateChart = collect(range(1, 12))->map(function ($month) use ($certificateRows) {
            $rows = $certificateRows->filter(fn ($row) => (int) $row->completed_at->format('n') === $month);
            return [
                'month' => \Carbon\Carbon::create(null, $month)->translatedFormat('M'),
                'integral' => $rows->where('source_type', 'training_certificates')->count(),
                'jct' => $rows->where('source_type', 'jct_certificates')->count(),
                'other' => $rows->where('source_type', 'other_documents')->count(),
                'total' => $rows->count(),
            ];
        });
        $createRoute = match ($request->source) {
            'training_certificates' => 'electronic-signatures.integral.create',
            'jct_certificates' => 'electronic-signatures.jct.create',
            'other_documents' => 'electronic-signatures.documents.create',
            default => 'electronic-signatures.create',
        };
        $jctOverview = null;
        if ($request->source === 'jct_certificates') {
            $localSyncCounts = ElectronicSignatureDocument::query()
                ->whereIn('electronic_signature_request_id', (clone $visible)->where('source_type', 'jct_certificates')->select('id'))
                ->get(['jct_sync_status'])
                ->countBy(fn ($document) => $document->jct_sync_status ?: 'pending');
            $sync = app(JctSyncService::class);
            $diagnostics = $sync->diagnostics();
            try {
                $remoteTemplates = collect(app(JctClient::class)->templates())
                    ->map(fn ($item) => $this->normalizeJctTemplate((array) $item, $sync));
                $jctOverview = [
                    'api_connected' => true,
                    'template_count' => $remoteTemplates->count(),
                    'certificate_count' => $remoteTemplates->sum(fn ($item) => count($item['user_ids'])),
                    'db_configured' => $diagnostics['configured'],
                    'db_connected' => $diagnostics['connected'],
                    'message' => $diagnostics['message'],
                    'local_synced' => (int) ($localSyncCounts['synced'] ?? 0),
                    'local_failed' => (int) ($localSyncCounts['failed'] ?? 0),
                    'local_pending' => (int) (($localSyncCounts['pending'] ?? 0) + ($localSyncCounts['syncing'] ?? 0)),
                ];
            } catch (\Throwable $exception) {
                report($exception);
                $jctOverview = [
                    'api_connected' => false,
                    'template_count' => 0,
                    'certificate_count' => 0,
                    'db_configured' => $diagnostics['configured'],
                    'db_connected' => $diagnostics['connected'],
                    'message' => 'API JCT belum dapat dihubungi: '.Str::limit($exception->getMessage(), 140),
                    'local_synced' => (int) ($localSyncCounts['synced'] ?? 0),
                    'local_failed' => (int) ($localSyncCounts['failed'] ?? 0),
                    'local_pending' => (int) (($localSyncCounts['pending'] ?? 0) + ($localSyncCounts['syncing'] ?? 0)),
                ];
            }
        }
        return view('electronic-signatures.index', compact('requests', 'myPending', 'sourceCounts', 'documentStats', 'createRoute', 'certificateYear', 'certificateChart', 'jctOverview'));
    }

    public function category(Request $request, string $source)
    {
        abort_unless(in_array($source, ['training_certificates','jct_certificates','other_documents'], true), 404);
        $request->merge(['source' => $source]);
        return $this->index($request);
    }

public function create(Request $request, JctClient $jct, ?string $source = null)
    {
        $this->authorizeManager();
        $user = Auth::user();
        $users = User::where('role', 'penandatangan')->whereNotNull('nip_nik')->where('nip_nik', '!=', '')
            ->orderBy('name')->get(['id', 'name', 'nip_nik', 'jabatan', 'bidang']);
        $trainings = Training::query()
            ->when($user->role === 'admin_bidang', fn ($query) => $query->where('bidang', $user->bidang))
            ->whereHas('participantCertificates', fn ($query) => $query->whereNotNull('generated_file_path'))
            ->withCount(['participantCertificates as generated_certificates_count' => fn ($query) => $query->whereNotNull('generated_file_path')])
            ->orderByDesc('tgl_mulai')->get(['id', 'nama_pelatihan', 'bidang', 'tgl_mulai']);
        $presetSource = in_array($source, ['training_certificates','jct_certificates','other_documents'], true) ? $source : null;
        $jctConfigured = filled(config('services.jct.url'));
        $jctOptions = collect(); $jctError = null; $jctWarning = null; $jctDbConfigured = false; $jctDbConnected = false;
        if ($presetSource === 'jct_certificates' && $jctConfigured) {
            $jctSync = app(JctSyncService::class);
            try {
                $diagnostics = $jctSync->diagnostics();
                $jctDbConfigured = $diagnostics['configured'];
                $jctDbConnected = $diagnostics['connected'];
                if (!$jctDbConnected) $jctWarning = $diagnostics['message'];
                $jctOptions = collect($jct->templates())
                    ->map(fn ($item) => $this->normalizeJctTemplate((array) $item, $jctSync))
                    ->filter(fn ($item) => filled($item['activity_id']));
            } catch (\Throwable $exception) { $jctError = 'Data JCT belum dapat diambil: '.Str::limit($exception->getMessage(), 160); }
        }
        return view('electronic-signatures.create', compact('users', 'trainings', 'jctConfigured', 'presetSource', 'jctOptions', 'jctError', 'jctWarning', 'jctDbConfigured', 'jctDbConnected'));
    }

    public function createIntegral(Request $request, JctClient $jct)
    {
        $request->route()->setParameter('source', 'training_certificates');
        return $this->create($request, $jct, 'training_certificates');
    }

    public function createJct(Request $request, JctClient $jct)
    {
        return $this->create($request, $jct, 'jct_certificates');
    }

    public function createDocuments(Request $request, JctClient $jct)
    {
        return $this->create($request, $jct, 'other_documents');
    }

    public function storeIntegral(Request $request, JctClient $jct)
    {
        $request->merge(['source_type' => 'training_certificates']);
        return $this->store($request, $jct);
    }

    public function storeJct(Request $request, JctClient $jct)
    {
        $request->merge(['source_type' => 'jct_certificates']);
        return $this->store($request, $jct);
    }

public function storeDocuments(Request $request, JctClient $jct)
    {
        $request->merge(['source_type' => 'other_documents']);
        return $this->store($request, $jct);
    }

    public function jctDownloadInfo(Request $request, JctClient $jct)
    {
        $this->authorizeManager();
        $data = $request->validate([
            'id' => 'required|string|max:255',
            'activity' => 'required|string|max:255',
        ]);
        $idTemplate = $data['id'];
        $activityId = $data['activity'];
        $jctSync = app(JctSyncService::class);
        $ordering = collect($jctSync->orderingRows($idTemplate));
        $template = null;
        $pemaraf = '';
        try {
            $template = collect($jct->templates())->firstWhere('id_template', $idTemplate);
            if ($template) {
                $pemaraf = (string) ($template['pemaraf'] ?? '');
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
        if ($ordering->isEmpty()) {
            $ordering = collect(($template['ordering_num_template'] ?? []) ?: [])
                ->map(fn ($row) => ['user_id' => data_get($row, 'user_id', ''), 'versi_tandatangan' => data_get($row, 'versi_tandatangan', '')]);
        }
        if ($ordering->isEmpty() && !$jctSync->diagnostics()['connected']) {
            return response()->json(['message' => 'Jumlah sertifikat belum dapat dibaca karena database bridge JCT belum terhubung. Lengkapi konfigurasi JCT_DB_* pada server Integral.'], 422);
        }
        $perVersion = [];
        $ada = 0;
        $tidakAda = 0;
        foreach ($ordering as $row) {
            $userId = (string) ($row['user_id'] ?? '');
            $versi = trim((string) ($row['versi_tandatangan'] ?? ''));
            if ($versi === '') $versi = 'unknown';
            if (!isset($perVersion[$versi])) $perVersion[$versi] = ['versi' => $versi, 'total' => 0, 'haveDownload' => 0, 'notHaveDownload' => 0];
            $perVersion[$versi]['total']++;
            if (Storage::disk('local')->exists($this->jctCertificatePath($activityId, $userId))) {
                $ada++;
                $perVersion[$versi]['haveDownload']++;
            } else {
                $tidakAda++;
                $perVersion[$versi]['notHaveDownload']++;
            }
        }
        if (!empty($perVersion)) {
            uksort($perVersion, function ($a, $b) {
                if ($a === 'unknown' && $b === 'unknown') return 0;
                if ($a === 'unknown') return 1;
                if ($b === 'unknown') return -1;
                $ta = strtotime($a);
                $tb = strtotime($b);
                if ($ta === false && $tb === false) return strcmp($a, $b) * -1;
                if ($ta === false) return 1;
                if ($tb === false) return -1;
                return $tb <=> $ta;
            });
        }
        return response()->json([
            'total_sertif' => $ordering->count(),
            'haveDownload' => $ada,
            'notHaveDownload' => $tidakAda,
            'perVersion' => array_values($perVersion),
            'sertif' => $ordering,
            'pemaraf' => $pemaraf,
            'id_template' => $idTemplate,
        ]);
    }

    public function jctDownloadSingle(Request $request, JctClient $jct)
    {
        $this->authorizeManager();
        $data = $request->validate([
            'id_template' => 'required|string|max:255',
            'id_activity' => 'required|string|max:255',
            'user_id' => 'required|string|max:255',
            'row' => 'nullable|integer',
            'pemaraf' => 'nullable|string',
        ]);
        $rowNumber = (int) ($data['row'] ?? 1);
        $userId = $data['user_id'];
        $return = [
            'status' => true,
            'msg' => $rowNumber.'. Gagal. [USER: '.$userId.']',
            'download_status' => false,
            'update_status' => false,
        ];
        try {
            $pdf = $jct->downloadCertificate($data['id_activity'], $userId);
            Storage::disk('local')->put($this->jctCertificatePath($data['id_activity'], $userId), $pdf);
            $return['msg'] = $rowNumber.'. Berhasil download. [USER: '.$userId.']';
            $return['download_status'] = true;
            $jctSync = app(JctSyncService::class);
            if ($jctSync->isConfigured()) {
                $return['update_status'] = $jctSync->syncDownloaded($data['id_template'], $userId, blank($data['pemaraf']) ? null : $data['pemaraf']);
            }
        } catch (\Throwable $exception) {
            $return['status'] = false;
            $return['msg'] = $rowNumber.'. Gagal. [USER: '.$userId.'] '.Str::limit($exception->getMessage(), 200);
        }
        return response()->json($return);
    }

    private function jctCertificatePath(string $activityId, string $userId): string
    {
        return 'jct_certificate/'.$activityId.'/'.$userId.'.pdf';
    }

private function normalizeJctTemplate(array $item, ?JctSyncService $jctSync = null): array
    {
        $templateId = (string) ($item['id_template'] ?? $item['template_id'] ?? data_get($item, 'template.id', ''));
        $activityId = (string) ($item['activity_id'] ?? $item['id_activity'] ?? data_get($item, 'activity._id', data_get($item, 'activity.id', '')));
        $apiUserIds = collect(data_get($item, 'ordering_num_template', data_get($item, 'participants', data_get($item, 'sertif', data_get($item, 'certificates', [])))))
            ->map(fn ($row) => is_scalar($row) ? (string) $row : (string) data_get($row, 'user_id', data_get($row, 'id_user', data_get($row, 'user.id', ''))))->filter()->unique()->values()->all();
        $dbUserIds = $jctSync?->participantUserIds($templateId) ?? [];
        $userIds = collect(array_merge($dbUserIds, $apiUserIds))->filter()->unique()->values()->all();
        return [
            'template_id' => $templateId,
            'activity_id' => $activityId,
            'name' => (string) (data_get($item, 'pelatihan.full_name') ?: data_get($item, 'activity.detail.title') ?: ($item['nama_template'] ?? $item['title'] ?? 'Kegiatan JCT')),
            'user_ids' => $userIds,
            'signer' => (string) ($item['signer'] ?? ''),
            'pemaraf' => $item['pemaraf'] ?? null,
            'sign_mode' => (int) ($item['sign_mode'] ?? 1),
        ];
    }

    public function store(Request $request, JctClient $jct)
    {
        $this->authorizeManager();
        $data = $request->validate([
            'source_type' => 'required|in:other_documents,training_certificates,jct_certificates',
            'training_id' => 'required_if:source_type,training_certificates|nullable|exists:trainings,id',
            'title' => 'required_if:source_type,other_documents|nullable|string|max:255',
            'description' => 'nullable|string|max:2000', 'bidang' => 'nullable|string|max:255',
            'document_zip' => 'nullable|file|mimes:zip|max:512000',
            'documents' => 'nullable|array|max:100', 'documents.*' => 'file|mimes:pdf|max:20480',
'jct_activity_id' => 'required_if:source_type,jct_certificates|nullable|string|max:255',
            'jct_template_id' => 'required_if:source_type,jct_certificates|nullable|string|max:255',
            'jct_training_name' => 'required_if:source_type,jct_certificates|nullable|string|max:255',
            'jct_user_ids' => 'required_if:source_type,jct_certificates|nullable|string|max:50000',
            'reviewer_ids' => 'nullable|array|max:10',
            'reviewer_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'signer_id' => ['required', 'integer', 'exists:users,id', Rule::notIn($request->input('reviewer_ids', []))],
        ]);
        $training = null;
        $certificates = collect();
        $jctDocuments = collect();
        if ($data['source_type'] === 'training_certificates') {
            $training = Training::findOrFail($data['training_id']);
            $this->authorizeTraining($training);
            $certificates = ParticipantCertificate::with('participant')->where('training_id', $training->id)
                ->whereNotNull('generated_file_path')->orderBy('participant_id')->get()
                ->filter(fn ($certificate) => Storage::disk('local')->exists($certificate->generated_file_path));
            if ($certificates->isEmpty()) return back()->withInput()->withErrors(['training_id' => 'Pelatihan belum memiliki PDF sertifikat hasil generate yang tersedia.']);
} elseif ($data['source_type'] === 'jct_certificates') {
            $userIds = collect(preg_split('/[\r\n,;]+/', $data['jct_user_ids']))->map(fn ($id) => trim($id))->filter()->unique()->values();
            if ($userIds->isEmpty() || $userIds->count() > 500) return back()->withInput()->withErrors(['jct_user_ids' => 'Masukkan 1 sampai 500 User ID JCT.']);
            $missingUserIds = $userIds->filter(fn ($userId) => !Storage::disk('local')->exists($this->jctCertificatePath($data['jct_activity_id'], $userId)))->values();
            if ($missingUserIds->isNotEmpty()) return back()->withInput()->withErrors(['jct_user_ids' => 'Sertifikat JCT belum di-download ke Integral. Silakan download dulu dari JCT. (User ID belum ada: '.$missingUserIds->implode(', ').')']);
            foreach ($userIds as $userId) $jctDocuments->push(['user_id' => $userId, 'content' => Storage::disk('local')->get($this->jctCertificatePath($data['jct_activity_id'], $userId))]);
        }
        $signatureRequest = DB::transaction(function () use ($request, $data, $training, $certificates, $jctDocuments) {
            $signatureRequest = SignatureRequest::create([
                'uuid' => (string) Str::uuid(),
                'title' => $training ? 'Sertifikat - '.$training->nama_pelatihan : ($data['source_type'] === 'jct_certificates' ? 'Sertifikat JCT - '.$data['jct_training_name'] : $data['title']),
                'description' => $data['description'] ?? ($training ? 'Penandatanganan '.$certificates->count().' sertifikat peserta.' : null),
                'bidang' => $training?->bidang ?: (Auth::user()->role === 'admin_bidang' ? Auth::user()->bidang : $request->input('bidang')),
'source_type' => $data['source_type'], 'training_id' => $training?->id,
                'external_reference' => $data['source_type'] === 'jct_certificates' ? $data['jct_activity_id'] : null,
                'external_template_id' => $data['source_type'] === 'jct_certificates' ? $data['jct_template_id'] : null,
                'status' => 'in_progress', 'created_by' => Auth::id(),
            ]);
            $actors = collect($data['reviewer_ids'] ?? [])->filter()->values()
                ->map(fn ($id, $index) => $signatureRequest->actors()->create(['user_id' => $id, 'role' => 'reviewer', 'sequence' => $index + 1]));
            $actors->push($signatureRequest->actors()->create(['user_id' => $data['signer_id'], 'role' => 'signer', 'sequence' => $actors->count() + 1]));
            if ($training) {
                foreach ($certificates as $certificate) {
                    $this->createDocument($signatureRequest, $actors, $certificate->generated_file_path,
                        ($certificate->participant?->nip_nik ?: $certificate->id).'.pdf', $certificate->id);
                }
            } elseif ($data['source_type'] === 'jct_certificates') {
                foreach ($jctDocuments as $jctDocument) {
                    $path = 'electronic-signatures/'.$signatureRequest->id.'/original/'.Str::uuid().'.pdf';
                    Storage::disk('local')->put($path, $jctDocument['content']);
                    $this->createDocument($signatureRequest, $actors, $path, $jctDocument['user_id'].'.pdf', null, $jctDocument['user_id']);
                }
            } elseif ($data['source_type'] === 'other_documents') {
                foreach ($request->file('documents', []) as $file) {
                    $path = $file->storeAs('electronic-signatures/'.$signatureRequest->id.'/original', Str::uuid().'.pdf', 'local');
                    $this->createDocument($signatureRequest, $actors, $path, $file->getClientOriginalName());
                }
                if ($request->hasFile('document_zip')) $this->createDocumentsFromZip($request->file('document_zip'), $signatureRequest, $actors);
                abort_if($signatureRequest->documents()->doesntExist(), 422, 'Unggah beberapa PDF atau satu ZIP berisi PDF.');
            }
return $signatureRequest;
        });
        if ($data['source_type'] === 'jct_certificates') $this->syncJctOnStore($signatureRequest);
        return redirect()->route('electronic-signatures.show', $signatureRequest)->with('success',
            $training ? $certificates->count().' sertifikat berhasil dimasukkan ke antrean tanda tangan.' : 'Dokumen berhasil diajukan.');
    }

    private function syncJctOnStore(SignatureRequest $signatureRequest): void
    {
        $jctSync = app(JctSyncService::class);
        if (!$jctSync->isConfigured()) return;
        try {
            $templateId = $signatureRequest->external_template_id;
            $jctOption = collect(app(JctClient::class)->templates())
                ->map(fn ($item) => $this->normalizeJctTemplate((array) $item))
                ->firstWhere('template_id', $templateId);
            $templatePemaraf = $this->decodePemarafPayload((string) ($jctOption['pemaraf'] ?? ''));
            $statusPemarafJson = !empty($templatePemaraf) ? $jctOption['pemaraf'] : null;
            $jctSync->syncTemplate(
                $templateId,
                (string) ($jctOption['signer'] ?? ''),
                $templatePemaraf,
                (int) ($jctOption['sign_mode'] ?? 1)
            );
            foreach ($signatureRequest->documents()->whereNotNull('external_user_id')->get() as $document) {
                $jctSync->syncDownloaded($templateId, $document->external_user_id, $statusPemarafJson);
            }
        } catch (\Throwable $exception) { report($exception); }
    }

    private function decodePemarafPayload(string $json): array
    {
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function syncJctOnSign(ElectronicSignatureAction $action): void
    {
        $jctSync = app(JctSyncService::class);
        if (!$jctSync->isConfigured()) return;
        $templateId = $action->document->request->external_template_id;
        $userId = $action->document->external_user_id;
        if ($action->actor->role === 'reviewer') {
            $jctSync->syncPemarafSigned($templateId, $userId, null, $action->actor->sequence);
            return;
        }
        if ($action->actor->role === 'signer') {
            $jctSync->syncSignerSigned($templateId, $userId);
        }
    }

    private function createDocumentsFromZip($upload, SignatureRequest $request, $actors): void
    {
        $zip = new ZipArchive();
        abort_unless($zip->open($upload->getRealPath()) === true, 422, 'File ZIP tidak dapat dibaca.');
        $created = 0; $totalBytes = 0;
        try {
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = $zip->getNameIndex($index);
                if (!$entry || str_ends_with($entry, '/') || strtolower(pathinfo($entry, PATHINFO_EXTENSION)) !== 'pdf') continue;
                $content = $zip->getFromIndex($index);
                if ($content === false || !str_starts_with($content, '%PDF-')) continue;
                $totalBytes += strlen($content);
                abort_if(++$created > 500 || $totalBytes > 524288000, 422, 'ZIP melampaui batas 500 PDF atau 500 MB hasil ekstraksi.');
                $name = basename(str_replace('\\', '/', $entry));
                $path = 'electronic-signatures/'.$request->id.'/original/'.Str::uuid().'.pdf';
                Storage::disk('local')->put($path, $content);
                $this->createDocument($request, $actors, $path, $name);
            }
        } finally { $zip->close(); }
        abort_if($created === 0, 422, 'ZIP tidak berisi file PDF yang valid.');
    }

    private function createDocument(SignatureRequest $request, $actors, string $path, string $name, ?int $certificateId = null, ?string $externalUserId = null): void
    {
        $document = $request->documents()->create(['participant_certificate_id' => $certificateId, 'external_user_id' => $externalUserId, 'original_name' => $name,
            'original_path' => $path, 'current_path' => $path, 'file_size' => Storage::disk('local')->size($path), 'status' => 'waiting',
            'jct_sync_status' => $request->source_type === 'jct_certificates' ? 'pending' : null]);
        foreach ($actors as $index => $actor) $document->actions()->create(['electronic_signature_actor_id' => $actor->id, 'status' => $index === 0 ? 'pending' : 'waiting']);
    }

    public function show(SignatureRequest $electronicSignature)
    {
        $this->authorizeView($electronicSignature);

        // Pulihkan aksi yang gagal atau prosesnya terputus agar penandatangan dapat mencoba ulang.
        ElectronicSignatureAction::whereHas('document', fn ($query) => $query->where('electronic_signature_request_id', $electronicSignature->id))
            ->where('status', 'processing')
            ->where(function ($query) {
                $query->whereNotNull('error_message')
                    ->orWhere('updated_at', '<=', now()->subMinutes(5));
            })
            ->update(['status' => 'pending']);

        $electronicSignature->load(['creator', 'actors.user', 'actors.actions', 'documents.participantCertificate.participant', 'documents.internshipParticipant', 'documents.actions.actor.user', 'documents.actions.attempts']);
        return view('electronic-signatures.show', ['signatureRequest' => $electronicSignature]);
    }

    public function verify(string $token)
    {
        $document = ElectronicSignatureDocument::where('verification_token', $token)
            ->with(['request', 'participantCertificate.participant', 'actions.actor.user'])->firstOrFail();
        return view('electronic-signatures.verify', compact('document'));
    }

    public function verifyDownload(string $token)
    {
        $document = ElectronicSignatureDocument::where('verification_token', $token)->firstOrFail();
        abort_unless($document->current_path && Storage::disk('local')->exists($document->current_path), 404, 'File dokumen tidak tersedia.');

        $baseName = pathinfo($document->original_name ?: 'dokumen', PATHINFO_FILENAME);
        $fileName = Str::slug($baseName, '-').($document->status === 'completed' ? '.signed' : '').'.pdf';

        return Storage::disk('local')->download($document->current_path, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }
    public function sign(Request $request, ElectronicSignatureAction $action, BsreClient $bsre, JctClient $jct, PdfLegalNoticeService $legalNotice)
    {
        $action->load(['actor.user', 'document.request', 'document.participantCertificate', 'document.internshipParticipant']);
        $this->authorizeAction($action);
        $request->validate(['passphrase' => 'required|string|min:1|max:255']);
        abort_unless(ElectronicSignatureAction::whereKey($action->id)->where('status', 'pending')->update(['status' => 'processing', 'error_message' => null]), 409, 'Dokumen sedang diproses atau giliran sudah berubah.');
        $start = microtime(true);
        $stampedPath = null;
        try {
            $pdfPath = Storage::disk('local')->path($action->document->current_path);
            if ($action->actor->role === 'signer') {
                $stampedPath = $legalNotice->stamp($pdfPath, route('electronic-signatures.verify', $action->document->verification_token));
                $pdfPath = $stampedPath;
            }
            $appearance = $action->actor->role === 'signer' ? $legalNotice->signatureAppearance($pdfPath) : [];
            $pdf = $action->actor->role === 'signer'
                ? $bsre->signVisible($pdfPath, (string) $action->actor->user->nip_nik, $request->string('passphrase')->toString(), array_merge($appearance, [
                    'image' => 'false', 'linkQR' => route('electronic-signatures.verify', $action->document->verification_token),
                ]))
                : $bsre->signInvisible($pdfPath, (string) $action->actor->user->nip_nik, $request->string('passphrase')->toString());
            $signatureCount = $action->document->actions()->where('status', 'completed')->count() + 1;
            $signedFileName = $this->signedFileName($action->document, $signatureCount);
            $newPath = 'electronic-signatures/'.$action->document->electronic_signature_request_id.'/signed/'.$action->document->id.'/'.$signedFileName;
            Storage::disk('local')->put($newPath, $pdf);
            $hasNext = $action->document->actions()->where('status', 'waiting')->exists();
            DB::transaction(function () use ($action, $newPath) {
                $action->update(['status' => 'completed', 'signed_at' => now(), 'error_message' => null]);
                $document = $action->document; $document->update(['current_path' => $newPath]);
                $next = $document->actions()->where('status', 'waiting')->join('electronic_signature_actors', 'electronic_signature_actors.id', '=', 'electronic_signature_actions.electronic_signature_actor_id')
                    ->orderBy('electronic_signature_actors.sequence')->select('electronic_signature_actions.*')->first();
                if ($next) $next->update(['status' => 'pending']);
                else {
                    $document->update(['status' => 'completed', 'final_path' => $newPath, 'completed_at' => now()]);
                    if ($document->participantCertificate) $document->participantCertificate->update(['final_file_path' => $newPath, 'uploaded_at' => now(), 'sent_at' => null, 'sent_by' => null, 'downloaded_at' => null, 'uploaded_by' => Auth::id()]);
                    if ($document->internshipParticipant) $document->internshipParticipant->update(['certificate_file_path' => $newPath, 'certificate_sent_at' => null, 'certificate_sent_by' => null, 'certificate_downloaded_at' => null]);
                }
                if (!$document->request->documents()->where('status', '!=', 'completed')->exists()) $document->request->update(['status' => 'completed', 'completed_at' => now()]);
            });
            if (!$hasNext && $action->document->request->source_type === 'training_certificates') {
                try { $this->archiveIntegralDocument($action->document->fresh('request'), $newPath); }
                catch (\Throwable $archiveException) { report($archiveException); }
            }
            $jctSyncSucceeded = null;
            if ($action->document->request->source_type === 'jct_certificates') {
                if (!$hasNext) {
                    $jctSyncSucceeded = $this->syncFinalJctDocument($action->document->fresh('request'), $newPath, $jct);
                } else {
                    try { $this->syncJctOnSign($action); }
                    catch (\Throwable $syncException) { report($syncException); }
                }
            }
            ElectronicSignatureAttempt::create(['electronic_signature_action_id' => $action->id, 'user_id' => Auth::id(), 'successful' => true, 'response_code' => 'PDF', 'message' => 'Tanda tangan berhasil', 'duration_ms' => (int) ((microtime(true)-$start)*1000), 'ip_address' => $request->ip()]);
            $message = 'Dokumen berhasil ditandatangani melalui BSrE.';
            if ($jctSyncSucceeded === true) $message .= ' Sertifikat berhasil dikirim dan status JCT telah diperbarui.';
            if ($jctSyncSucceeded === false) $message .= ' TTE lokal tersimpan, tetapi sinkronisasi JCT gagal dan dapat dikirim ulang oleh pengelola.';
            if ($request->expectsJson()) return response()->json(['ok' => true, 'message' => $message, 'document_id' => $action->document->id]);
            return back()->with('success', $message);
        } catch (\Throwable $exception) {
            $action->update(['status' => 'pending', 'error_message' => Str::limit($exception->getMessage(), 1000)]);
            ElectronicSignatureAttempt::create(['electronic_signature_action_id' => $action->id, 'user_id' => Auth::id(), 'successful' => false, 'response_code' => (string) $exception->getCode(), 'message' => Str::limit($exception->getMessage(), 1000), 'duration_ms' => (int) ((microtime(true)-$start)*1000), 'ip_address' => $request->ip()]);
            if ($request->expectsJson()) return response()->json(['ok' => false, 'message' => $exception->getMessage(), 'document_id' => $action->document->id], 422);
            return back()->withErrors(['passphrase' => $exception->getMessage()]);
        } finally {
            // Pengamanan tambahan: jangan biarkan aksi terkunci jika proses gagal di tengah jalan.
            ElectronicSignatureAction::whereKey($action->id)
                ->where('status', 'processing')
                ->update(['status' => 'pending']);
            if ($stampedPath && is_file($stampedPath)) @unlink($stampedPath);
            $request->request->remove('passphrase');
        }
    }

    public function download(ElectronicSignatureDocument $document)
    {
        $this->authorizeView($document->request); $path = $document->final_path ?: $document->current_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        $signatureCount = $document->actions()->where('status', 'completed')->count();
        $downloadName = $signatureCount > 0 ? $this->signedFileName($document, $signatureCount) : $document->original_name;
        return Storage::disk('local')->download($path, $downloadName);
    }

    public function downloadZip(SignatureRequest $electronicSignature)
    {
        $this->authorizeView($electronicSignature);
        $documents = $electronicSignature->documents()->where('status', 'completed')->whereNotNull('final_path')->with('actions')->get()
            ->filter(fn ($document) => Storage::disk('local')->exists($document->final_path));
        abort_if($documents->isEmpty(), 422, 'Belum ada dokumen yang selesai ditandatangani untuk diunduh.');

        $temporaryPath = tempnam(sys_get_temp_dir(), 'integral-tte-');
        $zip = new ZipArchive();
        abort_unless($temporaryPath && $zip->open($temporaryPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'Bundel ZIP tidak dapat dibuat.');
        foreach ($documents as $document) {
            $signatureCount = $document->actions->where('status', 'completed')->count();
            $name = $this->signedFileName($document, $signatureCount);
            if ($zip->locateName($name) !== false) $name = $document->id.'-'.$name;
            $zip->addFile(Storage::disk('local')->path($document->final_path), $name);
        }
        $zip->close();

        $safeTitle = trim((string) preg_replace('/[^\pL\pN._-]+/u', '-', $electronicSignature->title), '-');
        return response()->download($temporaryPath, ($safeTitle ?: 'bundel-tte').'.zip')->deleteFileAfterSend(true);
    }

    private function archiveIntegralDocument(ElectronicSignatureDocument $document, string $signedPath): void
    {
        $request = $document->request;
        if ($request->source_type !== 'training_certificates' || !$request->training_id || !Storage::disk('local')->exists($signedPath)) return;
        $training = Training::find($request->training_id);
        if (!$training) return;

        $root = Folder::firstOrCreate(
            ['training_id' => $training->id, 'parent_id' => null],
            ['name' => $training->nama_pelatihan.' - Angkatan '.$training->angkatan, 'bidang' => $training->bidang, 'user_id' => $request->created_by, 'is_public' => false]
        );
        $folder = Folder::firstOrCreate(
            ['training_id' => $training->id, 'parent_id' => $root->id, 'name' => 'TANDA TANGAN ELEKTRONIK'],
            ['bidang' => $training->bidang, 'user_id' => $request->created_by, 'is_public' => false]
        );
        $signatureCount = $document->actions()->where('status', 'completed')->count();
        $fileName = $this->signedFileName($document, $signatureCount);
        $publicPath = 'documents/training-'.$training->id.'/electronic-signatures/'.$request->id.'/'.$document->id.'-'.$fileName;
        Storage::disk('public')->put($publicPath, Storage::disk('local')->get($signedPath));
        DocumentFile::updateOrCreate(
            ['folder_id' => $folder->id, 'file_path' => $publicPath],
            ['display_name' => $fileName, 'file_type' => 'pdf', 'file_size' => Storage::disk('public')->size($publicPath), 'user_id' => $request->created_by]
        );
    }
    public function destroyRequest(SignatureRequest $electronicSignature)
    {
        $electronicSignature->load(['documents.participantCertificate','documents.internshipParticipant']);
        $user = Auth::user();
        abort_unless($user->role === 'superadmin' || ($user->role === 'admin_bidang' && $user->bidang === $electronicSignature->bidang), 403);

        $route = $this->categoryRoute($electronicSignature->source_type);
        DB::transaction(function () use ($electronicSignature) {
            foreach ($electronicSignature->documents as $document) {
                if ($document->participantCertificate && $document->participantCertificate->final_file_path === $document->final_path) {
                    $document->participantCertificate->update(['final_file_path' => null, 'uploaded_at' => null, 'downloaded_at' => null, 'uploaded_by' => null]);
                }
                if ($document->internshipParticipant && $document->internshipParticipant->certificate_file_path === $document->final_path) {
                    $document->internshipParticipant->update(['certificate_file_path' => null, 'certificate_sent_at' => null, 'certificate_sent_by' => null, 'certificate_downloaded_at' => null]);
                }
                collect([$document->current_path, $document->final_path, $document->original_path])
                    ->filter(fn ($path) => filled($path) && str_starts_with($path, 'electronic-signatures/'))
                    ->unique()->each(fn ($path) => Storage::disk('local')->delete($path));
            }
            $electronicSignature->delete();
        });

        return redirect()->route($route)->with('success', 'Pengajuan TTE beserta seluruh berkasnya berhasil dihapus.');
    }

    public function retryJctSync(ElectronicSignatureDocument $document, JctClient $jct)
    {
        $document->load('request');
        abort_unless($document->request?->source_type === 'jct_certificates', 404);
        $user = Auth::user();
        abort_unless($user->role === 'superadmin' || ($user->role === 'admin_bidang' && $user->bidang === $document->request->bidang), 403);
        abort_unless($document->status === 'completed' && filled($document->final_path) && Storage::disk('local')->exists($document->final_path), 422, 'PDF final belum tersedia untuk dikirim ke JCT.');

        $success = $this->syncFinalJctDocument($document, $document->final_path, $jct);
        return back()->with($success ? 'success' : 'error', $success
            ? 'Sertifikat berhasil dikirim ulang dan status JCT telah menjadi true.'
            : 'Sinkronisasi JCT masih gagal. Periksa keterangan error dan konfigurasi koneksi JCT.');
    }

    private function syncFinalJctDocument(ElectronicSignatureDocument $document, string $signedPath, JctClient $jct): bool
    {
        $document->update([
            'jct_sync_status' => 'syncing', 'jct_sync_error' => null,
            'jct_sync_attempts' => ((int) $document->jct_sync_attempts) + 1,
        ]);
        try {
            $signatureRequest = $document->request;
            if (!$signatureRequest?->external_reference || !$signatureRequest?->external_template_id || !$document->external_user_id) {
                throw new \RuntimeException('Referensi activity, template, atau user JCT tidak lengkap.');
            }
            $jct->uploadFinalCertificate($signatureRequest->external_reference, $document->external_user_id, Storage::disk('local')->path($signedPath));
            $jctSync = app(JctSyncService::class);
            if (!$jctSync->isConfigured()) throw new \RuntimeException('Database bridge JCT belum dikonfigurasi.');

            $reviewerActions = $document->actions()->with('actor')->where('status', 'completed')
                ->whereHas('actor', fn ($query) => $query->where('role', 'reviewer'))->get();
            foreach ($reviewerActions as $reviewerAction) {
                if (!$jctSync->syncPemarafSigned($signatureRequest->external_template_id, $document->external_user_id, null, $reviewerAction->actor->sequence)) {
                    throw new \RuntimeException('Status pemaraf JCT gagal diperbarui pada urutan '.$reviewerAction->actor->sequence.'.');
                }
            }
            if (!$jctSync->syncSignerSigned($signatureRequest->external_template_id, $document->external_user_id)) {
                throw new \RuntimeException("Kolom status_proses_ttde JCT gagal diperbarui menjadi 'true'.");
            }
            $document->update(['jct_sync_status' => 'synced', 'jct_synced_at' => now(), 'jct_sync_error' => null]);
            return true;
        } catch (\Throwable $exception) {
            report($exception);
            $document->update(['jct_sync_status' => 'failed', 'jct_synced_at' => null, 'jct_sync_error' => Str::limit($exception->getMessage(), 2000)]);
            return false;
        }
    }
    private function signedFileName(ElectronicSignatureDocument $document, int $signatureCount): string
    {
        $baseName = pathinfo($document->original_name, PATHINFO_FILENAME);
        $baseName = trim((string) preg_replace('/[^\pL\pN ._-]+/u', '_', $baseName), " ._-");
        $baseName = $baseName !== '' ? $baseName : 'dokumen-'.$document->id;
        $suffix = implode('_', array_fill(0, max(1, $signatureCount), 'sign'));

        return $baseName.'.'.$suffix.'.pdf';
    }
    private function categoryRoute(string $source): string
    {
        return ['training_certificates' => 'electronic-signatures.integral', 'jct_certificates' => 'electronic-signatures.jct', 'other_documents' => 'electronic-signatures.documents'][$source] ?? 'electronic-signatures.index';
    }

    public function accounts(Request $request)
    {
        abort_unless(Auth::user()->role === 'superadmin', 403); $search = trim((string) $request->query('q'));
        $accounts = User::where('role', 'penandatangan')->when($search, fn ($query) => $query->where(fn ($scope) => $scope->where('name', 'like', '%'.$search.'%')->orWhere('nip_nik', 'like', '%'.$search.'%')->orWhere('jabatan', 'like', '%'.$search.'%')))
            ->withCount('electronicSignatureActors')->orderBy('name')->paginate(15)->withQueryString();
        return view('electronic-signatures.accounts', compact('accounts', 'search'));
    }

    public function storeAccount(Request $request)
    {
        abort_unless(Auth::user()->role === 'superadmin', 403);
        $data = $request->validate(['name' => 'required|string|max:255', 'nip_nik' => ['required','string','max:50','unique:users,nip_nik','unique:users,username'], 'jabatan' => 'nullable|string|max:255', 'whatsapp' => 'nullable|string|max:30', 'password' => 'required|string|min:8|confirmed']);
        $identity = preg_replace('/\s+/', '', trim($data['nip_nik']));
        User::create(['name' => $data['name'], 'username' => $identity, 'nip_nik' => $identity, 'jabatan' => $data['jabatan'] ?? null, 'whatsapp' => $data['whatsapp'] ?? null, 'role' => 'penandatangan', 'user_type_status' => 'approved', 'password' => Hash::make($data['password'])]);
        return back()->with('success', 'Akun penandatangan berhasil dibuat. Username login: '.$identity);
    }

    public function updateAccountPassword(Request $request, User $user)
    {
        abort_unless(Auth::user()->role === 'superadmin' && $user->role === 'penandatangan', 403);
        $data = $request->validate(['password' => 'required|string|min:8|confirmed']); $user->update(['password' => Hash::make($data['password'])]);
        return back()->with('success', 'Password login '.$user->name.' berhasil diperbarui.');
    }

    public function destroyAccount(User $user)
    {
        abort_unless(Auth::user()->role === 'superadmin' && $user->role === 'penandatangan', 403);
        if ($user->electronicSignatureActors()->exists()) return back()->with('error', 'Akun sudah memiliki riwayat tanda tangan dan tidak dapat dihapus.');
        $user->delete(); return back()->with('success', 'Akun penandatangan berhasil dihapus.');
    }

    private function authorizeManager(): void { abort_unless(in_array(Auth::user()->role, ['superadmin', 'admin_bidang'], true), 403); }
    private function authorizeTraining(Training $training): void { $user = Auth::user(); abort_unless($user->role === 'superadmin' || ($user->role === 'admin_bidang' && $user->bidang === $training->bidang), 403); }
    private function authorizeView(SignatureRequest $request): void { $user = Auth::user(); abort_unless($user->role === 'superadmin' || $request->created_by === $user->id || $request->actors()->where('user_id', $user->id)->exists() || ($user->role === 'admin_bidang' && filled($user->bidang) && $request->bidang === $user->bidang), 403); }
    private function authorizeAction(ElectronicSignatureAction $action): void { abort_unless($action->actor->user_id === Auth::id() && $action->status === 'pending', 403, 'Belum menjadi giliran Anda.'); abort_if(blank($action->actor->user->nip_nik), 422, 'NIK/NIP penandatangan belum tersedia.'); }
}
