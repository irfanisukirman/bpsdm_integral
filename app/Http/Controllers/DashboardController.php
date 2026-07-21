<?php


namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\EvaluationResultL1;
use App\Models\EvaluationResultL2;
use App\Models\EvaluationResultL34;
use Illuminate\Support\Facades\Auth;
use DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Training::query();

        // Filter jika bukan superadmin
        if ($user->role !== 'superadmin') {
            $query->where('bidang', $user->bidang);
        }

        $trainingIds = $query->pluck('id');

        // 1. Statistik Dasar
        $totalTraining = $query->count();
        $totalParticipant = DB::table('participants')->whereIn('training_id', $trainingIds)->count();

        // 2. Rata-rata Level 1 (Reaction)
        $avgL1 = EvaluationResultL1::whereIn('training_id', $trainingIds)->avg('score') ?? 0;

        // 3. Rata-rata Level 2 (Pre vs Post)
        $avgPre = DB::table('evaluation_results_l2')
                    ->whereIn('participant_id', function($q) use ($trainingIds){
                        $q->select('id')->from('participants')->whereIn('training_id', $trainingIds);
                    })->avg('pretest') ?? 0;
                    
        $avgPost = DB::table('evaluation_results_l2')
                    ->whereIn('participant_id', function($q) use ($trainingIds){
                        $q->select('id')->from('participants')->whereIn('training_id', $trainingIds);
                    })->avg('postest') ?? 0;

        // 4. Rata-rata Level 3 & 4 (Impact 360)
        $avgL34 = EvaluationResultL34::whereIn('participant_id', function($q) use ($trainingIds){
                        $q->select('id')->from('participants')->whereIn('training_id', $trainingIds);
                    })->avg('score') ?? 0;

        return view('dashboard.index', compact(
            'totalTraining', 'totalParticipant', 'avgL1', 'avgPre', 'avgPost', 'avgL34'
        ));
    }
}