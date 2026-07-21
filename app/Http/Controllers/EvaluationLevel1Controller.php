<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Schedule;
use App\Models\Participant;
use App\Models\Question;
use App\Models\EvaluationResultL1;
use App\Models\EvaluationFormL1; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluationLevel1Controller extends Controller
{
    /**
     * Menampilkan daftar semua pelatihan (untuk Sidebar)
     */
    public function indexAll()
    {
        $query = Training::query();
        if (Auth::user()->role !== 'superadmin') {
            $query->where('bidang', Auth::user()->bidang);
        }
        $trainings = $query->latest()->get();
        return view('evaluasi.l1_all', compact('trainings'));
    }

    public function index($id)
    {
        $training = Training::with(['schedules', 'participants'])->findOrFail($id);
        $forms = EvaluationFormL1::where('training_id', $id)->get();
        
        // Mengambil data hasil evaluasi untuk menghitung progres di tabel
        $results = EvaluationResultL1::where('training_id', $id)
            ->select('schedule_id', DB::raw('count(distinct participant_id) as total_filler'))
            ->groupBy('schedule_id')
            ->get();

        return view('evaluasi.l1_index', compact('training', 'forms', 'results'));
    }

    public function storeForm(Request $request, $id)
    {
        $request->validate([
            'type' => 'required',
            'name' => 'required',
        ]);

        $data = [
            'training_id' => $id,
            'type' => $request->type,
            'name' => $request->name,
        ];

        if ($request->type == 'narasumber') {
            $schedule = Schedule::findOrFail($request->schedule_id);
            $data['schedule_id'] = $schedule->id;
            $data['target_name'] = $schedule->pic;
            $data['materi'] = $schedule->activity;
        } else {
            $data['target_name'] = $request->instansi_penyelenggara;
        }

        EvaluationFormL1::create($data);

        return redirect()->back()->with('success', 'Form Evaluasi berhasil dibuat.');
    }

    public function destroyForm($id)
    {
        $form = EvaluationFormL1::findOrFail($id);
        
        // Hapus juga respon yang terkait dengan form ini agar database bersih
        EvaluationResultL1::where('training_id', $form->training_id)
            ->where('schedule_id', $form->schedule_id)
            ->delete();
            
        $form->delete();

        return redirect()->back()->with('success', 'Form Evaluasi berhasil dihapus.');
    }

    public function showProgres($id, Request $request)
    {
        $training = Training::with('participants')->findOrFail($id);
        
        // Ambil parameter sid (Schedule ID)
        $sid_param = $request->query('sid');
        $schedule_id = ($sid_param === 'null' || $sid_param === '') ? null : $sid_param;
        
        // Cari data jadwal jika sid tidak null
        $schedule = $schedule_id ? Schedule::find($schedule_id) : null;

        // Ambil ID peserta yang sudah mengisi
        $filledParticipantIds = EvaluationResultL1::where('training_id', $id)
                                ->where('schedule_id', $schedule_id)
                                ->distinct('participant_id')
                                ->pluck('participant_id')
                                ->toArray();

        // PERBAIKAN: Tambahkan 'schedule' ke dalam compact
        return view('evaluasi.l1_progres', compact('training', 'filledParticipantIds', 'schedule', 'schedule_id'));
    }

    public function publicForm(Request $request, $id)
    {
        $training = Training::findOrFail($id);
        $type = $request->query('type');
        $schedule_id = $request->query('sid');
        
        // Pastikan schedule_id diproses sebagai null jika itu penyelenggara
        $sid = ($schedule_id && $schedule_id !== 'null') ? $schedule_id : null;

        // 1. Ambil ID peserta yang SUDAH mengisi UNTUK OBJEK INI SAJA
        $filledIds = EvaluationResultL1::where('training_id', $id)
                    ->where('schedule_id', $sid)
                    ->pluck('participant_id');

        // 2. Peserta yang belum mengisi (untuk dropdown)
        $participants = Participant::where('training_id', $id)
                        ->whereNotIn('id', $filledIds)->orderBy('name')->get();
        
        // 3. Peserta yang sudah mengisi (untuk list progres di kanan)
        $alreadyFilled = Participant::where('training_id', $id)
                        ->whereIn('id', $filledIds)->orderBy('name')->get();

        $category = ($type == 'penyelenggara') ? 'l1_penyelenggara' : 'l1_narasumber';
        $questions = Question::where('category', $category)->get();
        $schedule = null;
        if ($type == 'narasumber' && $sid && $sid !== 'null') {
            $schedule = Schedule::find($sid);
        }

        return view('evaluasi.l1_public_form', compact('training', 'participants', 'alreadyFilled', 'questions', 'type', 'schedule', 'sid'));
    }

    public function publicStore(Request $request, $id)
    {
        // Cek apakah data sampai di sini
        $request->validate([
            'participant_id' => 'required',
            'answers' => 'required|array',
        ]);

        $sid = ($request->schedule_id && $request->schedule_id !== '') ? $request->schedule_id : null;

        foreach ($request->answers as $q_id => $value) {
            EvaluationResultL1::create([
                'training_id'    => $id,
                'participant_id' => $request->participant_id,
                'question_id'    => $q_id,
                'schedule_id'    => $sid,
                'score'          => is_numeric($value) ? $value : null,
                'note'           => !is_numeric($value) ? $value : null,
            ]);
        }

        return redirect()->back()->with('success', 'Evaluasi berhasil disimpan!');
    }

    /**
     * SISI ADMIN: Lihat Progres Detail (Siapa saja yang sudah mengisi)
     */

    /**
     * Method Pendukung untuk Level 3 & 4 (Index All)
     */
    public function indexAllL34()
    {
        $query = Training::query();
        if (Auth::user()->role !== 'superadmin') {
            $query->where('bidang', Auth::user()->bidang);
        }
        $trainings = $query->latest()->get();
        return view('evaluasi.l34_all', compact('trainings'));
    }

}