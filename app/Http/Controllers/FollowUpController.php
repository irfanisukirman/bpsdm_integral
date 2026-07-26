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
        
        // Ambil data "TIDAK" dengan relasi training dan question
        $query = MonitoringResult::with(['training', 'question'])->where('answer', 'tidak');

        // Filter jika bukan superadmin
        if ($user->role !== 'superadmin') {
            $query->where('follow_up_target', $user->bidang);
        }

        $followUps = $query->latest()->get();

        return view('monitoring.follow_up', compact('followUps'));
    }

    public function resolve(Request $request, $id)
    {
        $request->validate([
            'resolution_notes' => 'required|string|min:5',
            'evidence_file' => 'required|mimes:pdf|max:10240',
        ]);

        $result = MonitoringResult::findOrFail($id);

        if ($request->hasFile('evidence_file')) {
            $file = $request->file('evidence_file');
            $path = $file->store('evidence_monitoring', 'public');
            $result->evidence_file = $path;
        }

        $result->resolution_notes = $request->resolution_notes;
        $result->is_resolved = true;
        $result->save();

        return redirect()->back()->with('success', 'Tindak lanjut berhasil diselesaikan.');
    }
    
}
