<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\Pengajar;
use App\Models\PengajarScheduleDocument;
use App\Models\Schedule;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PengajarController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $this->ensureAccess($user);
        $trainings = Training::whereHas('schedules', fn ($query) => $query->where('pengajar_id', $user->id))
            ->with(['schedules' => fn ($query) => $query->where('pengajar_id', $user->id)->orderBy('date')->orderBy('start_time')])
            ->orderBy('tgl_mulai')->get();
        return view('pengajar.list', compact('trainings'));
    }

    public function manage(Training $training)
    {
        $user = Auth::user();
        $this->ensureAccess($user);
        abort_unless($training->schedules()->where('pengajar_id', $user->id)->exists(), 403);
        $user->load('pengajar');
        $schedules = $training->schedules()->with('pengajarDocuments')->where('pengajar_id', $user->id)
            ->orderBy('date')->orderBy('start_time')->get();
        $this->syncExistingRequirements($user, $training);
        return view('pengajar.index', compact('user', 'schedules', 'training'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $this->ensureAccess($user);
        $data = $request->validate([
            'npwp' => 'required|string|max:50', 'nomor_rekening' => 'required|string|max:50',
            'nama_bank' => 'required|string|max:100', 'nama_rekening' => 'required|string|max:255',
        ]);
        Pengajar::updateOrCreate(['user_id' => $user->id], $data);
        return back()->with('success', 'Data administrasi pengajar berhasil disimpan.');
    }

    public function uploadRequirements(Request $request, Training $training)
    {
        $user = Auth::user();
        $this->ensureAccess($user);
        abort_unless($training->schedules()->where('pengajar_id', $user->id)->exists(), 403);
        $request->validate([
            'file_cv' => 'nullable|required_without_all:file_sertifikat,file_surat_tugas|file|mimes:pdf|max:5120',
            'file_sertifikat' => 'nullable|file|mimes:pdf|max:5120',
            'file_surat_tugas' => 'nullable|file|mimes:pdf|max:5120',
        ]);
        $pengajar = Pengajar::firstOrCreate(['user_id' => $user->id]);
        $fields = [
            'file_cv' => ['cv_path', 'CV PENGAJAR'], 'file_sertifikat' => ['sertifikat_path', 'SERTIFIKAT TOT PENGAJAR'],
            'file_surat_tugas' => ['surat_tugas_path', 'SURAT TUGAS PENGAJAR'],
        ];
        foreach ($fields as $input => [$column, $label]) {
            if (!$request->hasFile($input)) continue;
            $oldPath = $pengajar->{$column};
            $path = $request->file($input)->store('pengajar/kelengkapan', 'public');
            $pengajar->{$column} = $path;
            $this->archiveUpload($user, $training, null, $request->file($input), $path, $label);
            if ($oldPath && $oldPath !== $path) Storage::disk('public')->delete($oldPath);
        }
        $pengajar->save();
        return back()->with('success', 'Kelengkapan dokumen pengajar berhasil diperbarui.');
    }

    public function uploadSession(Request $request, Schedule $schedule)
    {
        $user = Auth::user();
        $this->ensureAccess($user);
        abort_unless((int) $schedule->pengajar_id === (int) $user->id, 403);
        $request->validate([
            'bahan_ajar' => 'nullable|required_without_all:rbpmp_rp,bukti_mengajar|file|mimes:pdf,ppt,pptx,doc,docx,xls,xlsx|max:20480',
            'rbpmp_rp' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'bukti_mengajar' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);
        $document = PengajarScheduleDocument::firstOrCreate(['schedule_id' => $schedule->id], ['user_id' => $user->id]);
        $fields = [
            'bahan_ajar' => ['bahan_ajar_path', 'BAHAN AJAR'], 'rbpmp_rp' => ['rbpmp_rp_path', 'RBPMP-RP'],
            'bukti_mengajar' => ['bukti_mengajar_path', 'BUKTI MENGAJAR'],
        ];
        foreach ($fields as $input => [$column, $label]) {
            if (!$request->hasFile($input)) continue;
            $oldPath = $document->{$column};
            $path = $request->file($input)->store('pengajar/sesi', 'public');
            $document->{$column} = $path;
            $this->archiveUpload($user, $schedule->training, $schedule, $request->file($input), $path, $label);
            if ($oldPath && $oldPath !== $path) Storage::disk('public')->delete($oldPath);
        }
        $document->user_id = $user->id;
        $document->save();
        return back()->with('success', 'Dokumen administrasi sesi berhasil diperbarui.');
    }

    private function ensureAccess($user): void
    {
        abort_unless($user?->canAccessNarasumberPortal(), 403);
    }

    private function archiveUpload($user, Training $training, ?Schedule $schedule, $uploadedFile, string $path, string $label): void
    {
        $root = Folder::firstOrCreate(['training_id' => $training->id, 'parent_id' => null],
            ['name' => $training->nama_pelatihan.' - Angkatan '.$training->angkatan, 'bidang' => $training->bidang, 'user_id' => $user->id]);
        if ($schedule) {
            $parent = Folder::firstOrCreate(['name' => 'ADMINISTRASI PENGAJAR', 'parent_id' => $root->id,
                'training_id' => $training->id, 'bidang' => $training->bidang], ['user_id' => $user->id]);
            $folder = Folder::firstOrCreate(['name' => Str::limit(Str::upper($user->name).' - '.$schedule->activity, 250, ''),
                'parent_id' => $parent->id, 'training_id' => $training->id, 'bidang' => $training->bidang], ['user_id' => $user->id]);
        } else {
            $parent = Folder::firstOrCreate(['name' => 'KELENGKAPAN NARASUMBER', 'parent_id' => $root->id,
                'training_id' => $training->id, 'bidang' => $training->bidang], ['user_id' => $user->id]);
            $folder = Folder::firstOrCreate(['name' => Str::upper($user->name), 'parent_id' => $parent->id,
                'training_id' => $training->id, 'bidang' => $training->bidang], ['user_id' => $user->id]);
        }
        File::where('folder_id', $folder->id)->where('display_name', 'like', $label.'.%')->delete();
        File::create(['folder_id' => $folder->id, 'display_name' => $label.'.'.$uploadedFile->getClientOriginalExtension(),
            'file_path' => $path, 'file_type' => $uploadedFile->getClientOriginalExtension(), 'file_size' => $uploadedFile->getSize(),
            'user_id' => $user->id]);
    }

    private function syncExistingRequirements($user, Training $training): void
    {
        $pengajar = $user->pengajar;
        if (!$pengajar) return;
        foreach (['cv_path' => 'CV PENGAJAR', 'sertifikat_path' => 'SERTIFIKAT TOT PENGAJAR', 'surat_tugas_path' => 'SURAT TUGAS PENGAJAR'] as $column => $label) {
            $path = $pengajar->{$column};
            if (!$path || !Storage::disk('public')->exists($path)) continue;
            $mock = new class($path) {
                public function __construct(private string $path) {}
                public function getClientOriginalExtension() { return pathinfo($this->path, PATHINFO_EXTENSION); }
                public function getSize() { return Storage::disk('public')->size($this->path); }
            };
            $this->archiveUpload($user, $training, null, $mock, $path, $label);
        }
    }
}
