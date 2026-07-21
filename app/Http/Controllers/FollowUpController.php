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
        $query = MonitoringResult::with('training')->where('answer', 'tidak');

        // Jika bukan superadmin, hanya lihat yang ditujukan ke bidangnya
        if ($user->role !== 'superadmin') {
            $query->where('follow_up_target', $user->bidang);
        }

        $followUps = $query->latest()->get();

        return view('monitoring.follow_up', compact('followUps'));
    }

    public function resolve(Request $request, $id)
    {
        $request->validate([
            'resolution_notes' => 'required|string|min:10',
            'evidence_file' => 'required|mimes:pdf|max:10240', // Max 10MB PDF
        ], [
            'resolution_notes.required' => 'Narasi penyelesaian wajib diisi.',
            'evidence_file.required' => 'Bukti fisik PDF wajib diunggah.',
            'evidence_file.mimes' => 'Format file harus PDF.',
        ]);

        $result = MonitoringResult::findOrFail($id);

        // Upload File
        if ($request->hasFile('evidence_file')) {
            // Hapus file lama jika ada (opsional)
            if ($result->evidence_file) {
                Storage::disk('public')->delete($result->evidence_file);
            }
            
            $file = $request->file('evidence_file');
            $path = $file->store('evidence_monitoring', 'public');
            $result->evidence_file = $path;
        }

        $result->resolution_notes = $request->resolution_notes;
        $result->is_resolved = true; // Tandai selesai
        $result->save();

        return redirect()->back()->with('success', 'Tindak lanjut berhasil diselesaikan.');
    }
    
}
