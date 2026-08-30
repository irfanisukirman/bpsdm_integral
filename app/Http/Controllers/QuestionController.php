<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use App\Imports\QuestionImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $bidangOptions = $this->bidangOptions();
        $isSuperadmin = $user->role === 'superadmin';
        $selectedBidang = $isSuperadmin ? $request->query('bidang') : $user->bidang;
        $programOptions = ['semua', 'PKTI/PKTU', 'CPNS', 'PKP', 'PKA', 'PKN'];
        $selectedProgram = $request->query('program');
        if ($selectedProgram && !in_array($selectedProgram, $programOptions, true)) {
            abort(404);
        }

        if ($selectedBidang && !in_array($selectedBidang, $bidangOptions, true)) {
            abort(404);
        }

        $questions = $selectedBidang
            ? $this->evaluationQuestions()
                ->where('bidang', $selectedBidang)
                ->when($selectedProgram, fn ($query) => $query->where('program_evaluasi', $selectedProgram))
                ->latest()->get()
            : collect();

        $counts = $this->evaluationQuestions()
            ->selectRaw('bidang, COUNT(*) as total')
            ->groupBy('bidang')
            ->pluck('total', 'bidang');
        $bundleStats = collect($bidangOptions)->map(fn ($bidang) => [
            'bidang' => $bidang,
            'total' => (int) ($counts[$bidang] ?? 0),
            'l1' => $this->evaluationQuestions()->where('bidang', $bidang)->where('category', 'like', 'l1_%')->count(),
            'l34' => $this->evaluationQuestions()->where('bidang', $bidang)->where('category', 'like', 'l34_%')->count(),
        ]);

        return view('questions.index', compact(
            'questions', 'bidangOptions', 'isSuperadmin', 'selectedBidang', 'bundleStats',
            'programOptions', 'selectedProgram'
        ));
    }

    public function duplicateBundle(Request $request)
    {
        abort_unless(Auth::user()->role === 'superadmin', 403);
        $options = $this->bidangOptions();
        $data = $request->validate([
            'source_bidang' => ['required', Rule::in($options)],
            'target_bidang' => ['required', 'different:source_bidang', Rule::in($options)],
        ]);

        $sourceQuestions = $this->evaluationQuestions()
            ->where('bidang', $data['source_bidang'])
            ->orderBy('id')
            ->get();

        if ($sourceQuestions->isEmpty()) {
            return back()->with('error', 'Bidang sumber belum memiliki bundel pertanyaan evaluasi.');
        }

        $targetKeys = $this->evaluationQuestions()
            ->where('bidang', $data['target_bidang'])
            ->get()
            ->mapWithKeys(fn (Question $question) => [$this->duplicateKey($question) => true]);

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($sourceQuestions, $data, $targetKeys, &$created, &$skipped) {
            foreach ($sourceQuestions as $source) {
                $key = $this->duplicateKey($source);
                if ($targetKeys->has($key)) {
                    $skipped++;
                    continue;
                }

                $copy = $source->replicate();
                $copy->bidang = $data['target_bidang'];
                $copy->training_type = $data['target_bidang'];
                $copy->training_id = null;
                $copy->save();
                $targetKeys->put($key, true);
                $created++;
            }
        });

        return redirect()->route('questions.index', ['bidang' => $data['target_bidang']])
            ->with('success', $created . ' pertanyaan berhasil diduplikasi. ' . $skipped . ' pertanyaan identik dilewati.');
    }

    public function duplicateQuestion(Request $request, Question $question)
    {
        abort_unless(Auth::user()->role === 'superadmin', 403);

        $copy = $question->replicate();
        $copy->bidang = $question->bidang;
        $copy->training_type = $question->bidang;
        $copy->training_id = null;
        $copy->save();

        return redirect()->route('questions.index', ['bidang' => $question->bidang])
            ->with('success', 'Pertanyaan berhasil diduplikasi di bidang yang sama.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bidang'        => Auth::user()->role === 'superadmin' ? ['required', 'string', Rule::in($this->bidangOptions())] : ['nullable'],
            'category'      => ['required', Rule::in(['l1_penyelenggara', 'l1_narasumber', 'l34_mandiri', 'l34_rekan', 'l34_atasan'])],
            'program_evaluasi' => ['required', Rule::in(['semua', 'CPNS', 'PKP', 'PKA', 'PKN', 'PKTI/PKTU'])],
            'sub_category'  => str_starts_with((string) $request->input('category'), 'l34_')
                ? ['required', Rule::in(['Data Diri Alumni', 'Penempatan Tugas dan Transfer Learning', 'Perubahan Perilaku', 'Dampak Pelatihan'])]
                : ['nullable'],
            'metode'        => in_array($request->input('category'), ['l1_penyelenggara', 'l1_narasumber'], true)
                ? ['required', Rule::in(['semua', 'klasikal', 'full learning', 'blended'])]
                : ['nullable', Rule::in(['semua'])],
            'type'          => ['required', Rule::in(['slider', 'text', 'dropdown', 'checkbox'])],
            'question_text' => 'required|string',
            'options'       => 'nullable|array',
            'options.*'     => 'nullable|string',
        ]);

        $data['bidang'] = Auth::user()->role === 'superadmin' ? $data['bidang'] : Auth::user()->bidang;
        abort_if(blank($data['bidang']), 422, 'Bidang akun Admin belum ditentukan.');
        $data['training_type'] = $data['bidang'];
        $data['program_evaluasi'] = str_starts_with($data['category'], 'l34_')
            ? $data['program_evaluasi']
            : 'PKTI/PKTU';
        $data['metode'] = in_array($data['category'], ['l1_penyelenggara', 'l1_narasumber'], true)
            ? strtolower($data['metode'] ?? 'semua')
            : 'semua';
        $data['sub_category'] = str_starts_with($data['category'], 'l34_')
            ? $data['sub_category']
            : null;

        // Logic: Bersihkan options jika tipe bukan dropdown
        if (!in_array($request->type, ['dropdown', 'checkbox'], true)) {
            $data['options'] = null;
        } else {
            // Hilangkan input kosong jika user menambah field tapi tidak mengisi
            $data['options'] = array_values(array_filter((array) $request->options, fn ($option) => filled($option)));
            if (empty($data['options'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'options' => 'Tambahkan minimal satu pilihan jawaban.',
                ]);
            }
        }

        Question::create($data);
        return redirect()->back()->with('success', 'Pertanyaan berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'bidang'        => Auth::user()->role === 'superadmin' ? ['required', 'string', Rule::in($this->bidangOptions())] : ['nullable'],
            'category'      => ['required', Rule::in(['l1_penyelenggara', 'l1_narasumber', 'l34_mandiri', 'l34_rekan', 'l34_atasan'])],
            'program_evaluasi' => ['required', Rule::in(['semua', 'CPNS', 'PKP', 'PKA', 'PKN', 'PKTI/PKTU'])],
            'sub_category'  => str_starts_with((string) $request->input('category'), 'l34_')
                ? ['required', Rule::in(['Data Diri Alumni', 'Penempatan Tugas dan Transfer Learning', 'Perubahan Perilaku', 'Dampak Pelatihan'])]
                : ['nullable'],
            'metode'        => in_array($request->input('category'), ['l1_penyelenggara', 'l1_narasumber'], true)
                ? ['required', Rule::in(['semua', 'klasikal', 'full learning', 'blended'])]
                : ['nullable', Rule::in(['semua'])],
            'type'          => 'required|in:slider,text,dropdown,checkbox,ya_tidak',
            'question_text' => 'required|string',
            'options'       => 'nullable|array',
            'options.*'     => 'nullable|string',
        ]);

        $question = Question::findOrFail($id);
        $this->authorizeQuestion($question);
        $data['bidang'] = Auth::user()->role === 'superadmin' ? $data['bidang'] : Auth::user()->bidang;
        $data['training_type'] = $data['bidang'];
        $data['program_evaluasi'] = str_starts_with($data['category'], 'l34_')
            ? $data['program_evaluasi']
            : 'PKTI/PKTU';
        $data['metode'] = in_array($data['category'], ['l1_penyelenggara', 'l1_narasumber'], true)
            ? strtolower($data['metode'] ?? 'semua')
            : 'semua';
        $data['sub_category'] = str_starts_with($data['category'], 'l34_')
            ? $data['sub_category']
            : null;

        if (!in_array($request->type, ['dropdown', 'checkbox'], true)) {
            $data['options'] = null;
        } else {
            $data['options'] = array_values(array_filter((array) $request->options, fn ($option) => filled($option)));
            if (empty($data['options'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'options' => 'Tambahkan minimal satu pilihan jawaban.',
                ]);
            }
        }

        $question->update($data);
        return redirect()->back()->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $this->authorizeQuestion($question);
        
        // Hapus manual hasil evaluasi yang merujuk ke soal ini agar tidak error constraint
        \App\Models\EvaluationResultL1::where('question_id', $id)->delete();
        \App\Models\EvaluationResultL34::where('question_id', $id)->delete();
        
        $question->delete();
        return redirect()->back()->with('success', 'Soal dan data jawaban terkait berhasil dihapus.');
    }

    public function destroyBundle(Request $request)
    {
        $data = $request->validate([
            'bidang' => ['required', 'string', Rule::in($this->bidangOptions())],
        ]);
        $user = Auth::user();
        $bidang = $user->role === 'superadmin' ? $data['bidang'] : $user->bidang;
        abort_if(blank($bidang) || ($user->role !== 'superadmin' && $data['bidang'] !== $bidang), 403);

        $questionIds = $this->evaluationQuestions()->where('bidang', $bidang)->pluck('id');
        DB::transaction(function () use ($questionIds) {
            \App\Models\EvaluationResultL1::whereIn('question_id', $questionIds)->delete();
            \App\Models\EvaluationResultL34::whereIn('question_id', $questionIds)->delete();
            Question::whereIn('id', $questionIds)->delete();
        });

        return redirect()->route('questions.index', $user->role === 'superadmin' ? ['bidang' => $bidang] : [])
            ->with('success', $questionIds->count().' pertanyaan pada bidang tersebut berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'bidang' => ['required', 'string', Rule::in($this->bidangOptions())],
        ]);

        $defaultBidang = Auth::user()->role === 'superadmin' ? $request->input('bidang') : Auth::user()->bidang;
        abort_if(blank($defaultBidang), 422, 'Bidang akun Admin belum ditentukan.');
        abort_if(Auth::user()->role !== 'superadmin' && $request->input('bidang') !== $defaultBidang, 403);
        Excel::import(new QuestionImport($defaultBidang), $request->file('file'));

        return redirect()->route('questions.index', Auth::user()->role === 'superadmin' ? ['bidang' => $defaultBidang] : [])
            ->with('success', 'Bank soal berhasil diimpor ke '.$defaultBidang.'.');
    }

    public function downloadTemplate(Request $request)
    {
        $bidang = Auth::user()->role === 'superadmin' ? $request->query('bidang') : Auth::user()->bidang;
        abort_if(blank($bidang) || !in_array($bidang, $this->bidangOptions(), true), 422, 'Pilih bidang terlebih dahulu.');
        $header = [
            ['bidang', 'metode', 'program_evaluasi', 'level_peran', 'sub_kategori', 'tipe_jawaban', 'pertanyaan', 'pilihan_jawaban'],
            [$bidang, 'klasikal', 'PKTI/PKTU', 'Penyelenggara', '', 'slider', 'Bagaimana kualitas penyelenggaraan pelatihan?', ''],
            [$bidang, 'semua', 'PKTI/PKTU', 'Narasumber', '', 'slider', 'Bagaimana penguasaan materi narasumber?', ''],
        ];
        foreach ($bidang === 'Bidang Pengembangan Kompetensi Manajerial' ? ['CPNS', 'PKP', 'PKA', 'PKN'] : ['PKTI/PKTU'] as $program) {
            $header[] = [$bidang, 'semua', $program, 'Mandiri', 'Perubahan Perilaku', 'slider', 'Pertanyaan '.$program.' tentang perubahan perilaku...', ''];
            $header[] = [$bidang, 'semua', $program, 'Mandiri', 'Dampak Pelatihan', 'checkbox', 'Pertanyaan '.$program.' tentang dampak pelatihan...', 'Produktivitas meningkat, Kualitas kerja meningkat'];
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new class($header) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        }, 'template_bank_soal_'.\Illuminate\Support\Str::slug($bidang, '_').'.xlsx');
    }

    private function authorizeQuestion(Question $question): void
    {
        $user = Auth::user();
        abort_if($user->role !== 'superadmin' && $question->bidang !== $user->bidang, 403);
    }

    private function evaluationQuestions()
    {
        return Question::query()
            ->where('category', 'not like', '%monitoring%')
            ->where('category', 'not like', '%l2%')
            ->where('type', '!=', 'ya_tidak');
    }

    private function duplicateKey(Question $question): string
    {
        return implode('|', [
            $question->category,
            strtolower((string) $question->metode),
            strtolower((string) $question->program_evaluasi),
            (string) $question->sub_category,
            $question->type,
            trim($question->question_text),
            json_encode(array_values($question->options ?? []), JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function bidangOptions(): array
    {
        return collect([
            'Semua Bidang',
            'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan',
            'Bidang Pengembangan Kompetensi Teknis Inti',
            'Bidang Pengembangan Kompetensi Teknis Umum',
            'Bidang Pengembangan Kompetensi Manajerial',
        ])->merge(\App\Models\Training::whereNotNull('bidang')->pluck('bidang'))
            ->merge(\App\Models\User::where('role', 'admin_bidang')->whereNotNull('bidang')->pluck('bidang'))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
