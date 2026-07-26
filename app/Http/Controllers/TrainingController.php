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
        // 1. Definisikan Rule Validasi Dasar
        $rules = [
            'nama_pelatihan' => 'required',
            'bidang' => 'required',
            'model' => 'required|in:standar,blended',
            'lokasi' => 'required',
            'angkatan' => 'required',
            'jumlah_peserta' => 'required|numeric',
            'jp' => 'required|numeric',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date',
        ];

        // 2. Jika model standar, metode wajib diisi dari form
        if ($request->model === 'standar') {
            $rules['metode'] = 'required';
        }

        $data = $request->validate($rules);

        // 3. Jika model blended, set metode utama sebagai 'blended' 
        // karena detail metode ada di tabel stages
        if ($request->model === 'blended') {
            $data['metode'] = 'blended';
        }

        // 4. Simpan Data Utama Pelatihan
        $training = Training::create($data);

        // 5. Simpan Tahapan (Hanya jika model Blended)
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

        return redirect()->route('trainings.index')->with('success', 'Pelatihan berhasil disimpan.');
    }

    public function storeParticipant(Request $request, $id)
    {
        $request->validate([
            // Aturan: NIP harus unik di dalam training_id yang sama
            'nip_nik' => 'required|string|unique:participants,nip_nik,NULL,id,training_id,' . $id,
            'name' => 'required|string|max:255',
            'jabatan' => 'required|string',
            'instansi' => 'required|string',
        ], [
            'nip_nik.unique' => 'NIP/NIK ini sudah terdaftar di pelatihan ini.'
        ]);

        \App\Models\Participant::create([
            'training_id' => $id,
            'nip_nik'     => ltrim($request->nip_nik, "'"),
            'name'        => $request->name,
            'jabatan'     => $request->jabatan,
            'instansi'    => $request->instansi,
        ]);

        return redirect()->back()->with('success', 'Peserta berhasil ditambahkan secara manual.');
    }

    public function showParticipants($id)
    {
        $training = Training::findOrFail($id);
        
        // Ambil peserta yang terhubung dengan pelatihan ini saja
        $participants = Participant::where('training_id', $id)->get();
        
        return view('trainings.participants', compact('training', 'participants'));
    }

    public function updateParticipant(Request $request, $id)
    {
        $participant = Participant::findOrFail($id);

        $request->validate([
            // Validasi NIP unik kecuali untuk ID peserta ini sendiri dalam pelatihan yang sama
            'nip_nik' => 'required|string|unique:participants,nip_nik,' . $id . ',id,training_id,' . $participant->training_id,
            'name' => 'required|string|max:255',
            'jabatan' => 'required|string',
            'instansi' => 'required|string',
        ]);

        $participant->update([
            'nip_nik' => ltrim($request->nip_nik, "'"), // Tetap bersihkan jika ada tanda kutip
            'name' => $request->name,
            'jabatan' => $request->jabatan,
            'instansi' => $request->instansi,
        ]);

        return redirect()->back()->with('success', 'Data peserta berhasil diperbarui.');
    }

    // Tambahkan juga fungsi hapus jika belum ada
    public function destroyParticipant($id)
    {
        Participant::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Peserta berhasil dihapus.');
    }

    public function showSchedules($id) {
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
        // Header yang akan muncul di Excel
        $header = [
            ['nip_nik', 'nama_lengkap', 'jabatan', 'instansi']
        ];

        // Menggunakan anonymous class untuk membuat file excel tanpa file fisik
        return Excel::download(new class($header) implements FromArray {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        }, 'template_import_peserta.xlsx');
    }
    /**
     * MENYIMPAN JADWAL BARU (Fungsi yang hilang)
     */

    // 1. Update fungsi simpan
    public function storeSchedule(Request $request, $id)
    {
        $request->validate([
            'date'       => 'required|date',
            'start_time' => 'required',
            'end_time'   => 'required',
            'activity'   => 'required|string|max:255',
            'pic'        => 'required|string|max:255', // Penanggung Jawab
        ]);

        Schedule::create([
            'training_id' => $id,
            'date'        => $request->date,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'activity'    => $request->activity,
            'pic'         => $request->pic,
        ]);

        return redirect()->back()->with('success', 'Sesi jadwal berhasil ditambahkan.');
    }
    public function destroySchedule($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->back()->with('success', 'Sesi jadwal berhasil dihapus.');
    }

    // 2. Tambah fungsi download PDF
    public function downloadSchedulePdf($id)
    {
        $training = Training::with('schedules')->findOrFail($id);
        $schedules = $training->schedules()->orderBy('date')->orderBy('start_time')->get();

        $pdf = Pdf::loadView('trainings.pdf_schedule', compact('training', 'schedules'));
        
        // Set format kertas landscape agar muat banyak kolom
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('Jadwal-Pelatihan-'.$training->id.'.pdf');
    }

    public function edit($id)
    {
        // Eager load stages untuk model blended
        $training = Training::with('stages')->findOrFail($id);
        $model = $training->model; // otomatis terdeteksi dari database

        return view('trainings.edit', compact('training', 'model'));
    }

    /**
     * Memproses Update Data Pelatihan
     */
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
        $training->update($data);

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

    /**
     * Menghapus Pelatihan (Fungsi yang tadi Missing)
     */
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
        
        // Nama file yang rapi
        $fileName = 'Hasil_Evaluasi_L1_L2_' . time() . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\TrainingEvaluationExport($training), 
            $fileName
        );
    }

    public function updateSchedule(Request $request, $id)
    {
        $request->validate([
            'date'       => 'required|date',
            'start_time' => 'required',
            'end_time'   => 'required',
            'activity'   => 'required|string|max:255',
            'pic'        => 'required|string|max:255',
        ]);

        $schedule = Schedule::findOrFail($id);
        $schedule->update($request->all());

        return redirect()->back()->with('success', 'Jadwal berhasil diperbarui.');
    }
}
