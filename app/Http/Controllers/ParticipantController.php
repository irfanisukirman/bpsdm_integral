<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Folder;
use App\Models\File;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

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

        $postEvaluationTrainings = Participant::with('training')
            ->where('user_id', $user->id)
            ->where('registration_status', 'approved')
            ->get()
            ->filter(fn ($participant) =>
                $participant->is_core_training_complete
                && $participant->is_post_evaluation_due
                && !$participant->hasFilledL34Mandiri()
            )
            ->sortBy(fn ($participant) => $participant->training?->tgl_sebar_l34)
            ->values();

        return view('participant.dashboard', compact(
            'user', 'totalFollowed', 'myJpThisYear', 'postEvaluationTrainings'
        ));
    }

    /**
     * Menu: Daftar Pelatihan (Melihat semua pelatihan yang dibuka oleh Bidang)
     */
    public function availableTrainings()
    {
        $user = auth()->user();

        $enrollments = Participant::with('training')
            ->where('user_id', $user->id)
            ->get();

        $canJoinNewTraining = $this->canJoinNewTraining($enrollments);

        // Tampilkan pelatihan aktif/belum lengkap serta evaluasi pasca yang sudah jatuh tempo.
        $myTrainings = \App\Models\Training::with([
            'participants',
            'stages',
            'schedules' => fn ($query) => $query
                ->with(['pengajar', 'bookings.asset'])
                ->orderBy('date')
                ->orderBy('start_time'),
        ])
            ->whereHas('participants', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderByDesc('tgl_mulai')
            ->get()
            ->filter(function($t) use ($user) {
                $participant = $t->participants->where('user_id', $user->id)->first();
                $needsPostEvaluation = $participant->is_post_evaluation_due && !$participant->hasFilledL34Mandiri();
                return !$participant->is_core_training_complete || $needsPostEvaluation;
            });

        return view('participant.available_trainings', compact('myTrainings', 'canJoinNewTraining'));
    }

    public function enrollByCode(Request $request)
    {
        $request->validate([
            'invitation_code' => ['required', 'string'],
            'biodata' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'surat_tugas' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'pas_foto' => ['required', 'file', 'mimes:jpeg,jpg,png', 'max:5120'],
        ], [
            'biodata.required' => 'Dokumen biodata wajib diunggah.',
            'surat_tugas.required' => 'Surat tugas wajib diunggah.',
            'pas_foto.required' => 'Pas foto wajib diunggah.',
        ]);

        $enrollments = Participant::with('training')
            ->where('user_id', auth()->id())
            ->get();
        if (!$this->canJoinNewTraining($enrollments)) {
            return redirect()->back()->with('error', 'Selesaikan pelatihan yang sedang diikuti, lengkapi tiga berkas, dan isi seluruh Evaluasi Level 1 sebelum mengikuti pelatihan baru.');
        }
        
        // Cari pelatihan berdasarkan kode
        $training = \App\Models\Training::where('invitation_code', strtoupper($request->invitation_code))->first();

        if (!$training) {
            return redirect()->back()->with('error', 'Kode Undangan tidak valid atau pelatihan tidak ditemukan.');
        }

        // Gunakan fungsi enroll yang sudah kita buat sebelumnya (Tinggal panggil logikanya)
        return $this->enroll($request, $training->id);
    }

    private function canJoinNewTraining($enrollments): bool
    {
        return $enrollments->isEmpty() || $enrollments->every(fn ($participant) =>
            $participant->registration_status === 'rejected' || $participant->is_core_training_complete
        );
    }

    /**
     * Halaman Lengkapi Profil (Muncul setelah login Google pertama kali)
     */
    public function completeProfile()
    {
        $user = Auth::user();
        abort_unless($user->role === 'participant', 403, 'Form registrasi ini hanya untuk akun pengguna publik.');
        return view('participant.complete_profile', compact('user'));
    }

    /**
     * Simpan Pelengkapan Profil
     */
    public function storeProfile(Request $request)
    {
        $user = \App\Models\User::findOrFail(auth()->id());
        abort_unless($user->role === 'participant', 403, 'Form registrasi ini hanya untuk akun pengguna publik.');

        $request->validate([
            'user_type' => 'required|in:peserta,narasumber,mitra',
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

        $requestedType = $request->user_type;
        $role = $requestedType === 'narasumber' ? 'pengajar' : 'participant';
        $typeStatus = in_array($requestedType, ['peserta', 'narasumber'], true) ? 'approved' : 'pending';
        $user->update([
            'user_type' => $requestedType,
            'user_type_status' => $typeStatus,
            // Narasumber langsung aktif, sedangkan akun administratif tetap tidak tersedia di form publik.
            'role' => $role,
            'bidang' => null,
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

        if ($requestedType === 'narasumber') {
            return redirect()->route('pengajar.setup')
                ->with('success', 'Akun Narasumber berhasil diaktifkan. Lengkapi data pengajar untuk melanjutkan.');
        }

        $message = $requestedType === 'peserta'
            ? 'Profil peserta berhasil disimpan.'
            : 'Profil berhasil disimpan. Pengajuan sebagai Mitra menunggu persetujuan admin.';

        return redirect()->route('participant.dashboard')->with('success', $message);
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

        $storedPaths = [];

        try {
            DB::transaction(function () use ($request, $training, $user, $id, &$storedPaths) {
                $participant = Participant::updateOrCreate([
                'training_id' => $id,
                'nip_nik' => $user->nip_nik,
                ], [
                'user_id'            => $user->id,
                'registration_status' => 'pending',
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

                foreach (['biodata', 'surat_tugas', 'pas_foto'] as $type) {
                    $storedPaths[] = $this->storeRequirementFile(
                        $request->file($type), $type, $training, $participant, $user
                    );
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            report($exception);

            return redirect()->back()->withInput($request->only('invitation_code'))
                ->with('error', 'Pendaftaran gagal disimpan. Silakan coba unggah kembali dokumen Anda.');
        }

        return redirect()->route('participant.trainings')
            ->with('success', 'Pendaftaran dan dokumen kelengkapan berhasil dikirim. Silakan menunggu pemeriksaan admin.');
    }

    private function storeRequirementFile($uploadedFile, string $type, Training $training, Participant $participant, User $user): string
    {
        $parentFolder = Folder::firstOrCreate(
            ['training_id' => $training->id, 'parent_id' => null],
            [
                'name' => $training->nama_pelatihan . ' - Angkatan ' . $training->angkatan,
                'bidang' => $training->bidang,
                'user_id' => $user->id,
            ]
        );
        $requirementsFolder = Folder::firstOrCreate([
            'name' => 'KELENGKAPAN PESERTA',
            'parent_id' => $parentFolder->id,
            'training_id' => $training->id,
            'bidang' => $training->bidang,
        ], ['user_id' => $user->id]);
        $participantFolder = Folder::firstOrCreate([
            'name' => strtoupper($participant->name),
            'parent_id' => $requirementsFolder->id,
            'training_id' => $training->id,
            'bidang' => $training->bidang,
        ], ['user_id' => $user->id]);

        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $fileName = strtoupper($type) . '_' . Str::slug($user->name, '_') . '_' . Str::uuid() . '.' . $extension;
        $path = $uploadedFile->storeAs('documents', $fileName, 'public');
        $fileRecord = File::create([
            'folder_id' => $participantFolder->id,
            'display_name' => $uploadedFile->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $extension,
            'file_size' => $uploadedFile->getSize(),
            'user_id' => $user->id,
        ]);

        $participant->update([$type . '_file_id' => $fileRecord->id]);

        return $path;
    }

    public function showTrainingDetail($id)
    {
        $training = Training::with([
            'stages',
            'schedules' => fn ($query) => $query
                ->with('pengajar')
                ->orderBy('date')
                ->orderBy('start_time'),
            'participants' => fn ($query) => $query
                ->with('user')
                ->where('registration_status', 'approved')
                ->orderBy('name'),
        ])->findOrFail($id);
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
        $formsL1 = \App\Models\EvaluationFormL1::with(['training','schedule.pengajar'])->where('training_id', $id)->get();
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

        $participantCertificate = \App\Models\ParticipantCertificate::where('training_id', $training->id)
            ->where('participant_id', $participant->id)
            ->first();

        return view('participant.training_detail', compact('training', 'participant', 'formsL1', 'user', 'attendanceDays', 'participantCertificate'));
    }

    public function myHistory(Request $request)
    {
        $user = auth()->user();
        $search = trim((string) $request->query('search'));

        $historyItems = Participant::with(['training', 'certificate'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);
                if (filled($user->nip_nik)) {
                    $query->orWhere('nip_nik', $user->nip_nik);
                }
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('training', function ($training) use ($search) {
                    $training->where('nama_pelatihan', 'like', '%'.$search.'%')
                        ->orWhere('bidang', 'like', '%'.$search.'%')
                        ->orWhere('model', 'like', '%'.$search.'%')
                        ->orWhereYear('tgl_mulai', is_numeric($search) ? (int) $search : 0);
                });
            })
            ->get()
            ->filter(fn ($participant) => $participant->is_core_training_complete)
            ->sortByDesc(fn ($participant) => $participant->training?->tgl_selesai)
            ->values();

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        $history = new \Illuminate\Pagination\LengthAwarePaginator(
            $historyItems->forPage($page, $perPage)->values(),
            $historyItems->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $summary = [
            'trainings' => $historyItems->count(),
            'certificates' => $historyItems->filter(fn ($participant) => filled($participant->certificate?->final_file_path))->count(),
            'latest_year' => $historyItems->first()?->training?->tgl_selesai
                ? \Carbon\Carbon::parse($historyItems->first()->training->tgl_selesai)->year
                : null,
        ];

        return view('participant.history', compact('history', 'summary', 'search'));
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
