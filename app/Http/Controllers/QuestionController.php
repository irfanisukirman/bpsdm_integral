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
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $bidangOptions = $this->bidangOptions();
        $isSuperadmin = $user->role === 'superadmin';
        $selectedBidang = $isSuperadmin ? ($bidangOptions[0] ?? null) : $user->bidang;
        $programOptions = ['semua', 'Teknis', 'LATSAR', 'PKP', 'PKA', 'PKN'];
        $selectedProgram = $request->query('program');
        if ($selectedProgram && !in_array($selectedProgram, $programOptions, true)) {
            abort(404);
        }

        if ($selectedBidang && !in_array($selectedBidang, $bidangOptions, true)) {
            abort(404);
        }

        $search = trim((string) $request->query('q', ''));
        $selectedCategory = $request->query('category');
        $selectedType = $request->query('type');
        $categoryOptions = ['l1_penyelenggara', 'l1_narasumber', 'l34_mandiri', 'l34_rekan', 'l34_atasan'];
        $typeOptions = ['slider', 'dropdown', 'checkbox', 'text'];
        abort_if($selectedCategory && !in_array($selectedCategory, $categoryOptions, true), 404);
        abort_if($selectedType && !in_array($selectedType, $typeOptions, true), 404);
        abort_if(mb_strlen($search) > 100, 422, 'Pencarian maksimal 100 karakter.');

        $questions = $this->evaluationQuestions()
                ->when(!$isSuperadmin, fn ($query) => $query->where('bidang', $user->bidang))
                ->when($selectedProgram, fn ($query) => $query->whereIn(DB::raw('UPPER(program_evaluasi)'), \App\Support\EvaluationProgram::aliases($selectedProgram)))
                ->when($selectedCategory, fn ($query) => $query->where('category', $selectedCategory))
                ->when($selectedType, fn ($query) => $query->where('type', $selectedType))
                ->when($search !== '', fn ($query) => $query->where('question_text', 'like', '%'.$search.'%'))
                ->latest()
                ->paginate(20)
                ->withQueryString();

        return view('questions.index', compact(
            'questions', 'bidangOptions', 'isSuperadmin', 'selectedBidang',
            'programOptions', 'selectedProgram', 'search', 'selectedCategory', 'selectedType', 'categoryOptions', 'typeOptions'
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
            'program_evaluasi' => ['required', Rule::in(['semua', 'LATSAR', 'PKP', 'PKA', 'PKN', 'Teknis'])],
            'sub_category'  => str_starts_with((string) $request->input('category'), 'l34_')
                ? ['required', Rule::in(Question::l34SubCategoryOptions())]
                : ['nullable'],
            'metode'        => in_array($request->input('category'), ['l1_penyelenggara', 'l1_narasumber'], true)
                ? ['required', Rule::in(['semua', 'klasikal', 'full learning', 'blended'])]
                : ['nullable', Rule::in(['semua'])],
            'type'          => ['required', Rule::in(['slider', 'text', 'dropdown', 'checkbox'])],
            'question_text' => 'required|string',
            'options'       => 'nullable|array',
            'options.*'     => 'nullable|string',
        ]);

        if ($data['category'] === 'l34_rekan' && \App\Support\EvaluationProgram::isManagerial($data['program_evaluasi'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['category' => 'LATSAR, PKP, PKA, dan PKN hanya menggunakan penilaian mandiri dan atasan.']);
        }
        $data['bidang'] = Auth::user()->role === 'superadmin' ? $data['bidang'] : Auth::user()->bidang;
        abort_if(blank($data['bidang']), 422, 'Bidang akun Admin belum ditentukan.');
        $data['training_type'] = $data['bidang'];

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
            'program_evaluasi' => ['required', Rule::in(['semua', 'LATSAR', 'PKP', 'PKA', 'PKN', 'Teknis'])],
            'sub_category'  => str_starts_with((string) $request->input('category'), 'l34_')
                ? ['required', Rule::in(Question::l34SubCategoryOptions())]
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
        if ($data['category'] === 'l34_rekan' && \App\Support\EvaluationProgram::isManagerial($data['program_evaluasi'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['category' => 'LATSAR, PKP, PKA, dan PKN hanya menggunakan penilaian mandiri dan atasan.']);
        }
        $data['bidang'] = Auth::user()->role === 'superadmin' ? $data['bidang'] : Auth::user()->bidang;
        $data['training_type'] = $data['bidang'];

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

    public function destroySelected(Request $request)
    {
        $data = $request->validate([
            'bidang' => ['nullable', 'string', Rule::in($this->bidangOptions())],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['required', 'integer', 'distinct', 'exists:evaluation_questions,id'],
            'program' => ['nullable', Rule::in(['semua', 'Teknis', 'LATSAR', 'PKP', 'PKA', 'PKN'])],
        ], [
            'question_ids.required' => 'Pilih minimal satu pertanyaan yang akan dihapus.',
            'question_ids.min' => 'Pilih minimal satu pertanyaan yang akan dihapus.',
        ]);

        $user = Auth::user();
        $bidang = $user->role === 'superadmin' ? ($data['bidang'] ?? null) : $user->bidang;
        abort_if($user->role !== 'superadmin' && blank($bidang), 403);

        $questionIds = collect($data['question_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $authorizedIds = $this->evaluationQuestions()
            ->when($user->role !== 'superadmin', fn ($query) => $query->where('bidang', $bidang))
            ->whereIn('id', $questionIds)
            ->pluck('id');

        abort_unless($authorizedIds->count() === $questionIds->count(), 403, 'Sebagian pertanyaan tidak dapat dihapus dari bidang ini.');

        DB::transaction(function () use ($authorizedIds) {
            \App\Models\EvaluationResultL1::whereIn('question_id', $authorizedIds)->delete();
            \App\Models\EvaluationResultL34::whereIn('question_id', $authorizedIds)->delete();
            Question::whereIn('id', $authorizedIds)->delete();
        });

        $parameters = [];
        if (filled($data['program'] ?? null)) {
            $parameters['program'] = $data['program'];
        }

        return redirect()->route('questions.index', $parameters)
            ->with('success', $authorizedIds->count().' pertanyaan terpilih beserta data jawaban terkait berhasil dihapus.');
    }
    public function destroyBundle(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->role === 'superadmin', 403);
        abort_if($user->role !== 'superadmin' && blank($user->bidang), 403);

        $questionIds = Question::query()
            ->where('category', 'not like', '%monitoring%')
            ->where('category', 'not like', '%l2%')
            ->where('type', '!=', 'ya_tidak')
            ->when($user->role !== 'superadmin', fn ($query) => $query->where('bidang', $user->bidang))
            ->pluck('id');
        DB::transaction(function () use ($questionIds) {
            \App\Models\EvaluationResultL1::whereIn('question_id', $questionIds)->delete();
            \App\Models\EvaluationResultL34::whereIn('question_id', $questionIds)->delete();
            Question::whereIn('id', $questionIds)->delete();
        });

        return redirect()->route('questions.index')
            ->with('success', $questionIds->count().' pertanyaan evaluasi beserta jawaban terkait berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $user = Auth::user();
        $isSuperadmin = $user->role === 'superadmin';
        $bidangOptions = $this->bidangOptions();
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'bidang' => ['required', 'string', Rule::in($isSuperadmin ? [...$bidangOptions, '__excel__'] : $bidangOptions)],
        ]);

        $selected = $request->input('bidang');
        abort_if(!$isSuperadmin && blank($user->bidang), 422, 'Bidang akun Admin belum ditentukan.');
        abort_if(!$isSuperadmin && $selected !== $user->bidang, 403);
        $defaultBidang = $selected === '__excel__' ? null : $selected;
        try {
            Excel::import(new QuestionImport($defaultBidang, $bidangOptions), $request->file('file'));
        } catch (\InvalidArgumentException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        return redirect()->route('questions.index', $isSuperadmin && $defaultBidang ? ['bidang' => $defaultBidang] : [])
            ->with('success', 'Bank soal berhasil diimpor'.($defaultBidang ? ' ke '.$defaultBidang : ' sesuai bidang di file Excel').'.');
    }

    public function exportAll()
    {
        $user = Auth::user();
        $query = $this->evaluationQuestions()
            ->when($user->role !== 'superadmin', function ($query) use ($user) {
                abort_if(blank($user->bidang), 422, 'Bidang akun Admin belum ditentukan.');
                $query->where('bidang', $user->bidang);
            })
            ->orderBy('bidang')
            ->orderBy('program_evaluasi')
            ->orderBy('category')
            ->orderBy('sub_category')
            ->orderBy('id');

        $rows = [QuestionImport::HEADINGS];

        $query->each(function (Question $question) use (&$rows) {
            $rows[] = [
                $question->bidang ?: $question->training_type,
                $question->metode ?: 'semua',
                $question->program_evaluasi ?: 'Teknis',
                match ($question->category) {
                    'l1_penyelenggara' => 'Penyelenggara',
                    'l1_narasumber' => 'Narasumber',
                    'l34_mandiri' => 'Mandiri',
                    'l34_atasan' => 'Atasan',
                    'l34_rekan' => 'Rekan',
                    default => $question->category,
                },
                $question->sub_category,
                $question->type,
                $question->question_text,
                $question->options ? json_encode(array_values($question->options), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) : null,
            ];
        });

        return Excel::download(new class($rows) extends StringValueBinder implements FromArray, WithCustomValueBinder, \Maatwebsite\Excel\Concerns\WithStyles {
            public function __construct(private array $rows) {}
            public function array(): array { return $this->rows; }
            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
            {
                $sheet->freezePane('A2');
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                foreach (['A' => 42, 'B' => 17, 'C' => 21, 'D' => 17, 'E' => 32, 'F' => 17, 'G' => 65, 'H' => 55] as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }
                $sheet->getStyle('G:H')->getAlignment()->setWrapText(true);
                return [1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF198754']]]];
            }
        }, 'seluruh-pertanyaan-evaluasi-'.now()->format('Ymd-His').'.xlsx');
    }
    public function downloadTemplate(Request $request)
    {
        $bidang = Auth::user()->role === 'superadmin' ? $request->query('bidang') : Auth::user()->bidang;
        abort_if(blank($bidang) || !in_array($bidang, $this->bidangOptions(), true), 422, 'Pilih bidang terlebih dahulu.');
        $header = [
            QuestionImport::HEADINGS,
            [$bidang, 'klasikal', 'Teknis', 'Penyelenggara', '', 'slider', 'Bagaimana kualitas penyelenggaraan pelatihan?', ''],
            [$bidang, 'semua', 'Teknis', 'Narasumber', '', 'slider', 'Bagaimana penguasaan materi narasumber?', ''],
        ];
        {
            $programs = ['LATSAR', 'PKP', 'PKA', 'PKN'];
            $commonSections = [
                'Perubahan Sikap Perilaku',
                'Dampak Pelatihan',
                'Faktor Pendukung Aktualisasi',
                'Faktor Penghambat Aktualisasi',
                'Faktor Pendukung Aksi Perubahan',
                'Faktor Penghambat Aksi Perubahan',
                'Faktor Pendukung Proyek Perubahan',
                'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi',
                'Kemanfaatan Rekomendasi',
            ];

            foreach ($programs as $program) {
                foreach (['Mandiri', 'Atasan'] as $peran) {
                    $sections = $commonSections;
                    if ($peran === 'Mandiri') {
                        array_unshift($sections, 'Data Diri Alumni');
                    } elseif ($peran === 'Atasan') {
                        array_unshift($sections, 'Data Diri Atasan');
                    }

                    foreach ($sections as $section) {
                        $usesSlider = in_array($section, [
                            'Perubahan Sikap Perilaku',
                            'Dampak Pelatihan',
                            'Kesesuaian Rekomendasi Kebijakan Dengan Kebutuhan Instansi',
                        ], true);
                        $header[] = [
                            $bidang,
                            'semua',
                            $program,
                            $peran,
                            $section,
                            $usesSlider ? 'slider' : 'text',
                            'Tuliskan butir pertanyaan untuk bagian '.$section,
                            '',
                        ];
                    }
                }
            }
        }
        {
            foreach (['Teknis'] as $program) {
                foreach (['Mandiri', 'Atasan', 'Rekan'] as $peran) {
                    $header[] = [$bidang, 'semua', $program, $peran, 'Perubahan Perilaku', 'slider', 'Sejauh mana kompetensi hasil pelatihan diterapkan dalam pekerjaan?', ''];
                    $header[] = [$bidang, 'semua', $program, $peran, 'Dampak Pelatihan', 'checkbox', 'Dampak pelatihan apa saja yang terlihat setelah pelatihan?', 'Produktivitas meningkat, Kualitas kerja meningkat'];
                }
            }
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
            ->where('is_active', true)
            ->where('category', 'not like', '%monitoring%')
            ->where('category', 'not like', '%l2%')
            ->where('type', '!=', 'ya_tidak')
            ->where(function ($query) {
                $query->where('category', '!=', 'l34_rekan')
                    ->orWhereNull('program_evaluasi')
                    ->orWhereNotIn(DB::raw('UPPER(program_evaluasi)'), ['MANAJERIAL', 'LATSAR', 'CPNS', 'PKP', 'PKA', 'PKN']);
            });
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
        return collect(array_merge(['Semua Bidang'], \App\Models\Bidang::names()))->merge(\App\Models\Training::whereNotNull('bidang')->pluck('bidang'))
            ->merge(\App\Models\User::withRoles(['admin_bidang'])->whereNotNull('bidang')->pluck('bidang'))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
