<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Schedule;
use App\Models\Participant;
use App\Models\EvaluationResultL1;
use App\Models\EvaluationResultL2;
use App\Models\EvaluationResultL34;
use App\Models\MonitoringResult;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\File;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ==========================================
        // 1. JIKA ROLE ADALAH PARTICIPANT (PESERTA)
        // ==========================================
        if ($user->role === 'participant') {
            return redirect()->route('participant.dashboard');
        }

        // ==========================================
        // 2. JIKA ROLE ADALAH PENGAJAR
        // ==========================================
        if ($user->role === 'pengajar') {
            if (!$user->pengajar) {
                return redirect()->route('pengajar.setup')
                    ->with('warning', 'Silakan lengkapi profil dan data rekening Anda terlebih dahulu.');
            }

            $user->load('pengajar');

            // 1. DATA PELATIHAN YANG DIAJAR
            $myTrainings = Training::whereHas('schedules', function($q) use ($user) {
                $q->where('pengajar_id', $user->id);
            })->get();

            // Pelatihan pada tahun berjalan
            $myTrainingsThisYear = Training::whereHas('schedules', function($q) use ($user) {
                $q->where('pengajar_id', $user->id);
            })->whereYear('tgl_mulai', date('Y'))->get();

            // 2. TOTAL JP (Diambil langsung dari tabel SCHEDULES milik pengajar ini)
            $totalJp = abs((int) Schedule::where('pengajar_id', $user->id)->sum('jp'));

            // 3. JP TAHUN INI (Diambil dari kolom JP tabel SCHEDULES berdasarkan tahun pada tanggal jadwal 'date')
            $jpTahunIni = abs((int) Schedule::where('pengajar_id', $user->id)
                ->whereYear('date', date('Y'))
                ->sum('jp'));

            // 4. Jumlah Pelatihan
            $totalPelatihan = $myTrainings->count();

            // 5. Pelatihan Tahun Ini
            $pelatihanTahunIni = $myTrainingsThisYear->count();
            
            // 6. Persentase Capaian JP (Target 20 JP per tahun, dikunci maksimal 100%)
            $targetJp = 20; 
            $persentaseJp = $targetJp > 0 ? min(100, max(0, round(($jpTahunIni / $targetJp) * 100))) : 0;

            return view('pengajar.dashboard', compact(
                'user', 
                'totalJp', 
                'jpTahunIni', 
                'totalPelatihan', 
                'pelatihanTahunIni', 
                'persentaseJp'
            ));
        }

        $query = Training::query();
        if ($user->role !== 'superadmin') {
            $query->where('bidang', $user->bidang);
        }

        $trainings = $query
            ->withCount(['participants', 'schedules'])
            ->orderByDesc('tgl_mulai')
            ->get();
        $trainingIds = $trainings->pluck('id');
        $today = Carbon::today('Asia/Jakarta');

        $participantsQuery = Participant::whereIn('training_id', $trainingIds);
        $attendanceQuery = Attendance::whereHas('schedule', fn ($q) => $q->whereIn('training_id', $trainingIds));
        $attendanceTotal = (clone $attendanceQuery)->count();
        $attendancePresent = (clone $attendanceQuery)->where('status', 'hadir')->count();

        $stats = [
            'total_training' => $trainings->count(),
            'ongoing_training' => $trainings->filter(fn ($item) =>
                Carbon::parse($item->tgl_mulai)->startOfDay()->lte($today) &&
                Carbon::parse($item->tgl_selesai)->endOfDay()->gte($today)
            )->count(),
            'upcoming_training' => $trainings->filter(fn ($item) => Carbon::parse($item->tgl_mulai)->startOfDay()->gt($today))->count(),
            'completed_training' => $trainings->filter(fn ($item) => Carbon::parse($item->tgl_selesai)->endOfDay()->lt($today))->count(),
            'total_participants' => (clone $participantsQuery)->count(),
            'approved_participants' => (clone $participantsQuery)->where('registration_status', 'approved')->count(),
            'pending_participants' => (clone $participantsQuery)->where('registration_status', 'pending')->count(),
            'attendance_present' => $attendancePresent,
            'attendance_total' => $attendanceTotal,
            'attendance_rate' => $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100, 1) : 0,
            'total_jp' => (int) Schedule::whereIn('training_id', $trainingIds)->sum('jp'),
            'total_documents' => File::when($user->role !== 'superadmin', function ($q) use ($user) {
                $q->whereHas('folder', fn ($folder) => $folder->where('bidang', $user->bidang));
            })->count(),
        ];

        // 2. KIRKPATRICK LEVEL 1 (Reaction)
        $avgL1 = EvaluationResultL1::whereIn('training_id', $trainingIds)->avg('score') ?? 0;

        // 3. KIRKPATRICK LEVEL 2 (Learning - Pre vs Post)
        $avgL2Pre = EvaluationResultL2::whereHas('participant', function($q) use ($trainingIds){
                        $q->whereIn('training_id', $trainingIds);
                    })->avg('pretest') ?? 0;
        $avgL2Post = EvaluationResultL2::whereHas('participant', function($q) use ($trainingIds){
                        $q->whereIn('training_id', $trainingIds);
                    })->avg('postest') ?? 0;

        // 4. KIRKPATRICK LEVEL 3 & 4 (Impact 360)
        $avgL3 = EvaluationResultL34::whereIn('training_id', $trainingIds)->where('evaluator_role', '!=', 'mandiri')->avg('score') ?? 0;
        $avgL4 = EvaluationResultL34::whereIn('training_id', $trainingIds)->avg('score') ?? 0; // Agregat seluruhnya

        $monYa = MonitoringResult::whereIn('training_id', $trainingIds)->where('answer', 'ya')->count();
        $monTidak = MonitoringResult::whereIn('training_id', $trainingIds)->where('answer', 'tidak')->count();
        $monitoringRate = ($monYa + $monTidak) > 0 ? round(($monYa / ($monYa + $monTidak)) * 100, 1) : 0;

        $latestTrainings = $trainings->take(6);
        $year = $today->year;
        $monthlyTrainings = collect(range(1, 12))->map(fn ($month) =>
            $trainings->filter(fn ($item) =>
                Carbon::parse($item->tgl_mulai)->year === $year &&
                Carbon::parse($item->tgl_mulai)->month === $month
            )->count()
        )->values();

        return view('dashboard.index', compact(
            'stats', 'avgL1', 'avgL2Pre', 'avgL2Post', 'avgL3', 'avgL4',
            'monYa', 'monTidak', 'monitoringRate', 'latestTrainings',
            'monthlyTrainings', 'year'
        ));
    }
}
