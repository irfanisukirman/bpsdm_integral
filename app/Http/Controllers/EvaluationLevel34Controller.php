<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Participant;
use App\Models\Question;
use App\Models\EvaluationResultL34;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AlumniProfile;
use App\Exports\EvaluationL34Export; // Import di bagian atas
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;


class EvaluationLevel34Controller extends Controller
{
    public function indexAll()
    {
        $query = Training::query();

        // Probis: Admin Bidang hanya melihat pelatihan bidangnya sendiri
        if (Auth::user()->role !== 'superadmin') {
            $query->where('bidang', Auth::user()->bidang);
        }

        $trainings = $query->latest()->get();

        return view('evaluasi.l34_all', compact('trainings'));
    }
    /**
     * SISI ADMIN: Memantau progres penilaian 360 derajat
     */
    public function index($id)
    {
        // Load pelatihan beserta peserta dan hasil evaluasi L34-nya
        $training = Training::findOrFail($id);
        $participants = Participant::where('training_id', $id)->get();

        return view('evaluasi.l34_index', compact('training', 'participants'));
    }

    /**
     * SISI PUBLIK: Halaman Gateway (Pilih Peran)
     */
    public function publicGateway($training_id)
    {
        $training = Training::findOrFail($training_id);
        return view('evaluasi.l34_public_gateway', compact('training'));
    }

    /**
     * SISI PUBLIK: Form Pengisian sesuai Peran (Mandiri/Atasan/Rekan)
     */
    public function publicForm($training_id, $role)
    {
        $training = Training::findOrFail($training_id);
        
        // 1. Ambil ID peserta yang SUDAH mengisi untuk role ini
        $filledIds = EvaluationResultL34::where('evaluator_role', $role)
            ->whereHas('participant', function($q) use ($training_id) {
                $q->where('training_id', $training_id);
            })
            ->pluck('participant_id');

        // 2. Peserta yang BELUM mengisi (Untuk Dropdown)
        $participants = Participant::where('training_id', $training_id)
            ->whereNotIn('id', $filledIds)
            ->orderBy('name', 'asc')
            ->get();

        // 3. Peserta yang SUDAH mengisi (Untuk Daftar Antrean/Progres)
        $alreadyFilled = Participant::where('training_id', $training_id)
            ->whereIn('id', $filledIds)
            ->orderBy('name', 'asc')
            ->get();

        $categorySearch = 'l34_' . strtolower($role);
        $questions = Question::where('category', $categorySearch)->get()->groupBy('sub_category');

        return view('evaluasi.l34_public_form', compact('training', 'participants', 'alreadyFilled', 'role', 'questions'));
    }

    /**
     * SISI PUBLIK: Menyimpan Hasil Penilaian 360
     */
    public function publicStore(Request $request, $training_id, $role)
    {
        $rules = [
            'participant_id' => 'required',
            'scores'         => 'required|array',
        ];

        if ($role !== 'mandiri') {
            $rules += [
                'evaluator_name' => 'required',
                'evaluator_nip'  => 'required',
            ];
        }

        $request->validate($rules);

        // Cek apakah sudah pernah mengisi (Double Input)
        $exists = EvaluationResultL34::where('participant_id', $request->participant_id)
            ->where('evaluator_role', $role)
            ->where('training_id', $training_id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Anda sudah melakukan pengisian untuk alumni ini.');
        }

        try {
            DB::beginTransaction();

            // 1. Simpan Profil jika Mandiri
            if ($role == 'mandiri') {
                AlumniProfile::updateOrCreate(
                    ['participant_id' => $request->participant_id, 'training_id' => $training_id],
                    [
                        'edu_during_training'  => $request->edu_before,
                        'edu_current'          => $request->edu_after,
                        'rank_during_training' => $request->rank_before,
                        'rank_current'         => $request->rank_after,
                        'pos_during_training'  => $request->pos_before,
                        'pos_current'          => $request->pos_after,
                        'unit_during_training' => $request->unit_before,
                        'unit_current'         => $request->unit_after,
                    ]
                );
            }

            // 2. Simpan Penempatan Tugas (Kualitatif) - MENGGUNAKAN NULL BUKAN 0
            if ($request->has('task')) {
                foreach ($request->task as $index => $val) {
                    EvaluationResultL34::create([
                        'training_id'    => $training_id,
                        'participant_id' => $request->participant_id,
                        'evaluator_role' => $role,
                        'evaluator_name' => ($role == 'mandiri') ? 'Diri Sendiri' : $request->evaluator_name,
                        'question_id'    => null, // INI PERBAIKANNYA
                        'note'           => "Penempatan Tugas ke-" . ($index+1) . ": $val",
                    ]);
                }
            }

            // 3. Simpan Jawaban Skor (Slider)
            foreach ($request->scores as $q_id => $value) {
                EvaluationResultL34::create([
                    'training_id'    => $training_id,
                    'participant_id' => $request->participant_id,
                    'evaluator_role' => $role,
                    'evaluator_name' => ($role == 'mandiri') ? 'Diri Sendiri' : $request->evaluator_name,
                    'question_id'    => $q_id,
                    'score'          => is_numeric($value) ? $value : null,
                    'note'           => !is_numeric($value) ? $value : null,
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Data berhasil dikirim ke database.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal Simpan: ' . $e->getMessage());
        }
    }

    public function exportExcel($id)
    {
        // Ambil data pelatihan
        $training = Training::findOrFail($id);

        // Nama file: Laporan_Dampak_NamaPelatihan.xlsx
        $fileName = 'Laporan_Dampak_L3_L4_' . str_replace(' ', '_', $training->nama_pelatihan) . '.xlsx';

        return Excel::download(new EvaluationL34Export($training), $fileName);
    }

    public function exportWord($id)
    {
        // Set locale agar nama bulan otomatis bahasa Indonesia
        \Carbon\Carbon::setLocale('id');

        // Eager loading agar performa cepat
        $training = Training::with(['participants.alumniProfile'])->findOrFail($id);
        
        $templatePath = public_path('templates/template_laporan_lv34.docx');
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'File template tidak ditemukan.');
        }

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);


        // --- 1. DATA INFORMASI UMUM ---
        $templateProcessor->setValue('nama_pelatihan', strtoupper($training->nama_pelatihan));
        $templateProcessor->setValue('tahunberjalan', date('Y'));
        $templateProcessor->setValue('tahunpelaksanaan', \Carbon\Carbon::parse($training->tgl_mulai)->format('Y'));
        $templateProcessor->setValue('tanggal_mulai', \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y'));
        $templateProcessor->setValue('tanggal_selesai', \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y'));
        $templateProcessor->setValue('tanggalsebarlink', $training->tgl_sebar_l34->translatedFormat('d F Y'));
        $templateProcessor->setValue('jumlah_peserta', $training->participants->count());

        // --- 2. STATISTIK RESPONDEN ---
       $results = EvaluationResultL34::where('training_id', $id)->get();
    $respondenAlumni = $results->where('evaluator_role', 'mandiri')->unique('participant_id')->count();
    $respondenAtasan = $results->where('evaluator_role', 'atasan')->unique('participant_id')->count();
    $respondenRekan = $results->where('evaluator_role', 'rekan')->unique('participant_id')->count();

    $templateProcessor->setValue('jumlah_alumni', $respondenAlumni);
    $templateProcessor->setValue('jumlah_atasan', $respondenAtasan);
    $templateProcessor->setValue('jumlah_rekan', $respondenRekan);

    // --- A. INFORMASI UMUM (PENDIDIKAN & GOLONGAN) ---
    $profiles = AlumniProfile::where('training_id', $id)->get();
    
    $eduLevels = ['S2/S3', 'D4/S1', 'D3', 'SMA/K', 'SD/SMP'];
    foreach($eduLevels as $edu) {
        $key = strtolower(str_replace(['/', ' '], '_', $edu));
        $templateProcessor->setValue('edu_'.$key.'_before', $profiles->where('edu_during_training', $edu)->count());
        $templateProcessor->setValue('edu_'.$key.'_after', $profiles->where('edu_current', $edu)->count());
    }

    $golLevels = ['IV', 'III', 'II', 'I'];
    foreach($golLevels as $gol) {
        $templateProcessor->setValue('gol_'.$gol.'_before', $profiles->filter(fn($p) => str_contains($p->rank_during_training, $gol))->count());
        $templateProcessor->setValue('gol_'.$gol.'_after', $profiles->filter(fn($p) => str_contains($p->rank_current, $gol))->count());
    }

    // Perubahan Jabatan & Unit Kerja
    $jabatanBerubah = $profiles->filter(fn($p) => $p->pos_during_training != $p->pos_current)->count();
    $unitBerubah = $profiles->filter(fn($p) => $p->unit_during_training != $p->unit_current)->count();
    
    $templateProcessor->setValue('jab_berubah', $jabatanBerubah);
    $templateProcessor->setValue('jab_tetap', $respondenAlumni - $jabatanBerubah);
    $templateProcessor->setValue('unit_berubah', $unitBerubah);
    $templateProcessor->setValue('unit_tetap', $respondenAlumni - $unitBerubah);

    // --- B. PENUGASAN (BAGIAN 2) ---
    // Logika Persentase (Jawaban "Ya")
    for ($i = 1; $i <= 4; $i++) {
        $countYa = $results->filter(fn($r) => str_contains($r->note, "Tugas ke-$i") && str_contains($r->note, 'Ya'))->count();
        $totalRes = $results->filter(fn($r) => str_contains($r->note, "Tugas ke-$i"))->count();
        $persen = ($totalRes > 0) ? round(($countYa / $totalRes) * 100, 2) : 0;
        $templateProcessor->setValue("task_{$i}_persen", $persen . '%');
    }

    // --- C. PERUBAHAN PERILAKU & DAMPAK (BAGIAN 3 & 4) ---
    $questions = Question::where('category', 'LIKE', 'l34%')->get();
    
    // Contoh: Pertanyaan "Sumber Daya" (Perilaku No 1)
    $q1Results = $results->where('question_id', $questions->where('sub_category', 'Perubahan Perilaku')->first()->id ?? 0);
    $baikCount = $q1Results->where('score', '>=', 81)->count();
    $persenKetersediaan = ($q1Results->count() > 0) ? round(($baikCount / $q1Results->count()) * 100, 2) : 0;
    $templateProcessor->setValue('persentase_ketersedaian', $persenKetersediaan . '%');

    // --- REKAPITULASI TABEL AKHIR ---
    $participants = Participant::where('training_id', $id)->orderBy('name', 'asc')->get();
    $templateProcessor->cloneRow('res_nama', $participants->count());
    foreach ($participants as $index => $p) {
        $row = $index + 1;
        $templateProcessor->setValue("res_nama#$row", $p->name . " | " . $p->nip_nik);
        $templateProcessor->setValue("res_jab#$row", $p->jabatan);
        $templateProcessor->setValue("res_m#$row", $p->hasFilledL34('mandiri') ? 'Sudah Isi' : 'Belum Isi');
        $templateProcessor->setValue("res_a#$row", $p->hasFilledL34('atasan') ? 'Sudah Isi' : 'Belum Isi');
        $templateProcessor->setValue("res_r#$row", $p->hasFilledL34('rekan') ? 'Sudah Isi' : 'Belum Isi');
    }

        // --- 3. TABEL ASAL INSTANSI ---
        $instansiData = Participant::where('training_id', $id)
            ->select('instansi', \DB::raw('count(*) as total'))
            ->groupBy('instansi')->get();

        // Pastikan variabel 'ins_nama' ada di Word
        if ($instansiData->count() > 0 && in_array('ins_nama', $templateProcessor->getVariables())) {
            $templateProcessor->cloneRow('ins_nama', $instansiData->count());
            foreach ($instansiData as $index => $ins) {
                $row = $index + 1;
                $templateProcessor->setValue("ins_nama#$row", $ins->instansi);
                $templateProcessor->setValue("ins_jml#$row", $ins->total);
            }
        }

        // --- 4. DATA PERUBAHAN PERILAKU ---
        $qSumberDaya = EvaluationResultL34::where('training_id', $id)->where('evaluator_role', 'mandiri')->where('score', '>=', 81)->count();
        $persenSumberDaya = ($respondenAlumni > 0) ? round(($qSumberDaya / ($respondenAlumni ?: 1)) * 100, 2) : 0;
        $templateProcessor->setValue('persentase_ketersedaian', $persenSumberDaya . '%');

        // --- 5. REKAP RESPONDEN (HANYA BOLEH DIPANGGIL 1 KALI) ---
        $participants = Participant::where('training_id', $id)->orderBy('name', 'asc')->get();

        // Cek apakah variabel 'res_nama' terdeteksi di dokumen Word
        if ($participants->count() > 0 && in_array('res_nama', $templateProcessor->getVariables())) {
            $templateProcessor->cloneRow('res_nama', $participants->count());
            
            foreach ($participants as $index => $p) {
                $currRow = $index + 1;
                $templateProcessor->setValue("res_nama#$currRow", $p->name . " | " . $p->nip_nik);
                $templateProcessor->setValue("res_jabatan#$currRow", $p->jabatan);
                $templateProcessor->setValue("res_instansi#$currRow", $p->instansi);
                
                // Ambil semua role yang sudah diisi
                $statusRoles = EvaluationResultL34::where('participant_id', $p->id)->pluck('evaluator_role')->toArray();

                $templateProcessor->setValue("res_m#$currRow", in_array('mandiri', $statusRoles) ? 'Sudah Isi' : 'Belum Isi');
                $templateProcessor->setValue("res_a#$currRow", in_array('atasan', $statusRoles) ? 'Sudah Isi' : 'Belum Isi');
                $templateProcessor->setValue("res_r#$currRow", in_array('rekan', $statusRoles) ? 'Sudah Isi' : 'Belum Isi');
            }
        }

        // --- 6. PROSES DOWNLOAD ---
        $filename = "Laporan_Evaluasi_L34_" . str_replace(' ', '_', $training->nama_pelatihan) . ".docx";
        
        return response()->streamDownload(function() use($templateProcessor) {
            $templateProcessor->saveAs('php://output');
        }, $filename);
    }
    
}