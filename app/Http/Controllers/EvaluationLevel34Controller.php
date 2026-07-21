<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Participant;
use App\Models\Question;
use App\Models\EvaluationResultL34;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $participants = Participant::where('training_id', $training_id)->orderBy('name')->get();
        
        // Ambil soal dari bank soal berdasarkan kategori (l34_mandiri / l34_rekan / l34_atasan)
        $category = 'l34_' . $role;
        $questions = Question::where('category', $category)
                             ->where('training_type', $training->training_type)
                             ->get();

        return view('evaluasi.l34_public_form', compact('training', 'participants', 'role', 'questions'));
    }

    /**
     * SISI PUBLIK: Menyimpan Hasil Penilaian 360
     */
    public function publicStore(Request $request, $training_id, $role)
    {
        $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'scores'         => 'required|array',
            'evaluator_name' => ($role != 'mandiri') ? 'required|string|max:255' : 'nullable',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->scores as $question_id => $score) {
                EvaluationResultL34::create([
                    'participant_id' => $request->participant_id,
                    'evaluator_role' => $role,
                    'evaluator_name' => ($role == 'mandiri') ? 'Diri Sendiri' : $request->evaluator_name,
                    'question_id'    => $question_id,
                    'score'          => $score,
                ]);
            }

            DB::commit();

            return redirect()->route('public.l34.gateway', $training_id)
                             ->with('success', 'Penilaian berhasil disimpan. Terima kasih atas partisipasi Anda.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }
}