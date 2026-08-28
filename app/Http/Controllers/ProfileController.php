<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pengajar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman edit profil
     */
    public function edit()
    {
        $user = Auth::user();
        if ($user->role === 'pengajar') {
            $user->load('pengajar');
        }
        return view('profile.edit', compact('user'));
    }

    /**
     * Memproses update data profil (Umum & Pengajar beserta upload berkas)
     */
    public function update(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        // 1. Aturan validasi dasar (Ditambahkan field wilayah & kepegawaian)
        $rules = [
            'name'               => 'required|string|max:255',
            'whatsapp'           => 'required|numeric',
            'profile_photo'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gender'             => 'required',
            'status_kepegawaian' => 'required',
            'nip_nik'            => 'required|string|max:50',
            'jabatan'            => 'required|string|max:255',
            'instansi'           => 'required|string|max:255',
            'provinsi'           => 'required|string',
            'kota'               => 'required|string',
            'kecamatan'          => 'required|string',
            'kelurahan'          => 'required|string',
            'latitude'           => 'nullable|numeric|between:-90,90|required_with:longitude',
            'longitude'          => 'nullable|numeric|between:-180,180|required_with:latitude',
        ];

        // Tambahan validasi khusus jika role adalah Pengajar
        if ($user->role === 'pengajar' && $request->has('pangkat_golongan')) {
            $rules = array_merge($rules, [
                'pangkat_golongan'   => 'required|string|max:100',
                'npwp'               => 'nullable|string|max:50',
                'nomor_rekening'     => 'required|numeric',
                'nama_bank'          => 'required|string|max:100',
                'nama_rekening'      => 'required|string|max:255',
                'file_cv'            => 'nullable|file|mimes:pdf|max:5120',
                'file_sertifikat'    => 'nullable|file|mimes:pdf|max:5120',
                'file_surat_tugas'   => 'nullable|file|mimes:pdf|max:5120',
            ]);
        }

        $request->validate($rules, [
            'file_cv.mimes'          => 'Berkas CV harus berformat PDF.',
            'file_sertifikat.mimes'  => 'Berkas Sertifikat harus berformat PDF.',
            'file_surat_tugas.mimes' => 'Berkas Surat Tugas harus berformat PDF.',
        ]);

        // 2. Update Foto Profil
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $user->profile_photo = $request->file('profile_photo')->store('avatars', 'public');
        }

        // 3. Simpan Data Profil Umum ke tabel Users
        $user->name               = $request->name;
        $user->whatsapp           = $request->whatsapp;
        $user->nip_nik           = $request->nip_nik;
        $user->gender             = $request->gender;
        $user->jabatan            = $request->jabatan;
        $user->instansi           = $request->instansi;
        $user->status_kepegawaian = $request->status_kepegawaian;
        
        // BAGIAN PENTING: Simpan Data Wilayah
        $user->provinsi           = $request->provinsi;
        $user->kota               = $request->kota;
        $user->kecamatan          = $request->kecamatan;
        $user->kelurahan          = $request->kelurahan;
        $user->latitude           = $request->latitude;
        $user->longitude          = $request->longitude;

        // 4. Update Data Khusus Pengajar & Berkas Dokumen (Jika ada)
        if ($user->role === 'pengajar' && $request->has('pangkat_golongan')) {
            $pengajar = $user->pengajar ?? new \App\Models\Pengajar(['user_id' => $user->id]);

            if ($request->hasFile('file_cv')) {
                if ($pengajar->cv_path) Storage::disk('public')->delete($pengajar->cv_path);
                $pengajar->cv_path = $request->file('file_cv')->store('pengajar/cv', 'public');
            }

            if ($request->hasFile('file_sertifikat')) {
                if ($pengajar->sertifikat_path) Storage::disk('public')->delete($pengajar->sertifikat_path);
                $pengajar->sertifikat_path = $request->file('file_sertifikat')->store('pengajar/sertifikat', 'public');
            }

            if ($request->hasFile('file_surat_tugas')) {
                if ($pengajar->surat_tugas_path) Storage::disk('public')->delete($pengajar->surat_tugas_path);
                $pengajar->surat_tugas_path = $request->file('file_surat_tugas')->store('pengajar/surat_tugas', 'public');
            }

            $pengajar->pangkat_golongan   = $request->pangkat_golongan;
            $pengajar->instansi           = $request->instansi;
            $pengajar->npwp               = $request->npwp;
            $pengajar->nama_bank          = $request->nama_bank;
            $pengajar->nomor_rekening     = $request->nomor_rekening;
            $pengajar->nama_rekening      = $request->nama_rekening;
            $pengajar->save();
        }

        $user->save();

        // 5. Otomatis Update tabel Participants (Sinkronisasi NIP)
        \App\Models\Participant::where('user_id', $user->id)->update([
            'nip_nik'            => $user->nip_nik,
            'name'               => $user->name,
            'gender'             => $user->gender,
            'jabatan'            => $user->jabatan,
            'instansi'           => $user->instansi,
            'provinsi'           => $user->provinsi,
            'kota'               => $user->kota,
            'kecamatan'          => $user->kecamatan,
            'kelurahan'          => $user->kelurahan,
            'status_kepegawaian' => $user->status_kepegawaian,
        ]);

        return redirect()->back()->with('success', 'Profil dan data wilayah berhasil diperbarui.');
    }

    /**
     * Memproses penggantian password akun
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:6|confirmed',
        ], [
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.'
        ]);

        $user = User::findOrFail(Auth::id());
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password berhasil diperbarui.');
    }
}
