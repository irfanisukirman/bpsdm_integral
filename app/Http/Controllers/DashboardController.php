<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Schedule;
use App\Models\Participant;
use App\Models\EvaluationResultL1;
use App\Models\EvaluationResultL2;
use App\Models\LevelOneRecapImport;
use App\Services\EvaluationL34DashboardService;
use App\Services\LevelOneEvaluationScores;
use App\Models\MonitoringResult;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use App\Models\File;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(LevelOneEvaluationScores $levelOne, EvaluationL34DashboardService $levelThreeFour)
    {
        $user = Auth::user();

        // Kebijakan baru: pilihan Narasumber pada registrasi langsung mengaktifkan akses Pengajar.
        // Blok ini juga menyelaraskan akun lama yang masih pending saat pemiliknya kembali login.
        if (
            $user->role === 'participant'
            && $user->user_type === 'narasumber'
            && $user->user_type_status === 'pending'
        ) {
            $user->update([
                'role' => 'pengajar',
                'user_type_status' => 'approved',
                'bidang' => null,
            ]);
            $user->refresh();
        }

        if ($user->role === 'evaluator') {
            return redirect()->route('post-evaluation.index');
        }

        if ($user->role === 'intern') {
            return redirect()->route('internships.dashboard');
        }
        if ($user->role === 'resepsionis') {
            return redirect()->route('guest-book.index');
        }

        if ($user->role === 'pengelola_magang') {
            return redirect()->route('internships.index');
        }

        if ($user->role === 'penandatangan') {
            return redirect()->route('electronic-signatures.index');
        }

        if ($user->role === 'admin_aset') {
            return redirect()->route('assets.dashboard');
        }

        // ==========================================
        // 1. JIKA ROLE ADALAH PARTICIPANT (PESERTA)
        // ==========================================
        if ($user->role === 'participant') {
            return redirect()->route('participant.dashboard');
        }
        if ($user->role === 'mitra') {
            return redirect()->route('mitra.dashboard');
        }

        // Akun Narasumber menggunakan /pengajar sebagai satu-satunya dashboard portal.
        if ($user->role === 'pengajar') {
            if (!$user->pengajar) {
                return redirect()->route('pengajar.setup')
                    ->with('warning', 'Silakan lengkapi profil dan data rekening Anda terlebih dahulu.');
            }

            return redirect()->route('pengajar.index');
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
        $registrationCounts = $participantsQuery->selectRaw('registration_status, COUNT(*) AS total')
            ->groupBy('registration_status')->pluck('total', 'registration_status');
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
            'total_participants' => $registrationCounts->sum(),
            'approved_participants' => (int) $registrationCounts->get('approved', 0),
            'pending_participants' => (int) $registrationCounts->get('pending', 0),
            'attendance_present' => $attendancePresent,
            'attendance_total' => $attendanceTotal,
            'attendance_rate' => $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100, 1) : 0,
            'total_jp' => (int) Schedule::whereIn('training_id', $trainingIds)->sum('jp'),
            'total_documents' => File::when($user->role !== 'superadmin', function ($q) use ($user) {
                $q->whereHas('folder', fn ($folder) => $folder->where('bidang', $user->bidang));
            })->count(),
        ];

        // 2. KIRKPATRICK LEVEL 1 (Reaction)
        $l1ByTraining = EvaluationResultL1::whereIn('training_id', $trainingIds)->whereNotNull('score')
            ->selectRaw('training_id, AVG(score) AS average')->groupBy('training_id')->pluck('average', 'training_id');
        $imports = LevelOneRecapImport::whereIn('training_id', $trainingIds)->get()->groupBy('training_id');
        if ($imports->isNotEmpty()) {
            $native = EvaluationResultL1::with('question')->whereIn('training_id', $imports->keys())->get()->groupBy('training_id');
            foreach ($imports as $trainingId => $recaps) {
                $l1ByTraining[$trainingId] = $levelOne->calculate($native->get($trainingId, collect()), $recaps)['average'];
            }
        }
        $avgL1 = $l1ByTraining->avg();
        $avgL1 = $avgL1 === null ? null : round($avgL1, 1);

        // 3. KIRKPATRICK LEVEL 2 (Learning - Pre vs Post)
        $avgL2Pre = EvaluationResultL2::whereHas('participant', function($q) use ($trainingIds){
                        $q->whereIn('training_id', $trainingIds);
                    })->avg('pretest');
        $avgL2Post = EvaluationResultL2::whereHas('participant', function($q) use ($trainingIds){
                        $q->whereIn('training_id', $trainingIds);
                    })->avg('postest');
        $learningScores = array_map(fn ($score) => $score === null ? null : round($score, 1), [$avgL2Pre, $avgL2Post]);

        // 4. KIRKPATRICK LEVEL 3 & 4 (Impact 360)
        [$avgL3, $avgL4] = $levelThreeFour->portfolioAverages($trainings);

        $monYa = MonitoringResult::whereIn('training_id', $trainingIds)->where('answer', 'ya')->count();
        $monTidak = MonitoringResult::whereIn('training_id', $trainingIds)->where('answer', 'tidak')->count();
        $monitoringRate = ($monYa + $monTidak) > 0 ? round(($monYa / ($monYa + $monTidak)) * 100, 1) : 0;

        $latestTrainings = $trainings->take(6);
        $year = $today->year;
        $registrationSeries = [
            'Disetujui' => $stats['approved_participants'],
            'Menunggu persetujuan' => $stats['pending_participants'],
            'Ditolak' => (int) $registrationCounts->get('rejected', 0),
        ];
        $otherRegistrations = $stats['total_participants'] - array_sum($registrationSeries);
        if ($otherRegistrations > 0) {
            $registrationSeries['Status lainnya'] = $otherRegistrations;
        }
        $monthlyTrainings = collect(range(1, 12))->map(fn ($month) =>
            $trainings->filter(fn ($item) =>
                Carbon::parse($item->tgl_mulai)->year === $year &&
                Carbon::parse($item->tgl_mulai)->month === $month
            )->count()
        )->values();

        return view('dashboard.index', compact(
            'stats', 'avgL1', 'learningScores', 'avgL3', 'avgL4',
            'monYa', 'monTidak', 'monitoringRate', 'latestTrainings',
            'monthlyTrainings', 'year', 'registrationSeries'
        ));
    }
}
