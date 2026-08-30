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
        $training = Training::with([
            'schedules' => fn ($query) => $query
                ->whereNotNull('pengajar_id')
                ->with('pengajar')
                ->orderBy('date')
                ->orderBy('start_time'),
            'participants',
        ])->findOrFail($id);
        $forms = EvaluationFormL1::with('schedule.pengajar')
            ->where('training_id', $id)
            ->get();
        
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
            'type' => 'required|in:penyelenggara,narasumber',
            'name' => 'required|string',
            'schedule_id' => 'required_if:type,narasumber|nullable|exists:schedules,id',
        ]);

        $data = [
            'training_id' => $id,
            'type' => $request->type,
            'name' => $request->name,
        ];

        if ($request->type == 'narasumber') {
            $schedule = Schedule::with('pengajar')
                ->where('training_id', $id)
                ->whereNotNull('pengajar_id')
                ->findOrFail($request->schedule_id);
            $data['schedule_id'] = $schedule->id;
            $data['target_name'] = $schedule->pengajar->name;
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
        $schedule = $schedule_id
            ? Schedule::with('pengajar')->where('training_id', $id)->findOrFail($schedule_id)
            : null;

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
        abort_unless(in_array($type, ['penyelenggara', 'narasumber'], true), 404);
        $schedule_id = $request->query('sid');
        
        // Pastikan schedule_id diproses sebagai null jika itu penyelenggara
        $sid = ($schedule_id && $schedule_id !== 'null') ? $schedule_id : null;
        $formQuery = EvaluationFormL1::with(['training','schedule.pengajar'])->where('training_id', $id)->where('type', $type);
        $sid ? $formQuery->where('schedule_id', $sid) : $formQuery->whereNull('schedule_id');
        $activeForm = $formQuery->firstOrFail();
        $opensAt = $activeForm->opensAt();
        abort_if(!$activeForm->isOpen(), 403, 'Evaluasi ini baru dapat diisi pada '.($opensAt?->translatedFormat('d F Y, H:i') ?: 'waktu yang ditentukan').'.');

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
        $questions = Question::forTraining($training, $category)->orderBy('id')->get();
        $schedule = null;
        if ($type == 'narasumber' && $sid && $sid !== 'null') {
            $schedule = Schedule::with('pengajar')
                ->where('training_id', $id)
                ->whereNotNull('pengajar_id')
                ->findOrFail($sid);
        }
        abort_if($type === 'narasumber' && !$schedule, 404);

        return view('evaluasi.l1_public_form', compact('training', 'participants', 'alreadyFilled', 'questions', 'type', 'schedule', 'sid'));
    }

    public function publicStore(Request $request, $id)
    {
        // Cek apakah data sampai di sini
        $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'answers' => 'required|array',
        ]);

        $sid = ($request->schedule_id && $request->schedule_id !== '') ? $request->schedule_id : null;
        $training = Training::findOrFail($id);
        Participant::where('training_id', $id)->findOrFail($request->participant_id);

        if ($sid) {
            Schedule::where('training_id', $id)->findOrFail($sid);
        }
        $type = $sid ? 'narasumber' : 'penyelenggara';
        $formQuery = EvaluationFormL1::with(['training','schedule.pengajar'])->where('training_id', $id)->where('type', $type);
        $sid ? $formQuery->where('schedule_id', $sid) : $formQuery->whereNull('schedule_id');
        $activeForm = $formQuery->firstOrFail();
        $opensAt = $activeForm->opensAt();
        abort_if(!$activeForm->isOpen(), 403, 'Evaluasi ini baru dapat diisi pada '.($opensAt?->translatedFormat('d F Y, H:i') ?: 'waktu yang ditentukan').'.');

        $category = $sid ? 'l1_narasumber' : 'l1_penyelenggara';
        $applicableQuestions = Question::forTraining($training, $category)->get();
        $allowedQuestions = $applicableQuestions
            ->whereIn('id', array_keys($request->answers))
            ->pluck('id')
            ->mapWithKeys(fn ($questionId) => [(string) $questionId => true]);

        foreach ($applicableQuestions->where('type', 'checkbox') as $checkboxQuestion) {
            if (empty($request->input('answers.' . $checkboxQuestion->id, []))) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'answers.' . $checkboxQuestion->id => 'Pilih minimal satu jawaban pada pertanyaan checkbox.',
                ]);
            }
        }

        foreach ($request->answers as $q_id => $value) {
            if (!$allowedQuestions->has((string) $q_id)) {
                continue;
            }
            $isMultipleChoice = is_array($value);
            $storedValue = $isMultipleChoice
                ? json_encode(array_values(array_filter($value)), JSON_UNESCAPED_UNICODE)
                : $value;
            EvaluationResultL1::create([
                'training_id'    => $id,
                'participant_id' => $request->participant_id,
                'question_id'    => $q_id,
                'schedule_id'    => $sid,
                'score'          => !$isMultipleChoice && is_numeric($storedValue) ? $storedValue : null,
                'note'           => $isMultipleChoice || !is_numeric($storedValue) ? $storedValue : null,
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

    public function exportExcel($form_id)
    {
        $form = \App\Models\EvaluationFormL1::with(['training', 'schedule.pengajar'])->findOrFail($form_id);
        if ($form->type === 'narasumber' && $form->schedule?->pengajar) {
            // Menjaga form lama yang target_name-nya masih berisi PIC.
            $form->target_name = $form->schedule->pengajar->name;
        }
        $export = new \App\Exports\EvaluationL1Export($form);
        
        $prefix = ($form->type == 'penyelenggara') ? 'REKAP_L1_PENYELENGGARA_' : 'REKAP_L1_NARASUMBER_';
        $fileName = $prefix . time() . '.xlsx';

        // --- PROSES AUTO ARCHIVE ---
        $fileContent = \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        \App\Http\Controllers\DocumentController::archiveInternal($form->training_id, 'REKAP EVALUASI L1', $fileName, $fileContent, 'xlsx');

        return response()->streamDownload(function() use($fileContent) { echo $fileContent; }, $fileName);
    }

}
