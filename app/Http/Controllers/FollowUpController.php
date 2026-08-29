<?php

namespace App\Http\Controllers;

use App\Models\MonitoringResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FollowUpController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $query = MonitoringResult::with(['training', 'question', 'stage', 'submitter', 'verifier'])
            ->where('answer', 'tidak');

        if ($user->role !== 'superadmin') {
            abort_unless($user->role === 'admin_bidang', 403);
            $query->where(function ($scope) use ($user) {
                $scope->where('follow_up_target', $user->bidang)
                    ->orWhereHas('training', fn ($training) => $training->where('bidang', $user->bidang));
            });
        }
        $query->when(request('training_id'), fn ($q, $trainingId) => $q->where('training_id', $trainingId))
            ->when(request('status'), fn ($q, $status) => $q->where('workflow_status', $status));

        $followUps = $query->latest()->get();
        $summary = [
            'total' => $followUps->count(),
            'open' => $followUps->whereIn('workflow_status', ['open', 'in_progress', 'rejected'])->count(),
            'submitted' => $followUps->where('workflow_status', 'submitted')->count(),
            'verified' => $followUps->where('workflow_status', 'verified')->count(),
            'overdue' => $followUps->filter(fn ($item) =>
                $item->workflow_status !== 'verified' && $item->due_date && $item->due_date->isPast()
            )->count(),
        ];

        return view('monitoring.follow_up', compact('followUps', 'summary'));
    }

    public function resolve(Request $request, $id)
    {
        $request->validate([
            'resolution_notes' => 'required|string|min:5',
            'evidence_file' => 'required|mimes:pdf|max:10240',
        ]);

        $result = MonitoringResult::with('training')->findOrFail($id);
        $user = Auth::user();
        abort_unless(
            $user->role === 'superadmin' || ($user->role === 'admin_bidang' && $result->follow_up_target === $user->bidang),
            403
        );

        if ($request->hasFile('evidence_file')) {
            $file = $request->file('evidence_file');
            $path = $file->store('evidence_monitoring', 'public');
            $result->evidence_file = $path;

            $archiveName = 'BUKTI_TINDAK_LANJUT_' . $result->id . '_' . now()->format('Ymd_His') . '.pdf';
            DocumentController::archiveInternal(
                $result->training_id,
                'BUKTI TINDAK LANJUT MONITORING',
                $archiveName,
                file_get_contents($file->getRealPath()),
                'pdf'
            );
        }

        $result->resolution_notes = $request->resolution_notes;
        $result->workflow_status = 'submitted';
        $result->submitted_by = $user->id;
        $result->submitted_at = now();
        $result->verified_by = null;
        $result->verified_at = null;
        $result->verification_notes = null;
        $result->is_resolved = false;
        $result->save();

        return redirect()->back()->with('success', 'Tindak lanjut berhasil diajukan untuk verifikasi.');
    }

    public function verify(Request $request, $id)
    {
        $data = $request->validate([
            'decision' => 'required|in:approve,reject',
            'verification_notes' => 'required|string|min:5',
        ]);
        $result = MonitoringResult::with('training')->findOrFail($id);
        $user = Auth::user();
        $canVerify = $user->role === 'superadmin' || (
            $user->role === 'admin_bidang' &&
            $result->training?->bidang === $user->bidang &&
            $result->follow_up_target !== $user->bidang
        );
        abort_unless($canVerify, 403);
        abort_unless($result->workflow_status === 'submitted', 422, 'Tindak lanjut belum diajukan.');

        $approved = $data['decision'] === 'approve';
        $result->workflow_status = $approved ? 'verified' : 'rejected';
        $result->is_resolved = $approved;
        $result->verified_by = $user->id;
        $result->verified_at = now();
        $result->verification_notes = $data['verification_notes'];
        $result->save();

        return back()->with('success', $approved
            ? 'Tindak lanjut telah diverifikasi dan dinyatakan selesai.'
            : 'Tindak lanjut dikembalikan untuk diperbaiki.');
    }
    
}
