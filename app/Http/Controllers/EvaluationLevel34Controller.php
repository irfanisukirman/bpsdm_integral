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
    
}