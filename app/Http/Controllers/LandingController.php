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
        $uniqueAlumni = (int) \App\Models\Participant::query()
            ->selectRaw("COUNT(DISTINCT CASE WHEN user_id IS NOT NULL THEN CONCAT('user:', user_id) WHEN nip_nik IS NOT NULL AND nip_nik <> '' THEN CONCAT('nip:', nip_nik) ELSE CONCAT('participant:', id) END) AS total")
            ->value('total');
        $stats = [
            'total_training' => \App\Models\Training::count(),
            'active_training' => \App\Models\Training::whereDate('tgl_mulai', '<=', $today)->whereDate('tgl_selesai', '>=', $today)->count(),
            'unique_alumni' => $uniqueAlumni,
            'total_participants' => $uniqueAlumni,
            'total_participations' => \App\Models\Participant::count(),
            'satisfaction_rate' => round((float) (\App\Models\EvaluationResultL1::whereNotNull('score')->avg('score') ?? 0), 1),
            'impact_score' => round((float) (\App\Models\EvaluationResultL34::whereNotNull('score')->whereHas('question', fn ($query) => $query->where('sub_category', 'Dampak Pelatihan'))->avg('score') ?? 0), 1),
            'upcoming_agendas' => \App\Models\AgendaSchedule::whereHas('agenda', fn ($query) => $query->where('is_public', true))->where('ends_at', '>=', now())->count(),
            'available_assets' => \App\Models\Asset::where('is_active', true)->count(),
        ];
        $publicSchedules = \App\Models\AgendaSchedule::with(['agenda', 'bookings.asset'])
            ->whereHas('agenda', fn ($query) => $query->where('is_public', true))
            ->where('ends_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(30)
            ->get();
        $publicAgendas = $publicSchedules->pluck('agenda')->filter()->unique('id');
        $publicAssets = \App\Models\Asset::with('images')
            ->where('is_active', true)->where('is_public', true)
            ->latest()->limit(12)->get();

        return view('welcome', compact('trainingsToday', 'stats', 'publicSchedules', 'publicAgendas', 'publicAssets'));
    }
}
