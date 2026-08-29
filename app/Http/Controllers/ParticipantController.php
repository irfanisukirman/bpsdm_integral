<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Folder;
use App\Models\File;
use App\Models\Schedule;
use App\Models\Attendance;

class ParticipantController extends Controller
{
    /**
     * Tampilan Dashboard khusus Peserta
     */
    public function index()
    {
        if (Auth::user()->role !== 'participant') {
            return redirect()->route('dashboard');
        }

        $user = Auth::user();
        $currentYear = date('Y');

        // 1. Hitung total pelatihan yang pernah diikuti (seluruh waktu)
        $totalFollowed = \App\Models\Participant::where('nip_nik', $user->nip_nik)->count();

        // 2. Hitung Total JP Tahun Ini
        // Mengambil data dari tabel participant -> join ke training -> ambil kolom JP -> filter tahun berjalan
        $myJpThisYear = \App\Models\Participant::where('nip_nik', $user->nip_nik)
            ->whereHas('training', function ($q) use ($currentYear) {
                $q->whereYear('tgl_mulai', $currentYear);
            })
            ->with('training')
            ->get()
            ->sum(function ($p) {
                return $p->training->jp;
            });

        // Akun participant dapat sekaligus ditugaskan menjadi pengajar tanpa perubahan role.
        $isPengajar = Schedule::where('pengajar_id', $user->id)->exists();
        $teachingJpTotal = $isPengajar
            ? (int) Schedule::where('pengajar_id', $user->id)->sum('jp')
            : 0;
        $teachingJpThisYear = $isPengajar
            ? (int) Schedule::where('pengajar_id', $user->id)->whereYear('date', $currentYear)->sum('jp')
            : 0;
        $teachingCount = $isPengajar
            ? Schedule::where('pengajar_id', $user->id)->distinct('training_id')->count('training_id')
            : 0;

        return view('participant.dashboard', compact(
            'user', 'totalFollowed', 'myJpThisYear', 'isPengajar',
            'teachingJpTotal', 'teachingJpThisYear', 'teachingCount'
        ));
    }

    /**
     * Menu: Daftar Pelatihan (Melihat semua pelatihan yang dibuka oleh Bidang)
     */
    public function availableTrainings()
    {
        $user = auth()->user();

        // Ambil pelatihan yang diikuti user
        $myTrainings = \App\Models\Training::with(['participants', 'stages'])
            ->whereHas('participants', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get()
            ->filter(function($t) use ($user) {
                $participant = $t->participants->where('user_id', $user->id)->first();
                // KUNCI: Hanya tampilkan jika belum menyelesaikan semua kewajiban
                // Jika sudah selesai semua (L1-L4 & Berkas), maka card hilang (pindah ke riwayat)
                return !$participant->is_all_finished;
            });

        return view('participant.available_trainings', compact('myTrainings'));
    }

    public function enrollByCode(Request $request)
    {
        $request->validate(['invitation_code' => 'required|string']);
        
        // Cari pelatihan berdasarkan kode
        $training = \App\Models\Training::where('invitation_code', strtoupper($request->invitation_code))->first();

        if (!$training) {
            return redirect()->back()->with('error', 'Kode Undangan tidak valid atau pelatihan tidak ditemukan.');
        }

        // Gunakan fungsi enroll yang sudah kita buat sebelumnya (Tinggal panggil logikanya)
        return $this->enroll($request, $training->id);
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
        $user = \App\Models\User::findOrFail(auth()->id());

        $request->validate([
            'nip_nik' => 'required|unique:users,nip_nik,' . $user->id,
            'whatsapp' => 'required',
            'gender' => 'required',
            'jabatan' => 'required',
            'instansi' => 'required',
            'provinsi' => 'required',
            'kota' => 'required', // <--- Gunakan 'kota'
            'kecamatan' => 'required',
            'kelurahan' => 'required',
            'status_kepegawaian' => 'required',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user->update([
            'nip_nik' => $request->nip_nik,
            'whatsapp' => $request->whatsapp,
            'gender' => $request->gender,
            'jabatan' => $request->jabatan,
            'instansi' => $request->instansi,
            'status_kepegawaian' => $request->status_kepegawaian,
            'provinsi' => $request->provinsi,
            'kota' => $request->kota, // <--- Simpan ke kolom 'kota'
            'kecamatan' => $request->kecamatan,
            'kelurahan' => $request->kelurahan,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // Sinkronisasi ke tabel participants
        \App\Models\Participant::where('nip_nik', $user->nip_nik)
            ->update(['user_id' => $user->id]);

        return redirect()->route('participant.dashboard')->with('success', 'Profil berhasil disimpan.');
    }

    // Proses Daftar dengan Kode
    public function enroll(Request $request, $id)
    {
        $training = \App\Models\Training::findOrFail($id);
        $user = Auth::user();

        // Validasi Token
        if (strtoupper($request->invitation_code) !== strtoupper($training->invitation_code)) {
            return redirect()->back()->with('error', 'Kode Undangan Salah!');
        }

        // OTOMATIS COPY DATA DARI USER KE PARTICIPANT
        \App\Models\Participant::updateOrCreate(
            [
                'training_id' => $id,
                'nip_nik' => $user->nip_nik // Kunci utama pencocokan
            ],
            [
                'user_id'            => $user->id,
                'registration_status' => 'pending',
                'name'               => $user->name,
                'gender'             => $user->gender,
                'jabatan'            => $user->jabatan,
                'instansi'           => $user->instansi,
                'provinsi'           => $user->provinsi,
                'kabupaten_kota'     => $user->kabupaten_kota,
                'kecamatan'          => $user->kecamatan,
                'kelurahan'          => $user->kelurahan,
                'status_kepegawaian' => $user->status_kepegawaian,
            ]
        );

        return redirect()->route('participant.training.show', ['id' => $id, 'tab' => 'kelengkapan'])
            ->with('success_enroll', 'Pendaftaran Berhasil! Data profil Anda telah disinkronkan ke pelatihan ini.');
    }

    public function showTrainingDetail($id)
    {
        $training = Training::with(['stages', 'schedules'])->findOrFail($id);
        $user = Auth::user();

        // Cari data peserta yang sinkron dengan User Login
        $participant = Participant::where('training_id', $id)
            ->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                ->orWhere('nip_nik', $user->nip_nik);
            })->first();

        // 1. Jika data peserta tidak ditemukan sama sekali
        if (!$participant) {
            return redirect()->route('participant.trainings')->with('error', 'Akses ditolak. Anda belum terdaftar di pelatihan ini.');
        }

        // 2. Jika status masih PENDING, jangan biarkan masuk detail
        if ($participant->registration_status === 'pending') {
            return redirect()->route('participant.trainings')->with('error', 'Pendaftaran Anda sedang menunggu persetujuan (Approval) dari Admin Bidang.');
        }

        // 3. Jika status REJECTED
        if ($participant->registration_status === 'rejected') {
            return redirect()->route('participant.trainings')->with('error', 'Maaf, pendaftaran Anda ditolak oleh Admin.');
        }

        // Ambil form evaluasi
        $formsL1 = \App\Models\EvaluationFormL1::where('training_id', $id)->get();
        $participantAttendances = Attendance::with('schedule')
            ->where('participant_id', $participant->id)
            ->whereHas('schedule', fn ($query) => $query->where('training_id', $training->id))
            ->get()
            ->keyBy(fn ($attendance) => (string) $attendance->schedule->date);
        $attendanceDays = $training->schedules
            ->groupBy(fn ($schedule) => (string) $schedule->date)
            ->map(function ($schedules, $date) use ($participantAttendances) {
                return [
                    'date' => $date,
                    'setup' => $schedules->first(),
                    'attendance' => $participantAttendances->get($date),
                ];
            })
            ->sortBy('date')
            ->values();

        return view('participant.training_detail', compact('training', 'participant', 'formsL1', 'user', 'attendanceDays'));
    }

    public function myHistory()
    {
        $user = auth()->user();
        
        $history = \App\Models\Training::whereHas('participants', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->get()
            ->filter(function($t) use ($user) {
                $participant = $t->participants->where('user_id', $user->id)->first();
                // KUNCI: Muncul di Riwayat HANYA JIKA sudah selesai semua
                return $participant->is_all_finished;
            });

        return view('participant.history', compact('history'));
    }

    // Upload Kelengkapan & Masuk Folder Otomatis
    public function uploadRequirement(Request $request, $id)
    {
        $allowedMimes = ($request->type == 'pas_foto') ? 'jpeg,png,jpg' : 'pdf';

        $request->validate([
            'file' => "required|mimes:$allowedMimes|max:5120",
            'type' => 'required|in:biodata,surat_tugas,pas_foto'
        ]);

        $user = Auth::user();
        $training = Training::with('folder')->findOrFail($id);

        // PERBAIKAN: Pencarian data peserta agar tidak langsung menampilkan 404
        $participant = Participant::where('training_id', $id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('nip_nik', $user->nip_nik);
            })->first();

        // Jika tidak ditemukan juga, beri pesan error yang jelas 
        if (!$participant) {
            return redirect()->route('participant.training.show', ['id' => $id, 'tab' => 'kelengkapan'])
                ->with('error', 'Data peserta tidak ditemukan. Pastikan Anda sudah terdaftar di pelatihan ini.');
        }

        // Pastikan user_id terhubung jika sebelumnya kosong
        if (empty($participant->user_id)) {
            $participant->update(['user_id' => $user->id]);
        }

        // PERBAIKAN: Cari peserta berdasarkan training_id DAN (user_id ATAU nip_nik)
        $participant = Participant::where('training_id', $id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('nip_nik', $user->nip_nik);
            })->first();

        // Jika tidak ditemukan juga, baru kita beri pesan error yang jelas (bukan 404)
        if (!$participant) {
            return redirect()->route('participant.training.show', ['id' => $id, 'tab' => 'kelengkapan'])
                ->with('error', 'Data peserta tidak ditemukan. Pastikan Anda sudah terdaftar di pelatihan ini.');
        }

        // Pastikan user_id terhubung jika sebelumnya kosong
        if (empty($participant->user_id)) {
            $participant->update(['user_id' => $user->id]);
        }

        // 1. Dapatkan/Buat Folder Utama Pelatihan
        $parentFolder = Folder::firstOrCreate(
            ['training_id' => $id, 'parent_id' => null],
            [
                'name' => $training->nama_pelatihan,
                'bidang' => $training->bidang,
                'user_id' => Auth::id() ?? 1
            ]
        );

        // 2. Dapatkan/Buat Sub-Folder "KELENGKAPAN PESERTA"
        $subFolder = Folder::firstOrCreate([
            'name' => 'KELENGKAPAN PESERTA',
            'parent_id' => $parentFolder->id, // <--- PERBAIKI INI: Gunakan $parentFolder->id
            'training_id' => $id,
            'bidang' => $training->bidang
        ], ['user_id' => Auth::id() ?? 1]);

        $participantFolder = Folder::firstOrCreate([
            'name' => strtoupper($participant->name),
            'parent_id' => $subFolder->id,
            'training_id' => $id,
            'bidang' => $training->bidang,
        ], ['user_id' => $user->id]);

        // 3. Simpan File
        $extension = $request->file('file')->getClientOriginalExtension();
        $fileName = strtoupper($request->type) . '_' . str_replace(' ', '_', $user->name) . '_' . time() . '.' . $extension;
        $path = $request->file('file')->storeAs('documents', $fileName, 'public');

        // 4. Catat ke tabel Files
        $fileRecord = File::create([
            'folder_id' => $participantFolder->id,
            'display_name' => $fileName,
            'file_path' => $path,
            'file_type' => $extension,
            'file_size' => $request->file('file')->getSize(),
            'user_id' => $user->id
        ]);

        // 5. Update referensi file di tabel Participants
        $column = $request->type . '_file_id';
        $participant->update([$column => $fileRecord->id]);

        return redirect()->route('participant.training.show', ['id' => $id, 'tab' => 'kelengkapan'])
            ->with('success', 'Berkas ' . strtoupper($request->type) . ' berhasil diunggah.');
    }
}
