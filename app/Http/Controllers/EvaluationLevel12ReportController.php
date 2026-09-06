<?php

namespace App\Http\Controllers;

use App\Models\EvaluationFormL1;
use App\Models\EvaluationResultL1;
use App\Models\EvaluationL1TextSummary;
use App\Models\EvaluationResultL2;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use App\Models\Participant;
use App\Models\Training;
use App\Models\AiGeneration;
use App\Services\AiEvaluationDashboardService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpWord\TemplateProcessor;

class EvaluationLevel12ReportController extends Controller
{
    public function dashboard(int $id)
    {
        $training = Training::withCount('participants')->findOrFail($id);
        $user = Auth::user();
        abort_unless(
            $user->role === 'superadmin'
            || ($user->role === 'admin_bidang' && $user->bidang === $training->bidang),
            403
        );

        $participantsTotal = $training->participants_count;
        $l1Results = EvaluationResultL1::with('question')
            ->where('training_id', $training->id)
            ->get();
        $numericL1 = $l1Results->whereNotNull('score');
        $l1Respondents = $l1Results->pluck('participant_id')->unique()->count();
        $l1ResponseRate = $participantsTotal > 0 ? round(($l1Respondents / $participantsTotal) * 100, 1) : 0;
        $l1Average = $numericL1->isNotEmpty() ? round($numericL1->avg('score'), 1) : null;
        $organizerScores = $numericL1->whereNull('schedule_id');
        $speakerScores = $numericL1->whereNotNull('schedule_id');
        $organizerAverage = $organizerScores->isNotEmpty() ? round($organizerScores->avg('score'), 1) : null;
        $speakerAverage = $speakerScores->isNotEmpty() ? round($speakerScores->avg('score'), 1) : null;

        $indicatorData = $numericL1
            ->filter(fn ($result) => $result->question)
            ->groupBy('question_id')
            ->map(function ($results) {
                $question = $results->first()->question;

                return [
                    'label' => $question->question_text,
                    'average' => round($results->avg('score'), 1),
                    'responses' => $results->count(),
                ];
            })
            ->sortBy('average')
            ->values();
        $lowestIndicator = $indicatorData->first();

        $l2Results = EvaluationResultL2::with('participant')
            ->whereHas('participant', fn ($query) => $query->where('training_id', $training->id))
            ->get();
        $l2Count = $l2Results->count();
        $preAverage = $l2Count ? round($l2Results->avg(fn ($result) => (float) $result->pretest), 1) : null;
        $postAverage = $l2Count ? round($l2Results->avg(fn ($result) => (float) $result->postest), 1) : null;
        $gainAverage = $l2Count ? round($postAverage - $preAverage, 1) : null;
        $l2Status = [
            'increased' => $l2Results->filter(fn ($result) => (float) $result->postest > (float) $result->pretest)->count(),
            'same' => $l2Results->filter(fn ($result) => (float) $result->postest === (float) $result->pretest)->count(),
            'decreased' => $l2Results->filter(fn ($result) => (float) $result->postest < (float) $result->pretest)->count(),
        ];
        $increaseRate = $l2Count ? round(($l2Status['increased'] / $l2Count) * 100, 1) : 0;

        $textQuestions = Question::forTraining($training, 'l1_penyelenggara')->where('type', 'text')->pluck('id');
        $textFeedback = $l1Results
            ->whereNull('schedule_id')
            ->whereIn('question_id', $textQuestions)
            ->filter(fn ($result) => filled(trim((string) $result->note)));
        $adminSummary = EvaluationL1TextSummary::with('reviewer')->where('training_id', $training->id)->first();

        $recommendations = collect();
        if ($participantsTotal > 0 && $l1ResponseRate < 100) {
            $recommendations->push([
                'priority' => $l1ResponseRate < 75 ? 'tinggi' : 'sedang',
                'finding' => ($participantsTotal - $l1Respondents).' peserta belum memberikan respons L1.',
                'action' => 'Melakukan pengingat dan verifikasi pengisian hingga data mencapai target keterisian.',
            ]);
        }
        if ($lowestIndicator) {
            $recommendations->push([
                'priority' => $lowestIndicator['average'] <= 80 ? 'tinggi' : 'rendah',
                'finding' => 'Indikator terendah: '.$lowestIndicator['label'].' ('.$lowestIndicator['average'].').',
                'action' => $lowestIndicator['average'] <= 80
                    ? 'Menelaah akar masalah dan menetapkan perbaikan terukur pada pelaksanaan berikutnya.'
                    : 'Mempertahankan praktik yang telah berjalan baik dan memantau konsistensinya.',
            ]);
        }
        if ($l2Status['decreased'] > 0 || ($gainAverage !== null && $gainAverage <= 0)) {
            $recommendations->push([
                'priority' => 'tinggi',
                'finding' => $l2Status['decreased'].' peserta mengalami penurunan hasil belajar; perubahan rerata '.($gainAverage ?? 0).' poin.',
                'action' => 'Melakukan telaah asesmen dan memberikan penguatan atau remediasi kepada peserta terkait.',
            ]);
        }
        if ($adminSummary) {
            $recommendations->push([
                'priority' => 'sedang',
                'finding' => $textFeedback->count().' masukan tertulis telah dirangkum oleh admin.',
                'action' => $adminSummary->follow_up,
            ]);
        }

        $scoreCategory = static fn (?float $score): string => match (true) {
            $score === null => 'Belum tersedia',
            $score > 90 => 'Sangat Baik',
            $score > 80 => 'Baik',
            $score > 70 => 'Cukup',
            $score > 60 => 'Kurang',
            default => 'Sangat Kurang',
        };
        $executiveNarrative = $l1Average === null
            ? 'Data Level 1 belum tersedia untuk menilai reaksi peserta.'
            : 'Reaksi peserta memperoleh skor '.$l1Average.'/100 dengan kategori '.$scoreCategory($l1Average).'. ';
        $executiveNarrative .= $gainAverage === null
            ? 'Data Level 2 belum tersedia untuk mengukur perubahan hasil belajar.'
            : 'Rerata hasil belajar berubah '.($gainAverage >= 0 ? '+' : '').$gainAverage.' poin, dengan '.$increaseRate.'% peserta mengalami peningkatan.';

        $aiAnalysis = session('l12_ai_analysis') ?: AiGeneration::where('training_id', $training->id)->where('feature', 'evaluation_dashboard_l12')->where('status', 'completed')->latest()->value('generated_content');

        return view('evaluasi.l12_dashboard', compact(
            'training', 'participantsTotal', 'l1Respondents', 'l1ResponseRate', 'l1Average',
            'organizerAverage', 'speakerAverage', 'indicatorData', 'lowestIndicator', 'l2Count',
            'preAverage', 'postAverage', 'gainAverage', 'l2Status', 'increaseRate', 'textFeedback',
            'adminSummary', 'recommendations', 'scoreCategory', 'executiveNarrative', 'aiAnalysis'
        ));
    }

    public function generateAiAnalysis(int $id, AiEvaluationDashboardService $ai)
    {
        $training = Training::withCount('participants')->findOrFail($id); $user = Auth::user();
        abort_unless($user->role === 'superadmin' || ($user->role === 'admin_bidang' && $user->bidang === $training->bidang), 403);
        $l1 = EvaluationResultL1::with('question')->where('training_id', $id)->get(); $numeric = $l1->whereNotNull('score');
        $l2 = EvaluationResultL2::whereHas('participant', fn ($q) => $q->where('training_id', $id))->get();
        $pre = $l2->isNotEmpty() ? round($l2->avg(fn ($x) => (float) $x->pretest), 1) : null;
        $post = $l2->isNotEmpty() ? round($l2->avg(fn ($x) => (float) $x->postest), 1) : null;
        $summary = EvaluationL1TextSummary::where('training_id', $id)->first();
        $payload = [
            'pelatihan' => ['nama' => $training->nama_pelatihan, 'angkatan' => $training->angkatan, 'bidang' => $training->bidang],
            'cakupan' => ['peserta' => $training->participants_count, 'responden_l1' => $l1->pluck('participant_id')->unique()->count(), 'peserta_l2' => $l2->count()],
            'level_1' => ['rerata' => $numeric->isNotEmpty() ? round($numeric->avg('score'), 1) : null,
                'penyelenggara' => $numeric->whereNull('schedule_id')->isNotEmpty() ? round($numeric->whereNull('schedule_id')->avg('score'), 1) : null,
                'narasumber' => $numeric->whereNotNull('schedule_id')->isNotEmpty() ? round($numeric->whereNotNull('schedule_id')->avg('score'), 1) : null,
                'indikator' => $numeric->filter(fn ($x) => $x->question)->groupBy('question_id')->map(fn ($items) => ['label' => $items->first()->question->question_text, 'rerata' => round($items->avg('score'), 1), 'respons' => $items->count()])->values()->all()],
            'level_2' => ['rerata_pretest' => $pre, 'rerata_posttest' => $post, 'perubahan' => $pre !== null ? round($post - $pre, 1) : null,
                'meningkat' => $l2->filter(fn ($x) => (float) $x->postest > (float) $x->pretest)->count(),
                'tetap' => $l2->filter(fn ($x) => (float) $x->postest === (float) $x->pretest)->count(),
                'menurun' => $l2->filter(fn ($x) => (float) $x->postest < (float) $x->pretest)->count()],
            'kesimpulan_masukan_anonim' => $summary?->conclusion,
        ];
        return $this->runAiDashboard($training, $user, $ai, $payload, 'evaluation_dashboard_l12', 'Level 1 dan 2', 'l12_ai_analysis');
    }

    private function runAiDashboard(Training $training, $user, AiEvaluationDashboardService $ai, array $payload, string $feature, string $level, string $sessionKey)
    {
        $generation = AiGeneration::create(['training_id' => $training->id, 'user_id' => $user->id, 'feature' => $feature,
            'model' => $ai->provider().':'.$ai->model(), 'source_hash' => hash('sha256', json_encode($payload)), 'status' => 'processing',
            'input_summary' => ['aggregate_only' => true, 'participant_identities_sent' => false]]);
        try {
            $analysis = $ai->generate($level, $payload);
            $generation->update(['status' => 'completed', 'generated_content' => $analysis, 'generated_at' => now()]);
            return back()->with($sessionKey, $analysis)->with('success', 'Analisis eksekutif AI berhasil dibuat.');
        } catch (\Throwable $exception) {
            report($exception); $generation->update(['status' => 'failed', 'error_message' => mb_substr($exception->getMessage(), 0, 2000)]);
            return back()->with($sessionKey.'_error', $exception->getMessage());
        }
    }
    public function exportWord(int $id)
    {
        Carbon::setLocale('id');

        $training = Training::with([
            'participants.evaluationL2',
            'schedules.pengajar',
        ])->findOrFail($id);
        $templatePath = public_path('templates/template_laporan_lv12.docx');
        abort_unless(file_exists($templatePath), 404, 'Template laporan Level 1 dan 2 tidak ditemukan.');

        $template = new TemplateProcessor($templatePath);
        $participants = $training->participants->sortBy('name')->values();
        $adminTextSummary = EvaluationL1TextSummary::where('training_id', $id)->first();
        $l1Results = EvaluationResultL1::where('training_id', $id)->get();
        $forms = EvaluationFormL1::with('schedule.pengajar')
            ->where('training_id', $id)
            ->orderByRaw("CASE WHEN type = 'penyelenggara' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get()
            ->unique(fn ($form) => $form->type . ':' . ($form->schedule_id ?? 'umum'))
            ->values();

        $scoreCategory = static fn (float $score): string => match (true) {
            $score > 90 => 'Sangat Baik',
            $score > 80 => 'Baik',
            $score > 70 => 'Cukup',
            $score > 60 => 'Kurang',
            default => 'Sangat Kurang',
        };
        $formatScore = static fn (?float $score): string => $score === null
            ? '–'
            : number_format($score, 1, ',', '.');
        $average = static function (Collection $values): ?float {
            $numeric = $values->filter(fn ($value) => $value !== null && is_numeric($value))->map(fn ($value) => (float) $value);
            return $numeric->isEmpty() ? null : round($numeric->avg(), 1);
        };

        $l1Scores = $l1Results->pluck('score')->filter(fn ($score) => $score !== null)->map(fn ($score) => (float) $score);
        $l1Average = $l1Scores->isEmpty() ? null : round($l1Scores->avg(), 1);
        $organizerScores = $l1Results->whereNull('schedule_id')->pluck('score')->filter(fn ($score) => $score !== null);
        $speakerScores = $l1Results->whereNotNull('schedule_id')->pluck('score')->filter(fn ($score) => $score !== null);
        $l1OrganizerAverage = $organizerScores->isEmpty() ? null : round($organizerScores->avg(), 1);
        $l1SpeakerAverage = $speakerScores->isEmpty() ? null : round($speakerScores->avg(), 1);
        $l1Respondents = $l1Results->pluck('participant_id')->unique()->count();

        $l1ObjectRows = $forms->map(function ($form, $index) use ($l1Results, $average, $formatScore, $scoreCategory) {
            $objectResults = $form->schedule_id
                ? $l1Results->where('schedule_id', $form->schedule_id)
                : $l1Results->whereNull('schedule_id');
            $score = $average($objectResults->pluck('score'));
            $name = $form->type === 'narasumber'
                ? ($form->schedule?->pengajar?->name ?? $form->target_name ?? 'Pengajar belum ditentukan')
                : ($form->target_name ?: 'Penyelenggara');

            return [
                'no' => $index + 1,
                'jenis' => $form->type === 'narasumber' ? 'Narasumber' : 'Penyelenggara',
                'objek' => $name,
                'materi' => $form->type === 'narasumber' ? ($form->schedule?->activity ?? $form->materi ?? '-') : '-',
                'responden' => $objectResults->pluck('participant_id')->unique()->count(),
                'skor' => $formatScore($score),
                'kategori' => $score === null ? 'Belum dinilai' : $scoreCategory($score),
            ];
        })->all();

        $questionIds = $l1Results->pluck('question_id')->filter()->unique();
        $questions = \App\Models\Question::whereIn('id', $questionIds)->get()->keyBy('id');
        $organizerTextFeedback = $l1Results
            ->filter(function ($result) use ($questions) {
                $question = $questions->get($result->question_id);

                return $result->schedule_id === null
                    && $question?->category === 'l1_penyelenggara'
                    && $question?->type === 'text'
                    && filled(trim((string) $result->note));
            })
            ->map(function ($result) use ($questions) {
                return [
                    'question' => $questions->get($result->question_id)?->question_text ?: 'Saran dan masukan',
                    'participant_id' => $result->participant_id,
                    'note' => trim((string) $result->note),
                ];
            })
            ->values();
        $l1IndicatorRows = $l1Results
            ->filter(fn ($result) => $result->score !== null && $questions->has($result->question_id))
            ->groupBy(fn ($result) => ($result->schedule_id ?? 'penyelenggara') . ':' . $result->question_id)
            ->map(function ($items, $key) use ($questions, $training, $average, $formatScore, $scoreCategory) {
                $first = $items->first();
                $question = $questions[$first->question_id];
                $schedule = $first->schedule_id ? $training->schedules->firstWhere('id', $first->schedule_id) : null;
                $score = $average($items->pluck('score'));

                return [
                    'objek' => $schedule
                        ? ($schedule->pengajar?->name ?? 'Pengajar belum ditentukan')
                        : 'Penyelenggara',
                    'indikator' => $question->question_text,
                    'responden' => $items->pluck('participant_id')->unique()->count(),
                    'skor' => $formatScore($score),
                    'kategori' => $score === null ? 'Belum dinilai' : $scoreCategory($score),
                    'score_value' => $score,
                ];
            })
            ->values()
            ->map(fn ($row, $index) => ['no' => $index + 1] + $row)
            ->all();

        $l2Rows = $participants->filter(fn ($participant) => $participant->evaluationL2 !== null)
            ->map(function ($participant, $index) {
                $pre = (float) $participant->evaluationL2->pretest;
                $post = (float) $participant->evaluationL2->postest;
                $delta = round($post - $pre, 1);

                return [
                    'no' => $index + 1,
                    'nama' => $participant->name,
                    'nip' => $participant->nip_nik ?: '-',
                    'pre' => number_format($pre, 1, ',', '.'),
                    'post' => number_format($post, 1, ',', '.'),
                    'delta' => ($delta > 0 ? '+' : '') . number_format($delta, 1, ',', '.'),
                    'status' => $delta > 0 ? 'Meningkat' : ($delta < 0 ? 'Menurun' : 'Tetap'),
                    'pre_value' => $pre,
                    'post_value' => $post,
                    'delta_value' => $delta,
                ];
            })->values();
        $l2Count = $l2Rows->count();
        $preAverage = $l2Count ? round($l2Rows->avg('pre_value'), 1) : null;
        $postAverage = $l2Count ? round($l2Rows->avg('post_value'), 1) : null;
        $gainAverage = $l2Count ? round($l2Rows->avg('delta_value'), 1) : null;
        $increasedCount = $l2Rows->where('status', 'Meningkat')->count();
        $sameCount = $l2Rows->where('status', 'Tetap')->count();
        $decreasedCount = $l2Rows->where('status', 'Menurun')->count();
        $increaseRate = $l2Count ? round(($increasedCount / $l2Count) * 100, 1) : 0;

        $values = [
            'nama_pelatihan' => strtoupper($training->nama_pelatihan),
            'tahunpelaksanaan' => Carbon::parse($training->tgl_mulai)->format('Y'),
            'tahunberjalan' => now()->format('Y'),
            'tanggal_mulai' => Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y'),
            'tanggal_selesai' => Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y'),
            'bidang' => $training->bidang ?: '-',
            'metode' => $training->metode ? ucwords(str_replace('_', ' ', $training->metode)) : '-',
            'jumlah_peserta' => $participants->count(),
            'jumlah_responden_l1' => $l1Respondents,
            'response_rate_l1' => $participants->count() ? round(($l1Respondents / $participants->count()) * 100, 1) . '%' : '0%',
            'jumlah_responden_l2' => $l2Count,
            'response_rate_l2' => $participants->count() ? round(($l2Count / $participants->count()) * 100, 1) . '%' : '0%',
            'skor_l1' => $formatScore($l1Average),
            'kategori_l1' => $l1Average === null ? 'Belum dapat dinilai' : $scoreCategory($l1Average),
            'skor_penyelenggara' => $formatScore($l1OrganizerAverage),
            'skor_narasumber' => $formatScore($l1SpeakerAverage),
            'rerata_pretest' => $formatScore($preAverage),
            'rerata_posttest' => $formatScore($postAverage),
            'rerata_gain' => $gainAverage === null ? '-' : (($gainAverage > 0 ? '+' : '') . number_format($gainAverage, 1, ',', '.')),
            'jumlah_meningkat' => $increasedCount,
            'jumlah_tetap' => $sameCount,
            'jumlah_menurun' => $decreasedCount,
            'persentase_meningkat' => number_format($increaseRate, 1, ',', '.') . '%',
        ];
        foreach ($values as $key => $value) {
            $template->setValue($key, htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8'));
        }

        $this->cloneRows($template, 'l1_obj_no', $l1ObjectRows, [
            'no', 'jenis', 'objek', 'materi', 'responden', 'skor', 'kategori',
        ], 'Belum ada formulir evaluasi Level 1.');
        $this->cloneRows($template, 'l1_ind_no', $l1IndicatorRows, [
            'no', 'objek', 'indikator', 'responden', 'skor', 'kategori',
        ], 'Belum ada jawaban indikator Level 1 yang dapat dihitung.');
        $this->cloneRows($template, 'l2_no', $l2Rows->map(fn ($row) => collect($row)->except(['pre_value', 'post_value', 'delta_value'])->all())->all(), [
            'no', 'nama', 'nip', 'pre', 'post', 'delta', 'status',
        ], 'Belum ada nilai pretest dan posttest.');

        $followUpRows = [];
        $totalParticipants = $participants->count();
        if ($totalParticipants > 0 && ($l1Respondents < $totalParticipants || $l2Count < $totalParticipants)) {
            $followUpRows[] = [
                'temuan' => "Cakupan data belum lengkap: Level 1 {$l1Respondents}/{$totalParticipants} peserta dan Level 2 {$l2Count}/{$totalParticipants} peserta.",
                'tindakan' => 'Melakukan verifikasi peserta yang belum mengisi, mengirim pengingat terjadwal, dan menutup pengumpulan data setelah target keterisian tercapai.',
                'penanggung' => 'Admin bidang dan panitia pelatihan',
                'waktu' => 'Maksimal 7 hari setelah evaluasi',
                'indikator' => 'Seluruh peserta memiliki status pengisian yang terverifikasi dan tingkat keterisian mencapai 100% atau disertai alasan ketidakisian.',
            ];
        }
        $lowestIndicator = collect($l1IndicatorRows)
            ->filter(fn ($row) => $row['score_value'] !== null)
            ->sortBy('score_value')
            ->first();
        if ($lowestIndicator) {
            $followUpRows[] = [
                'temuan' => "Indikator Level 1 terendah pada {$lowestIndicator['objek']}: {$lowestIndicator['indikator']} dengan skor {$lowestIndicator['skor']} ({$lowestIndicator['kategori']}).",
                'tindakan' => $lowestIndicator['score_value'] <= 80
                    ? 'Melakukan telaah akar masalah bersama penyelenggara/pengajar, menetapkan perbaikan layanan atau strategi fasilitasi, dan memantau penerapannya pada angkatan berikutnya.'
                    : 'Mempertahankan praktik yang sudah baik, mendokumentasikan cara kerja efektif, dan menjadikannya standar minimum untuk pelatihan berikutnya.',
                'penanggung' => str_contains(strtolower($lowestIndicator['objek']), 'penyelenggara')
                    ? 'Ketua penyelenggara dan admin bidang'
                    : 'Pengajar terkait dan penanggung jawab akademik',
                'waktu' => 'Sebelum pelaksanaan angkatan berikutnya',
                'indikator' => $lowestIndicator['score_value'] <= 80
                    ? 'Rencana perbaikan terdokumentasi dan skor indikator meningkat minimal 5 poin pada evaluasi berikutnya.'
                    : 'Skor indikator dipertahankan di atas 80 dan praktik baik diterapkan secara konsisten.',
            ];
        }
        if ($organizerTextFeedback->isNotEmpty()) {
            $followUpRows[] = [
                'temuan' => $organizerTextFeedback->count().' saran dan masukan tertulis diterima dari '.$organizerTextFeedback->pluck('participant_id')->unique()->count().' peserta.',
                'tindakan' => $adminTextSummary
                    ? trim($adminTextSummary->follow_up)
                    : 'Menelaah seluruh masukan tertulis peserta dan menetapkan tindakan perbaikan penyelenggaraan.',
                'penanggung' => 'Ketua penyelenggara dan admin bidang',
                'waktu' => 'Sebelum pelaksanaan angkatan berikutnya',
                'indikator' => 'Setiap masukan telah ditelaah dan isu prioritas memiliki keputusan tindak lanjut yang terdokumentasi.',
            ];
        }
        if ($l2Count > 0 && ($gainAverage <= 0 || $decreasedCount > 0)) {
            $followUpRows[] = [
                'temuan' => "Rerata perubahan nilai {$values['rerata_gain']} poin; {$decreasedCount} peserta menurun dan {$sameCount} peserta tetap.",
                'tindakan' => 'Menelaah keselarasan tujuan, materi, metode, dan butir asesmen; memberikan penguatan atau remediasi kepada peserta yang nilainya tetap/menurun.',
                'penanggung' => 'Penanggung jawab akademik dan pengajar',
                'waktu' => 'Maksimal 14 hari setelah evaluasi',
                'indikator' => 'Tersedia hasil telaah asesmen, daftar peserta tindak lanjut, dan bukti pelaksanaan remediasi/penguatan.',
            ];
        } elseif ($l2Count > 0) {
            $followUpRows[] = [
                'temuan' => "Rerata hasil belajar meningkat {$values['rerata_gain']} poin dan {$increasedCount} peserta ({$values['persentase_meningkat']}) mengalami peningkatan.",
                'tindakan' => 'Mempertahankan strategi pembelajaran yang efektif, mendokumentasikan praktik baik, dan memberikan pendampingan khusus kepada peserta yang belum meningkat.',
                'penanggung' => 'Penanggung jawab akademik dan pengajar',
                'waktu' => 'Sebelum dan pada angkatan berikutnya',
                'indikator' => 'Praktik baik masuk dalam rencana pembelajaran dan proporsi peserta yang meningkat pada angkatan berikutnya tidak menurun.',
            ];
        }
        if (empty($followUpRows)) {
            $followUpRows[] = [
                'temuan' => 'Data evaluasi belum memadai untuk menetapkan tindak lanjut substantif.',
                'tindakan' => 'Melengkapi formulir Level 1 serta nilai pretest dan posttest sebelum rapat evaluasi.',
                'penanggung' => 'Admin bidang dan panitia pelatihan',
                'waktu' => 'Sebelum laporan ditetapkan',
                'indikator' => 'Data minimum tersedia dan dapat dianalisis.',
            ];
        }
        $followUpRows = collect($followUpRows)->values()->map(fn ($row, $index) => ['no' => $index + 1] + $row)->all();
        $this->cloneRows($template, 'rtl12_temuan', $followUpRows, [
            'temuan', 'tindakan', 'penanggung', 'waktu', 'indikator',
        ], 'Belum tersedia tindak lanjut.');

        $l1Narrative = $l1Average === null
            ? 'Belum tersedia skor numerik Level 1 yang cukup untuk menggambarkan reaksi peserta.'
            : "Rerata reaksi peserta adalah {$formatScore($l1Average)}/100 dengan kategori {$scoreCategory($l1Average)}. Rerata penyelenggara {$formatScore($l1OrganizerAverage)} dan rerata narasumber {$formatScore($l1SpeakerAverage)}.";
        $l2Narrative = $l2Count === 0
            ? 'Belum tersedia pasangan nilai pretest dan posttest untuk menganalisis perubahan hasil belajar.'
            : "Dari {$l2Count} peserta yang memiliki nilai, rerata berubah dari {$formatScore($preAverage)} menjadi {$formatScore($postAverage)}, dengan selisih {$values['rerata_gain']} poin. Sebanyak {$increasedCount} peserta ({$values['persentase_meningkat']}) mengalami peningkatan.";
        $template->setValue('narasi_l1', $l1Narrative);
        $template->setValue('narasi_l2', $l2Narrative);
        $template->setValue('ringkasan_temuan', $l1Narrative . ' ' . $l2Narrative);
        $template->setValue('keterbatasan',
            'Laporan menggunakan data yang tersedia saat dokumen diunduh. Level 1 menggambarkan persepsi responden, sedangkan Level 2 menggambarkan perubahan skor pretest-posttest. Data tidak dengan sendirinya membuktikan bahwa seluruh perubahan disebabkan oleh pelatihan.');
        $template->setValue('kesimpulan',
            ($l1Average !== null || $l2Count > 0)
                ? $l1Narrative . ' ' . $l2Narrative
                : 'Data belum memadai untuk menarik kesimpulan evaluasi Level 1 dan Level 2.');
        $recommendationSections = [
            'Rekomendasi disusun berdasarkan hasil Evaluasi Level 1, hasil pretest-posttest Level 2, tingkat keterisian data, indikator prioritas, serta telaah saran dan masukan peserta. Pelaksanaannya perlu dipantau melalui bukti tindak lanjut dan dievaluasi kembali pada penyelenggaraan berikutnya.',
            "DASAR ANALISIS\n"
                ."• Level 1: {$l1Respondents} dari {$participants->count()} peserta memberikan respons ({$values['response_rate_l1']}), dengan rerata {$values['skor_l1']} dan kategori {$values['kategori_l1']}.\n"
                ."• Penyelenggara: rerata skor {$values['skor_penyelenggara']}; narasumber: rerata skor {$values['skor_narasumber']}.\n"
                ."• Level 2: {$l2Count} dari {$participants->count()} peserta memiliki pasangan nilai ({$values['response_rate_l2']}), dengan perubahan rerata {$values['rerata_gain']} poin.\n"
                ."• Masukan tertulis: {$organizerTextFeedback->count()} masukan dari {$organizerTextFeedback->pluck('participant_id')->unique()->count()} peserta.",
        ];

        if ($adminTextSummary) {
            $recommendationSections[] = "KESIMPULAN UMUM SARAN DAN MASUKAN\n".trim($adminTextSummary->conclusion);
        } elseif ($organizerTextFeedback->isNotEmpty()) {
            $recommendationSections[] = "KESIMPULAN UMUM SARAN DAN MASUKAN\nKesimpulan belum ditetapkan oleh admin. Seluruh masukan perlu ditelaah sebelum laporan digunakan sebagai dasar keputusan perbaikan.";
        }

        $priorityRecommendations = collect($followUpRows)->map(function ($row, $index) {
            return ($index + 1).'. Temuan: '.$row['temuan']."\n"
                .'   Tindakan perbaikan: '.$row['tindakan']."\n"
                .'   Penanggung jawab: '.$row['penanggung']."\n"
                .'   Target waktu: '.$row['waktu']."\n"
                .'   Indikator keberhasilan: '.$row['indikator'];
        })->implode("\n\n");
        $recommendationSections[] = "PRIORITAS REKOMENDASI DAN TINDAK LANJUT\n".$priorityRecommendations;
        $recommendationSections[] = "ARAH PELAKSANAAN\n"
            .'Penanggung jawab perlu menetapkan bukti pelaksanaan untuk setiap tindakan, memantau pencapaian indikator sesuai target waktu, dan mendokumentasikan hasil verifikasi. Rekomendasi yang belum tercapai harus dibawa ke rapat evaluasi berikutnya sampai dinyatakan selesai.';

        $recommendationText = implode("\n\n", $recommendationSections);
        $template->setValue('rekomendasi', htmlspecialchars($recommendationText, ENT_QUOTES | ENT_XML1, 'UTF-8'));

        $fileName = 'LAPORAN_EVALUASI_LV1_LV2_' . str_replace(' ', '_', $training->nama_pelatihan) . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'lv12_');
        $template->saveAs($tempFile);
        $content = file_get_contents($tempFile);
        unlink($tempFile);

        DocumentController::archiveInternal($id, 'LAPORAN EVALUASI LEVEL 1 DAN 2', $fileName, $content, 'docx');

        return response()->streamDownload(static fn () => print($content), $fileName);
    }

    private function cloneRows(TemplateProcessor $template, string $anchor, array $rows, array $fields, string $emptyMessage): void
    {
        if (empty($rows)) {
            $rows = [array_fill_keys($fields, '-')];
            $rows[0][$fields[0]] = 1;
            $rows[0][$fields[1]] = $emptyMessage;
        }

        $template->cloneRow($anchor, count($rows));
        $prefix = str_ends_with($anchor, '_no')
            ? substr($anchor, 0, -3)
            : substr($anchor, 0, strrpos($anchor, '_'));
        foreach ($rows as $index => $row) {
            $number = $index + 1;
            foreach ($fields as $field) {
                $template->setValue($prefix . "_{$field}#{$number}", htmlspecialchars((string) ($row[$field] ?? '-'), ENT_QUOTES | ENT_XML1, 'UTF-8'));
            }
        }
    }

    private function recommendation(?float $l1Average, ?float $gainAverage, int $l1Respondents, int $l2Respondents, int $participants): string
    {
        $recommendations = [];
        if ($participants > 0 && ($l1Respondents < $participants || $l2Respondents < $participants)) {
            $recommendations[] = 'Meningkatkan kelengkapan pengisian evaluasi dan pencatatan nilai agar hasil lebih representatif.';
        }
        if ($l1Average !== null && $l1Average <= 80) {
            $recommendations[] = 'Menelaah indikator reaksi dengan skor terendah dan menetapkan perbaikan pada penyelenggaraan maupun dukungan narasumber.';
        }
        if ($gainAverage !== null && $gainAverage <= 0) {
            $recommendations[] = 'Meninjau kesesuaian materi, metode pembelajaran, asesmen, dan dukungan belajar karena rerata hasil belum menunjukkan peningkatan.';
        } elseif ($gainAverage !== null) {
            $recommendations[] = 'Mempertahankan strategi pembelajaran yang efektif dan menindaklanjuti peserta yang nilainya tetap atau menurun.';
        }

        return $recommendations
            ? implode(' ', $recommendations)
            : 'Melengkapi data evaluasi terlebih dahulu sebelum menetapkan rekomendasi program.';
    }
}
