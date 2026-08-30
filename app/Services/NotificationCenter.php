<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\EvaluationFormL1;
use App\Models\EvaluationResultL1;
use App\Models\EvaluationResultL34;
use App\Models\FolderUserPermission;
use App\Models\MonitoringResult;
use App\Models\Participant;
use App\Models\Schedule;
use App\Models\Training;
use App\Models\TrainingForumRead;
use App\Models\TrainingMessage;
use App\Models\User;
use App\Models\AssetBooking;
use App\Models\AgendaSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class NotificationCenter
{
    public function unreadCountForTraining(User $user, Training $training): int
    {
        $lastReadId = (int) TrainingForumRead::where('training_id', $training->id)
            ->where('user_id', $user->id)
            ->value('last_read_message_id');

        return TrainingMessage::where('training_id', $training->id)
            ->where('user_id', '!=', $user->id)
            ->where('id', '>', $lastReadId)
            ->count();
    }

    public function forUser(User $user): Collection
    {
        $items = collect();

        if ($user->role === 'participant') {
            $items = $items->merge($this->participantItems($user));
        }

        if ($user->role === 'pengajar' || $user->teachingSchedules()->exists()) {
            $items = $items->merge($this->teacherItems($user));
        }

        if (in_array($user->role, ['superadmin', 'admin_bidang'], true)) {
            $items = $items->merge($this->adminItems($user));
        }
        if (in_array($user->role, ['superadmin', 'admin_aset', 'admin_bidang'], true)) {
            $items = $items->merge($this->assetAgendaItems($user));
        }

        $items = $items->merge($this->forumItems($user));
        $items = $items->merge($this->folderShareItems($user));

        $weight = ['danger' => 1, 'warning' => 2, 'info' => 3, 'success' => 4];

        return $items->sortBy(fn ($item) => sprintf(
            '%d-%s-%s',
            $weight[$item['level']] ?? 9,
            $item['due_at'] ?? '9999-12-31 23:59:59',
            $item['title']
        ))->values();
    }

    private function folderShareItems(User $user): Collection
    {
        return FolderUserPermission::with(['folder','sharer'])->where('user_id',$user->id)->whereNull('seen_at')->latest()->get()->filter(fn($permission)=>$permission->folder)->map(function($permission){$role=$permission->permission==='contributor'?'Kontributor':'Pelihat';return $this->item('shared-folder-'.$permission->id,'Folder dibagikan kepada Anda',($permission->sharer?->name?:'Administrator').' membagikan folder '.$permission->folder->name.' sebagai '.$role.'.','info','bx-folder-open',route('documents.index',['folder'=>$permission->folder_id,'bidang'=>$permission->folder->bidang]),'Buka folder',$permission->created_at?->format('Y-m-d H:i:s'));})->values();
    }
    private function participantItems(User $user): Collection
    {
        $items = collect();
        $participants = Participant::with(['training.schedules'])
            ->where(function ($query) {
                $query->where('registration_status', 'approved')
                    ->orWhereNull('registration_status');
            })
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('nip_nik', $user->nip_nik ?: $user->username);
            })->get();

        foreach ($participants as $participant) {
            $training = $participant->training;
            if (!$training) {
                continue;
            }

            $detailUrl = route('participant.training.show', $training->id);
            $missingDocuments = collect([
                'Biodata' => $participant->biodata_file_id,
                'Surat tugas' => $participant->surat_tugas_file_id,
                'Pas foto' => $participant->pas_foto_file_id,
            ])->filter(fn ($value) => blank($value))->keys();

            if ($missingDocuments->isNotEmpty()) {
                $items->push($this->item(
                    'participant-docs-'.$participant->id,
                    'Dokumen peserta belum lengkap',
                    $training->nama_pelatihan.' — '.implode(', ', $missingDocuments->all()).' belum diunggah.',
                    'warning',
                    'bx-folder-open',
                    $detailUrl.'?tab=kelengkapan',
                    'Lengkapi dokumen'
                ));
            }

            $today = now('Asia/Jakarta')->toDateString();
            $todaySchedules = $training->schedules->where('date', $today);
            if ($todaySchedules->isNotEmpty()) {
                $hasAttended = Attendance::where('participant_id', $participant->id)
                    ->whereIn('schedule_id', $todaySchedules->pluck('id'))
                    ->exists();

                if (!$hasAttended) {
                    $setup = $todaySchedules->sortBy('start_time')->first();
                    $items->push($this->item(
                        'attendance-'.$participant->id.'-'.$today,
                        'Presensi hari ini belum diisi',
                        $training->nama_pelatihan.' — presensi tanggal '.Carbon::parse($today)->translatedFormat('d F Y').'.',
                        'danger',
                        'bx-fingerprint',
                        route('public.attendance.daily', [
                            'training_id' => $training->id,
                            'date' => $today,
                            'participant_id' => $participant->id,
                        ]),
                        'Isi presensi',
                        $today.' '.($setup->attendance_close ?: $setup->end_time ?: '23:59:59')
                    ));
                }
            }

            $forms = EvaluationFormL1::with(['training','schedule.pengajar'])->where('training_id', $training->id)->get()->filter(fn ($form) => $form->isOpen());
            $missingL1 = $forms->filter(function ($form) use ($participant) {
                return !EvaluationResultL1::where('training_id', $participant->training_id)
                    ->where('participant_id', $participant->id)
                    ->where('schedule_id', $form->schedule_id)
                    ->exists();
            });

            if ($missingL1->isNotEmpty()) {
                $items->push($this->item(
                    'evaluation-l1-'.$participant->id,
                    'Evaluasi Level 1 belum lengkap',
                    $training->nama_pelatihan.' — '.$missingL1->count().' formulir masih perlu diisi.',
                    'warning',
                    'bx-edit-alt',
                    $detailUrl.'?tab=evaluasi',
                    'Isi evaluasi'
                ));
            }

            $hasL34 = EvaluationResultL34::where('training_id', $training->id)
                ->where('participant_id', $participant->id)
                ->where('evaluator_role', 'mandiri')
                ->exists();
            $postEvaluationAt = $training->tgl_sebar_l34?->copy()->startOfDay();
            $postEvaluationIsDue = $postEvaluationAt
                && now('Asia/Jakarta')->startOfDay()->greaterThanOrEqualTo($postEvaluationAt);
            if ($postEvaluationIsDue && !$hasL34) {
                $items->push($this->item(
                    'evaluation-l34-'.$participant->id,
                    'Evaluasi pascapelatihan belum diisi',
                    $training->nama_pelatihan.' — evaluasi mandiri Level 3 & 4 menunggu jawaban Anda.',
                    'info',
                    'bx-line-chart',
                    route('public.l34.gateway', $training->id),
                    'Isi evaluasi',
                    $postEvaluationAt->format('Y-m-d 00:00:00')
                ));
            }
        }

        return $items;
    }

    private function teacherItems(User $user): Collection
    {
        $items = collect();
        $schedules = Schedule::with(['training', 'pengajarDocuments'])
            ->where('pengajar_id', $user->id)
            ->orderBy('date')
            ->get();

        if ($schedules->isEmpty()) {
            return $items;
        }

        $profile = $user->pengajar;
        $profileFieldsComplete = $profile && collect([
            $profile->npwp, $profile->nomor_rekening, $profile->nama_bank, $profile->nama_rekening,
        ])->every(fn ($value) => filled($value));
        $profileFilesComplete = $profile && collect([
            $profile->cv_path, $profile->sertifikat_path, $profile->surat_tugas_path,
        ])->every(fn ($path) => filled($path) && Storage::disk('public')->exists($path));

        if (!$profileFieldsComplete || !$profileFilesComplete) {
            $training = $schedules->first()->training;
            $items->push($this->item(
                'teacher-profile-'.$user->id,
                'Administrasi pengajar belum lengkap',
                'Lengkapi rekening, NPWP, CV, sertifikat TOT, dan surat tugas pengajar.',
                'warning',
                'bx-id-card',
                route('pengajar.manage', $training),
                'Lengkapi sekarang'
            ));
        }

        foreach ($schedules->groupBy('training_id') as $trainingSchedules) {
            $incomplete = $trainingSchedules->filter(fn ($schedule) =>
                !$schedule->pengajarDocuments || !$schedule->pengajarDocuments->isComplete()
            );
            if ($incomplete->isEmpty()) {
                continue;
            }

            $training = $trainingSchedules->first()->training;
            $items->push($this->item(
                'teacher-sessions-'.$user->id.'-'.$training->id,
                'Dokumen sesi mengajar belum lengkap',
                $training->nama_pelatihan.' — '.$incomplete->count().' sesi belum memiliki bahan ajar, RBPMP/RP, atau bukti mengajar.',
                'warning',
                'bx-book-content',
                route('pengajar.manage', $training),
                'Kelola dokumen',
                optional($incomplete->sortBy('date')->first())->date
            ));
        }

        return $items;
    }

    private function adminItems(User $user): Collection
    {
        $items = collect();
        $trainingScope = Training::query()
            ->when($user->role !== 'superadmin', fn ($query) => $query->where('bidang', $user->bidang));
        $trainingIds = (clone $trainingScope)->pluck('id');

        $pending = Participant::with('training')
            ->whereIn('training_id', $trainingIds)
            ->where('registration_status', 'pending')
            ->get()
            ->groupBy('training_id');
        foreach ($pending as $participants) {
            $training = $participants->first()->training;
            $items->push($this->item(
                'pending-participants-'.$training->id,
                'Pendaftaran peserta menunggu persetujuan',
                $training->nama_pelatihan.' — '.$participants->count().' peserta perlu diperiksa.',
                'warning',
                'bx-user-check',
                route('trainings.participants', $training->id),
                'Periksa peserta'
            ));
        }

        $followUps = MonitoringResult::with('training')
            ->where('answer', 'tidak')
            ->where('workflow_status', '!=', 'verified')
            ->where(function ($query) use ($user, $trainingIds) {
                if ($user->role === 'superadmin') {
                    $query->whereIn('training_id', $trainingIds);
                } else {
                    $query->where('follow_up_target', $user->bidang)
                        ->orWhereIn('training_id', $trainingIds);
                }
            })->get();

        if ($followUps->isNotEmpty()) {
            $overdue = $followUps->filter(fn ($result) => $result->due_date && $result->due_date->isPast())->count();
            $items->push($this->item(
                'monitoring-follow-up-'.$user->id,
                'Rekomendasi monitoring perlu ditindaklanjuti',
                $followUps->count().' rekomendasi aktif'.($overdue ? ', '.$overdue.' melewati tenggat.' : '.'),
                $overdue ? 'danger' : 'warning',
                'bx-task-x',
                route('followup.index'),
                'Buka rekomendasi',
                optional($followUps->sortBy('due_date')->first())->due_date?->format('Y-m-d')
            ));
        }

        $today = now('Asia/Jakarta')->toDateString();
        $todayTrainings = (clone $trainingScope)->with([
            'schedules' => fn ($query) => $query->where('date', $today),
            'participants' => fn ($query) => $query->where('registration_status', 'approved'),
        ])->whereHas('schedules', fn ($query) => $query->where('date', $today))->get();

        foreach ($todayTrainings as $training) {
            $scheduleIds = $training->schedules->pluck('id');
            $attendedCount = Attendance::whereIn('schedule_id', $scheduleIds)
                ->distinct('participant_id')->count('participant_id');
            $missingCount = max(0, $training->participants->count() - $attendedCount);
            if ($missingCount > 0) {
                $items->push($this->item(
                    'admin-attendance-'.$training->id.'-'.$today,
                    'Presensi peserta belum lengkap',
                    $training->nama_pelatihan.' — '.$missingCount.' peserta belum tercatat hadir hari ini.',
                    'info',
                    'bx-calendar-exclamation',
                    route('attendance.index', $training->id),
                    'Pantau presensi'
                ));
            }
        }

        return $items;
    }

    private function item(
        string $id,
        string $title,
        string $message,
        string $level,
        string $icon,
        string $url,
        string $action,
        ?string $dueAt = null
    ): array {
        return [
            'id' => $id,
            'title' => $title,
            'message' => $message,
            'level' => $level,
            'icon' => $icon,
            'url' => $url,
            'action' => $action,
            'due_at' => $dueAt,
        ];
    }

    private function forumItems(User $user): Collection
    {
        $trainings = Training::query()
            ->when($user->role !== 'superadmin', function ($query) use ($user) {
                $query->where(function ($access) use ($user) {
                    if ($user->role === 'admin_bidang') {
                        $access->where('created_by', $user->id)
                            ->orWhere(function ($legacy) use ($user) {
                                $legacy->whereNull('created_by')->where('bidang', $user->bidang);
                            });
                    } else {
                        $access->whereHas('schedules', fn ($schedule) => $schedule->where('pengajar_id', $user->id))
                            ->orWhereHas('participants', function ($participant) use ($user) {
                                $participant->where(function ($identity) use ($user) {
                                    $identity->where('user_id', $user->id)
                                        ->orWhere('nip_nik', $user->nip_nik ?: $user->username);
                                })->where(function ($status) {
                                    $status->where('registration_status', 'approved')->orWhereNull('registration_status');
                                });
                            });
                    }
                });
            })->get(['id', 'nama_pelatihan']);

        return $trainings->map(function ($training) use ($user) {
            $unread = $this->unreadCountForTraining($user, $training);
            if (!$unread) return null;

            return $this->item(
                'forum-'.$training->id,
                'Pesan baru di forum pelatihan',
                $training->nama_pelatihan.' — '.$unread.' pesan belum dibaca.',
                'info',
                'bx-conversation',
                route('training.forum.index', $training),
                'Buka forum'
            );
        })->filter()->values();
    }

    private function assetAgendaItems(User $user): Collection
    {
        $items = collect();
        $until = now('Asia/Jakarta')->addDay();

        if (in_array($user->role, ['superadmin', 'admin_aset'], true)) {
            $bookings = AssetBooking::with('asset')
                ->whereBetween('starts_at', [now('Asia/Jakarta'), $until])
                ->orderBy('starts_at')->get();
            if ($bookings->isNotEmpty()) {
                $first = $bookings->first();
                $items->push($this->item(
                    'asset-usage-upcoming-'.$user->id,
                    'Pemakaian aset akan dimulai',
                    $bookings->count().' reservasi dalam 24 jam. Terdekat: '.$first->asset->name.' pukul '.$first->starts_at->format('H:i').'.',
                    'info', 'bx-calendar-check', route('assets.monitoring', ['date' => $first->starts_at->toDateString()]), 'Lihat timeline',
                    $first->starts_at->format('Y-m-d H:i:s')
                ));
            }
        }

        if ($user->role === 'admin_bidang') {
            $schedules = AgendaSchedule::with('agenda')
                ->whereHas('agenda', fn ($query) => $query->where('bidang', $user->bidang))
                ->whereBetween('starts_at', [now('Asia/Jakarta'), $until])
                ->orderBy('starts_at')->get();
            if ($schedules->isNotEmpty()) {
                $first = $schedules->first();
                $items->push($this->item(
                    'agenda-upcoming-'.$user->id,
                    'Agenda bidang akan dimulai',
                    $schedules->count().' jadwal dalam 24 jam. Terdekat: '.$first->agenda->name.' pukul '.$first->starts_at->format('H:i').'.',
                    'info', 'bx-calendar-event', route('agendas.index'), 'Buka agenda',
                    $first->starts_at->format('Y-m-d H:i:s')
                ));
            }
        }
        return $items;
    }
}
