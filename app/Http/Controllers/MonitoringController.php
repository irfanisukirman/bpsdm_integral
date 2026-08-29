<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Question;
use App\Models\MonitoringResult;
use App\Models\MonitoringSummary;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use App\Exports\MonitoringCeklisExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonitoringController extends Controller
{
    /**
     * Menampilkan daftar pelatihan untuk monitoring
     */
    public function index()
    {
        $query = Training::query();

        // Probis: Admin Bidang hanya melihat pelatihan bidangnya sendiri
        if (Auth::user()->role !== 'superadmin') {
            $query->where('bidang', Auth::user()->bidang);
        }

        $trainings = $query->latest()->get();

        return view('monitoring.index', compact('trainings'));
    }

    /**
     * Form pengisian instrumen monitoring
     */
    public function create(Request $request, $id)
    {
        // Eager Load relasi agar data jawaban dan kesimpulan terbawa
        $training = Training::with(['stages', 'monitoringResults'])->findOrFail($id);
        $this->authorizeTraining($training);
        $organizers = \App\Models\User::where('role', 'admin_bidang')
            ->whereNotNull('bidang')
            ->orderBy('bidang')
            ->get()
            ->unique('bidang')
            ->values();
        $monitoringDate = null;
        if ($training->model === 'standar') {
            $requestedDate = $request->query('monitoring_date', now()->toDateString());
            $monitoringDate = Carbon::parse($requestedDate);
            $startDate = Carbon::parse($training->tgl_mulai)->startOfDay();
            $endDate = Carbon::parse($training->tgl_selesai)->endOfDay();
            if (!$monitoringDate->betweenIncluded($startDate, $endDate)) {
                $monitoringDate = $startDate;
            }
            $monitoringDate = $monitoringDate->toDateString();
        }

        $questionsByStage = [];
        if ($training->model == 'standar') {
            $questionsByStage['standar'] = \App\Models\Question::where('category', 'LIKE', 'Monitoring%')
                ->where(function($q) use ($training) {
                    $q->where('metode', $training->metode)->orWhere('metode', 'semua');
                })->get()->groupBy('category');
        } else {
            foreach ($training->stages as $st) {
                $questionsByStage[$st->id] = \App\Models\Question::where('category', 'LIKE', 'Monitoring%')
                    ->where(function($q) use ($st) {
                        $q->where('metode', $st->metode)->orWhere('metode', 'semua');
                    })->get()->groupBy('category');
            }
        }

        return view('monitoring.fill', compact('training', 'questionsByStage', 'organizers', 'monitoringDate'));
    }

    /**
     * Simpan hasil pengisian instrumen
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'stage_id' => 'required',
            'ans' => 'required|array',
            'final_conclusion' => 'required|string',
            'monitoring_date' => 'nullable|date',
        ]);
        $training = Training::with('stages')->findOrFail($id);
        $this->authorizeTraining($training);

        // Konversi stage_id ke integer agar sinkron dengan database
        $stage_id = ($request->stage_id === 'std' || $request->stage_id === null) ? null : (int)$request->stage_id;
        $stage = $stage_id ? $training->stages->firstWhere('id', $stage_id) : null;
        abort_if($stage_id && !$stage, 422, 'Tahapan tidak sesuai dengan pelatihan.');
        $monitoringDate = $stage_id ? null : ($request->monitoring_date ?: now()->toDateString());

        foreach ($request->ans as $qId => $answer) {
            if (!in_array($answer, ['ya', 'tidak'], true)) {
                throw ValidationException::withMessages(["ans.$qId" => 'Jawaban indikator tidak valid.']);
            }
            if ($answer === 'tidak') {
                $required = [
                    'notes' => 'Temuan wajib dijelaskan.',
                    'target' => 'Bidang tujuan tindak lanjut wajib dipilih.',
                    'recommendation' => 'Rekomendasi tindakan wajib diisi.',
                    'priority' => 'Prioritas wajib dipilih.',
                    'due_date' => 'Batas waktu tindak lanjut wajib diisi.',
                ];
                foreach ($required as $field => $message) {
                    if (blank($request->input("{$field}.{$qId}"))) {
                        throw ValidationException::withMessages(["{$field}.{$qId}" => $message]);
                    }
                }
            }
        }

        DB::transaction(function () use ($request, $id, $stage_id, $monitoringDate, $training, $stage) {
            // 1. Simpan Jawaban Indikator
            foreach ($request->ans as $q_id => $answer) {
                $method = $stage?->metode ?? $training->metode;
                $question = \App\Models\Question::where('category', 'LIKE', 'Monitoring%')
                    ->where(function ($query) use ($method) {
                        $query->where('metode', $method)->orWhere('metode', 'semua');
                    })
                    ->findOrFail($q_id);
                $keys = [
                    'training_id' => $id,
                    'training_stage_id' => $stage_id,
                    'monitoring_date' => $monitoringDate,
                    'question_id' => $q_id,
                ];
                $existing = MonitoringResult::where($keys)->first();
                $isChangedFinding = $existing && $answer === 'tidak' && (
                    $existing->notes !== $request->input("notes.$q_id") ||
                    $existing->recommendation !== $request->input("recommendation.$q_id") ||
                    $existing->follow_up_target !== $request->input("target.$q_id")
                );
                $workflowStatus = $answer === 'ya'
                    ? 'not_required'
                    : (($existing && !$isChangedFinding && in_array($existing->workflow_status, ['submitted', 'verified'], true))
                        ? $existing->workflow_status
                        : 'open');

                MonitoringResult::updateOrCreate(
                [
                    ...$keys,
                ],
                [
                    'category' => $question->sub_category ?? $question->category,
                    'answer' => $answer,
                    'notes' => $answer === 'tidak' ? $request->input("notes.$q_id") : null,
                    'recommendation' => $answer === 'tidak' ? $request->input("recommendation.$q_id") : null,
                    'follow_up_target' => $answer === 'tidak' ? $request->input("target.$q_id") : null,
                    'priority' => $answer === 'tidak' ? $request->input("priority.$q_id", 'sedang') : 'sedang',
                    'due_date' => $answer === 'tidak' ? $request->input("due_date.$q_id") : null,
                    'workflow_status' => $workflowStatus,
                    'is_resolved' => $workflowStatus === 'verified',
                ]
                );
            }

            // 2. Simpan Kesimpulan per Kategori
            if ($request->has('category_conclusions')) {
                foreach ($request->category_conclusions as $cat => $text) {
                    \App\Models\MonitoringSummary::updateOrCreate(
                        ['training_id' => $id, 'training_stage_id' => $stage_id, 'category' => $cat],
                        ['conclusion' => $text]
                    );
                }
            }

            // 3. Simpan Kesimpulan Akhir Tahapan
            \App\Models\MonitoringSummary::updateOrCreate(
                ['training_id' => $id, 'training_stage_id' => $stage_id, 'category' => 'STAGE_FINAL_SUMMARY'],
                ['conclusion' => $request->final_conclusion]
            );
        });

        return redirect()->back()->with('success', 'Data untuk tahapan berhasil disimpan dan diperbarui.');
    }

    public function storeFinalSummary(Request $request, $id)
    {
        \App\Models\MonitoringSummary::updateOrCreate(
            ['training_id' => $id, 'category' => 'FINAL_SUMMARY', 'training_stage_id' => null],
            ['conclusion' => $request->final_conclusion]
        );

        return redirect()->back()->with('success', 'Kesimpulan akhir berhasil disimpan.');
    }

    /**
     * Placeholder untuk fungsi Export (Sempurnakan sesuai kebutuhan template)
     */
    public function exportLaporan($id, Request $request)
    {
        $training = Training::with(['stages', 'summaries'])->findOrFail($id);
        $stage_id = $request->query('stage_id'); 
        
        $stage = $training->stages->where('id', $stage_id)->first();
        $metode = $stage ? $stage->metode : $training->metode;
        $nama_tahapan = $stage ? $stage->nama_tahapan : 'Utama';

        $templateProcessor = new TemplateProcessor(public_path('templates/laporan_monitoring.docx'));

        // 1. Data Global
        $templateProcessor->setValue('nama_pelatihan', $training->nama_pelatihan);
        $templateProcessor->setValue('jumlah_peserta', $training->jumlah_peserta);
        $templateProcessor->setValue('tgl_mulai', \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y'));
        $templateProcessor->setValue('tgl_selesai', \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y'));
        $templateProcessor->setValue('tahapan', $nama_tahapan);

        // 2. Logika Kesimpulan (Manual vs Standar)
        $categories = [
            'Monitoring Penyelenggara' => 'kesimpulan_penyelenggara',
            'Monitoring Peserta' => 'kesimpulan_peserta',
            'Monitoring Tenaga Kediklatan' => 'kesimpulan_tenaga',
            'Monitoring Sarana Prasarana' => 'kesimpulan_sarpras',
            'STAGE_FINAL_SUMMARY' => 'kesimpulan_final'
        ];

        foreach ($categories as $dbCategory => $wordPlaceholder) {
            $manualEntry = $training->summaries
                ->where('training_stage_id', $stage_id)
                ->where('category', $dbCategory)
                ->first();

            if ($manualEntry && !empty($manualEntry->conclusion)) {
                $text = $manualEntry->conclusion;
            } else {
                $text = $this->getFallbackConclusion($dbCategory, $metode, $training->nama_pelatihan);
            }
            $templateProcessor->setValue($wordPlaceholder, $text);
        }

        // --- PROSES AUTO ARCHIVE ---
        $fileName = "LAPORAN_MONITORING_" . str_replace(' ', '_', $training->nama_pelatihan) . "_$nama_tahapan.docx";
        
        // Simpan ke temp untuk ambil content
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $templateProcessor->saveAs($tempFile);
        $fileContent = file_get_contents($tempFile);

        // Panggil fungsi arsip di DocumentController
        \App\Http\Controllers\DocumentController::archiveInternal(
            $training->id, 
            'LAPORAN MONITORING', 
            $fileName, 
            $fileContent, 
            'docx'
        );

        unlink($tempFile); // Hapus file temp

        return response()->streamDownload(function() use($fileContent) {
            echo $fileContent;
        }, $fileName);
    }

    public function exportTindakLanjut($id, Request $request)
    {
        $training = Training::findOrFail($id);
        $stage_id = $request->query('stage_id');
        
        // Load Template
        $templateProcessor = new TemplateProcessor(public_path('templates/laporan_tindak_lanjut.docx'));

        // Header Dokumen
        $templateProcessor->setValue('nama_pelatihan', $training->nama_pelatihan);
        $templateProcessor->setValue('tanggal_cetak', Carbon::now()->translatedFormat('d F Y'));

        // Daftar Pilar dan Kategori Database
        $pillars = [
            1 => 'Monitoring Penyelenggara',
            2 => 'Monitoring Peserta',
            3 => 'Monitoring Tenaga Kediklatan',
            4 => 'Monitoring Sarana Prasarana'
        ];

        $goldenParagraph = "Dalam pilar ini, tim monitoring melaporkan bahwa tidak ditemukan kendala maupun temuan yang bersifat signifikan selama proses pelaksanaan kegiatan. Seluruh komponen dan indikator yang menjadi objek monitoring telah dilaksanakan sesuai dengan standar, prosedur, serta ketentuan yang berlaku. Berdasarkan hasil pemantauan, implementasi pada pilar ini berjalan dengan baik, tertib, dan konsisten sehingga tidak memerlukan tindak lanjut maupun perbaikan khusus. Ke depan, kondisi yang sudah baik ini diharapkan dapat terus dipertahankan dan ditingkatkan guna menjaga kualitas pelaksanaan kegiatan secara berkelanjutan.";

        foreach ($pillars as $num => $categoryName) {
            $findings = MonitoringResult::where('training_id', $id)
                ->where('category', $categoryName)
                ->where('answer', 'tidak')
                ->get();

            if ($findings->isEmpty()) {
                $templateProcessor->setValue("ket$num", $goldenParagraph);
                // Isi satu baris saja dengan keterangan kosong/strip agar XML tabel tidak rusak
                $templateProcessor->cloneRow("n$num", 1);
                $templateProcessor->setValue("n{$num}#1", "-");
                $templateProcessor->setValue("ind{$num}#1", "Seluruh indikator terpenuhi (YA)");
                $templateProcessor->setValue("tem{$num}#1", "-");
                $templateProcessor->setValue("tuj{$num}#1", "-");
                // 1. Tampilkan paragraf "Tidak ada temuan"
                $templateProcessor->setValue("ket$num", $goldenParagraph);
                
                // 2. HAPUS tabel (beserta tag block-nya)
                $templateProcessor->deleteBlock("block$num");
            } else {
                // 1. Tampilkan teks pengantar
                $templateProcessor->setValue("ket$num", "Ditemukan beberapa hal yang memerlukan tindak lanjut:");

                // Cukup lakukan cloneRow seperti biasa
                $templateProcessor->cloneRow("n$num", $findings->count());
                foreach ($findings as $index => $res) {
                    $row = $index + 1;
                    $templateProcessor->setValue("n{$num}#{$row}", $row);
                    $templateProcessor->setValue("ind{$num}#{$row}", $res->question->question_text ?? '-');
                    $templateProcessor->setValue("tem{$num}#{$row}", $res->notes ?? '-');
                    $templateProcessor->setValue("tuj{$num}#{$row}", $res->follow_up_target ?? '-');
                }
                
            }
        }

        $attachments = \App\Models\MonitoringResult::with('question')
            ->where('training_id', $id)
            ->where('training_stage_id', $stage_id)
            ->whereNotNull('evidence_file')
            ->get();

        if ($attachments->isNotEmpty()) {
            // Clone baris berdasarkan jumlah lampiran
            $templateProcessor->cloneRow('la', $attachments->count());

            foreach ($attachments as $index => $res) {
                $rowNum = $index + 1;
                
                // 1. Set Nomor
                $templateProcessor->setValue("la#$rowNum", $rowNum);
                
                // 2. Set Nama Indikator & Catatan Temuan (Digabung agar ringkas)
                $indikatorText = ($res->question->question_text ?? 'Indikator') . "\nTemuan: " . ($res->notes ?? '-');
                $templateProcessor->setValue("ind_la#$rowNum", $indikatorText);
                
                // 3. Set Tautan URL (Menghasilkan link lengkap: http://domain.com/storage/...)
                $fileUrl = url('storage/' . $res->evidence_file);
                $templateProcessor->setValue("url_la#$rowNum", $fileUrl);
            }
        } else {
            // Jika tidak ada lampiran sama sekali, isi dengan keterangan "-"
            $templateProcessor->cloneRow('la', 1);
            $templateProcessor->setValue("la#1", "-");
            $templateProcessor->setValue("ind_la#1", "Tidak ada dokumen yang diunggah");
            $templateProcessor->setValue("url_la#1", "-");
        }

        // Simpan dan Download
        $fileName = "LAPORAN_TINDAK_LANJUT_" . str_replace(' ', '_', $training->nama_pelatihan) . ".docx";
        $tempFile = tempnam(sys_get_temp_dir(), 'PHPWord');
        $templateProcessor->saveAs($tempFile);
        $fileContent = file_get_contents($tempFile);

        \App\Http\Controllers\DocumentController::archiveInternal($training->id, 'LAPORAN TINDAK LANJUT', $fileName, $fileContent, 'docx');
        unlink($tempFile);

        return response()->streamDownload(function() use($fileContent) { echo $fileContent; }, $fileName);
    }

    public function exportCeklis($id)
    {
        $training = Training::with(['stages', 'monitoringResults.question', 'summaries'])->findOrFail($id);
        
        // Ambil data pendukung untuk export
        $questions = \App\Models\Question::where('category', 'LIKE', 'Monitoring%')->orderBy('category')->get()->groupBy('category');
        $stages = $training->model == 'standar' ? [(object)['id' => null, 'nama_tahapan' => 'Pelatihan', 'metode' => $training->metode, 'tgl_mulai' => $training->tgl_mulai, 'tgl_selesai' => $training->tgl_selesai]] : $training->stages;

        $export = new MonitoringCeklisExport($training, $questions, $stages);
        $fileName = 'CEKLIS_MONITORING_' . str_replace(' ', '_', $training->nama_pelatihan) . '.xlsx';

        // --- PROSES AUTO ARCHIVE ---
        $fileContent = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);

        \App\Http\Controllers\DocumentController::archiveInternal($training->id, 'CEKLIS MONITORING', $fileName, $fileContent, 'xlsx');

        return response()->streamDownload(function() use($fileContent) { echo $fileContent; }, $fileName);
    }

    private function getFallbackConclusion($category, $metode, $namaPelatihan)
    {
        // Normalisasi metode agar sesuai key (Case Insensitive)
        $metodeKey = (strtolower($metode) == 'klasikal') ? 'Klasikal' : 'Full Learning';

        $templates = [
            "Monitoring Penyelenggara" => [
                "Full Learning" => "Berdasarkan hasil monitoring penyelenggaraan pelatihan {TP}, dapat disimpulkan bahwa pelaksanaan kegiatan telah memenuhi sebagian besar indikator yang dipersyaratkan. Seluruh komponen monitoring menunjukkan hasil “YA”, mulai dari kepemilikan SP/SK, kompetensi IT, hingga tersedianya host virtual yang kompeten. Penyelenggara telah menyiapkan evaluasi lengkap (pre-test, post-test, dan evaluasi penyelenggaraan) sehingga kegiatan berlangsung efektif.",
                "Klasikal" => "Berdasarkan hasil monitoring penyelenggara pelatihan {TP}, dapat disimpulkan bahwa seluruh proses penyelenggaraan kegiatan telah dilaksanakan dengan baik dan sesuai dengan standar. Seluruh indikator monitoring menunjukkan hasil “YA”. Penyelenggara memiliki sertifikat MOT/TOC serta SP Panitia yang resmi. Administrasi seperti biodata, daftar hadir, dan tanda pengenal peserta telah dipersiapkan secara profesional."
            ],
            "Monitoring Peserta" => [
                "Full Learning" => "Berdasarkan hasil monitoring peserta pada pelatihan {TP}, seluruh peserta telah memenuhi indikator yang ditetapkan. Peserta memiliki akun aktif di LMS, memenuhi presensi di atas 85%, serta mematuhi tata tertib kelas daring (on-camera). Partisipasi aktif dalam diskusi kelompok dan penyelesaian penugasan menunjukkan keterlibatan yang positif.",
                "Klasikal" => "Berdasarkan hasil monitoring peserta pada pelatihan {TP}, seluruh peserta telah mengikuti kegiatan secara disiplin dan aktif. Peserta memenuhi kualifikasi, hadir tepat waktu di kelas, dan mentaati tata tertib luring. Tingkat kehadiran terpantau sangat baik dan seluruh peserta terlibat aktif dalam dinamika pembelajaran langsung."
            ],
            "Monitoring Tenaga Kediklatan" => [
                "Full Learning" => "Berdasarkan hasil monitoring tenaga kediklatan pelatihan {TP}, seluruh fasilitator telah memenuhi indikator. Fasilitator hadir tepat waktu, menguasai fitur platform daring (polling/whiteboard), serta menyediakan bahan ajar yang lengkap di LMS. Etika profesionalisme pengajar ASN tetap terjaga selama sesi berlangsung.",
                "Klasikal" => "Berdasarkan hasil monitoring tenaga kediklatan pada pelatihan {TP}, tenaga kediklatan telah menjalankan tugas secara profesional. Koordinasi dengan fasilitator berjalan harmonis, penyampaian panduan jelas, dan kedisiplinan waktu terjaga. Pengarsipan dokumen pelatihan dilakukan secara sistematis untuk kebutuhan laporan."
            ],
            "Monitoring Sarana Prasarana" => [ // Nama kategori disesuaikan dengan bank soal
                "Full Learning" => "Berdasarkan hasil monitoring sarana prasarana pelatihan {TP}, fasilitas pendukung berfungsi sangat baik. LMS stabil, platform Video Conference memiliki lisensi memadai, dan jaringan internet panitia lancar. Perangkat broadcasting berfungsi optimal didukung helpdesk teknis yang responsif.",
                "Klasikal" => "Berdasarkan hasil monitoring sarana prasarana pada pelatihan {TP}, seluruh fasilitas tersedia sesuai standar. Kursi-meja belajar layak, proyektor berfungsi baik, dan jaringan internet LAN/WAN tersedia. Sarana pendukung seperti ruang ibadah dan perlengkapan P3K juga tersedia dalam kondisi siap digunakan."
            ],
            "STAGE_FINAL_SUMMARY" => [
                "Full Learning" => "Pelaksanaan pelatihan {TP} secara keseluruhan berjalan efektif, tertib, dan profesional sesuai dengan standar penyelenggaraan daring yang ditetapkan.",
                "Klasikal" => "Secara keseluruhan, pelaksanaan pelatihan {TP} berlangsung kondusif dan mampu mendukung tercapainya tujuan kegiatan secara optimal sesuai standar."
            ]
        ];

        $text = $templates[$category][$metodeKey] ?? "";
        return str_replace('{TP}', $namaPelatihan, $text);
    }

    private function authorizeTraining(Training $training): void
    {
        $user = Auth::user();
        abort_unless(
            $user && ($user->role === 'superadmin' || ($user->role === 'admin_bidang' && $user->bidang === $training->bidang)),
            403
        );
    }
    
    
}
