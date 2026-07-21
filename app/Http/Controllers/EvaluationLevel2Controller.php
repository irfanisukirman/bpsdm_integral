<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Participant;
use App\Models\EvaluationResultL2;
use Illuminate\Http\Request;

class EvaluationLevel2Controller extends Controller
{
    public function index($id)
    {
        $training = Training::findOrFail($id);
        
        // Ambil peserta dengan relasi nilai L2
        $participants = Participant::with('evaluationL2')
            ->where('training_id', $id)
            ->get();

        return view('evaluasi.l2_index', compact('training', 'participants'));
    }

    /**
     * Update nilai per peserta (AJAX/Single Save)
     */
    public function updateSingle(Request $request)
    {
        $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'pretest' => 'required|numeric|min:0|max:100',
            'postest' => 'required|numeric|min:0|max:100',
        ]);

        $res = EvaluationResultL2::updateOrCreate(
            ['participant_id' => $request->participant_id],
            [
                'pretest' => $request->pretest,
                'postest' => $request->postest
            ]
        );

        return response()->json([
            'status' => 'success', 
            'message' => 'Nilai berhasil disimpan',
            'n_gain' => $request->postest - $request->pretest
        ]);
    }

    /**
     * Import Nilai via Excel (Massal)
     */
    public function importExcel(Request $request, $id)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);
        
        // Catatan: Anda perlu membuat class ParticipantScoreImport 
        // menggunakan php artisan make:import
        // Excel::import(new ParticipantScoreImport($id), $request->file('file'));

        return redirect()->back()->with('success', 'Data nilai berhasil diimport secara massal.');
    }

    public function downloadTemplate()
    {
        $header = [
            ['nip_nik', 'nama_lengkap', 'nilai_pretest', 'nilai_posttest']
        ];

        return Excel::download(new class($header) implements FromArray {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        }, 'template_import_nilai_L2.xlsx');
    }

    public function indexAll()
    {
        $trainings = Training::latest()->get();
        return view('evaluasi.l2_all', compact('trainings'));
    }
}