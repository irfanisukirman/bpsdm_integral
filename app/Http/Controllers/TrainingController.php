<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Schedule;
use App\Models\Participant;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParticipantImport;
use App\Exports\TrainingEvaluationExport;
use Maatwebsite\Excel\Concerns\FromArray;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Folder;
use App\Helpers\LogHelper;
use App\Exports\ParticipantTemplateExport;
use App\Exports\ParticipantExport;


class TrainingController extends Controller
{
    public function index()
    {
        $query = Training::with('schedules');

        // Jika bukan superadmin, filter berdasarkan bidang user
        if (Auth::user()->role !== 'superadmin') {
            $query->where('bidang', Auth::user()->bidang);
        }

        $trainings = $query->latest()->get();
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
            'bidang' => 'required',
            'model' => 'required',
            'lokasi' => 'required',
            'angkatan' => 'required',
            'jumlah_peserta' => 'required|numeric',
            'jp' => 'required|numeric',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date',
        ];
        if ($request->model === 'standar') {
            $rules['metode'] = 'required';
        }
        $data = $request->validate($rules);

        if ($request->model === 'blended') {
            $data['metode'] = 'blended';
        }

        // 2. Simpan Data Pelatihan
        $training = Training::create($data);

        // 3. LOGIKA OTOMATIS: BUAT FOLDER DOKUMEN
        Folder::create([
            'training_id' => $training->id,
            'name' => $training->nama_pelatihan . ' - Angkatan ' . $training->angkatan,
            'bidang' => $training->bidang,
            'user_id' => Auth::id(),
            'parent_id' => null, // Jadi folder utama di bidang tersebut
            'is_public' => false, // Default private
        ]);

        // 4. Simpan Tahapan (Jika Blended)
        if ($request->model === 'blended' && $request->has('stages')) {
            foreach ($request->stages as $stage) {
                $training->stages()->create([
                    'nama_tahapan' => $stage['nama'],
                    'metode' => $stage['metode'],
                    'tgl_mulai' => $stage['mulai'],
                    'tgl_selesai' => $stage['selesai'],
                ]);
            }
        }

        LogHelper::record('Pelatihan', 'Membuat pelatihan & folder dokumen: ' . $training->nama_pelatihan);

        return redirect()->route('trainings.index')->with('success', 'Pelatihan dan Folder Dokumen berhasil dibuat.');
    }

    public function storeParticipant(Request $request, $id)
    {
        $request->validate([
            'nip_nik' => 'required|string|unique:participants,nip_nik,NULL,id,training_id,' . $id,
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

        \App\Models\Participant::create([
            'training_id' => $id,
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

        return redirect()->back()->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function showParticipants(Request $request, $id)
    {
        $training = Training::findOrFail($id);

        // Tangkap input search dari URL (?search=...)
        $search = $request->query('search');

        // Ambil peserta yang terhubung dengan pelatihan ini dengan filter pencarian
        $participants = \App\Models\Participant::where('training_id', $id)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%")
                        ->orWhere('nip_nik', 'LIKE', "%$search%")
                        ->orWhere('instansi', 'LIKE', "%$search%");
                });
            })
            ->get();

        return view('trainings.participants', compact('training', 'participants', 'search'));
    }

    public function updateParticipant(Request $request, $id)
    {
        $participant = \App\Models\Participant::findOrFail($id);

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
        Participant::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Peserta berhasil dihapus.');
    }

    public function showSchedules($id)
    {
        $training = Training::findOrFail($id);
        $schedules = Schedule::where('training_id', $id)->orderBy('date')->get();
        return view('trainings.schedules', compact('training', 'schedules'));
    }

    public function importParticipants(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        // KIRIM $id KE CONSTRUCTOR IMPORT
        Excel::import(new ParticipantImport($id), $request->file('file'));

        return redirect()->back()->with('success', 'Data peserta berhasil diimport.');
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new ParticipantTemplateExport(),
            'template_peserta_integral.xlsx'
        );
    }

    public function storeSchedule(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'activity' => 'required|string|max:255',
            'pic' => 'required|string|max:255', // Penanggung Jawab
        ]);

        Schedule::create([
            'training_id' => $id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'activity' => $request->activity,
            'pic' => $request->pic,
        ]);

        return redirect()->back()->with('success', 'Sesi jadwal berhasil ditambahkan.');
    }

    public function destroySchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->back()->with('success', 'Sesi jadwal berhasil dihapus.');
    }

    public function downloadSchedulePdf($id)
    {
        $training = Training::with('schedules')->findOrFail($id);
        $schedules = $training->schedules()->orderBy('date')->orderBy('start_time')->get();

        $pdf = Pdf::loadView('trainings.pdf_schedule', compact('training', 'schedules'));
        $pdf->setPaper('a4', 'landscape');

        // --- PROSES AUTO ARCHIVE ---
        $fileName = "JADWAL_PELATIHAN_" . str_replace(' ', '_', $training->nama_pelatihan) . ".pdf";
        $fileContent = $pdf->output(); // Ambil string binary PDF

        \App\Http\Controllers\DocumentController::archiveInternal(
            $training->id,
            'JADWAL PELATIHAN',
            $fileName,
            $fileContent,
            'pdf'
        );

        return response()->streamDownload(function () use ($fileContent) {
            echo $fileContent;
        }, $fileName);
    }

    public function edit($id)
    {
        // Eager load stages untuk model blended
        $training = Training::with('stages')->findOrFail($id);
        $model = $training->model; // otomatis terdeteksi dari database

        return view('trainings.edit', compact('training', 'model'));
    }

    public function update(Request $request, $id)
    {
        $training = Training::findOrFail($id);

        $rules = [
            'nama_pelatihan' => 'required',
            'bidang' => 'required',
            'lokasi' => 'required',
            'angkatan' => 'required',
            'jumlah_peserta' => 'required|numeric',
            'jp' => 'required|numeric',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date',
        ];

        if ($training->model === 'standar') {
            $rules['metode'] = 'required';
        }

        $data = $request->validate($rules);

        // Update data utama
        $training->update($request->all());

        Folder::where('training_id', $training->id)->update([
            'name' => $training->nama_pelatihan . ' - Angkatan ' . $training->angkatan
        ]);

        // Jika model blended, update tahapan
        if ($training->model === 'blended' && $request->has('stages')) {
            // Hapus tahapan lama, ganti dengan yang baru dari form (Simple Approach)
            $training->stages()->delete();
            foreach ($request->stages as $stage) {
                $training->stages()->create([
                    'nama_tahapan' => $stage['nama'],
                    'metode' => $stage['metode'],
                    'tgl_mulai' => $stage['mulai'],
                    'tgl_selesai' => $stage['selesai'],
                ]);
            }
        }

        return redirect()->route('trainings.index')->with('success', 'Data pelatihan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $training = Training::findOrFail($id);

        // Karena kita pakai onDelete('cascade') di migration, 
        // maka jadwal, peserta, dan tahapan akan terhapus otomatis.
        $training->delete();

        return redirect()->route('trainings.index')->with('success', 'Pelatihan berhasil dihapus dari sistem.');
    }

    public function exportEvaluation($id)
    {
        $training = Training::findOrFail($id);
        $export = new \App\Exports\TrainingEvaluationExport($training);
        $fileName = 'HASIL_EVALUASI_L1_L2_' . str_replace(' ', '_', $training->nama_pelatihan) . '.xlsx';

        // --- PROSES AUTO ARCHIVE ---
        $fileContent = \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        \App\Http\Controllers\DocumentController::archiveInternal($training->id, 'HASIL EVALUASI L1 L2', $fileName, $fileContent, 'xlsx');

        return response()->streamDownload(function () use ($fileContent) {
            echo $fileContent; }, $fileName);
    }

    public function updateSchedule(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'activity' => 'required|string|max:255',
            'pic' => 'required|string|max:255',
        ]);

        $schedule = Schedule::findOrFail($id);
        $schedule->update($request->all());

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function generateNewCode($id)
    {
        $training = Training::findOrFail($id);
        $training->update(['invitation_code' => strtoupper(\Illuminate\Support\Str::random(6))]);
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
        // Eager load data untuk menghindari query berulang
        $training = Training::withCount('participants')->with(['schedules'])->findOrFail($id);

        // Ambil data evaluasi L1 untuk pengecekan status di Hub jika diperlukan
        $formsL1 = \App\Models\EvaluationFormL1::where('training_id', $id)->get();

        return view('trainings.manage', compact('training', 'formsL1'));
    }

    public function exportParticipants($id)
    {
        $training = Training::findOrFail($id);
        $export = new ParticipantExport($id);
        $fileName = 'DATA_PESERTA_' . str_replace(' ', '_', $training->nama_pelatihan) . '.xlsx';

        // --- PROSES AUTO ARCHIVE ---
        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        \App\Http\Controllers\DocumentController::archiveInternal($id, 'DATA PESERTA PELATIHAN', $fileName, $fileContent, 'xlsx');

        return response()->streamDownload(function () use ($fileContent) {
            echo $fileContent;
        }, $fileName);
    }
}