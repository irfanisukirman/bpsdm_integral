<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EvaluationFormL1;
use App\Models\EvaluationResultL1;
use App\Models\EvaluationResultL2;
use App\Models\EvaluationResultL34;
use App\Models\Folder;
use App\Models\MonitoringResult;
use App\Models\ParticipantCertificate;
use App\Models\Training;
use App\Models\TrainingActivityDocumentation;
use Carbon\Carbon;

class TrainingManageDashboard
{
    public function build(Training $training): array
    {
        $now = now('Asia/Jakarta');
        $start = Carbon::parse($training->tgl_mulai, 'Asia/Jakarta')->startOfDay();
        $end = Carbon::parse($training->tgl_selesai, 'Asia/Jakarta')->endOfDay();
        [$status, $statusLabel, $statusTone] = match (true) {
            $now->lt($start) => ['preparation', 'Persiapan', 'info'],
            $now->between($start, $end) => ['ongoing', 'Sedang Berlangsung', 'success'],
            default => ['completed', 'Selesai', 'secondary'],
        };

        $participants = $training->participants()->get(['id', 'registration_status']);
        $approvedIds = $participants->where('registration_status', 'approved')->pluck('id');
        $approved = $approvedIds->count();
        $pending = $participants->where('registration_status', 'pending')->count();
        $schedules = $training->schedules()->with('pengajar')->orderBy('date')->orderBy('start_time')->get();
        $learningSchedules = $schedules->where('schedule_type', '!=', 'break');
        $missingTeachers = $learningSchedules->filter(fn ($schedule) => blank($schedule->pengajar_id) && blank($schedule->pic))->count();
        $scheduleIds = $learningSchedules->pluck('id');
        $attendances = Attendance::whereIn('schedule_id', $scheduleIds)->whereIn('participant_id', $approvedIds)->get();
        $expectedAttendance = $approved * $learningSchedules->count();
        $attendanceFilled = $expectedAttendance > 0 ? min(100, round(($attendances->count() / $expectedAttendance) * 100)) : 0;
        $attendanceRate = $expectedAttendance > 0 ? round(($attendances->where('status', 'hadir')->count() / $expectedAttendance) * 100, 1) : 0;

        $l1Forms = EvaluationFormL1::where('training_id', $training->id)->count();
        $l1Respondents = EvaluationResultL1::where('training_id', $training->id)->whereIn('participant_id', $approvedIds)->distinct()->count('participant_id');
        $l2Respondents = EvaluationResultL2::whereIn('participant_id', $approvedIds)->count();
        $l34Respondents = EvaluationResultL34::where('training_id', $training->id)->whereIn('participant_id', $approvedIds)->where('evaluator_role', 'mandiri')->distinct()->count('participant_id');
        $l34Due = $now->startOfDay()->greaterThanOrEqualTo($training->tgl_sebar_l34->copy()->startOfDay());
        $evaluationParts = [$approved ? $l1Respondents / $approved : 0, $approved ? $l2Respondents / $approved : 0];
        if ($l34Due) {
            $evaluationParts[] = $approved ? $l34Respondents / $approved : 0;
        }
        $evaluationProgress = (int) round((array_sum($evaluationParts) / max(1, count($evaluationParts))) * 100);

        $monitoring = MonitoringResult::where('training_id', $training->id)->where('answer', 'tidak')->get();
        $monitoringOpen = $monitoring->whereIn('workflow_status', ['open', 'in_progress', 'rejected'])->count();
        $organizerFolder = Folder::where('training_id', $training->id)->where('name', 'KELENGKAPAN PENYELENGGARA')->whereNotNull('parent_id')->first();
        $organizerDocuments = $organizerFolder?->files()->with('user')->latest()->get() ?? collect();
        $photos = TrainingActivityDocumentation::where('training_id', $training->id)->where('include_in_report', true)->count();
        $report = $training->activityReport()->first();
        $certificates = ParticipantCertificate::where('training_id', $training->id)->whereNotNull('final_file_path')->count();

        $componentScores = [
            'Peserta' => ['weight' => 15, 'score' => $approved > 0 ? ($pending === 0 ? 100 : max(25, round(($approved / max(1, $participants->count())) * 100))) : 0],
            'Jadwal & pengajar' => ['weight' => 15, 'score' => $learningSchedules->isNotEmpty() ? ($missingTeachers === 0 ? 100 : max(30, round((($learningSchedules->count() - $missingTeachers) / $learningSchedules->count()) * 100))) : 0],
            'Kelengkapan' => ['weight' => 10, 'score' => $organizerDocuments->isNotEmpty() ? 100 : 0],
            'Presensi' => ['weight' => 15, 'score' => $attendanceFilled],
            'Evaluasi' => ['weight' => 20, 'score' => $evaluationProgress],
            'Monitoring' => ['weight' => 10, 'score' => $monitoring->isEmpty() || $monitoringOpen === 0 ? 100 : max(20, round((($monitoring->count() - $monitoringOpen) / $monitoring->count()) * 100))],
            'Laporan' => ['weight' => 10, 'score' => $report?->status === 'final' ? 100 : ($report ? 40 : 0)],
            'Sertifikat' => ['weight' => 5, 'score' => $approved > 0 ? round(($certificates / $approved) * 100) : 0],
        ];
        $progress = (int) round(collect($componentScores)->sum(fn ($item) => $item['weight'] * $item['score'] / 100));

        $attention = collect();
        if ($pending > 0) {
            $attention->push(['tone' => 'warning', 'icon' => 'bx-user-plus', 'title' => "$pending peserta menunggu persetujuan", 'description' => 'Periksa dokumen kelengkapan sebelum menyetujui peserta.', 'url' => route('trainings.participants', $training), 'action' => 'Periksa']);
        }
        if ($organizerDocuments->isEmpty()) {
            $attention->push(['tone' => 'warning', 'icon' => 'bx-folder-plus', 'title' => 'Kelengkapan penyelenggara belum tersedia', 'description' => 'Unggah dokumen administrasi utama pelatihan.', 'url' => '#organizerDocuments', 'action' => 'Lengkapi']);
        }
        if ($missingTeachers > 0) {
            $attention->push(['tone' => 'danger', 'icon' => 'bx-user-x', 'title' => "$missingTeachers jadwal belum memiliki pengajar", 'description' => 'Lengkapi pengajar agar jadwal siap digunakan.', 'url' => route('trainings.schedules', $training), 'action' => 'Atur']);
        }
        if ($status === 'completed' && $approved > $l1Respondents) {
            $attention->push(['tone' => 'info', 'icon' => 'bx-message-square-check', 'title' => ($approved - $l1Respondents).' peserta belum mengisi Evaluasi L1', 'description' => 'Pantau progres dan ingatkan peserta yang belum mengisi.', 'url' => route('evall1.progres', $training), 'action' => 'Lihat progres']);
        }
        if ($photos === 0) {
            $attention->push(['tone' => 'info', 'icon' => 'bx-images', 'title' => 'Dokumentasi kegiatan belum dipilih', 'description' => 'Tambahkan foto kegiatan untuk melengkapi laporan.', 'url' => route('training-activity-report.index', $training).'#documentation', 'action' => 'Tambah foto']);
        }
        if ($status === 'completed' && $report?->status !== 'final') {
            $attention->push(['tone' => 'primary', 'icon' => 'bx-file', 'title' => 'Laporan kegiatan belum final', 'description' => 'Lengkapi narasi, dokumentasi, lalu generate laporan.', 'url' => route('training-activity-report.index', $training), 'action' => 'Susun laporan']);
        }
        if ($monitoringOpen > 0) {
            $attention->push(['tone' => 'danger', 'icon' => 'bx-task-x', 'title' => "$monitoringOpen rekomendasi monitoring perlu aksi", 'description' => 'Tindak lanjuti temuan yang masih terbuka.', 'url' => route('followup.index', ['training_id' => $training->id]), 'action' => 'Tindak lanjuti']);
        }

        $nextSchedule = $schedules->first(function ($schedule) use ($now) {
            return Carbon::parse($schedule->date.' '.$schedule->end_time, 'Asia/Jakarta')->gte($now);
        });
        $scheduleState = null;
        if ($nextSchedule) {
            $sessionStart = Carbon::parse($nextSchedule->date.' '.$nextSchedule->start_time, 'Asia/Jakarta');
            $sessionEnd = Carbon::parse($nextSchedule->date.' '.$nextSchedule->end_time, 'Asia/Jakarta');
            $scheduleState = $now->between($sessionStart, $sessionEnd) ? 'Sedang berlangsung' : 'Jadwal berikutnya';
        }

        return compact('status', 'statusLabel', 'statusTone', 'progress', 'componentScores', 'attention', 'approved', 'pending', 'attendanceRate', 'attendanceFilled', 'l1Respondents', 'l2Respondents', 'l34Respondents', 'l34Due', 'evaluationProgress', 'missingTeachers', 'organizerFolder', 'organizerDocuments', 'photos', 'report', 'certificates', 'nextSchedule', 'scheduleState', 'learningSchedules');
    }
}
