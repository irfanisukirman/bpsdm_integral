<?php


namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Participant;
use App\Models\EvaluationResultL1;
use App\Models\EvaluationResultL2;
use App\Models\EvaluationResultL34;
use App\Models\MonitoringResult;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Training::query();

        // Role Filter
        if ($user->role !== 'superadmin') {
            $query->where('bidang', $user->bidang);
        }

        $trainingIds = $query->pluck('id');

        // 1. STATISTIK UTAMA
        $stats = [
            'total_training' => $query->count(),
            'total_participants' => Participant::whereIn('training_id', $trainingIds)->count(),
            'avg_attendance' => Attendance::whereHas('schedule', function($q) use ($trainingIds){
                                    $q->whereIn('training_id', $trainingIds);
                                })->where('status', 'hadir')->count(),
            'total_attendance_logs' => Attendance::whereHas('schedule', function($q) use ($trainingIds){
                                    $q->whereIn('training_id', $trainingIds);
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

        // 5. MONITORING SUMMARY (Compliance)
        $monYa = MonitoringResult::whereIn('training_id', $trainingIds)->where('answer', 'ya')->count();
        $monTidak = MonitoringResult::whereIn('training_id', $trainingIds)->where('answer', 'tidak')->count();

        // 6. PELATIHAN TERBARU
        $latestTrainings = Training::with('schedules')->whereIn('id', $trainingIds)->latest()->take(5)->get();

        return view('dashboard.index', compact(
            'stats', 'avgL1', 'avgL2Pre', 'avgL2Post', 'avgL3', 'avgL4', 'monYa', 'monTidak', 'latestTrainings'
        ));
    }
}