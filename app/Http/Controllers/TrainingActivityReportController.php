<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\EvaluationL1TextSummary;
use App\Models\EvaluationResultL1;
use App\Models\EvaluationResultL2;
use App\Models\EvaluationResultL34;
use App\Models\Training;
use App\Models\TrainingActivityDocumentation;
use App\Models\TrainingActivityReport;
use App\Models\TrainingActivityReportVersion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;

class TrainingActivityReportController extends Controller
{
    private const NARRATIVES = ['background', 'legal_basis', 'objectives', 'implementation', 'achievements', 'constraints', 'follow_up', 'conclusion', 'recommendations'];

    public function index(Training $training)
    {
        $this->authorizeTraining($training);
        $report = TrainingActivityReport::firstOrCreate(['training_id' => $training->id]);
        $report->load(['versions.generator', 'updater']);
        $photos = TrainingActivityDocumentation::with('uploader')->where('training_id', $training->id)->orderBy('sort_order')->orderBy('id')->get();
        $data = $this->reportData($training, $report);
        $checks = $this->completeness($training, $report, $photos, $data);
        $codes = $this->templateCodes();

        return view('trainings.activity-report.index', compact('training', 'report', 'photos', 'data', 'checks', 'codes'));
    }

    public function update(Request $request, Training $training)
    {
        $this->authorizeTraining($training);
        $rules = ['report_number' => 'nullable|string|max:255', 'signatory_name' => 'nullable|string|max:255', 'signatory_nip' => 'nullable|string|max:100', 'signatory_position' => 'nullable|string|max:255', 'approval_date' => 'nullable|date'];
        foreach (self::NARRATIVES as $field) {
            $rules[$field] = 'nullable|string|max:30000';
        }
        $data = $request->validate($rules);
        $data['updated_by'] = Auth::id();
        $data['status'] = 'draft';
        TrainingActivityReport::updateOrCreate(['training_id' => $training->id], $data);

        return back()->with('success', 'Narasi dan identitas laporan berhasil disimpan sebagai draft.');
    }

    public function uploadTemplate(Request $request, Training $training)
    {
        $this->authorizeTraining($training);
        $request->validate(['template' => 'required|file|mimes:docx|max:20480']);
        $report = TrainingActivityReport::firstOrCreate(['training_id' => $training->id]);
        $path = $request->file('template')->store('activity-report-templates', 'local');
        try {
            $processor = new TemplateProcessor(Storage::disk('local')->path($path));
            $variables = $processor->getVariables();
            abort_if(empty($variables), 422, 'Template tidak memiliki kode ${...}. Gunakan template contoh sebagai acuan.');
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);

            return back()->with('error', 'Template DOCX tidak dapat dibaca: '.$exception->getMessage());
        }
        if ($report->template_path) {
            Storage::disk('local')->delete($report->template_path);
        }
        $report->update(['template_path' => $path, 'updated_by' => Auth::id(), 'status' => 'draft']);

        return back()->with('success', 'Template laporan berhasil diunggah dan memuat '.count($variables).' kode.');
    }

    public function resetTemplate(Training $training)
    {
        $this->authorizeTraining($training);
        $report = TrainingActivityReport::firstOrCreate(['training_id' => $training->id]);
        if ($report->template_path) {
            Storage::disk('local')->delete($report->template_path);
        }
        $report->update(['template_path' => null, 'status' => 'draft', 'updated_by' => Auth::id()]);

        return back()->with('success', 'Template dikembalikan ke template standar sistem.');
    }

    public function storePhotos(Request $request, Training $training)
    {
        $this->authorizeTraining($training);
        $data = $request->validate([
            'photos' => 'required|array|min:1|max:20', 'photos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            'title' => 'required|string|max:255', 'caption' => 'nullable|string|max:1000',
            'category' => 'required|in:pembukaan,pembelajaran,diskusi,kegiatan_lapangan,evaluasi,penutupan,foto_bersama,sarana,lainnya', 'taken_at' => 'nullable|date',
        ]);
        $next = (int) TrainingActivityDocumentation::where('training_id', $training->id)->max('sort_order') + 1;
        foreach ($request->file('photos') as $photo) {
            TrainingActivityDocumentation::create([
                'training_id' => $training->id, 'title' => $data['title'], 'caption' => $data['caption'] ?? null,
                'category' => $data['category'], 'taken_at' => $data['taken_at'] ?? null,
                'file_path' => $photo->store('activity-documentation/'.$training->id, 'local'),
                'sort_order' => $next++, 'uploaded_by' => Auth::id(),
            ]);
        }
        TrainingActivityReport::where('training_id', $training->id)->update(['status' => 'draft', 'updated_by' => Auth::id()]);

        return back()->with('success', count($request->file('photos')).' foto dokumentasi berhasil ditambahkan.');
    }

    public function updatePhoto(Request $request, TrainingActivityDocumentation $documentation)
    {
        $this->authorizeTraining($documentation->training);
        $data = $request->validate(['title' => 'required|string|max:255', 'caption' => 'nullable|string|max:1000', 'category' => 'required|string|max:50', 'taken_at' => 'nullable|date', 'sort_order' => 'required|integer|min:0', 'include_in_report' => 'nullable|boolean', 'is_featured' => 'nullable|boolean']);
        $data['include_in_report'] = $request->boolean('include_in_report');
        $data['is_featured'] = $request->boolean('is_featured');
        if ($data['is_featured']) {
            TrainingActivityDocumentation::where('training_id', $documentation->training_id)->whereKeyNot($documentation->id)->update(['is_featured' => false]);
        }
        $documentation->update($data);
        TrainingActivityReport::where('training_id', $documentation->training_id)->update(['status' => 'draft', 'updated_by' => Auth::id()]);

        return back()->with('success', 'Informasi dokumentasi diperbarui.');
    }

    public function destroyPhoto(TrainingActivityDocumentation $documentation)
    {
        $this->authorizeTraining($documentation->training);
        Storage::disk('local')->delete($documentation->file_path);
        $documentation->delete();
        TrainingActivityReport::where('training_id', $documentation->training_id)->update(['status' => 'draft', 'updated_by' => Auth::id()]);

        return back()->with('success', 'Foto dokumentasi dihapus.');
    }

    public function viewPhoto(TrainingActivityDocumentation $documentation)
    {
        $this->authorizeTraining($documentation->training);
        abort_unless(Storage::disk('local')->exists($documentation->file_path), 404);

        return response()->file(Storage::disk('local')->path($documentation->file_path));
    }

    public function downloadTemplate(Training $training)
    {
        $this->authorizeTraining($training);
        $path = $this->buildDefaultTemplate(true);

        return response()->download($path, 'template_laporan_kegiatan_'.Str::slug($training->nama_pelatihan, '_').'.docx')->deleteFileAfterSend(true);
    }

    public function generate(Request $request, Training $training)
    {
        $this->authorizeTraining($training);
        $request->validate(['format' => 'required|in:docx,pdf', 'finalize' => 'nullable|boolean']);
        $report = TrainingActivityReport::firstOrCreate(['training_id' => $training->id]);
        $photos = TrainingActivityDocumentation::where('training_id', $training->id)->where('include_in_report', true)->orderByDesc('is_featured')->orderBy('sort_order')->take(20)->get();
        $data = $this->reportData($training, $report);
        if ($request->boolean('finalize')) {
            $allPhotos = TrainingActivityDocumentation::where('training_id', $training->id)->get();
            $incomplete = collect($this->completeness($training, $report, $allPhotos, $data))->where('complete', false)->pluck('label');
            if ($incomplete->isNotEmpty()) {
                return back()->with('error', 'Belum dapat difinalisasi. Lengkapi: '.$incomplete->implode(', ').'.');
            }
        }
        $temporaryTemplate = null;
        $templatePath = $report->template_path && Storage::disk('local')->exists($report->template_path)
            ? Storage::disk('local')->path($report->template_path) : ($temporaryTemplate = $this->buildDefaultTemplate(false));
        $processor = new TemplateProcessor($templatePath);
        $variables = $processor->getVariables();
        $version = ((int) $report->versions()->max('version')) + 1;
        $data['values']['versi_laporan'] = $version;

        foreach ($data['values'] as $key => $value) {
            if (in_array($key, $variables, true)) {
                $processor->setValue($key, $this->safe($value));
            }
        }
        $this->cloneRows($processor, $variables, 'participant_no', $data['participants']);
        $this->cloneRows($processor, $variables, 'schedule_no', $data['schedules']);
        $this->cloneRows($processor, $variables, 'attendance_no', $data['attendance_rows']);
        foreach (range(1, 20) as $number) {
            $photo = $photos->get($number - 1);
            if (in_array('foto_'.$number, $variables, true)) {
                if ($photo && Storage::disk('local')->exists($photo->file_path)) {
                    $processor->setImageValue('foto_'.$number, ['path' => Storage::disk('local')->path($photo->file_path), 'width' => 245, 'height' => 165, 'ratio' => true]);
                } else {
                    $processor->setValue('foto_'.$number, '-');
                }
            }
            foreach (['judul_foto_' => 'title', 'caption_foto_' => 'caption', 'tanggal_foto_' => 'taken_at'] as $prefix => $attribute) {
                $key = $prefix.$number;
                if (! in_array($key, $variables, true)) {
                    continue;
                }
                $value = $photo?->{$attribute};
                if ($attribute === 'taken_at') {
                    $value = $value?->translatedFormat('d F Y');
                }
                $processor->setValue($key, $this->safe($value ?: '-'));
            }
        }
        foreach ($processor->getVariables() as $unknown) {
            $processor->setValue($unknown, '-');
        }

        $tempDocx = tempnam(sys_get_temp_dir(), 'activity-report-').'.docx';
        $processor->saveAs($tempDocx);
        if ($temporaryTemplate) {
            @unlink($temporaryTemplate);
        }
        $baseName = 'Laporan_Kegiatan_'.Str::slug($training->nama_pelatihan, '_').'_v'.$version;
        $storedDocx = 'activity-reports/'.$training->id.'/'.$baseName.'.docx';
        Storage::disk('local')->put($storedDocx, file_get_contents($tempDocx));
        $storedPdf = null;
        $downloadPath = $tempDocx;

        if ($request->format === 'pdf') {
            $tempPdf = tempnam(sys_get_temp_dir(), 'activity-report-').'.pdf';
            try {
                Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
                Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));
                IOFactory::createWriter(IOFactory::load($tempDocx, 'Word2007'), 'PDF')->save($tempPdf);
                abort_unless(is_file($tempPdf) && filesize($tempPdf) > 0, 500, 'PDF gagal dibuat.');
            } catch (\Throwable $exception) {
                @unlink($tempDocx);
                @unlink($tempPdf);

                return back()->with('error', 'Konversi PDF gagal. Silakan gunakan Word atau sederhanakan elemen template: '.$exception->getMessage());
            }
            $storedPdf = 'activity-reports/'.$training->id.'/'.$baseName.'.pdf';
            Storage::disk('local')->put($storedPdf, file_get_contents($tempPdf));
            $downloadPath = $tempPdf;
        }

        TrainingActivityReportVersion::create(['training_activity_report_id' => $report->id, 'version' => $version, 'docx_path' => $storedDocx, 'pdf_path' => $storedPdf, 'snapshot' => $data['values'], 'generated_by' => Auth::id()]);
        $report->update(['status' => $request->boolean('finalize') ? 'final' : 'draft', 'updated_by' => Auth::id()]);
        DocumentController::archiveInternal($training->id, 'LAPORAN PENYELENGGARAAN PELATIHAN', $baseName.'.'.$request->format, file_get_contents($downloadPath), $request->format);
        if ($request->format === 'pdf') {
            @unlink($tempDocx);
        }

        return response()->download($downloadPath, $baseName.'.'.$request->format)->deleteFileAfterSend(true);
    }

    public function downloadVersion(TrainingActivityReportVersion $version, string $format)
    {
        $this->authorizeTraining($version->report->training);
        abort_unless(in_array($format, ['docx', 'pdf'], true), 404);
        $path = $format === 'pdf' ? $version->pdf_path : $version->docx_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    private function reportData(Training $training, TrainingActivityReport $report): array
    {
        Carbon::setLocale('id');
        $participants = $training->participants()->where('registration_status', 'approved')->orderBy('name')->get();
        $schedules = $training->schedules()->with('pengajar')->orderBy('date')->orderBy('start_time')->get();
        $attendance = Attendance::whereIn('schedule_id', $schedules->pluck('id'))->get();
        $l1 = EvaluationResultL1::where('training_id', $training->id)->whereNotNull('score')->get();
        $l2 = EvaluationResultL2::whereIn('participant_id', $participants->pluck('id'))->get();
        $l34 = EvaluationResultL34::where('training_id', $training->id)->whereNotNull('score')->get();
        $summary = EvaluationL1TextSummary::where('training_id', $training->id)->first();
        $totalSlots = max(1, $participants->count() * max(1, $schedules->where('schedule_type', '!=', 'break')->count()));
        $present = $attendance->where('status', 'hadir')->count();
        $percent = round(($present / $totalSlots) * 100, 1);
        $values = [
            'nama_pelatihan' => $training->nama_pelatihan, 'angkatan' => $training->angkatan, 'jenis_pelatihan' => $training->program_evaluasi ?: '-',
            'bidang_penyelenggara' => $training->bidang, 'tahun_pelatihan' => Carbon::parse($training->tgl_mulai)->year, 'nomor_laporan' => $report->report_number ?: '-',
            'tanggal_mulai' => Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y'), 'tanggal_selesai' => Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y'),
            'periode_pelatihan' => Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y').' s.d. '.Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y'),
            'lokasi_pelatihan' => $training->lokasi ?: '-', 'metode_pelatihan' => $training->metode ?: '-',
            'total_jp' => $schedules->where('schedule_type', '!=', 'break')->where('duration_unit', 'JP')->sum('jp'), 'total_oj' => $schedules->where('schedule_type', '!=', 'break')->where('duration_unit', 'OJ')->sum('jp'),
            'jumlah_pendaftar' => $training->participants()->count(), 'jumlah_peserta' => $participants->count(), 'jumlah_instansi' => $participants->pluck('instansi')->filter()->unique()->count(),
            'rata_rata_kehadiran' => number_format($percent, 1, ',', '.').'%', 'jumlah_hadir' => $present, 'jumlah_izin' => $attendance->where('status', 'izin')->count(), 'jumlah_sakit' => $attendance->where('status', 'sakit')->count(),
            'jumlah_tanpa_keterangan' => max(0, $totalSlots - $attendance->count()), 'jumlah_pengajar' => $schedules->pluck('pengajar_id')->filter()->unique()->count(),
            'nilai_evaluasi_l1' => $l1->isNotEmpty() ? number_format($l1->avg('score'), 1, ',', '.') : '-',
            'nilai_evaluasi_l2' => $l2->isNotEmpty() ? number_format($l2->avg(fn ($x) => (float) $x->postest), 1, ',', '.') : '-',
            'nilai_evaluasi_l3' => $l34->isNotEmpty() ? number_format($l34->avg('score'), 1, ',', '.') : '-', 'nilai_evaluasi_l4' => $l34->isNotEmpty() ? number_format($l34->avg('score'), 1, ',', '.') : '-',
            'kesimpulan_evaluasi' => $summary?->conclusion ?: '-', 'jumlah_saran' => EvaluationResultL1::where('training_id', $training->id)->whereNotNull('note')->where('note', '!=', '')->count(),
            'nama_penandatangan' => $report->signatory_name ?: '-', 'nip_penandatangan' => $report->signatory_nip ?: '-', 'jabatan_penandatangan' => $report->signatory_position ?: '-',
            'tanggal_pengesahan' => $report->approval_date?->translatedFormat('d F Y') ?: '-', 'tanggal_generate' => now()->translatedFormat('d F Y H:i'),
        ];
        foreach (self::NARRATIVES as $field) {
            $values['narasi_'.$field] = $report->{$field} ?: '-';
        }
        $values['narasi_tindak_lanjut'] = $values['narasi_follow_up'];
        foreach ([
            'latar_belakang' => 'background', 'dasar_hukum' => 'legal_basis', 'tujuan' => 'objectives',
            'pelaksanaan' => 'implementation', 'capaian' => 'achievements', 'kendala' => 'constraints',
            'kesimpulan' => 'conclusion', 'rekomendasi' => 'recommendations',
        ] as $alias => $field) {
            $values['narasi_'.$alias] = $values['narasi_'.$field];
        }

        return [
            'values' => $values,
            'participants' => $participants->values()->map(fn ($p, $i) => ['participant_no' => $i + 1, 'participant_name' => $p->name, 'participant_nip' => $p->nip_nik, 'participant_position' => $p->jabatan ?: '-', 'participant_institution' => $p->instansi ?: '-'])->all(),
            'schedules' => $schedules->values()->map(fn ($s, $i) => ['schedule_no' => $i + 1, 'schedule_date' => Carbon::parse($s->date)->translatedFormat('d M Y'), 'schedule_time' => substr($s->start_time, 0, 5).'–'.substr($s->end_time, 0, 5), 'schedule_activity' => $s->activity, 'schedule_teacher' => $s->pengajar?->name ?: ($s->pic ?: '-'), 'schedule_duration' => $s->schedule_type === 'break' ? 'Istirahat' : $s->duration_label])->all(),
            'attendance_rows' => $participants->values()->map(function ($p, $i) use ($attendance) {
                $rows = $attendance->where('participant_id', $p->id);

                return ['attendance_no' => $i + 1, 'attendance_name' => $p->name, 'attendance_present' => $rows->where('status', 'hadir')->count(), 'attendance_leave' => $rows->where('status', 'izin')->count(), 'attendance_sick' => $rows->where('status', 'sakit')->count()];
            })->all(),
        ];
    }

    private function completeness(Training $training, TrainingActivityReport $report, $photos, array $data): array
    {
        $filled = collect(self::NARRATIVES)->filter(fn ($field) => filled($report->{$field}))->count();

        return [
            ['label' => 'Identitas pelatihan', 'complete' => filled($training->nama_pelatihan) && filled($training->tgl_mulai)],
            ['label' => 'Jadwal pelatihan', 'complete' => count($data['schedules']) > 0],
            ['label' => 'Peserta disetujui', 'complete' => count($data['participants']) > 0],
            ['label' => 'Narasi admin ('.$filled.'/'.count(self::NARRATIVES).')', 'complete' => $filled === count(self::NARRATIVES)],
            ['label' => 'Dokumentasi terpilih', 'complete' => $photos->where('include_in_report', true)->isNotEmpty()],
            ['label' => 'Penandatangan', 'complete' => filled($report->signatory_name) && filled($report->signatory_position)],
        ];
    }

    private function cloneRows(TemplateProcessor $processor, array $variables, string $anchor, array $rows): void
    {
        if (! in_array($anchor, $variables, true)) {
            return;
        }
        if (empty($rows)) {
            $rows = [[$anchor => '-', str_replace('_no', '_name', $anchor) => 'Belum tersedia']];
        }
        $processor->cloneRow($anchor, count($rows));
        foreach ($rows as $index => $row) {
            foreach ($row as $key => $value) {
                $processor->setValue($key.'#'.($index + 1), $this->safe($value));
            }
        }
    }

    private function buildDefaultTemplate(bool $withGuide): string
    {
        $word = new PhpWord;
        $section = $word->addSection(['marginTop' => 900, 'marginBottom' => 900, 'marginLeft' => 1000, 'marginRight' => 1000]);
        $section->addText('LAPORAN KEGIATAN PELATIHAN', ['bold' => true, 'size' => 18], ['alignment' => 'center']);
        $section->addText('${nama_pelatihan}', ['bold' => true, 'size' => 16], ['alignment' => 'center']);
        $section->addText('Angkatan ${angkatan}', ['size' => 12], ['alignment' => 'center']);
        $section->addText('${periode_pelatihan}', [], ['alignment' => 'center']);
        $section->addTextBreak(5);
        $section->addText('${bidang_penyelenggara}', ['bold' => true], ['alignment' => 'center']);
        $section->addText('${tahun_pelatihan}', ['bold' => true], ['alignment' => 'center']);
        $section->addPageBreak();
        foreach ([['I. PENDAHULUAN', 'Latar Belakang', '${narasi_background}', 'Dasar Hukum', '${narasi_legal_basis}', 'Maksud dan Tujuan', '${narasi_objectives}'], ['II. PELAKSANAAN', 'Uraian Pelaksanaan', '${narasi_implementation}', 'Capaian', '${narasi_achievements}', 'Kendala', '${narasi_constraints}'], ['III. TINDAK LANJUT', 'Tindak Lanjut', '${narasi_follow_up}', 'Kesimpulan', '${narasi_conclusion}', 'Rekomendasi', '${narasi_recommendations}']] as $chapter) {
            $section->addTitle(array_shift($chapter), 1);
            while ($chapter) {
                $section->addTitle(array_shift($chapter), 2);
                $section->addText(array_shift($chapter), [], ['alignment' => 'both']);
            }
        }
        $section->addTitle('IV. DATA PELAKSANAAN', 1);
        $section->addText('Pelaksanaan: ${periode_pelatihan} | Lokasi: ${lokasi_pelatihan} | Metode: ${metode_pelatihan}');
        $section->addText('Peserta: ${jumlah_peserta} | Pengajar: ${jumlah_pengajar} | Kehadiran: ${rata_rata_kehadiran}');
        $section->addTitle('Jadwal', 2);
        $table = $section->addTable(['borderSize' => 6, 'cellMargin' => 80]);
        foreach ([['No', 'Tanggal', 'Waktu', 'Kegiatan/Materi', 'Pengajar', 'JP/OJ'], ['${schedule_no}', '${schedule_date}', '${schedule_time}', '${schedule_activity}', '${schedule_teacher}', '${schedule_duration}']] as $row) {
            $table->addRow();
            foreach ($row as $value) {
                $table->addCell()->addText($value);
            }
        }
        $section->addTitle('Peserta', 2);
        $table = $section->addTable(['borderSize' => 6, 'cellMargin' => 80]);
        foreach ([['No', 'Nama', 'NIP/NIK', 'Jabatan', 'Instansi'], ['${participant_no}', '${participant_name}', '${participant_nip}', '${participant_position}', '${participant_institution}']] as $row) {
            $table->addRow();
            foreach ($row as $value) {
                $table->addCell()->addText($value);
            }
        }
        $section->addTitle('Hasil Evaluasi', 2);
        $section->addText('Level 1: ${nilai_evaluasi_l1} | Level 2: ${nilai_evaluasi_l2} | Level 3: ${nilai_evaluasi_l3} | Level 4: ${nilai_evaluasi_l4}');
        $section->addText('${kesimpulan_evaluasi}');
        $section->addPageBreak();
        $section->addTitle('V. DOKUMENTASI KEGIATAN', 1);
        foreach (range(1, 8) as $i) {
            $section->addText('${foto_'.$i.'}', [], ['alignment' => 'center']);
            $section->addText('${judul_foto_'.$i.'} — ${caption_foto_'.$i.'}', ['italic' => true], ['alignment' => 'center']);
            if ($i % 2 === 0 && $i < 8) {
                $section->addPageBreak();
            }
        }
        $section->addPageBreak();
        $section->addText('${jabatan_penandatangan}', [], ['alignment' => 'right']);
        $section->addTextBreak(3);
        $section->addText('${nama_penandatangan}', ['bold' => true], ['alignment' => 'right']);
        $section->addText('NIP. ${nip_penandatangan}', [], ['alignment' => 'right']);
        if ($withGuide) {
            $section->addPageBreak();
            $section->addTitle('PANDUAN KODE TEMPLATE (hapus halaman ini sebelum template digunakan)', 1);
            $table = $section->addTable(['borderSize' => 6, 'cellMargin' => 80]);
            foreach ($this->templateCodes() as $item) {
                $table->addRow();
                $table->addCell(3500)->addText('${'.$item['code'].'}');
                $table->addCell(6500)->addText($item['description']);
            }
        }
        $path = tempnam(sys_get_temp_dir(), 'activity-template-').'.docx';
        IOFactory::createWriter($word, 'Word2007')->save($path);

        return $path;
    }

    private function templateCodes(): array
    {
        $codes = [
            'nama_pelatihan' => 'Nama pelatihan', 'angkatan' => 'Angkatan', 'bidang_penyelenggara' => 'Bidang penyelenggara', 'periode_pelatihan' => 'Rentang tanggal', 'lokasi_pelatihan' => 'Lokasi', 'metode_pelatihan' => 'Metode',
            'jumlah_peserta' => 'Jumlah peserta disetujui', 'jumlah_instansi' => 'Jumlah instansi', 'total_jp' => 'Total JP', 'total_oj' => 'Total OJ', 'rata_rata_kehadiran' => 'Persentase kehadiran',
            'nilai_evaluasi_l1' => 'Rata-rata Level 1', 'nilai_evaluasi_l2' => 'Rata-rata post-test Level 2', 'nilai_evaluasi_l3' => 'Rata-rata Level 3', 'nilai_evaluasi_l4' => 'Rata-rata Level 4', 'kesimpulan_evaluasi' => 'Kesimpulan evaluasi admin',
            'narasi_background' => 'Latar belakang', 'narasi_legal_basis' => 'Dasar hukum', 'narasi_objectives' => 'Tujuan', 'narasi_implementation' => 'Pelaksanaan', 'narasi_achievements' => 'Capaian', 'narasi_constraints' => 'Kendala', 'narasi_follow_up' => 'Tindak lanjut', 'narasi_conclusion' => 'Kesimpulan', 'narasi_recommendations' => 'Rekomendasi',
            'participant_no' => 'Anchor baris tabel peserta', 'participant_name' => 'Nama peserta', 'participant_nip' => 'NIP/NIK peserta', 'participant_position' => 'Jabatan peserta', 'participant_institution' => 'Instansi peserta',
            'schedule_no' => 'Anchor baris tabel jadwal', 'schedule_date' => 'Tanggal sesi', 'schedule_time' => 'Jam sesi', 'schedule_activity' => 'Materi/kegiatan', 'schedule_teacher' => 'Pengajar', 'schedule_duration' => 'Jumlah JP/OJ',
            'foto_1' => 'Foto dokumentasi pertama (tersedia foto_1 s.d. foto_20)', 'judul_foto_1' => 'Judul foto pertama', 'caption_foto_1' => 'Keterangan foto pertama', 'nama_penandatangan' => 'Nama penandatangan', 'nip_penandatangan' => 'NIP penandatangan', 'jabatan_penandatangan' => 'Jabatan penandatangan', 'tanggal_generate' => 'Waktu generate laporan',
        ];

        return collect($codes)->map(fn ($description, $code) => compact('code', 'description'))->values()->all();
    }

    private function safe($value): string
    {
        return htmlspecialchars((string) ($value ?? '-'), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function authorizeTraining(Training $training): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->role === 'superadmin' || ($user->role === 'admin_bidang' && $user->bidang === $training->bidang)), 403);
    }
}
