<?php

namespace App\Http\Controllers;

use App\Exports\LevelOneImportedRecapExport;
use App\Models\LevelOneRecapImport;
use App\Models\Training;
use App\Models\User;
use App\Services\LevelOneRecapReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class LevelOneRecapImportController extends Controller
{
    public function index(Request $request, Training $training)
    {
        $this->authorizeTraining($request, $training);
        $imports = LevelOneRecapImport::where('training_id', $training->id)->latest('id')->paginate(10)->withQueryString();
        $token = $request->query('preview');
        $preview = $token ? $this->getPreview($request, $training, $token) : null;
        $identities = $preview ? $this->identities($training, $preview['kind']) : collect();
        $names = $identities->groupBy(fn ($identity) => Str::lower(trim($identity->name)));

        return response()->view('evaluasi.l1_recap_import', compact('training', 'imports', 'token', 'preview', 'identities', 'names'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function preview(Request $request, Training $training, LevelOneRecapReader $reader)
    {
        $this->authorizeTraining($request, $training);
        $this->requireParticipants($training);
        $file = $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240']])['file'];
        $data = $reader->read($file->getRealPath());
        $data['filename'] = Str::limit($file->getClientOriginalName(), 255, '');
        if (LevelOneRecapImport::where('training_id', $training->id)->where('fingerprint', $data['fingerprint'])->exists()) {
            return to_route('evall1.recap-import.index', $training)->with('success', 'Data ini sudah pernah diimpor.');
        }

        $token = (string) Str::uuid();
        Cache::put($this->cacheKey($request, $training, $token), $data, now()->addMinutes(30));

        return to_route('evall1.recap-import.index', [$training, 'preview' => $token]);
    }

    public function store(Request $request, Training $training, string $token)
    {
        $this->authorizeTraining($request, $training);
        $data = $this->getPreview($request, $training, $token);
        $mapping = $request->validate(['mapping' => ['nullable', 'array'], 'mapping.*' => ['nullable', 'string', 'max:255']])['mapping'] ?? [];

        $created = DB::transaction(function () use ($request, $training, $data, $mapping) {
            Training::whereKey($training->id)->lockForUpdate()->firstOrFail();
            $this->requireParticipants($training);
            $identities = $this->identities($training, $data['kind'])->keyBy('id');
            $usedParticipants = [];
            foreach ($data['entries'] as &$entry) {
                $selected = trim((string) ($mapping[$entry['row']] ?? ''));
                if ($selected === '') continue;
                if (!preg_match('/^(\d+)(?: \| .*)?$/uD', $selected, $matches) || !$identities->has((int) $matches[1])) {
                    throw ValidationException::withMessages(['mapping.'.$entry['row'] => 'Pilih nama dari daftar yang tersedia.']);
                }
                $identity = $identities[(int) $matches[1]];
                if ($data['kind'] === 'penyelenggara' && isset($usedParticipants[$identity->id])) {
                    throw ValidationException::withMessages(['mapping.'.$entry['row'] => 'Peserta ini sudah dicocokkan ke baris lain.']);
                }
                $usedParticipants[$identity->id] = true;
                $entry['linked_id'] = $identity->id;
                $entry['linked_name'] = $identity->name;
            }
            unset($entry);

            return LevelOneRecapImport::firstOrCreate([
                'training_id' => $training->id, 'fingerprint' => $data['fingerprint'],
            ], $data + ['imported_by' => $request->user()->id])->wasRecentlyCreated;
        });

        Cache::forget($this->cacheKey($request, $training, $token));

        return to_route('evall1.recap-import.index', $training)
            ->with('success', $created ? 'Rekap lama berhasil diimpor.' : 'Data ini sudah pernah diimpor.');
    }

    public function download(Request $request, Training $training, LevelOneRecapImport $import)
    {
        $this->authorizeTraining($request, $training);
        abort_unless($import->training_id === $training->id, 404);

        return Excel::download(new LevelOneImportedRecapExport($import), "rekap-lama-{$import->kind}-{$import->id}.xlsx",
            \Maatwebsite\Excel\Excel::XLSX, ['Cache-Control' => 'private, no-store']);
    }

    private function identities(Training $training, string $kind)
    {
        return $kind === 'penyelenggara'
            ? $training->participants()->orderBy('name')->get(['id', 'name', 'nip_nik'])
            : User::withRoles(['pengajar'])->orderBy('name')->get(['id', 'name', 'nip_nik']);
    }

    private function authorizeTraining(Request $request, Training $training): void
    {
        $user = $request->user();
        abort_unless($user->role === 'superadmin' || ($user->role === 'admin_bidang' && $user->bidang === $training->bidang), 403);
    }

    private function requireParticipants(Training $training): void
    {
        if (!$training->participants()->exists()) {
            throw ValidationException::withMessages(['file' => 'Impor data peserta ke pelatihan ini sebelum mengimpor rekap evaluasi.']);
        }
    }

    private function getPreview(Request $request, Training $training, mixed $token): array
    {
        abort_unless(is_string($token) && Str::isUuid($token), 404);
        $data = Cache::get($this->cacheKey($request, $training, $token));
        abort_unless(is_array($data), 404, 'Pratinjau telah kedaluwarsa. Unggah kembali file Excel.');

        return $data;
    }

    private function cacheKey(Request $request, Training $training, string $token): string
    {
        return 'l1-recap-preview:'.$request->user()->id.':'.$training->id.':'.$token;
    }
}
