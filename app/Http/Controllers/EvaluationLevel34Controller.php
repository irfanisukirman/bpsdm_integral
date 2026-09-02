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
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;


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
        $participants = Participant::with('evaluationResultsL34.question')
            ->where('training_id', $id)
            ->get();

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
        abort_unless(in_array($role, ['mandiri', 'rekan', 'atasan'], true), 404);
        $training = Training::findOrFail($training_id);
        
        // 1. Ambil ID peserta yang SUDAH mengisi untuk role ini
        $filledIds = EvaluationResultL34::where('evaluator_role', $role)
            ->whereHas('participant', function($q) use ($training_id) {
                $q->where('training_id', $training_id);
            })
            ->pluck('participant_id');

        // 2. Peserta yang BELUM mengisi (Untuk Dropdown)
        $participants = Participant::with('user')->where('training_id', $training_id)
            ->whereNotIn('id', $filledIds)
            ->orderBy('name', 'asc')
            ->get();

        // 3. Peserta yang SUDAH mengisi (Untuk Daftar Antrean/Progres)
        $alreadyFilled = Participant::where('training_id', $training_id)
            ->whereIn('id', $filledIds)
            ->orderBy('name', 'asc')
            ->get();

        $categorySearch = 'l34_' . strtolower($role);
        $questions = Question::forTraining($training, $categorySearch)
            ->orderBy('id')
            ->get()
            ->groupBy('sub_category');
        $questionSections = [
            'profile' => $questions->first(fn ($items, $name) => str_contains(strtolower((string) $name), 'data diri')) ?? collect(),
            'placement' => $questions->first(fn ($items, $name) => str_contains(strtolower((string) $name), 'penempatan')) ?? collect(),
            'behavior' => $questions->first(fn ($items, $name) => str_contains(strtolower((string) $name), 'perubahan')) ?? collect(),
            'impact' => $questions->first(fn ($items, $name) => str_contains(strtolower((string) $name), 'dampak')) ?? collect(),
        ];

        return view('evaluasi.l34_public_form', compact(
            'training', 'participants', 'alreadyFilled', 'role', 'questions', 'questionSections'
        ));
    }

    /**
     * SISI PUBLIK: Menyimpan Hasil Penilaian 360
     */
    public function publicStore(Request $request, $training_id, $role)
    {
        abort_unless(in_array($role, ['mandiri', 'rekan', 'atasan'], true), 404);
        $rules = [
            'participant_id' => 'required|exists:participants,id',
            'scores'         => 'required|array',
        ];

        if ($role !== 'mandiri') {
            $rules += [
                'evaluator_name' => 'required',
                'evaluator_nip'  => 'required',
            ];
        } else {
            $rules += [
                'edu_before'  => 'required|string',
                'edu_after'   => 'required|string',
                'rank_before' => 'required|string',
                'rank_after'  => 'required|string',
                'pos_before'  => 'required|string',
                'pos_after'   => 'required|string',
                'unit_before' => 'required|string',
                'unit_after'  => 'required|string',
                'dept_before' => 'required|string',
                'dept_after'  => 'required|string',
            ];
        }

        $request->validate($rules);
        $training = Training::findOrFail($training_id);
        Participant::where('training_id', $training_id)->findOrFail($request->participant_id);
        $applicableQuestions = Question::forTraining($training, 'l34_' . $role)->get();
        $allowedQuestionIds = $applicableQuestions
            ->whereIn('id', array_keys($request->scores))
            ->pluck('id')
            ->mapWithKeys(fn ($questionId) => [(string) $questionId => true]);

        foreach ($applicableQuestions->where('type', 'checkbox') as $checkboxQuestion) {
            if (empty($request->input('scores.' . $checkboxQuestion->id, []))) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'scores.' . $checkboxQuestion->id => 'Pilih minimal satu jawaban pada pertanyaan checkbox.',
                ]);
            }
        }

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
                        'dept_during_training' => $request->dept_before,
                        'dept_current'         => $request->dept_after,
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
                if (!$allowedQuestionIds->has((string) $q_id)) {
                    continue;
                }
                $isMultipleChoice = is_array($value);
                $storedValue = $isMultipleChoice
                    ? json_encode(array_values(array_filter($value)), JSON_UNESCAPED_UNICODE)
                    : $value;
                EvaluationResultL34::create([
                    'training_id'    => $training_id,
                    'participant_id' => $request->participant_id,
                    'evaluator_role' => $role,
                    'evaluator_name' => ($role == 'mandiri') ? 'Diri Sendiri' : $request->evaluator_name,
                    'question_id'    => $q_id,
                    'score'          => !$isMultipleChoice && is_numeric($storedValue) ? $storedValue : null,
                    'note'           => $isMultipleChoice || !is_numeric($storedValue) ? $storedValue : null,
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
        $training = Training::findOrFail($id);
        $export = new EvaluationL34Export($training);
        
        $fileName = 'LAPORAN_DAMPAK_L3_L4_' . str_replace(' ', '_', $training->nama_pelatihan) . '.xlsx';

        // --- PROSES AUTO ARCHIVE ---
        // Gunakan Excel::raw untuk mendapatkan konten file tanpa langsung mendownload
        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        \App\Http\Controllers\DocumentController::archiveInternal(
            $training->id, 
            'HASIL EVALUASI DAMPAK', 
            $fileName, 
            $fileContent, 
            'xlsx'
        );

        return response()->streamDownload(function() use($fileContent) {
            echo $fileContent;
        }, $fileName);
    }

    public function exportWord($id)
    {
        // Set locale agar nama bulan otomatis bahasa Indonesia
        \Carbon\Carbon::setLocale('id');

        // Eager loading agar performa cepat
        $training = Training::with(['participants.alumniProfile', 'participants.evaluationResultsL34.question'])->findOrFail($id);
        
        $templatePath = public_path('templates/templateevaluasi34_integral.docx');
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'File template tidak ditemukan.');
        }

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);
        $chartFiles = [];


        // --- 1. DATA INFORMASI UMUM ---
        $templateProcessor->setValue('nama_pelatihan', $training->nama_pelatihan);
        $templateProcessor->setValue('tahunberjalan', date('Y'));
        $templateProcessor->setValue('tahunpelaksanaan', \Carbon\Carbon::parse($training->tgl_mulai)->format('Y'));
        $templateProcessor->setValue('tanggal_mulai', \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y'));
        $templateProcessor->setValue('tanggal_selesai', \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y'));
        $templateProcessor->setValue('tanggalsebarlink', $training->tgl_sebar_l34
            ? \Carbon\Carbon::parse($training->tgl_sebar_l34)->translatedFormat('d F Y')
            : 'Belum ditentukan');
        $templateProcessor->setValue('jumlah_peserta', $training->participants->count());
        $templateProcessor->setValue('bidang', $training->bidang ?: '-');
        $templateProcessor->setValue('metode', $training->metode ? ucwords(str_replace('_', ' ', $training->metode)) : '-');
        $surveyStart = $training->tgl_sebar_l34 ? \Carbon\Carbon::parse($training->tgl_sebar_l34) : null;
        $surveyEnd = $surveyStart?->copy()->addMonth();
        $templateProcessor->setValue('periode_evaluasi', $surveyStart
            ? $surveyStart->translatedFormat('d F Y').' sampai dengan '.$surveyEnd->translatedFormat('d F Y')
            : 'belum ditentukan');

        // --- 2. STATISTIK RESPONDEN ---
       $results = EvaluationResultL34::with('question')->where('training_id', $id)->get();
        $respondenAlumni = $results->where('evaluator_role', 'mandiri')->unique('participant_id')->count();
        $respondenAtasan = $results->where('evaluator_role', 'atasan')->unique('participant_id')->count();
        $respondenRekan = $results->where('evaluator_role', 'rekan')->unique('participant_id')->count();

        $templateProcessor->setValue('jumlah_alumni', $respondenAlumni);
        $templateProcessor->setValue('jumlah_atasan', $respondenAtasan);
        $templateProcessor->setValue('jumlah_rekan', $respondenRekan);
        $targetResponden = max(1, $training->participants->count());
        $templateProcessor->setValue('response_rate_mandiri', round(($respondenAlumni / $targetResponden) * 100, 1) . '%');
        $templateProcessor->setValue('response_rate_atasan', round(($respondenAtasan / $targetResponden) * 100, 1) . '%');
        $templateProcessor->setValue('response_rate_rekan', round(($respondenRekan / $targetResponden) * 100, 1) . '%');
        $templateProcessor->setValue('narasi_target_evaluasi',
            'Target kegiatan evaluasi pasca pelatihan adalah alumni '.$training->nama_pelatihan.' di lingkungan Pemerintah Provinsi Jawa Barat, yang pelatihannya telah dilaksanakan pada tanggal '.
            \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y').' sampai dengan '.
            \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y').
            ' dengan jumlah alumni pelatihan '.$training->participants->count().' orang. Sampai dengan tanggal terakhir pengisian kuesioner, sebanyak '.$respondenAlumni.' alumni, '.$respondenAtasan.' atasan alumni, dan '.$respondenRekan.' rekan kerja berpartisipasi.');
        $templateProcessor->setValue('narasi_hasil_umum',
            'Evaluasi pasca '.$training->nama_pelatihan.' di lingkungan Pemerintah Provinsi Jawa Barat dilaksanakan terhadap '.$training->participants->count().' alumni. Sampai waktu penyusunan laporan terdapat '.$respondenAlumni.' alumni, '.$respondenAtasan.' atasan alumni, dan '.$respondenRekan.' rekan kerja yang berpartisipasi mengisi survei.');

        // --- A. INFORMASI UMUM (PENDIDIKAN & GOLONGAN) ---
        $profiles = AlumniProfile::where('training_id', $id)->get();
        
        $eduLevels = ['S2/S3', 'D4/S1', 'D3', 'SMA/K', 'SD/SMP'];
        foreach($eduLevels as $edu) {
            $key = strtolower(str_replace(['/', ' '], '_', $edu));
            $templateProcessor->setValue('edu_'.$key.'_before', $profiles->where('edu_during_training', $edu)->count());
            $templateProcessor->setValue('edu_'.$key.'_after', $profiles->where('edu_current', $edu)->count());
        }

        $golLevels = ['IV', 'III', 'II', 'I'];
        foreach($golLevels as $gol) {
            $templateProcessor->setValue('gol_'.$gol.'_before', $profiles->filter(fn($p) => str_contains((string) $p->rank_during_training, $gol))->count());
            $templateProcessor->setValue('gol_'.$gol.'_after', $profiles->filter(fn($p) => str_contains((string) $p->rank_current, $gol))->count());
        }
        $educationChart = $this->createEvaluationSummaryBarChart(
            'Perbandingan Pendidikan Alumni',
            $eduLevels,
            [
                ['label' => 'Saat Pelatihan', 'values' => collect($eduLevels)->map(fn ($edu) => $profiles->where('edu_during_training', $edu)->count())->all(), 'color' => [47, 84, 150]],
                ['label' => 'Saat Evaluasi', 'values' => collect($eduLevels)->map(fn ($edu) => $profiles->where('edu_current', $edu)->count())->all(), 'color' => [112, 173, 71]],
            ]
        );
        $rankChart = $this->createEvaluationSummaryBarChart(
            'Perbandingan Pangkat/Golongan Alumni',
            collect($golLevels)->map(fn ($gol) => 'Golongan '.$gol)->all(),
            [
                ['label' => 'Saat Pelatihan', 'values' => collect($golLevels)->map(fn ($gol) => $profiles->filter(fn ($profile) => str_contains((string) $profile->rank_during_training, $gol))->count())->all(), 'color' => [47, 84, 150]],
                ['label' => 'Saat Evaluasi', 'values' => collect($golLevels)->map(fn ($gol) => $profiles->filter(fn ($profile) => str_contains((string) $profile->rank_current, $gol))->count())->all(), 'color' => [112, 173, 71]],
            ]
        );
        array_push($chartFiles, $educationChart, $rankChart);
        $templateProcessor->setImageValue('ringkasan_pendidikan', ['path' => $educationChart, 'width' => 620, 'height' => 285, 'ratio' => false]);
        $templateProcessor->setImageValue('ringkasan_pangkat', ['path' => $rankChart, 'width' => 620, 'height' => 270, 'ratio' => false]);

        // Perubahan Jabatan & Unit Kerja
        $jabatanBerubah = $profiles->filter(fn($p) => $p->pos_during_training != $p->pos_current)->count();
        $unitBerubah = $profiles->filter(fn($p) => $p->unit_during_training != $p->unit_current)->count();
        $deptBerubah = $profiles->filter(fn($p) => $p->dept_during_training != $p->dept_current)->count();
        
        $templateProcessor->setValue('jab_berubah', $jabatanBerubah);
        $templateProcessor->setValue('jab_tetap', $respondenAlumni - $jabatanBerubah);
        $templateProcessor->setValue('unit_berubah', $unitBerubah);
        $templateProcessor->setValue('unit_tetap', $respondenAlumni - $unitBerubah);
        $templateProcessor->setValue('dept_berubah', $deptBerubah);
        $templateProcessor->setValue('dept_tetap', max(0, $respondenAlumni - $deptBerubah));
        $positionChart = $this->createEvaluationSummaryBarChart('Perubahan Jabatan Alumni', ['Berubah', 'Tetap'], [
            ['label' => 'Jumlah Alumni', 'values' => [$jabatanBerubah, max(0, $respondenAlumni - $jabatanBerubah)], 'color' => [91, 155, 213]],
        ]);
        $unitChart = $this->createEvaluationSummaryBarChart('Perubahan Unit Kerja Alumni', ['Berubah', 'Tetap'], [
            ['label' => 'Jumlah Alumni', 'values' => [$unitBerubah, max(0, $respondenAlumni - $unitBerubah)], 'color' => [237, 125, 49]],
        ]);
        array_push($chartFiles, $positionChart, $unitChart);
        $templateProcessor->setImageValue('ringkasan_jabatan', ['path' => $positionChart, 'width' => 620, 'height' => 235, 'ratio' => false]);
        $templateProcessor->setImageValue('ringkasan_unit', ['path' => $unitChart, 'width' => 620, 'height' => 235, 'ratio' => false]);
        $templateProcessor->setValue('jumlah_instansi', $training->participants->pluck('instansi')->filter()->unique()->count());

        // --- B. PENUGASAN (BAGIAN 2) ---
        $taskQuestions = Question::forTraining($training, 'l34_mandiri')
            ->where('sub_category', 'Penempatan Tugas dan Transfer Learning')
            ->orderBy('id')
            ->take(4)
            ->get();
        foreach ($taskQuestions as $index => $taskQuestion) {
            $matchingIds = Question::query()
                ->whereIn('category', ['l34_mandiri', 'l34_atasan', 'l34_rekan'])
                ->where('bidang', $training->bidang)
                ->where('sub_category', $taskQuestion->sub_category)
                ->where('question_text', $taskQuestion->question_text)
                ->pluck('id');
            $taskResults = $results->whereIn('question_id', $matchingIds);
            $countYa = $taskResults->filter(fn($result) => strtoupper(trim((string) $result->note)) === 'YA')->count();
            $totalRes = $taskResults->count();
            $persen = ($totalRes > 0) ? round(($countYa / $totalRes) * 100, 2) : 0;
            $templateProcessor->setValue('task_' . ($index + 1) . '_persen', $persen . '%');
        }

        // --- C. PERUBAHAN PERILAKU & DAMPAK (BAGIAN 3 & 4) ---
        $questions = Question::query()
            ->whereIn('category', ['l34_mandiri', 'l34_atasan', 'l34_rekan'])
            ->where('bidang', $training->bidang)
            ->get();
        
        // Contoh: Pertanyaan "Sumber Daya" (Perilaku No 1)
        $firstBehavior = $questions->where('category', 'l34_mandiri')->where('sub_category', 'Perubahan Perilaku')->first();
        $behaviorIds = $firstBehavior
            ? $questions->where('sub_category', 'Perubahan Perilaku')->where('question_text', $firstBehavior->question_text)->pluck('id')
            : collect();
        $q1Results = $results->whereIn('question_id', $behaviorIds);
        $baikCount = $q1Results->filter(fn($result) =>
            ($result->score !== null && $result->score >= 80) ||
            in_array(strtolower(trim((string) $result->note)), ['baik', 'sangat baik'], true)
        )->count();
        $persenKetersediaan = ($q1Results->count() > 0) ? round(($baikCount / $q1Results->count()) * 100, 2) : 0;
        $templateProcessor->setValue('persentase_ketersedaian', $persenKetersediaan . '%');

        // --- 3. TABEL ASAL INSTANSI ---
        $instansiData = Participant::where('training_id', $id)
            ->select('instansi', \DB::raw('count(*) as total'))
            ->groupBy('instansi')->get();

        // Pastikan variabel 'ins_nama' ada di Word
        if ($instansiData->count() > 0 && in_array('ins_nama', $templateProcessor->getVariables())) {
            $templateProcessor->cloneRow('ins_nama', $instansiData->count());
            foreach ($instansiData as $index => $ins) {
                $row = $index + 1;
                $templateProcessor->setValue("ins_nama#$row", $ins->instansi);
                $templateProcessor->setValue("ins_jml#$row", $ins->total);
            }
        } else {
            $templateProcessor->setValue('ins_nama', 'Belum tersedia');
            $templateProcessor->setValue('ins_jml', 0);
        }

        // --- 4. ANALISIS DINAMIS LEVEL 3 DAN LEVEL 4 ---
        $toScore = static function ($result): ?float {
            if ($result->score !== null && is_numeric($result->score)) {
                return (float) $result->score;
            }

            return match (strtolower(trim((string) $result->note))) {
                'ya', 'sangat baik' => 100,
                'baik' => 80,
                'cukup' => 60,
                'kurang' => 40,
                'tidak', 'sangat kurang' => 20,
                default => null,
            };
        };
        $categoryLabel = static fn (float $score): string => match (true) {
            $score > 90 => 'Sangat Baik',
            $score > 80 => 'Baik',
            $score > 70 => 'Cukup',
            $score > 60 => 'Kurang',
            default => 'Sangat Kurang',
        };
        $canonicalQuestions = Question::forTraining($training, 'l34_mandiri')
            ->whereIn('sub_category', ['Perubahan Perilaku', 'Dampak Pelatihan'])
            ->orderBy('sub_category')
            ->orderBy('id')
            ->get();
        $allRoleQuestions = Question::query()
            ->whereIn('category', ['l34_mandiri', 'l34_atasan', 'l34_rekan'])
            ->where(function ($query) use ($training) {
                $query->where('bidang', $training->bidang)->orWhere('bidang', 'Semua Bidang');
            })
            ->get();

        $buildRows = function (string $subCategory) use ($canonicalQuestions, $allRoleQuestions, $results, $toScore): array {
            return $canonicalQuestions->where('sub_category', $subCategory)->values()->map(function ($question, $index) use ($allRoleQuestions, $results, $toScore) {
                $roleScores = collect(['mandiri', 'atasan', 'rekan'])->mapWithKeys(function ($role) use ($question, $allRoleQuestions, $results, $toScore) {
                    $roleQuestionIds = $allRoleQuestions
                        ->where('category', 'l34_' . $role)
                        ->where('sub_category', $question->sub_category)
                        ->where('question_text', $question->question_text)
                        ->pluck('id');
                    $scores = $results
                        ->where('evaluator_role', $role)
                        ->whereIn('question_id', $roleQuestionIds)
                        ->map($toScore)
                        ->filter(fn ($score) => $score !== null)
                        ->values();

                    return [$role => $scores];
                });
                $combined = $roleScores->flatten()->values();
                $format = static fn ($scores) => $scores->isNotEmpty() ? number_format($scores->avg(), 1, ',', '.') : '–';

                return [
                    'no' => $index + 1,
                    'indikator' => $question->question_text,
                    'mandiri' => $format($roleScores['mandiri']),
                    'atasan' => $format($roleScores['atasan']),
                    'rekan' => $format($roleScores['rekan']),
                    'gabungan' => $format($combined),
                    'n' => $combined->count(),
                    'values' => $combined,
                ];
            })->all();
        };
        $l3Rows = $buildRows('Perubahan Perilaku');
        $l4Rows = $buildRows('Dampak Pelatihan');
        $l3Values = collect($l3Rows)->pluck('values')->flatten()->values();
        $l4Values = collect($l4Rows)->pluck('values')->flatten()->values();
        $l3Score = $l3Values->isNotEmpty() ? round($l3Values->avg(), 1) : 0;
        $l4Score = $l4Values->isNotEmpty() ? round($l4Values->avg(), 1) : 0;

        // Isi bagian hasil pada template standar berdasarkan butir pertanyaan yang berlaku.
        $summarizeQuestionAnswers = function ($question) use ($allRoleQuestions, $results, $toScore): string {
            $roleLabels = ['mandiri' => 'Mandiri', 'atasan' => 'Atasan', 'rekan' => 'Rekan kerja'];
            return collect($roleLabels)->map(function ($label, $role) use ($question, $allRoleQuestions, $results, $toScore) {
                $questionIds = $allRoleQuestions
                    ->where('category', 'l34_'.$role)
                    ->where('sub_category', $question->sub_category)
                    ->where('question_text', $question->question_text)
                    ->pluck('id');
                $roleResults = $results->where('evaluator_role', $role)->whereIn('question_id', $questionIds);
                $scores = $roleResults->map($toScore)->filter(fn ($score) => $score !== null)->values();
                if ($scores->isNotEmpty()) {
                    return $label.': rata-rata '.number_format($scores->avg(), 1, ',', '.').'/100 (n='.$scores->count().')';
                }

                $answers = $roleResults->flatMap(function ($result) {
                    $note = trim((string) $result->note);
                    if ($note === '') return [];
                    $decoded = json_decode($note, true);
                    return is_array($decoded) ? array_values(array_filter($decoded, fn ($value) => trim((string) $value) !== '')) : [$note];
                });
                if ($answers->isEmpty()) return $label.': belum ada jawaban';

                $distribution = $answers->countBy()
                    ->sortDesc()
                    ->take(5)
                    ->map(fn ($count, $answer) => $answer.' ('.$count.')')
                    ->implode(', ');
                return $label.': '.$distribution;
            })->implode('; ');
        };
        $fillQuestionSlots = function (string $prefix, int $slotCount, $questions) use ($templateProcessor, $summarizeQuestionAnswers): void {
            $contents = $questions->values()->map(function ($question, $index) use ($summarizeQuestionAnswers) {
                return ($index + 1).'. '.$question->question_text.' Hasil: '.$summarizeQuestionAnswers($question).'.';
            })->all();
            if (empty($contents)) {
                $contents = ['Belum tersedia butir pertanyaan atau jawaban untuk bagian ini.'];
            }
            if (count($contents) > $slotCount) {
                $overflow = array_splice($contents, $slotCount - 1);
                $contents[] = implode(' ', $overflow);
            }
            for ($slot = 1; $slot <= $slotCount; $slot++) {
                $templateProcessor->setValue($prefix.'_'.$slot, $contents[$slot - 1] ?? '');
            }
        };
        $placementQuestions = Question::forTraining($training, 'l34_mandiri')
            ->where('sub_category', 'Penempatan Tugas dan Transfer Learning')
            ->orderBy('id')
            ->get();
        $fillQuestionSlots('placement_content', 8, $placementQuestions);
        $fillQuestionSlots('l3_content', 6, $canonicalQuestions->where('sub_category', 'Perubahan Perilaku'));
        $fillQuestionSlots('l4_content', 6, $canonicalQuestions->where('sub_category', 'Dampak Pelatihan'));

        foreach (['l3' => $l3Rows, 'l4' => $l4Rows] as $prefix => $rows) {
            if (empty($rows)) {
                $rows = [[
                    'no' => 1, 'indikator' => 'Belum tersedia pertanyaan atau jawaban terukur.',
                    'mandiri' => '–', 'atasan' => '–', 'rekan' => '–', 'gabungan' => '–', 'n' => 0,
                ]];
            }
            if (in_array($prefix . '_no', $templateProcessor->getVariables(), true)) {
                $templateProcessor->cloneRow($prefix . '_no', count($rows));
            }
            foreach ($rows as $index => $row) {
                $number = $index + 1;
                foreach (['no', 'indikator', 'mandiri', 'atasan', 'rekan', 'gabungan', 'n'] as $field) {
                    $templateProcessor->setValue("{$prefix}_{$field}#{$number}", $row[$field]);
                }
            }
        }

        $templateProcessor->setValue('skor_l3', number_format($l3Score, 1, ',', '.'));
        $templateProcessor->setValue('skor_l4', number_format($l4Score, 1, ',', '.'));
        $templateProcessor->setValue('kategori_l3', $l3Values->isNotEmpty() ? $categoryLabel($l3Score) : 'Belum dapat dinilai');
        $templateProcessor->setValue('kategori_l4', $l4Values->isNotEmpty() ? $categoryLabel($l4Score) : 'Belum dapat dinilai');
        $templateProcessor->setValue('jumlah_jawaban_l4', $l4Values->count());

        $responseComplete = min($respondenAlumni, $respondenAtasan, $respondenRekan);
        $templateProcessor->setValue('ringkasan_temuan',
            "Terdapat {$respondenAlumni} respons mandiri, {$respondenAtasan} respons atasan, dan {$respondenRekan} respons rekan kerja. " .
            ($l3Values->isNotEmpty() ? "Indeks perubahan perilaku (Level 3) sebesar {$l3Score}/100 dengan kategori {$categoryLabel($l3Score)}. " : 'Indeks Level 3 belum dapat dihitung. ') .
            ($l4Values->isNotEmpty() ? "Indeks hasil/dampak (Level 4) sebesar {$l4Score}/100 dengan kategori {$categoryLabel($l4Score)}." : 'Indeks Level 4 belum dapat dihitung.')
        );
        $templateProcessor->setValue('narasi_l3', $l3Values->isNotEmpty()
            ? "Berdasarkan {$l3Values->count()} jawaban terukur, indeks gabungan Level 3 adalah {$l3Score}/100 ({$categoryLabel($l3Score)}). Interpretasi perlu memperhatikan perbedaan jumlah respons antar-perspektif dan konteks pekerjaan alumni."
            : 'Belum tersedia jawaban terukur yang memadai untuk menyimpulkan perubahan perilaku.');
        $templateProcessor->setValue('narasi_l4', $l4Values->isNotEmpty()
            ? "Berdasarkan {$l4Values->count()} jawaban terukur, indeks gabungan Level 4 adalah {$l4Score}/100 ({$categoryLabel($l4Score)}). Nilai ini menunjukkan persepsi dampak dan bukan perhitungan Return on Training Investment (ROTI)."
            : 'Belum tersedia jawaban terukur yang memadai untuk menyimpulkan dampak pelatihan.');
        $templateProcessor->setValue('catatan_keterbatasan',
            "Laporan menggunakan data kuesioner yang tersedia pada saat unduh. Tingkat respons lengkap tiga perspektif baru mencakup {$responseComplete} dari {$training->participants->count()} alumni. Hasil bersifat deskriptif-persepsional, tidak membuktikan hubungan sebab-akibat, dan belum menghitung dampak finansial/ROTI karena data biaya serta manfaat moneter tidak tersedia.");
        $followUpRows = [];
        $participantCount = $training->participants->count();
        if ($participantCount > 0 && min($respondenAlumni, $respondenAtasan, $respondenRekan) < $participantCount) {
            $followUpRows[] = [
                'prioritas' => "Kelengkapan perspektif belum merata: mandiri {$respondenAlumni}, atasan {$respondenAtasan}, dan rekan kerja {$respondenRekan} dari {$participantCount} alumni.",
                'tindakan' => 'Memverifikasi responden yang belum mengisi, mengirim pengingat kepada alumni/atasan/rekan kerja, dan mendokumentasikan alasan ketidakisian.',
                'penanggung' => 'Admin bidang dan koordinator evaluasi',
                'waktu' => 'Maksimal 7 hari setelah evaluasi',
                'indikator' => 'Setiap alumni memiliki status tiga perspektif yang terverifikasi; target keterisian 100% atau tersedia alasan ketidakisian.',
            ];
        }
        $lowestL3 = collect($l3Rows)
            ->filter(fn ($row) => $row['values']->isNotEmpty())
            ->sortBy(fn ($row) => $row['values']->avg())
            ->first();
        if ($lowestL3) {
            $score = round($lowestL3['values']->avg(), 1);
            $followUpRows[] = [
                'prioritas' => "Indikator perubahan perilaku terendah: {$lowestL3['indikator']} dengan indeks {$score}/100 ({$categoryLabel($score)}).",
                'tindakan' => $score <= 80
                    ? 'Menyusun pendampingan penerapan hasil pelatihan, memastikan dukungan atasan dan sumber daya kerja, serta melakukan pemantauan bukti perubahan perilaku.'
                    : 'Mempertahankan praktik penerapan yang baik, mendokumentasikan contoh perilaku kerja, dan memperluas transfer pembelajaran kepada rekan kerja.',
                'penanggung' => 'Atasan alumni, alumni, dan bidang penyelenggara',
                'waktu' => '1-3 bulan setelah laporan',
                'indikator' => $score <= 80
                    ? 'Tersedia rencana penerapan, bukti pendampingan, dan indeks indikator meningkat minimal 5 poin pada pemantauan berikutnya.'
                    : 'Praktik baik terdokumentasi dan transfer pembelajaran terlaksana pada unit kerja.',
            ];
        }
        $lowestL4 = collect($l4Rows)
            ->filter(fn ($row) => $row['values']->isNotEmpty())
            ->sortBy(fn ($row) => $row['values']->avg())
            ->first();
        if ($lowestL4) {
            $score = round($lowestL4['values']->avg(), 1);
            $followUpRows[] = [
                'prioritas' => "Indikator dampak terendah: {$lowestL4['indikator']} dengan indeks {$score}/100 ({$categoryLabel($score)}).",
                'tindakan' => $score <= 80
                    ? 'Menetapkan indikator kinerja yang relevan, menyelaraskan penerapan kompetensi dengan sasaran unit kerja, dan memantau bukti hasil secara berkala.'
                    : 'Menjaga keberlanjutan dampak, mereplikasi praktik yang efektif, dan menghubungkan hasil pelatihan dengan indikator kinerja unit kerja.',
                'penanggung' => 'Pimpinan unit kerja, atasan alumni, dan bidang penyelenggara',
                'waktu' => '3-6 bulan setelah laporan',
                'indikator' => $score <= 80
                    ? 'Tersedia baseline, target, bukti capaian, dan peningkatan indikator dampak pada evaluasi berikutnya.'
                    : 'Praktik efektif direplikasi dan capaian kinerja terkait tetap terjaga atau meningkat.',
            ];
        }
        if (empty($followUpRows)) {
            $followUpRows[] = [
                'prioritas' => 'Data Level 3 dan Level 4 belum memadai untuk menetapkan tindak lanjut substantif.',
                'tindakan' => 'Melengkapi jawaban mandiri, atasan, dan rekan kerja sebelum pembahasan hasil evaluasi.',
                'penanggung' => 'Admin bidang dan koordinator evaluasi',
                'waktu' => 'Sebelum laporan ditetapkan',
                'indikator' => 'Data tiga perspektif tersedia dan indikator dapat dianalisis.',
            ];
        }
        if (in_array('rtl34_prioritas', $templateProcessor->getVariables(), true)) {
            $templateProcessor->cloneRow('rtl34_prioritas', count($followUpRows));
            foreach ($followUpRows as $index => $row) {
                $number = $index + 1;
                foreach (['prioritas', 'tindakan', 'penanggung', 'waktu', 'indikator'] as $field) {
                    $templateProcessor->setValue("rtl34_{$field}#{$number}", htmlspecialchars($row[$field], ENT_QUOTES | ENT_XML1, 'UTF-8'));
                }
            }
        }
        $recommendation = implode(' ', array_column($followUpRows, 'tindakan'));
        $templateProcessor->setValue('rekomendasi', $recommendation);
        $templateProcessor->setValue('kesimpulan',
            ($l3Values->isNotEmpty() && $l4Values->isNotEmpty())
                ? "Secara deskriptif, penerapan hasil pelatihan berada pada kategori {$categoryLabel($l3Score)} dan dampak terhadap pekerjaan/unit kerja berada pada kategori {$categoryLabel($l4Score)}. Kesimpulan ini berlaku untuk respons yang terkumpul dan perlu dibaca bersama keterbatasan laporan."
                : 'Data yang tersedia belum cukup untuk menghasilkan kesimpulan menyeluruh mengenai perubahan perilaku dan dampak pelatihan.');

        // --- 5. REKAP RESPONDEN (HANYA BOLEH DIPANGGIL 1 KALI) ---
        $participants = Participant::where('training_id', $id)->orderBy('name', 'asc')->get();

        // Cek apakah variabel 'res_nama' terdeteksi di dokumen Word
        if ($participants->count() > 0 && in_array('res_nama', $templateProcessor->getVariables())) {
            $templateProcessor->cloneRow('res_nama', $participants->count());
            
            foreach ($participants as $index => $p) {
                $currRow = $index + 1;
                $templateProcessor->setValue("res_nama#$currRow", $p->name . " | " . $p->nip_nik);
                $templateProcessor->setValue("res_jabatan#$currRow", $p->jabatan);
                $templateProcessor->setValue("res_instansi#$currRow", $p->instansi ?: '-');
                $templateProcessor->setValue("res_lingkup#$currRow", collect([$p->kota, $p->provinsi])->filter()->implode(', ') ?: '-');
                $templateProcessor->setValue("res_status#$currRow", $p->status_kepegawaian ?: $p->user?->status_kepegawaian ?: '-');
                
                $statusRoles = $results->where('participant_id', $p->id)->pluck('evaluator_role')->unique()->all();

                $templateProcessor->setValue("res_m#$currRow", in_array('mandiri', $statusRoles) ? 'Sudah Isi' : 'Belum Isi');
                $templateProcessor->setValue("res_a#$currRow", in_array('atasan', $statusRoles) ? 'Sudah Isi' : 'Belum Isi');
                $templateProcessor->setValue("res_r#$currRow", in_array('rekan', $statusRoles) ? 'Sudah Isi' : 'Belum Isi');
            }
        } else {
            $templateProcessor->setValue('res_nama', 'Belum tersedia peserta');
            $templateProcessor->setValue('res_jabatan', '-');
            $templateProcessor->setValue('res_instansi', '-');
            $templateProcessor->setValue('res_lingkup', '-');
            $templateProcessor->setValue('res_status', '-');
            $templateProcessor->setValue('res_m', '-');
            $templateProcessor->setValue('res_a', '-');
            $templateProcessor->setValue('res_r', '-');
        }

        $fileName = "LAPORAN_AKHIR_DAMPAK_L34_" . str_replace(' ', '_', $training->nama_pelatihan) . ".docx";
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $templateProcessor->saveAs($tempFile);
        $fileContent = file_get_contents($tempFile);
        foreach ($chartFiles as $chartFile) {
            if (is_file($chartFile)) {
                unlink($chartFile);
            }
        }

        \App\Http\Controllers\DocumentController::archiveInternal($training->id, 'LAPORAN AKHIR DAMPAK', $fileName, $fileContent, 'docx');
        unlink($tempFile);

        return response()->streamDownload(function() use($fileContent) { echo $fileContent; }, $fileName);
    }

    /**
     * Membuat grafik batang PNG sederhana untuk menggantikan placeholder ringkasan
     * pada template Word tanpa mengubah narasi baku dokumen.
     */
    private function createEvaluationSummaryBarChart(string $title, array $labels, array $series): string
    {
        $width = 1000;
        $rowHeight = 54;
        $height = max(300, 145 + (count($labels) * $rowHeight));
        $image = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $text = imagecolorallocate($image, 40, 40, 40);
        $muted = imagecolorallocate($image, 100, 100, 100);
        $grid = imagecolorallocate($image, 220, 225, 232);
        imagefilledrectangle($image, 0, 0, $width, $height, $white);
        if (function_exists('imageantialias')) {
            imageantialias($image, true);
        }

        $titleX = max(20, (int) (($width - (imagefontwidth(5) * strlen($title))) / 2));
        imagestring($image, 5, $titleX, 18, $title, $text);

        $legendX = 220;
        foreach ($series as $item) {
            [$red, $green, $blue] = $item['color'];
            $seriesColor = imagecolorallocate($image, $red, $green, $blue);
            imagefilledrectangle($image, $legendX, 51, $legendX + 18, 64, $seriesColor);
            imagestring($image, 3, $legendX + 25, 50, $item['label'], $text);
            $legendX += 55 + (imagefontwidth(3) * strlen($item['label']));
        }

        $allValues = collect($series)->flatMap(fn ($item) => $item['values'])->map(fn ($value) => (int) $value);
        $maxValue = max(1, (int) $allValues->max());
        $axisMax = max(5, (int) (ceil($maxValue / 5) * 5));
        $left = 190;
        $right = 55;
        $top = 92;
        $bottom = 42;
        $plotWidth = $width - $left - $right;
        $plotHeight = $height - $top - $bottom;

        for ($tick = 0; $tick <= 5; $tick++) {
            $x = $left + (int) (($plotWidth / 5) * $tick);
            imageline($image, $x, $top, $x, $top + $plotHeight, $grid);
            $tickValue = (string) (int) round(($axisMax / 5) * $tick);
            imagestring($image, 2, $x - (int) ((imagefontwidth(2) * strlen($tickValue)) / 2), $top + $plotHeight + 8, $tickValue, $muted);
        }

        $seriesCount = max(1, count($series));
        $barHeight = $seriesCount > 1 ? 15 : 23;
        $barGap = 4;
        $groupHeight = ($seriesCount * $barHeight) + (($seriesCount - 1) * $barGap);

        foreach (array_values($labels) as $labelIndex => $label) {
            $centerY = $top + (int) (($plotHeight / max(1, count($labels))) * ($labelIndex + 0.5));
            $shortLabel = \Illuminate\Support\Str::limit((string) $label, 24);
            $labelX = max(5, $left - 12 - (imagefontwidth(3) * strlen($shortLabel)));
            imagestring($image, 3, $labelX, $centerY - 7, $shortLabel, $text);
            $groupTop = $centerY - (int) ($groupHeight / 2);

            foreach (array_values($series) as $seriesIndex => $item) {
                $value = max(0, (int) ($item['values'][$labelIndex] ?? 0));
                [$red, $green, $blue] = $item['color'];
                $seriesColor = imagecolorallocate($image, $red, $green, $blue);
                $barTop = $groupTop + ($seriesIndex * ($barHeight + $barGap));
                $barRight = $left + (int) (($value / $axisMax) * $plotWidth);
                if ($value > 0) {
                    imagefilledrectangle($image, $left, $barTop, max($left + 2, $barRight), $barTop + $barHeight, $seriesColor);
                }
                imagestring($image, 2, min($width - 28, max($left + 5, $barRight + 6)), $barTop + 1, (string) $value, $text);
            }
        }

        $temporaryBase = tempnam(sys_get_temp_dir(), 'l34_chart_');
        $path = $temporaryBase.'.png';
        rename($temporaryBase, $path);
        imagepng($image, $path, 6);
        imagedestroy($image);

        return $path;
    }

    public function exportInvitation($id)
    {
        \Carbon\Carbon::setLocale('id');
        $training = Training::with('participants')->findOrFail($id);
        $templatePath = public_path('templates/template_undangan_l34.docx');
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Template surat undangan evaluasi tidak ditemukan.');
        }
        $templateProcessor = new TemplateProcessor($templatePath);

        // 1. Data Dasar Pelatihan
        $templateProcessor->setValue('nama_pelatihan', $training->nama_pelatihan);
        $templateProcessor->setValue('tgl_mulai_pelatihan', \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y'));
        $templateProcessor->setValue('tgl_selesai_pelatihan', \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y'));
        $templateProcessor->setValue('tgl_surat', \Carbon\Carbon::now()->translatedFormat('d F Y'));
        $templateProcessor->setValue('tgl_cetak', \Carbon\Carbon::now()->translatedFormat('d F Y'));
        $templateProcessor->setValue('tgl_surat_lengkap', 'Cimahi, ' . \Carbon\Carbon::now()->translatedFormat('d F Y'));
        $templateProcessor->setValue('bidang', $training->bidang);
        $templateProcessor->setValue('metode', ucfirst($training->metode));
        $templateProcessor->setValue('link_gateway', route('public.l34.gateway', $training->id));
        $templateProcessor->setValue('link_mandiri', route('public.l34.form', [$training->id, 'mandiri']));
        $templateProcessor->setValue('link_atasan', route('public.l34.form', [$training->id, 'atasan']));
        $templateProcessor->setValue('link_rekan', route('public.l34.form', [$training->id, 'rekan']));

        // 2. Logika Tanggal Batas Waktu (1 Bulan Setelah Link Dibuka)
        // tgl_sebar_l34 adalah accessor yang kita buat sebelumnya (4 bln / 1 thn)
        $tglMulaiSurvey = $training->tgl_sebar_l34;
        $tglBatasWaktu = $tglMulaiSurvey->copy()->addMonth();
        $templateProcessor->setValue('tgl_batas_waktu', $tglBatasWaktu->translatedFormat('d F Y'));

        // 3. Daftar Kepala Perangkat Daerah (Unik)
        // Mengambil instansi unik dari peserta, lalu tambah kata "Kepala"
        $uniqueInstansi = $training->participants->pluck('instansi')->unique();
        $daftarKepala = "";
        foreach ($uniqueInstansi as $index => $ins) {
            $daftarKepala .= ($index + 1) . ". Kepala " . $ins . "\n";
        }
        // Gunakan setComplexValue atau multiline agar \n terbaca sebagai baris baru
        $templateProcessor->setValue('daftar_kepala', $daftarKepala);

        // 4. Tabel Daftar Peserta (Cloning Row)
        $participants = $training->participants()->orderBy('name', 'asc')->get();
        if ($participants->count() > 0) {
            $templateProcessor->cloneRow('p_nama', $participants->count());
            foreach ($participants as $index => $p) {
                $row = $index + 1;
                $templateProcessor->setValue("p_no#$row", $row);
                $templateProcessor->setValue("p_nama#$row", $p->name);
                $templateProcessor->setValue("p_nip#$row", $p->nip_nik);
                $templateProcessor->setValue("p_jabatan#$row", $p->jabatan);
                $templateProcessor->setValue("p_instansi#$row", $p->instansi);
            }
        }

       $fileName = "SURAT_UNDANGAN_EVALUASI_L34_" . str_replace(' ', '_', $training->nama_pelatihan) . ".docx";
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $templateProcessor->saveAs($tempFile);
        $fileContent = file_get_contents($tempFile);

        \App\Http\Controllers\DocumentController::archiveInternal($training->id, 'SURAT UNDANGAN EVALUASI', $fileName, $fileContent, 'docx');
        unlink($tempFile);

        return response()->streamDownload(function() use($fileContent) { echo $fileContent; }, $fileName);
    }
    
}
