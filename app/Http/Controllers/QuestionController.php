<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::latest()->get();
        return view('questions.index', compact('questions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'training_type' => 'required',
            'category'      => 'required',
            'type'          => 'required|in:slider,text,dropdown,ya_tidak', // Tambahkan ya_tidak di sini
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
        Question::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Pertanyaan berhasil dihapus.');
    }
}