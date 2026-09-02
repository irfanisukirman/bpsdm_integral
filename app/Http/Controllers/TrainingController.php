<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Schedule;
use App\Models\Participant;
use App\Imports\ParticipantImport;
use App\Exports\TrainingEvaluationExport;
use App\Models\Folder;
use App\Models\File;
use App\Helpers\LogHelper; 
use App\Models\User; 
use App\Models\EvaluationFormL1;
use App\Models\Asset;
use App\Models\AssetBooking;
use App\Exports\ParticipantTemplateExport;
use App\Exports\ParticipantExport;
use App\Exports\ScheduleTemplateExport;
use App\Imports\ScheduleImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel; 
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon; 

class TrainingController extends Controller
{
    public function index()
    {
        $query = Training::with('schedules');

        // Jika bukan superadmin, filter berdasarkan bidang user
        if (Auth::user()->role !== 'superadmin') {
            $query->where('bidang', Auth::user()->bidang);
        }

        $trainings = $query->latest()->paginate(10);
        return view('trainings.index', compact('trainings'));
    }

    public function create(Request $request)
    {
        $model = $request->query('model', 'standar'); // standar atau blended
        return view('trainings.create', compact('model'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $rules = [
            'nama_pelatihan' => 'required',
            'bidang'         => 'required',
            'program_evaluasi' => 'required|in:CPNS,PKP,PKA,PKN,PKTI/PKTU',
            'model'          => 'required',
            'lokasi'         => 'required',
            'angkatan'       => 'required',
            'jumlah_peserta' => 'required|numeric',
            'jp'             => 'required|numeric',
            'tgl_mulai'      => 'required|date',
            'tgl_selesai'    => 'required|date',
        ];
        if ($request->model === 'standar') {
            $rules['metode'] = 'required';
        }
        $data = $request->validate($rules);

        // Pelatihan yang dibuat Admin Bidang selalu mengikuti bidang akunnya.
        if (Auth::user()->role === 'admin_bidang') {
            abort_if(blank(Auth::user()->bidang), 422, 'Bidang akun Admin belum ditentukan.');
            $data['bidang'] = Auth::user()->bidang;
        }
        if ($data['bidang'] !== 'Bidang Pengembangan Kompetensi Manajerial') {
            $data['program_evaluasi'] = 'PKTI/PKTU';
        }

        if ($request->model === 'blended') {
            $data['metode'] = 'blended';
        }

        // 2. Simpan Data Pelatihan
        $data['created_by'] = Auth::id();
        $training = Training::create($data);

        // 3. LOGIKA OTOMATIS: BUAT FOLDER DOKUMEN
        Folder::create([
            'training_id' => $training->id,
            'name'        => $training->nama_pelatihan . ' - Angkatan ' . $training->angkatan,
            'bidang'      => $training->bidang,
            'user_id'     => Auth::id(),
            'parent_id'   => null,
            'is_public'   => false,
        ]);

        // 4. Simpan Tahapan (Jika Blended)
        if ($request->model === 'blended' && $request->has('stages')) {
            foreach ($request->stages as $stage) {
                $training->stages()->create([
                    'nama_tahapan' => $stage['nama'],
                    'metode'       => $stage['metode'],
                    'tgl_mulai'    => $stage['mulai'],
                    'tgl_selesai'  => $stage['selesai'],
                ]);
            }
        }

        LogHelper::record('Pelatihan', 'Membuat pelatihan & folder dokumen: ' . $training->nama_pelatihan);

        return redirect()->route('trainings.index')->with('success', 'Pelatihan dan Folder Dokumen berhasil dibuat.');
    }

    public function storeParticipant(Request $request, $id)
    {
        $data = $request->validate(['user_id' => 'required|exists:users,id']);
        $user = User::where('role', 'participant')->findOrFail($data['user_id']);

        $alreadyRegistered = Participant::where('training_id', $id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('nip_nik', $user->nip_nik);
            })->exists();
        if ($alreadyRegistered) {
            return back()->with('error', 'User tersebut sudah terdaftar pada pelatihan ini.');
        }

        Participant::create([
            'training_id' => $id,
            'user_id' => $user->id,
            'nip_nik' => $user->nip_nik,
            'name' => $user->name,
            'gender' => $user->gender,
            'phone' => $user->whatsapp,
            'jabatan' => $user->jabatan,
            'instansi' => $user->instansi,
            'provinsi' => $user->provinsi,
            'kota' => $user->kota,
            'kecamatan' => $user->kecamatan,
            'kelurahan' => $user->kelurahan,
            'status_kepegawaian' => $user->status_kepegawaian,
            'registration_status' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function showParticipants(Request $request, $id)
    {
        $training = Training::findOrFail($id);
        $search = $request->query('search');

        $participants = Participant::with('user')
            ->where('training_id', $id)
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('nip_nik', 'LIKE', "%$search%")
                    ->orWhere('instansi', 'LIKE', "%$search%");
                });
            })
            ->latest()
            ->paginate(10); // <--- UBAH DARI get() MENJADI paginate(10)

        $registeredUserIds = Participant::where('training_id', $id)->whereNotNull('user_id')->pluck('user_id');
        $registeredNips = Participant::where('training_id', $id)->whereNotNull('nip_nik')->pluck('nip_nik');
        $availableUsers = User::where('role', 'participant')
            ->whereNotNull('nip_nik')->where('nip_nik', '!=', '')
            ->whereNotIn('id', $registeredUserIds)
            ->whereNotIn('nip_nik', $registeredNips)
            ->orderBy('name')->get(['id', 'name', 'nip_nik', 'instansi']);

        $pendingParticipantsCount = Participant::where('training_id', $id)
            ->where('registration_status', 'pending')->count();

        return view('trainings.participants', compact('training', 'participants', 'search', 'availableUsers', 'pendingParticipantsCount'));
    }

    public function updateParticipant(Request $request, $id)
    {
        $participant = Participant::findOrFail($id);

        $request->validate([
            'nip_nik' => 'required|string|unique:participants,nip_nik,' . $id . ',id,training_id,' . $participant->training_id,
            'name' => 'required|string|max:255',
            'gender' => 'required',
            'phone' => 'required|string|max:20',
            'jabatan' => 'required',
            'instansi' => 'required',
            'provinsi' => 'required',
            'kota' => 'required', // Disesuaikan
            'kecamatan' => 'required', // Ditambahkan
            'kelurahan' => 'required', // Ditambahkan
            'status_kepegawaian' => 'required',
        ]);

        $participant->update([
            'nip_nik' => ltrim($request->nip_nik, "'"),
            'name' => $request->name,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'jabatan' => $request->jabatan,
            'instansi' => $request->instansi,
            'provinsi' => $request->provinsi,
            'kota' => $request->kota, // Disesuaikan
            'kecamatan' => $request->kecamatan, // Ditambahkan
            'kelurahan' => $request->kelurahan, // Ditambahkan
            'status_kepegawaian' => $request->status_kepegawaian,
        ]);

        return redirect()->back()->with('success', 'Data peserta berhasil diperbarui.');
    }

    public function destroyParticipant($id)
    {
        $participant = Participant::with('training')->findOrFail($id);
        $user = Auth::user();
        abort_unless(
            $user->role === 'superadmin'
            || ($user->role === 'admin_bidang' && $participant->training?->bidang === $user->bidang),
            403
        );

        $fileIds = collect([
            $participant->biodata_file_id,
            $participant->surat_tugas_file_id,
            $participant->pas_foto_file_id,
        ])->filter()->unique()->values();
        $documentFiles = File::whereIn('id', $fileIds)->get();
        $participantName = $participant->name;
        $trainingName = $participant->training?->nama_pelatihan;

        DB::transaction(function () use ($participant, $fileIds) {
            DB::table('alumni_profiles')->where('participant_id', $participant->id)->delete();
            DB::table('attendances')->where('participant_id', $participant->id)->delete();
            DB::table('evaluation_results_l1')->where('participant_id', $participant->id)->delete();
            DB::table('evaluation_results_l2')->where('participant_id', $participant->id)->delete();
            DB::table('evaluation_results_l34')->where('participant_id', $participant->id)->delete();

            $participant->delete();

            foreach ($fileIds as $fileId) {
                $stillUsed = Participant::where('biodata_file_id', $fileId)
                    ->orWhere('surat_tugas_file_id', $fileId)
                    ->orWhere('pas_foto_file_id', $fileId)
                    ->exists();
                if (!$stillUsed) {
                    File::whereKey($fileId)->delete();
                }
            }
        });

        foreach ($documentFiles as $file) {
            if (!File::whereKey($file->id)->exists() && Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
        }

        LogHelper::record('Peserta', 'Menghapus kepesertaan '.$participantName.' dari pelatihan '.$trainingName.' beserta seluruh data terkait.');

        return redirect()->back()->with('success', 'Peserta dan seluruh data terkait pada pelatihan ini berhasil dihapus.');
    }

    /**
     * MENAMPILKAN JADWAL & DAFTAR PENGAJAR
     */
    public function showSchedules($id) 
    {
        $training = Training::findOrFail($id);
        
        // Eager load data relasi pengajar
        $schedules = Schedule::with(['pengajar', 'bookings.asset'])
            ->where('training_id', $id)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Hanya akun Narasumber yang telah disetujui dan memiliki akses Pengajar.
        $pengajars = User::where('role', 'pengajar')
            ->where('user_type', 'narasumber')
            ->where('user_type_status', 'approved')
            ->orderBy('name', 'asc')
            ->get();
        $assets = Asset::where('is_active', true)->orderBy('name')->get();

        return view('trainings.schedules', compact('training', 'schedules', 'pengajars', 'assets'));
    }

    public function importParticipants(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new ParticipantImport($id), $request->file('file'));

        return redirect()->back()->with('success', 'Data peserta berhasil diimport.');
    }

    public function downloadTemplate()
    {

        return Excel::download(
            new ParticipantTemplateExport(), 
            'template_peserta_integral.xlsx'
        );
    }


    /**
     * MENYIMPAN JADWAL BARU (DENGAN JP, LINK ZOOM & PENGAJAR)
     */
    public function storeSchedule(Request $request, $id)
    {
        $request->validate([
            'date'        => 'required|date',
            'start_time'  => 'required',
            'activity'    => 'required|string|max:255',
            'jp'          => 'required|integer|min:1|max:24',
            'link_zoom'   => 'nullable|url:http,https|max:500',
            'pic'         => 'required|string|max:255',
            'pengajar_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', 'pengajar')
                    ->where('user_type', 'narasumber')
                    ->where('user_type_status', 'approved')),
            ],
            'venue_type' => 'required|in:internal,external',
            'external_place' => 'nullable|string|max:255',
            'asset_ids' => 'nullable|required_if:venue_type,internal|array|min:1',
            'asset_ids.*' => 'integer|exists:assets,id',
        ]);

        if ($request->venue_type === 'external' && !$request->filled('external_place') && !$request->filled('link_zoom')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'external_place' => 'Isi tempat eksternal atau tautan Zoom/virtual meeting.',
            ]);
        }

        $endTime = Carbon::parse($request->start_time)
            ->addMinutes(((int) $request->jp) * 45)
            ->format('H:i:s');

        DB::transaction(function () use ($request, $id, $endTime) {
        $schedule = Schedule::create([
            'training_id' => $id,
            'date'        => $request->date,
            'start_time'  => $request->start_time,
            'end_time'    => $endTime,
            'activity'    => $request->activity,
            'jp'          => $request->jp,
            'link_zoom'   => $request->venue_type === 'external' ? $request->link_zoom : null,
            'pic'         => $request->pic,
            'pengajar_id' => $request->pengajar_id,
            'venue_type' => $request->venue_type,
            'external_place' => $request->venue_type === 'external' ? $request->external_place : null,
        ]);
        $this->syncScheduleAssets($schedule, $request->input('asset_ids', []));
        });

        return redirect()->back()->with('success', 'Sesi jadwal berhasil ditambahkan.');
    }

    /**
     * MEMPERBARUI SESI JADWAL (DENGAN JP, LINK ZOOM & PENGAJAR)
     */
    public function updateSchedule(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        $request->validate([
            'date'        => 'required|date',
            'start_time'  => 'required',
            'activity'    => 'required|string|max:255',
            'jp'          => 'required|integer|min:1|max:24',
            'link_zoom'   => 'nullable|url:http,https|max:500',
            'pic'         => 'required|string|max:255',
            'pengajar_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', 'pengajar')
                    ->where('user_type', 'narasumber')
                    ->where('user_type_status', 'approved')),
            ],
            'venue_type' => 'required|in:internal,external',
            'external_place' => 'nullable|string|max:255',
            'asset_ids' => 'nullable|required_if:venue_type,internal|array|min:1',
            'asset_ids.*' => 'integer|exists:assets,id',
        ]);

        if ($request->venue_type === 'external' && !$request->filled('external_place') && !$request->filled('link_zoom')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'external_place' => 'Isi tempat eksternal atau tautan Zoom/virtual meeting.',
            ]);
        }

        $endTime = Carbon::parse($request->start_time)
            ->addMinutes(((int) $request->jp) * 45)
            ->format('H:i:s');

        DB::transaction(function () use ($request, $schedule, $endTime) {
        $schedule->update([
            'date'        => $request->date,
            'start_time'  => $request->start_time,
            'end_time'    => $endTime,
            'activity'    => $request->activity,
            'jp'          => $request->jp,
            'link_zoom'   => $request->venue_type === 'external' ? $request->link_zoom : null,
            'pic'         => $request->pic,
            'pengajar_id' => $request->pengajar_id,
            'venue_type' => $request->venue_type,
            'external_place' => $request->venue_type === 'external' ? $request->external_place : null,
        ]);
        $this->syncScheduleAssets($schedule, $request->input('asset_ids', []));
        });

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroySchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->bookings()->delete();
        $schedule->delete();

        return redirect()->back()->with('success', 'Sesi jadwal berhasil dihapus.');
    }

    private function syncScheduleAssets(Schedule $schedule, array $assetIds): void
    {
        $assetIds = $schedule->venue_type === 'internal' ? array_values(array_unique($assetIds)) : [];
        $start = $schedule->date.' '.$schedule->start_time;
        $end = $schedule->date.' '.$schedule->end_time;
        foreach ($assetIds as $assetId) {
            if (AssetBooking::hasConflict((int) $assetId, $start, $end, Schedule::class, $schedule->id)) {
                $asset = Asset::find($assetId);
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'asset_ids' => 'Aset '.$asset?->name.' sudah digunakan oleh kegiatan lain pada waktu tersebut.',
                ]);
            }
        }
        $schedule->bookings()->delete();
        foreach ($assetIds as $assetId) {
            $schedule->bookings()->create(['asset_id' => $assetId, 'starts_at' => $start, 'ends_at' => $end, 'created_by' => Auth::id()]);
        }
    }

    public function downloadSchedulePdf($id)
    {
        $user = Auth::user();
        $training = Training::findOrFail($id);

        // Query dasar jadwal pelatihan
        $schedulesQuery = $training->schedules()
            ->with('pengajar')
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc');

        // PENGECEKAN ROLE PENGGUNA
        $isPengajar = ($user && $user->role === 'pengajar');

        if ($isPengajar) {
            // 1. JIKA PENGAJAR: Hanya ambil jadwal sesi yang ditugaskan ke dirinya sendiri
            $schedules = $schedulesQuery->where('pengajar_id', $user->id)->get();
            $pdfTitle  = "JADWAL MENGAJAR TENAGA PENGAJAR / FASILITATOR";
            
            // Nama file khusus pengajar
            $cleanPengajarName = strtoupper(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9\-]/', '', $user->name)));
            $fileName = "JADWAL_MENGAJAR_" . $cleanPengajarName . "_" . str_replace(' ', '_', $training->nama_pelatihan) . ".pdf";
        } else {
            // 2. JIKA ADMIN / PESERTA: Ambil seluruh sesi jadwal lengkap
            $schedules = $schedulesQuery->get();
            $pdfTitle  = "JADWAL KEGIATAN PELATIHAN";
            $fileName  = "JADWAL_PELATIHAN_" . str_replace(' ', '_', $training->nama_pelatihan) . ".pdf";
        }

        // Generate PDF
        $pdf = Pdf::loadView('trainings.pdf_schedule', compact(
            'training', 
            'schedules', 
            'isPengajar', 
            'pdfTitle', 
            'user'
        ));
        $pdf->setPaper('a4', 'landscape');

        $fileContent = $pdf->output();

        // Otomatis arsipkan ke folder dokumen HANYA jika didownload oleh Admin (dokumen master)
        if (!$isPengajar) {
            \App\Http\Controllers\DocumentController::archiveInternal(
                $training->id, 
                'JADWAL PELATIHAN', 
                $fileName, 
                $fileContent, 
                'pdf'
            );
        }

        return response()->streamDownload(function () use ($fileContent) {
            echo $fileContent;
        }, $fileName);
    }

    public function edit($id)
    {
        $training = Training::with('stages')->findOrFail($id);
        $model = $training->model;

        return view('trainings.edit', compact('training', 'model'));
    }

    public function update(Request $request, $id)
    {
        $training = Training::findOrFail($id);

        $rules = [
            'nama_pelatihan' => 'required',
            'bidang'         => 'required',
            'program_evaluasi' => 'required|in:CPNS,PKP,PKA,PKN,PKTI/PKTU',
            'lokasi'         => 'required',
            'angkatan'       => 'required',
            'jumlah_peserta' => 'required|numeric',
            'jp'             => 'required|numeric',
            'tgl_mulai'      => 'required|date',
            'tgl_selesai'    => 'required|date',
        ];

        if ($training->model === 'standar') {
            $rules['metode'] = 'required';
        }

        $data = $request->validate($rules);

        if (Auth::user()->role === 'admin_bidang') {
            abort_if(blank(Auth::user()->bidang), 422, 'Bidang akun Admin belum ditentukan.');
            $data['bidang'] = Auth::user()->bidang;
        }
        if ($data['bidang'] !== 'Bidang Pengembangan Kompetensi Manajerial') {
            $data['program_evaluasi'] = 'PKTI/PKTU';
        }

        $training->update($data);

        Folder::where('training_id', $training->id)->update([
            'name' => $training->nama_pelatihan . ' - Angkatan ' . $training->angkatan
        ]);

        if ($training->model === 'blended' && $request->has('stages')) {
            $training->stages()->delete();
            foreach ($request->stages as $stage) {
                $training->stages()->create([
                    'nama_tahapan' => $stage['nama'],
                    'metode'       => $stage['metode'],
                    'tgl_mulai'    => $stage['mulai'],
                    'tgl_selesai'  => $stage['selesai'],
                ]);
            }
        }

        return redirect()->route('trainings.index')->with('success', 'Data pelatihan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $training = Training::findOrFail($id);
        $user = Auth::user();
        abort_unless(
            $user->role === 'superadmin'
            || ($user->role === 'admin_bidang' && $training->bidang === $user->bidang),
            403
        );

        $participantIds = Participant::where('training_id', $training->id)->pluck('id');
        $scheduleIds = Schedule::where('training_id', $training->id)->pluck('id');

        // Ambil seluruh pohon folder, termasuk data lama yang training_id subfolder-nya kosong.
        $folderIds = Folder::where('training_id', $training->id)->pluck('id');
        do {
            $children = Folder::whereIn('parent_id', $folderIds)->whereNotIn('id', $folderIds)->pluck('id');
            if ($children->isNotEmpty()) {
                $folderIds = $folderIds->merge($children)->unique()->values();
            }
        } while ($children->isNotEmpty());

        $folderFiles = File::whereIn('folder_id', $folderIds)->get();
        $sessionDocuments = DB::table('pengajar_schedule_documents')->whereIn('schedule_id', $scheduleIds)->get();
        $monitoringEvidencePaths = DB::table('monitoring_results')
            ->where('training_id', $training->id)
            ->whereNotNull('evidence_file')
            ->pluck('evidence_file');
        $physicalPaths = $folderFiles->pluck('file_path')
            ->merge($sessionDocuments->flatMap(fn ($document) => [
                $document->bahan_ajar_path,
                $document->rbpmp_rp_path,
                $document->bukti_mengajar_path,
            ]))
            ->merge($monitoringEvidencePaths)
            ->filter()->unique()->values();
        $trainingName = $training->nama_pelatihan;

        DB::transaction(function () use ($training, $participantIds, $scheduleIds, $folderIds) {
            DB::table('asset_bookings')
                ->where('bookable_type', Schedule::class)
                ->whereIn('bookable_id', $scheduleIds)
                ->delete();
            DB::table('pengajar_schedule_documents')->whereIn('schedule_id', $scheduleIds)->delete();
            DB::table('attendances')->whereIn('participant_id', $participantIds)->delete();

            DB::table('evaluation_results_l1')
                ->where('training_id', $training->id)
                ->orWhereIn('participant_id', $participantIds)
                ->delete();
            DB::table('evaluation_results_l2')->whereIn('participant_id', $participantIds)->delete();
            DB::table('evaluation_results_l34')
                ->where('training_id', $training->id)
                ->orWhereIn('participant_id', $participantIds)
                ->delete();
            DB::table('alumni_profiles')
                ->where('training_id', $training->id)
                ->orWhereIn('participant_id', $participantIds)
                ->delete();

            DB::table('monitoring_results')->where('training_id', $training->id)->delete();
            DB::table('monitoring_summaries')->where('training_id', $training->id)->delete();
            DB::table('evaluation_forms')->where('training_id', $training->id)->delete();

            DB::table('training_forum_reads')->where('training_id', $training->id)->delete();
            DB::table('training_messages')->where('training_id', $training->id)->delete();
            DB::table('training_stages')->where('training_id', $training->id)->delete();

            File::whereIn('folder_id', $folderIds)->delete();
            $folders = Folder::whereIn('id', $folderIds)->get(['id', 'parent_id']);
            $depth = function ($folder) use ($folders) {
                $level = 0;
                $parentId = $folder->parent_id;
                while ($parentId && $level < 100) {
                    $level++;
                    $parentId = optional($folders->firstWhere('id', $parentId))->parent_id;
                }
                return $level;
            };
            foreach ($folders->sortByDesc($depth) as $folder) {
                Folder::whereKey($folder->id)->delete();
            }

            Participant::whereIn('id', $participantIds)->delete();
            Schedule::whereIn('id', $scheduleIds)->delete();

            $questionIds = DB::table('evaluation_questions')->where('training_id', $training->id)->pluck('id');
            DB::table('evaluation_results_l1')->whereIn('question_id', $questionIds)->delete();
            DB::table('evaluation_results_l34')->whereIn('question_id', $questionIds)->delete();
            DB::table('monitoring_results')->whereIn('question_id', $questionIds)->delete();
            DB::table('evaluation_questions')->whereIn('id', $questionIds)->delete();

            $training->delete();
        });

        foreach ($physicalPaths as $path) {
            $stillReferenced = File::where('file_path', $path)->exists()
                || DB::table('pengajars')->where('cv_path', $path)->orWhere('sertifikat_path', $path)->orWhere('surat_tugas_path', $path)->exists()
                || DB::table('pengajar_schedule_documents')->where('bahan_ajar_path', $path)->orWhere('rbpmp_rp_path', $path)->orWhere('bukti_mengajar_path', $path)->exists()
                || DB::table('monitoring_results')->where('evidence_file', $path)->exists();
            if (!$stillReferenced && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        LogHelper::record('Pelatihan', 'Menghapus pelatihan '.$trainingName.' beserta peserta, evaluasi, monitoring, jadwal, forum, dan seluruh dokumen terkait.');

        return redirect()->route('trainings.index')->with('success', 'Pelatihan dan seluruh data terkait berhasil dihapus dari sistem.');
    }

    public function exportEvaluation($id)
    {
        $training = Training::findOrFail($id);
        $export = new TrainingEvaluationExport($training);
        $fileName = 'HASIL_EVALUASI_L1_L2_' . str_replace(' ', '_', $training->nama_pelatihan) . '.xlsx';

        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        \App\Http\Controllers\DocumentController::archiveInternal($training->id, 'HASIL EVALUASI L1 L2', $fileName, $fileContent, 'xlsx');

        return response()->streamDownload(function () use ($fileContent) {
            echo $fileContent; }, $fileName);
    }

    public function generateNewCode($id) 
    {
        $training = Training::findOrFail($id);
        $training->update(['invitation_code' => strtoupper(Str::random(6))]);
        return redirect()->back()->with('success', 'Kode Undangan diperbarui: ' . $training->invitation_code);
    }

    public function setLmsLink(Request $request, $id)
    {
        $request->validate([
            'link_lms' => 'required|url'
        ], [
            'link_lms.url' => 'Format link tidak valid (gunakan http:// atau https://)'
        ]);

        $training = Training::findOrFail($id);
        $training->update(['link_lms' => $request->link_lms]);

        return redirect()->back()->with('success', 'Link LMS berhasil diperbarui.');
    }

    public function manage($id)
    {
        $training = Training::withCount('participants')->with(['schedules'])->findOrFail($id);
        // Ambil data evaluasi L1 untuk pengecekan status di Hub jika diperlukan
        $formsL1 = EvaluationFormL1::where('training_id', $id)->get();
        $monitoringFindings = \App\Models\MonitoringResult::where('training_id', $id)
            ->where('answer', 'tidak')
            ->get();
        $monitoringStats = [
            'total' => $monitoringFindings->count(),
            'open' => $monitoringFindings->whereIn('workflow_status', ['open', 'in_progress', 'rejected'])->count(),
            'submitted' => $monitoringFindings->where('workflow_status', 'submitted')->count(),
            'verified' => $monitoringFindings->where('workflow_status', 'verified')->count(),
            'overdue' => $monitoringFindings->filter(fn ($item) =>
                $item->workflow_status !== 'verified' && $item->due_date && $item->due_date->isPast()
            )->count(),
        ];
        return view('trainings.manage', compact('training', 'formsL1', 'monitoringStats'));
    }

    public function exportParticipants($id)
    {
        $training = Training::findOrFail($id);
        $export = new ParticipantExport($id);
        $fileName = 'DATA_PESERTA_' . str_replace(' ', '_', $training->nama_pelatihan) . '.xlsx';

        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        \App\Http\Controllers\DocumentController::archiveInternal($id, 'DATA PESERTA PELATIHAN', $fileName, $fileContent, 'xlsx');

        return response()->streamDownload(function () use ($fileContent) {
            echo $fileContent;
        }, $fileName);
    }

    /**
     * Menampilkan daftar pelatihan dan jadwal sesi yang diajar oleh Pengajar yang login
     */
    public function pengajarSchedules()
    {
        $user = Auth::user();

        abort_unless(
            $user?->canAccessNarasumberPortal(),
            403,
            'Anda belum memiliki akses sebagai pengajar.'
        );

        // Ambil hanya pelatihan dan sesi yang ditugaskan kepada akun yang sedang login.
        $myTrainings = Training::whereHas('schedules', function ($query) use ($user) {
            $query->where('pengajar_id', $user->id);
        })->with(['schedules' => function ($query) use ($user) {
            $query->where('pengajar_id', $user->id)
                ->with(['bookings.asset', 'pengajarDocuments'])
                ->orderBy('date')
                ->orderBy('start_time');
        }])->orderByDesc('tgl_mulai')->get();

        return view('pengajar.schedule', compact('myTrainings', 'user'));
    }

    /**
     * Download Template Excel Jadwal Pelatihan
     */
    public function downloadScheduleTemplate()
    {
        return Excel::download(new ScheduleTemplateExport(), 'template_jadwal_pelatihan.xlsx');
    }

    /**
     * Import Jadwal Pelatihan dari Excel
     */
    public function importSchedules(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:5120'
        ], [
            'file.required' => 'Silakan pilih file Excel terlebih dahulu.', 
            'file.mimes'    => 'Format file harus berupa (.xlsx atau .xls).',
            'file.max'      => 'Ukuran file maksimal 5MB.'
        ]);

        Excel::import(new ScheduleImport($id), $request->file('file'));

        return redirect()->back()->with('success', 'Jadwal pelatihan berhasil diimport.');
    }
    /**
     * Menampilkan riwayat pelatihan yang telah selesai diajar oleh Pengajar
     */
    public function pengajarHistory(Request $request)
    {
        $user = Auth::user();
        $search = trim((string) $request->query('search'));

        abort_unless(
            $user?->canAccessNarasumberPortal(),
            403,
            'Anda belum memiliki akses sebagai pengajar.'
        );

        $historySchedules = Schedule::where('pengajar_id', $user->id)
            ->whereHas('training', fn ($query) => $query->whereDate('tgl_selesai', '<', now('Asia/Jakarta')->toDateString()));

        $totalJpRiwayat = (clone $historySchedules)->sum('jp');
        $totalSesiRiwayat = (clone $historySchedules)->count();
        $totalPelatihanRiwayat = (clone $historySchedules)->distinct('training_id')->count('training_id');

        $trainings = Training::whereHas('schedules', function ($query) use ($user) {
                $query->where('pengajar_id', $user->id);
            })
            ->whereDate('tgl_selesai', '<', now('Asia/Jakarta')->toDateString())
            ->when($search !== '', function ($query) use ($search, $user) {
                $query->where(function ($filter) use ($search, $user) {
                    $filter->where('nama_pelatihan', 'LIKE', "%{$search}%")
                        ->orWhere('bidang', 'LIKE', "%{$search}%")
                        ->orWhere('lokasi', 'LIKE', "%{$search}%")
                        ->orWhereHas('schedules', function ($scheduleQuery) use ($search, $user) {
                            $scheduleQuery->where('pengajar_id', $user->id)
                                ->where('activity', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->with(['schedules' => function ($query) use ($user) {
                $query->where('pengajar_id', $user->id)
                    ->with(['bookings.asset', 'pengajarDocuments'])
                    ->orderBy('date')
                    ->orderBy('start_time');
            }])
            ->latest('tgl_selesai')
            ->paginate(10)
            ->withQueryString();

        return view('pengajar.history', compact(
            'trainings', 'user', 'search', 'totalJpRiwayat', 'totalSesiRiwayat', 'totalPelatihanRiwayat'
        ));
    }


    public function approveParticipant($id)
    {
            $participant = \App\Models\Participant::findOrFail($id);
            $participant->update(['registration_status' => 'approved']);

            return redirect()->back()->with('success', 'Pendaftaran ' . $participant->name . ' telah disetujui.');
    }   

    public function approveParticipantsBulk(Request $request, $id)
    {
        Training::findOrFail($id);

        $data = $request->validate([
            'mode' => ['required', Rule::in(['selected', 'all'])],
            'participant_ids' => ['nullable', 'array'],
            'participant_ids.*' => ['integer'],
        ]);

        $query = Participant::where('training_id', $id)
            ->where('registration_status', 'pending');

        if ($data['mode'] === 'selected') {
            $participantIds = collect($data['participant_ids'] ?? [])->filter()->unique()->values();
            if ($participantIds->isEmpty()) {
                return back()->with('error', 'Pilih minimal satu peserta yang akan disetujui.');
            }
            $query->whereIn('id', $participantIds);
        }

        $approvedCount = $query->update(['registration_status' => 'approved']);

        return back()->with(
            $approvedCount > 0 ? 'success' : 'error',
            $approvedCount > 0
                ? $approvedCount . ' peserta berhasil disetujui.'
                : 'Tidak ada peserta pending yang dapat disetujui.'
        );
    }

    public function rejectParticipant($id)
    {
        $participant = \App\Models\Participant::findOrFail($id);
        $participant->update(['registration_status' => 'rejected']);

        return redirect()->back()->with('success', 'Pendaftaran ' . $participant->name . ' telah ditolak.');
    }
}
