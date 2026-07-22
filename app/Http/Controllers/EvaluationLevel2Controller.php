<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Participant;
use App\Models\EvaluationResultL2;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParticipantScoreImport;
use Maatwebsite\Excel\Concerns\FromArray;

class EvaluationLevel2Controller extends Controller
{
    public function index($id)
    {
        $training = Training::findOrFail($id);
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
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new ParticipantScoreImport($id), $request->file('file'));

        return redirect()->back()->with('success', 'Nilai Pretest & Posttest berhasil diimport.');
    }

    public function downloadTemplate($id)
    {
        $training = Training::findOrFail($id);
        
        // Ambil semua peserta di pelatihan ini beserta nilai L2 jika sudah ada
        $participants = Participant::with('evaluationL2')
            ->where('training_id', $id)
            ->orderBy('name', 'asc')
            ->get();

        // Siapkan Header Excel
        $data = [
            ['nip_nik', 'nama_lengkap', 'nilai_pretest', 'nilai_posttest']
        ];

        // Masukkan data peserta ke dalam baris Excel
        foreach ($participants as $p) {
            $data[] = [
                "'" . $p->nip_nik, // Gunakan tanda kutip satu agar NIP tidak error di excel
                $p->name,
                $p->evaluationL2->pretest ?? '', // Kosongkan jika belum ada nilai
                $p->evaluationL2->postest ?? '', // Kosongkan jika belum ada nilai
            ];
        }

        $fileName = 'Template_Nilai_L2_' . str_replace(' ', '_', $training->nama_pelatihan) . '.xlsx';

        return Excel::download(new class($data) implements FromArray {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        }, $fileName);
    }

    public function indexAll()
    {
        $trainings = Training::latest()->get();
        return view('evaluasi.l2_all', compact('trainings'));
    }
}