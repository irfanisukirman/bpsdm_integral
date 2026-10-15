<?php
namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\QualityDocumentRecord;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QualityManagementController extends Controller
{
    public static function catalog(): array
    {
        return [
            'persiapan' => ['label' => 'Dokumen Persiapan', 'icon' => 'bx-clipboard', 'items' => [
                'panduan-kak'=>'Panduan/KAK','kurikulum'=>'Kurikulum','undangan-rapat-persiapan'=>'Undangan/Nota Dinas Rapat Persiapan','notulen-rapat-persiapan'=>'Notulen Rapat Persiapan','surat-pemanggilan-peserta'=>'Surat Pemanggilan Peserta','sk-tim-pengajar'=>'SK Penetapan Tim dan Pengajar','surat-penetapan-peserta'=>'Surat Penetapan Peserta',
            ]],
            'pelaksanaan' => ['label' => 'Dokumen Pelaksanaan', 'icon' => 'bx-calendar-check', 'items' => [
                'sk-penetapan-peserta'=>'SK Penetapan Peserta','sk-penetapan-hasil'=>'SK Penetapan Hasil','permohonan-narasumber'=>'Surat Permohonan Narasumber/Pengajar','nota-dinas-wi'=>'Nota Dinas Permohonan WI','surat-tugas-pengajar'=>'Surat Tugas Pengajar','undangan-pembukaan'=>'Surat Undangan Pembukaan','nota-undangan-pembukaan'=>'Nota Dinas Undangan Pembukaan','jadwal'=>'Jadwal','laporan-pembukaan'=>'Laporan Penyelenggaraan untuk Pembukaan','sambutan-pembukaan'=>'Sambutan Ka.BPSDM pd Pembukaan','daftar-hadir-peserta'=>'Daftar Hadir Peserta','biodata-pengajar'=>'Biodata Pengajar/Narasumber','biodata-peserta'=>'Biodata Peserta','soal-pre-post-test'=>'Soal Pre Test - Post Test','sambutan-penutupan'=>'Sambutan Ka.BPSDM pd Penutupan','laporan-penutupan'=>'Laporan Penyelenggaraan untuk Penutupan','bahan-materi'=>'Bahan Materi Pelatihan','surat-tugas-peserta'=>'Surat Tugas Peserta','nominatif'=>'Nominatif','undangan-penutupan'=>'Surat Undangan Penutupan','nota-undangan-penutupan'=>'Nota Dinas Undangan Penutupan',
            ]],
            'pasca' => ['label' => 'Dokumen Pasca Pelatihan', 'icon' => 'bx-check-shield', 'items' => [
                'evaluasi-peserta'=>'Evaluasi Peserta','evaluasi-pengajar'=>'Evaluasi WI/Narsum/Fasilitator','evaluasi-penyelenggara'=>'Evaluasi Penyelenggara','hasil-pre-post-test'=>'Hasil Pretes & Posttest','rekap-nilai'=>'Rekapitulasi Nilai Peserta','sertifikat-sttp'=>'Sertifikat/STTP','pengembalian-peserta'=>'Surat Pengembalian Peserta','ucapan-terima-kasih'=>'Surat Ucapan terima kasih','dokumentasi'=>'Dokumentasi/Foto Kegiatan','laporan-hasil'=>'Laporan Hasil','tugas-akhir'=>'Tugas Akhir',
            ]],
            'skpk' => ['label' => 'Dokumen SKPK', 'icon' => 'bx-search-alt', 'items' => [
                'instrumen-monev'=>'Instrumen Monev','laporan-monev'=>'Laporan Monev','tindak-lanjut-monev'=>'Tindak Lanjut Monev',
            ]],
        ];
    }

    public function index(Request $request)
    {
        $this->authorizeRole();
        $query = Training::query()->withCount([
            'qualityDocuments as quality_completed_count' => fn($q) => $q->where('is_required', true)->whereNotNull('file_id'),
            'qualityDocuments as quality_not_required_count' => fn($q) => $q->where('is_required', false),
        ]);
        $this->scopeTrainings($query);
        $query->when($request->filled('bidang'), fn($q) => $q->where('bidang', $request->bidang))
            ->when($request->filled('search'), fn($q) => $q->where('nama_pelatihan', 'like', '%'.$request->search.'%'));
        $trainings = $query->orderByDesc('tgl_mulai')->paginate(12)->withQueryString();
        foreach ($trainings as $training) $this->syncExistingDocuments($training);
        $trainings->loadCount([
            'qualityDocuments as quality_completed_count' => fn($q) => $q->where('is_required', true)->whereNotNull('file_id'),
            'qualityDocuments as quality_not_required_count' => fn($q) => $q->where('is_required', false),
        ]);
        $bidangs = Training::query()->tap(fn($q) => $this->scopeTrainings($q))->whereNotNull('bidang')->distinct()->orderBy('bidang')->pluck('bidang');
        $totalRequirements = collect(self::catalog())->sum(fn($group) => count($group['items']));
        return view('quality-management.index', compact('trainings','bidangs','totalRequirements'));
    }

    public function show(Training $training)
    {
        $this->authorizeTraining($training);
        $this->syncExistingDocuments($training);
        $records = $training->qualityDocuments()->with(['file','uploader'])->get()->keyBy('requirement_key');
        $catalog = self::catalog();
        $catalogTotal = collect($catalog)->sum(fn($group) => count($group['items']));
        $notRequired = $records->where('is_required', false)->count();
        $total = $catalogTotal - $notRequired;
        $completed = $records->filter(fn($record) => $record->is_required && $record->file_id)->count();
        return view('quality-management.show', compact('training','records','catalog','total','completed','catalogTotal','notRequired'));
    }

    public function upload(Request $request, Training $training, string $key)
    {
        $this->authorizeTraining($training);
        abort_unless($this->requirementLabel($key), 404);
        $data = $request->validate(['document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip|max:20480']);
        $folder = $this->qualityFolder($training);
        $upload = $data['document'];
        $path = $upload->store('documents/quality-management/'.$training->id, 'public');
        $file = File::create(['folder_id'=>$folder->id,'display_name'=>$upload->getClientOriginalName(),'file_path'=>$path,'file_type'=>strtolower($upload->getClientOriginalExtension()),'file_size'=>$upload->getSize(),'user_id'=>Auth::id()]);
        QualityDocumentRecord::updateOrCreate(['training_id'=>$training->id,'requirement_key'=>$key],['file_id'=>$file->id,'source'=>'uploaded','uploaded_by'=>Auth::id()]);
        return back()->with('success', $this->requirementLabel($key).' berhasil diunggah dan progres telah diperbarui.');
    }

    public function updateRequirement(Request $request, Training $training, string $key)
    {
        $this->authorizeTraining($training);
        abort_unless($this->requirementLabel($key), 404);
        $data = $request->validate(['status' => 'required|in:required,not_applicable']);
        $isRequired = $data['status'] === 'required';

        QualityDocumentRecord::updateOrCreate(
            ['training_id' => $training->id, 'requirement_key' => $key],
            ['is_required' => $isRequired, 'requirement_updated_by' => Auth::id(), 'requirement_updated_at' => now()]
        );

        return back()->with('success', $this->requirementLabel($key).' ditetapkan sebagai '.($isRequired ? 'Wajib.' : 'Tidak Berlaku (N/A).'));
    }

    public function view(Training $training, QualityDocumentRecord $record)
    {
        $file = $this->authorizedRecordFile($training, $record);
        abort_unless(Storage::disk('public')->exists($file->file_path), 404);
        return response()->file(Storage::disk('public')->path($file->file_path));
    }

    public function download(Training $training, QualityDocumentRecord $record)
    {
        $file = $this->authorizedRecordFile($training, $record);
        abort_unless(Storage::disk('public')->exists($file->file_path), 404);
        return Storage::disk('public')->download($file->file_path, $file->display_name);
    }

    private function authorizedRecordFile(Training $training, QualityDocumentRecord $record): File
    {
        $this->authorizeTraining($training);
        abort_unless((int) $record->training_id === (int) $training->id && $record->file_id, 404);
        return $record->file()->firstOrFail();
    }

    private function authorizeRole(): void
    {
        abort_unless(in_array(Auth::user()->role, ['superadmin','admin_bidang','manajemen_mutu'], true), 403);
    }
    private function authorizeTraining(Training $training): void
    {
        $this->authorizeRole();
        if (Auth::user()->role === 'admin_bidang') abort_unless(Auth::user()->bidang === $training->bidang, 403, 'Pelatihan ini dikelola bidang lain.');
    }
    private function scopeTrainings($query): void
    {
        if (Auth::user()->role === 'admin_bidang') $query->where('bidang', Auth::user()->bidang);
    }
    private function requirementLabel(string $key): ?string
    {
        foreach (self::catalog() as $group) if (isset($group['items'][$key])) return $group['items'][$key];
        return null;
    }
    private function qualityFolder(Training $training): Folder
    {
        $root = Folder::firstOrCreate(['training_id'=>$training->id,'parent_id'=>null],['name'=>$training->nama_pelatihan,'bidang'=>$training->bidang ?: 'Semua Bidang','user_id'=>$training->created_by ?: Auth::id()]);
        return Folder::firstOrCreate(['parent_id'=>$root->id,'name'=>'Manajemen Mutu'],['bidang'=>$training->bidang ?: 'Semua Bidang','user_id'=>$root->user_id]);
    }
    private function syncExistingDocuments(Training $training): void
    {
        $roots = Folder::where('training_id', $training->id)->pluck('id')->all();
        if (!$roots) return;
        $ids = $roots; $frontier = $roots;
        while ($frontier) { $frontier = Folder::whereIn('parent_id', $frontier)->pluck('id')->all(); $ids = array_merge($ids, $frontier); }
        $files = File::whereIn('folder_id', array_unique($ids))->latest()->get();
        if ($files->isEmpty()) return;
        foreach (self::catalog() as $group) foreach ($group['items'] as $key=>$label) {
            if (QualityDocumentRecord::where('training_id',$training->id)->where('requirement_key',$key)->whereNotNull('file_id')->exists()) continue;
            $needle = $this->normalize($label);
            $match = $files->first(function($file) use ($needle, $key) {
                $name = $this->normalize(pathinfo($file->display_name, PATHINFO_FILENAME));
                $aliases = match($key) { 'jadwal'=>['jadwal'], 'sertifikat-sttp'=>['sertifikat','sttp'], 'dokumentasi'=>['dokumentasi','foto kegiatan'], default=>[] };
                return Str::contains($name, $needle) || collect($aliases)->contains(fn($a)=>Str::contains($name,$a));
            });
            if ($match) QualityDocumentRecord::updateOrCreate(['training_id'=>$training->id,'requirement_key'=>$key],['file_id'=>$match->id,'source'=>'existing','uploaded_by'=>$match->user_id]);
        }
    }
    private function normalize(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($value)))));
    }
}
