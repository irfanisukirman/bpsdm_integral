<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetBooking;
use App\Models\EvaluationResultL1;
use App\Models\EvaluationResultL2;
use App\Models\Participant;
use App\Models\Schedule;
use App\Models\Training;
use App\Models\TrainingActivityReport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternalAiAssistantController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless(in_array($user->role, ['superadmin', 'admin_bidang', 'admin_aset'], true), 403);
        $question = trim((string) $request->query('q', ''));
        $result = $question === '' ? null : $this->answer($question, $user);
        return view('ai-assistant.index', compact('question', 'result'));
    }

    private function answer(string $question, $user): array
    {
        abort_if(mb_strlen($question) > 300, 422, 'Pertanyaan maksimal 300 karakter.');
        $q = mb_strtolower($question);
        return match (true) {
            str_contains($q, 'aset'), str_contains($q, 'ruang'), str_contains($q, 'amphitheater') => $this->assets($q),
            str_contains($q, 'pengajar'), str_contains($q, 'widyaiswara'), preg_match('/\bwi\b/u', $q) === 1, str_contains($q, '32 jp') => $this->teachers($user),
            str_contains($q, 'alumni'), str_contains($q, 'lebih dari satu'), str_contains($q, 'berulang') => $this->repeatedParticipants($user),
            str_contains($q, 'laporan'), str_contains($q, 'belum lengkap') => $this->reports($user),
            str_contains($q, 'evaluasi'), str_contains($q, 'nilai'), str_contains($q, 'terendah') => $this->evaluations($user),
            default => $this->trainings($user),
        };
    }

    private function trainingQuery($user): Builder
    {
        return Training::query()->when($user->role === 'admin_bidang', fn ($query) => $query->where('bidang', $user->bidang));
    }

    private function trainings($user): array
    {
        $today = today('Asia/Jakarta');
        $items = $this->trainingQuery($user)->withCount(['participants', 'schedules'])->orderByDesc('tgl_mulai')->limit(12)->get()
            ->map(fn ($training) => ['title' => $training->nama_pelatihan, 'subtitle' => $training->bidang.' · '.Carbon::parse($training->tgl_mulai)->translatedFormat('d M Y').'–'.Carbon::parse($training->tgl_selesai)->translatedFormat('d M Y'),
                'metric' => $training->participants_count.' peserta · '.$training->schedules_count.' sesi', 'url' => route('trainings.manage', $training),
                'tone' => Carbon::parse($training->tgl_mulai)->lte($today) && Carbon::parse($training->tgl_selesai)->gte($today) ? 'success' : 'primary'])->all();
        return $this->result('pelatihan', 'Ditemukan '.count($items).' pelatihan terbaru sesuai cakupan akses Anda.', $items, 'Urutan berdasarkan tanggal mulai terbaru.');
    }

    private function teachers($user): array
    {
        $ids = $this->trainingQuery($user)->pluck('id');
        $groups = Schedule::with(['pengajar:id,name,nip_nik'])->whereIn('training_id', $ids)->whereNotNull('pengajar_id')
            ->whereYear('date', now('Asia/Jakarta')->year)->get()->groupBy('pengajar_id');
        $items = $groups->map(function ($rows) {
            $teacher = $rows->first()->pengajar; $jp = $rows->where('duration_unit', 'JP')->sum('jp'); $oj = $rows->where('duration_unit', 'OJ')->sum('jp'); $total = $jp + $oj;
            return ['title' => $teacher?->name ?: 'Pengajar tidak ditemukan', 'subtitle' => $teacher?->nip_nik ? 'NIP/NIK '.$teacher->nip_nik : 'Tanpa NIP/NIK',
                'metric' => $total.' unit · '.$jp.' JP · '.$oj.' OJ', 'url' => route('teacher-monitoring.index', ['year' => now()->year]), 'tone' => $total >= 32 ? 'danger' : 'primary', '_sort' => $total];
        })->sortByDesc('_sort')->take(12)->map(fn ($item) => collect($item)->except('_sort')->all())->values()->all();
        return $this->result('pengajar', 'Beban mengajar tahun '.now()->year.' ditampilkan dari yang tertinggi.', $items, 'Ambang perhatian ≥32 unit JP/OJ ditandai merah.');
    }

    private function assets(string $question): array
    {
        $date = str_contains($question, 'besok') ? today('Asia/Jakarta')->addDay() : today('Asia/Jakarta');
        $bookings = AssetBooking::with('asset')->where('starts_at', '<=', $date->copy()->endOfDay())->where('ends_at', '>=', $date->copy()->startOfDay())->orderBy('starts_at')->get();
        $items = $bookings->take(12)->map(fn ($booking) => ['title' => $booking->asset?->name ?: 'Aset tidak ditemukan',
            'subtitle' => $booking->starts_at->format('H:i').'–'.$booking->ends_at->format('H:i').' WIB · '.$booking->asset?->location,
            'metric' => 'Terjadwal', 'url' => route('assets.monitoring', ['date' => $date->toDateString()]), 'tone' => 'warning'])->all();
        if ($bookings->isEmpty()) $items = Asset::where('is_active', true)->orderBy('name')->limit(12)->get()->map(fn ($asset) => [
            'title' => $asset->name, 'subtitle' => $asset->location, 'metric' => 'Tidak ada pemakaian tercatat',
            'url' => route('assets.monitoring', ['date' => $date->toDateString()]), 'tone' => 'success'])->all();
        return $this->result('aset', 'Jadwal aset untuk '.$date->translatedFormat('d F Y').'.', $items, 'Ketersediaan akhir tetap mengikuti persetujuan pengelola aset.');
    }

    private function reports($user): array
    {
        $trainings = $this->trainingQuery($user)->orderByDesc('tgl_mulai')->limit(30)->get(); $reports = TrainingActivityReport::whereIn('training_id', $trainings->pluck('id'))->get()->keyBy('training_id');
        $items = $trainings->filter(fn ($training) => ($reports[$training->id]->status ?? 'draft') !== 'final')->take(12)->map(fn ($training) => [
            'title' => $training->nama_pelatihan, 'subtitle' => $training->bidang, 'metric' => $reports->has($training->id) ? 'Laporan masih draft' : 'Laporan belum disusun',
            'url' => route('training-activity-report.index', $training), 'tone' => 'warning'])->values()->all();
        return $this->result('laporan', count($items).' laporan pelatihan memerlukan penyelesaian.', $items, 'Buka sumber untuk melengkapi narasi, dokumentasi, dan pengesahan.');
    }

    private function evaluations($user): array
    {
        $trainings = $this->trainingQuery($user)->orderByDesc('tgl_mulai')->limit(30)->get();
        $l1 = EvaluationResultL1::whereIn('training_id', $trainings->pluck('id'))->whereNotNull('score')->get()->groupBy('training_id');
        $items = $trainings->map(function ($training) use ($l1) { $scores = $l1->get($training->id, collect()); return [
            'title' => $training->nama_pelatihan, 'subtitle' => $training->bidang, 'metric' => $scores->isEmpty() ? 'L1 belum tersedia' : 'Rerata L1 '.number_format($scores->avg('score'), 1, ',', '.'),
            'url' => route('evall12.dashboard', $training->id), 'tone' => $scores->isEmpty() || $scores->avg('score') < 80 ? 'danger' : 'success', '_score' => $scores->isEmpty() ? -1 : $scores->avg('score')];
        })->sortBy('_score')->take(12)->map(fn ($item) => collect($item)->except('_score')->all())->values()->all();
        return $this->result('evaluasi', 'Pelatihan diurutkan dari hasil Evaluasi Level 1 terendah atau belum tersedia.', $items, 'Buka dashboard sumber untuk analisis Level 1–4 yang lebih lengkap.');
    }

    private function repeatedParticipants($user): array
    {
        $ids = $this->trainingQuery($user)->whereYear('tgl_mulai', now()->year)->pluck('id');
        $items = Participant::whereIn('training_id', $ids)->whereNotNull('nip_nik')->get()->groupBy('nip_nik')->filter(fn ($rows) => $rows->pluck('training_id')->unique()->count() > 1)
            ->sortByDesc(fn ($rows) => $rows->pluck('training_id')->unique()->count())->take(12)->map(function ($rows) {
                $person = $rows->first(); $count = $rows->pluck('training_id')->unique()->count(); return ['title' => $person->name, 'subtitle' => 'NIP/NIK '.$person->nip_nik.' · '.$person->instansi,
                    'metric' => $count.' pelatihan tahun '.now()->year, 'url' => route('alumni.index', ['search' => $person->nip_nik]), 'tone' => 'warning'];
            })->values()->all();
        return $this->result('alumni', count($items).' peserta pada hasil teratas mengikuti lebih dari satu pelatihan tahun berjalan.', $items, 'Informasi ini untuk pemerataan, bukan larangan mengikuti pelatihan.');
    }

    private function result(string $intent, string $answer, array $items, string $caveat): array
    {
        return compact('intent', 'answer', 'items', 'caveat') + ['generated_at' => now('Asia/Jakarta')];
    }
}
