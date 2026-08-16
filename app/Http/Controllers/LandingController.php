<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        // 1. Data Pelatihan Hari Ini
        $trainingsToday = \App\Models\Training::with('schedules')
            ->where('tgl_mulai', '<=', $today)
            ->where('tgl_selesai', '>=', $today)
            ->get();

        // 2. Data Statistik Global untuk Landing Page
        $stats = [
            'total_training' => \App\Models\Training::count(),
            'total_participants' => \App\Models\Participant::count(),
            'satisfaction_rate' => round(\App\Models\EvaluationResultL1::avg('score') ?? 0, 1),
            'impact_score' => round(\App\Models\EvaluationResultL34::avg('score') ?? 0, 1),
        ];

        return view('welcome', compact('trainingsToday', 'stats'));
    }
}