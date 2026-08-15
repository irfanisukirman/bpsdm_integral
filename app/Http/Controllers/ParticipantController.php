<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParticipantController extends Controller
{
    /**
     * Tampilan Dashboard khusus Peserta
     */
    public function index()
    {
        $user = Auth::user();
        $totalFollowed = Participant::where('nip_nik', $user->username)->count();
        
        return view('participant.dashboard', compact('user', 'totalFollowed'));
    }

    /**
     * Menu: Daftar Pelatihan (Melihat semua pelatihan yang dibuka oleh Bidang)
     */
    public function availableTrainings()
    {
        // Mengambil pelatihan yang belum selesai
        $trainings = Training::where('tgl_selesai', '>=', now())
            ->latest()
            ->get();

        return view('participant.available_trainings', compact('trainings'));
    }

    /**
     * Menu: Riwayat Pelatihan (Pelatihan yang pernah diikuti oleh ybs)
     */
    public function myHistory()
    {
        $user = Auth::user();
        
        // Mencari data di tabel participants berdasarkan NIP (username user)
        $history = Participant::with('training')
            ->where('nip_nik', $user->username)
            ->get();

        return view('participant.history', compact('history'));
    }

    /**
     * Halaman Lengkapi Profil (Muncul setelah login Google pertama kali)
     */
    public function completeProfile()
    {
        $user = Auth::user();
        return view('participant.complete_profile', compact('user'));
    }

    /**
     * Simpan Pelengkapan Profil
     */
    public function storeProfile(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $request->validate([
            'gender' => 'required',
            'provinsi' => 'required',
            'kabupaten_kota' => 'required',
            'status_kepegawaian' => 'required',
            'whatsapp' => 'required|numeric'
        ]);

        $user->update([
            'gender' => $request->gender,
            'provinsi' => $request->provinsi,
            'kabupaten_kota' => $request->kabupaten_kota,
            'status_kepegawaian' => $request->status_kepegawaian,
            'whatsapp' => $request->whatsapp,
        ]);

        return redirect()->route('dashboard')->with('success', 'Profil Anda berhasil dilengkapi.');
    }
}