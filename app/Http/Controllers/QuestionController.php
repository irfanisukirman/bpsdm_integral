<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Imports\QuestionImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::latest()->get();
        $trainings = \App\Models\Training::all(); // Ambil semua pelatihan untuk dropdown
        return view('questions.index', compact('questions', 'trainings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'training_type' => 'required',
            'training_id'   => 'nullable|exists:trainings,id', // Tangkap training_id
            'category'      => 'required',
            'type'          => 'required',
            'question_text' => 'required',
            'options'       => 'nullable|array',
        ]);

        // Logic: Bersihkan options jika tipe bukan dropdown
        if ($request->type !== 'dropdown') {
            $data['options'] = null;
        } else {
            // Hilangkan input kosong jika user menambah field tapi tidak mengisi
            $data['options'] = array_filter($request->options);
        }

        Question::create($data);
        return redirect()->back()->with('success', 'Pertanyaan berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'training_type' => 'required',
            'category'      => 'required',
            'type'          => 'required|in:slider,text,dropdown,ya_tidak',
            'question_text' => 'required',
            'options'       => 'nullable|array',
        ]);

        $question = Question::findOrFail($id);

        if ($request->type !== 'dropdown') {
            $data['options'] = null;
        } else {
            $data['options'] = array_filter($request->options);
        }

        $question->update($data);
        return redirect()->back()->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        
        // Hapus manual hasil evaluasi yang merujuk ke soal ini agar tidak error constraint
        \App\Models\EvaluationResultL1::where('question_id', $id)->delete();
        \App\Models\EvaluationResultL34::where('question_id', $id)->delete();
        
        $question->delete();
        return redirect()->back()->with('success', 'Soal dan data jawaban terkait berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        Excel::import(new QuestionImport, $request->file('file'));

        return redirect()->back()->with('success', 'Bank Soal berhasil diimport.');
    }

    public function downloadTemplate()
    {
        $header = [
            ['jenis_pelatihan', 'level_peran', 'sub_kategori', 'tipe_jawaban', 'pertanyaan', 'pilihan_jawaban'],
            ['Semua', 'Mandiri', 'Perubahan Perilaku', 'slider', 'Ybs memahami sumber daya yang diperlukan...', ''],
            ['Semua', 'Mandiri', 'Dampak Pelatihan', 'slider', 'Dampak pelatihan terhadap unit kerja', ''],
            ['Semua', 'Atasan', 'Perubahan Perilaku', 'dropdown', 'Bagaimana integritas alumni?', 'Sangat Baik, Baik, Cukup, Kurang'],
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new class($header) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        }, 'template_bank_soal_L34.xlsx');
    }
}