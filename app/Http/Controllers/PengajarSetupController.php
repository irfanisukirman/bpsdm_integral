<?php

namespace App\Http\Controllers;

use App\Models\Pengajar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PengajarSetupController extends Controller
{
    public function index()
    {
        if (Auth::user()->pengajar) {
            return redirect()->route('pengajar.dashboard')->with('info', 'Profil Pengajar sudah lengkap. Anda dapat mengakses dashboard pengajar.');
        }

        return view('pengajar.setup');
    }

    public function store(Request $request)
    {
        $request->validate([
            // Keamanan
            'new_password'       => 'required|min:6|confirmed',
            
            // Profil Utama
            'name'               => 'required|string|max:255',
            'nip_nik'            => 'required|string|max:50',
            'jabatan'            => 'required|string|max:255',
            'instansi'           => 'required|string|max:255',
            
            // Profile Keuangan
            'pangkat_golongan'   => 'required|string|max:100',
            'npwp'               => 'nullable|string|max:50',
            'nomor_rekening'     => 'required|numeric',
            'nama_bank'          => 'required|string|max:100',
            'nama_rekening'      => 'required|string|max:255',

            // Dokumen Administrasi (PDF Max 5MB = 5120 KB)
            'file_cv'            => 'nullable|file|mimes:pdf|max:5120',
            'file_sertifikat'    => 'nullable|file|mimes:pdf|max:5120',
            'file_surat_tugas'   => 'nullable|file|mimes:pdf|max:5120',
        ], [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min'       => 'Password minimal 6 karakter.',
            'file_cv.mimes'          => 'CV harus berformat PDF.',
            'file_cv.max'            => 'Ukuran CV maksimal 5MB.',
            'file_sertifikat.mimes'  => 'Sertifikat harus berformat PDF.',
            'file_sertifikat.max'    => 'Ukuran Sertifikat maksimal 5MB.',
            'file_surat_tugas.mimes' => 'Surat Tugas harus berformat PDF.',
            'file_surat_tugas.max'   => 'Ukuran Surat Tugas maksimal 5MB.',
        ]);

        $user = User::findOrFail(Auth::id());

        // 1. Upload Berkas Fisik jika ada
        $cvPath = null;
        if ($request->hasFile('file_cv')) {
            $cvPath = $request->file('file_cv')->store('pengajar/cv', 'public');
        }

        $sertifikatPath = null;
        if ($request->hasFile('file_sertifikat')) {
            $sertifikatPath = $request->file('file_sertifikat')->store('pengajar/sertifikat', 'public');
        }

        $suratTugasPath = null;
        if ($request->hasFile('file_surat_tugas')) {
            $suratTugasPath = $request->file('file_surat_tugas')->store('pengajar/surat_tugas', 'public');
        }

        // 2. Update data profil utama pada tabel users
        $user->update([
            'name'     => $request->name,
            'password' => Hash::make($request->new_password),
            'nip_nik'  => $request->nip_nik,
            'jabatan'  => $request->jabatan,
            'instansi' => $request->instansi,
        ]);

        // 3. Simpan data spesifik pengajar & dokumen ke tabel pengajars
        Pengajar::create([
            'user_id'            => $user->id,
            'pangkat_golongan'   => $request->pangkat_golongan,
            'instansi'           => $request->instansi,
            'npwp'               => $request->npwp,
            'nomor_rekening'     => $request->nomor_rekening,
            'nama_bank'          => $request->nama_bank,
            'nama_rekening'      => $request->nama_rekening,
            'cv_path'            => $cvPath,
            'sertifikat_path'    => $sertifikatPath,
            'surat_tugas_path'   => $suratTugasPath,
        ]);

        return redirect()->route('dashboard')->with('success', 'Profil Pengajar, Rekening, dan Dokumen berhasil disimpan!');
    }
}