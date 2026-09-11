@extends('layouts.master')

@section('title', 'Kelola Pelatihan')

@section('content')
@php
    $forumUnread = app(\App\Services\NotificationCenter::class)->unreadCountForTraining(auth()->user(), $training);
    $dashboard = app(\App\Services\TrainingManageDashboard::class)->build($training);
    $totalParticipants = max(1, $training->participants_count);
    $evaluationDone = max($dashboard['l1Respondents'], $dashboard['l2Respondents']);
    $sections = [
        'preparation' => [
            'label' => 'Persiapan', 'icon' => 'bx-list-check', 'description' => 'Siapkan peserta, jadwal, akses, dan administrasi.',
            'items' => [
                ['title' => 'Daftar Peserta', 'description' => $dashboard['approved'].' disetujui · '.$dashboard['pending'].' menunggu', 'icon' => 'bx-group', 'tone' => 'primary', 'url' => route('trainings.participants', $training), 'status' => $dashboard['pending'] ? $dashboard['pending'].' menunggu' : 'Siap', 'status_tone' => $dashboard['pending'] ? 'warning' : 'success'],
                ['title' => 'Jadwal & Pengajar', 'description' => $dashboard['learningSchedules']->count().' sesi pembelajaran', 'icon' => 'bx-calendar', 'tone' => 'info', 'url' => route('trainings.schedules', $training), 'status' => $dashboard['missingTeachers'] ? $dashboard['missingTeachers'].' belum lengkap' : 'Siap', 'status_tone' => $dashboard['missingTeachers'] ? 'danger' : 'success'],
                ['title' => 'Kelengkapan Penyelenggara', 'description' => $dashboard['organizerDocuments']->count().' dokumen tersimpan', 'icon' => 'bx-folder-open', 'tone' => 'warning', 'url' => '#organizerDocuments', 'status' => $dashboard['organizerDocuments']->isEmpty() ? 'Belum ada' : 'Tersedia', 'status_tone' => $dashboard['organizerDocuments']->isEmpty() ? 'warning' : 'success'],
                ['title' => 'Akses LMS / Zoom', 'description' => $training->link_lms ? 'Tautan sudah tersedia' : 'Tautan belum ditentukan', 'icon' => 'bx-link-alt', 'tone' => 'secondary', 'url' => '#', 'modal' => '#modalLms'.$training->id, 'status' => $training->link_lms ? 'Aktif' : 'Belum diatur', 'status_tone' => $training->link_lms ? 'success' : 'warning'],
            ],
        ],
        'execution' => [
            'label' => 'Pelaksanaan', 'icon' => 'bx-play-circle', 'description' => 'Pantau kegiatan, kehadiran, komunikasi, dan monitoring.',
            'items' => [
                ['title' => 'Kehadiran / Absensi', 'description' => 'Keterisian '.$dashboard['attendanceFilled'].'% · hadir '.$dashboard['attendanceRate'].'%', 'icon' => 'bx-user-check', 'tone' => 'success', 'url' => route('attendance.index', $training), 'status' => $dashboard['attendanceFilled'].'%', 'status_tone' => $dashboard['attendanceFilled'] >= 90 ? 'success' : 'info'],
                ['title' => 'Forum Pelatihan', 'description' => 'Komunikasi peserta dan pengelola', 'icon' => 'bx-conversation', 'tone' => 'primary', 'url' => route('training.forum.index', $training), 'status' => $forumUnread ? $forumUnread.' baru' : 'Terbaca', 'status_tone' => $forumUnread ? 'danger' : 'success'],
                ['title' => 'Instrumen Monitoring', 'description' => 'Periksa kesesuaian pelaksanaan', 'icon' => 'bx-search-alt', 'tone' => 'info', 'url' => route('monitoring.fill', $training), 'status' => $monitoringStats['total'].' temuan', 'status_tone' => $monitoringStats['open'] ? 'warning' : 'success'],
                ['title' => 'Tindak Lanjut Monitoring', 'description' => $monitoringStats['verified'].' selesai · '.$monitoringStats['open'].' perlu aksi', 'icon' => 'bx-task', 'tone' => 'warning', 'url' => route('followup.index', ['training_id' => $training->id]), 'status' => $monitoringStats['overdue'] ? $monitoringStats['overdue'].' terlambat' : 'Terkendali', 'status_tone' => $monitoringStats['overdue'] ? 'danger' : 'success'],
            ],
        ],
        'evaluation' => [
            'label' => 'Evaluasi', 'icon' => 'bx-bar-chart-alt-2', 'description' => 'Ukur reaksi, pembelajaran, perilaku, dan dampak.',
            'items' => [
                ['title' => 'Evaluasi Level 1', 'description' => $dashboard['l1Respondents'].' dari '.$dashboard['approved'].' peserta merespons', 'icon' => 'bx-smile', 'tone' => 'warning', 'url' => route('evall1.index', $training), 'status' => $dashboard['approved'] ? round($dashboard['l1Respondents'] / $dashboard['approved'] * 100).'%' : '0%', 'status_tone' => $dashboard['l1Respondents'] >= $dashboard['approved'] && $dashboard['approved'] ? 'success' : 'warning'],
                ['title' => 'Evaluasi Level 2', 'description' => $dashboard['l2Respondents'].' hasil belajar tercatat', 'icon' => 'bx-book-open', 'tone' => 'success', 'url' => route('evall2.index', $training), 'status' => $dashboard['l2Respondents'].' data', 'status_tone' => $dashboard['l2Respondents'] >= $dashboard['approved'] && $dashboard['approved'] ? 'success' : 'info'],
                ['title' => 'Dashboard Level 1 & 2', 'description' => 'Grafik eksekutif dan rekomendasi', 'icon' => 'bx-line-chart', 'tone' => 'primary', 'url' => route('evall12.dashboard', $training), 'status' => 'Lihat analisis', 'status_tone' => 'primary'],
                ['title' => 'Evaluasi Level 3 & 4', 'description' => $dashboard['l34Due'] ? $dashboard['l34Respondents'].' respons mandiri' : 'Belum memasuki waktu evaluasi', 'icon' => 'bx-trending-up', 'tone' => 'info', 'url' => route('evall34.index', $training), 'status' => $dashboard['l34Due'] ? $dashboard['l34Respondents'].' respons' : 'Belum jatuh tempo', 'status_tone' => $dashboard['l34Due'] ? 'info' : 'secondary'],
                ['title' => 'Dashboard Level 3 & 4', 'description' => 'Perilaku, dampak, dan perspektif 360°', 'icon' => 'bx-network-chart', 'tone' => 'secondary', 'url' => route('evall34.dashboard', $training), 'status' => 'Lihat analisis', 'status_tone' => 'primary'],
            ],
        ],
        'reporting' => [
            'label' => 'Pelaporan', 'icon' => 'bx-file', 'description' => 'Finalisasi laporan, dokumen, dan sertifikat peserta.',
            'items' => [
                ['title' => 'Laporan Kegiatan', 'description' => $dashboard['photos'].' foto dokumentasi terpilih', 'icon' => 'bx-file', 'tone' => 'success', 'url' => route('training-activity-report.index', $training), 'status' => $dashboard['report']?->status === 'final' ? 'Final' : 'Draft', 'status_tone' => $dashboard['report']?->status === 'final' ? 'success' : 'warning'],
                ['title' => 'Kelola Sertifikat', 'description' => $dashboard['certificates'].' dari '.$dashboard['approved'].' sertifikat final', 'icon' => 'bx-medal', 'tone' => 'warning', 'url' => route('training-certificates.index', $training), 'status' => $dashboard['certificates'].' terbit', 'status_tone' => $dashboard['certificates'] >= $dashboard['approved'] && $dashboard['approved'] ? 'success' : 'warning'],
                ['title' => 'Dokumen Pelatihan', 'description' => 'Arsip seluruh dokumen kegiatan', 'icon' => 'bx-archive', 'tone' => 'primary', 'url' => route('documents.index'), 'status' => 'Buka arsip', 'status_tone' => 'primary'],
            ],
        ],
    ];
@endphp

<div class="training-command-center">
    <nav aria-label="breadcrumb" class="mb-3"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('trainings.index') }}">Pelatihan</a></li><li class="breadcrumb-item active">Kelola</li></ol></nav>

    @foreach(['success' => 'success', 'error' => 'danger'] as $key => $tone)
        @if(session($key))<div class="alert alert-{{ $tone }} alert-dismissible border-0 shadow-sm"><i class="bx {{ $tone === 'success' ? 'bx-check-circle' : 'bx-error-circle' }} me-2"></i>{{ session($key) }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @endforeach

    <section class="training-hero mb-4">
        <div class="training-hero__main">
            <div class="training-hero__icon"><i class="bx bx-buildings"></i></div>
            <div class="min-w-0">
                <div class="d-flex flex-wrap gap-2 mb-2"><span class="hero-badge">Angkatan {{ $training->angkatan }}</span><span class="hero-badge"><i class="bx bx-pulse me-1"></i>{{ $dashboard['statusLabel'] }}</span></div>
                <h3 class="text-white fw-bold mb-2 text-break">{{ $training->nama_pelatihan }}</h3>
                <div class="training-hero__meta"><span><i class="bx bx-calendar"></i>{{ \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d M') }}–{{ \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d M Y') }}</span><span><i class="bx bx-map"></i>{{ $training->lokasi ?: 'Lokasi belum diatur' }}</span><span><i class="bx bx-laptop"></i>{{ ucfirst($training->metode) }}</span><span><i class="bx bx-buildings"></i>{{ $training->bidang }}</span></div>
            </div>
        </div>
        <div class="training-hero__side">
            <div class="hero-progress"><div class="hero-progress__head"><span>Progres keseluruhan</span><strong>{{ $dashboard['progress'] }}%</strong></div><div class="progress"><div class="progress-bar bg-warning" style="width:{{ $dashboard['progress'] }}%"></div></div><small>{{ $dashboard['statusLabel'] }} · {{ $dashboard['progress'] === 100 ? 'Semua proses lengkap' : 'Lanjutkan komponen yang belum lengkap' }}</small></div>
            <div class="hero-actions"><a href="{{ route('training.forum.index', $training) }}" class="btn btn-light position-relative"><i class="bx bx-conversation me-1"></i>Forum @if($forumUnread)<span class="badge bg-danger ms-1">{{ $forumUnread > 99 ? '99+' : $forumUnread }}</span>@endif</a><a href="{{ route('trainings.schedules', $training) }}" class="btn btn-warning"><i class="bx bx-calendar me-1"></i>Jadwal</a><a href="{{ route('trainings.index') }}" class="btn btn-outline-light"><i class="bx bx-arrow-back me-1"></i>Kembali</a></div>
        </div>
    </section>

    <section class="row g-3 mb-4">
        @foreach([
            ['Peserta disetujui', $dashboard['approved'].'/'.$training->participants_count, 'bx-group', 'primary', $dashboard['pending'] ? $dashboard['pending'].' menunggu persetujuan' : 'Seluruh pengajuan ditindaklanjuti', route('trainings.participants', $training)],
            ['Kehadiran', $dashboard['attendanceRate'].'%', 'bx-user-check', 'success', 'Keterisian presensi '.$dashboard['attendanceFilled'].'%', route('attendance.index', $training)],
            ['Jadwal', $dashboard['learningSchedules']->count(), 'bx-calendar-event', 'info', $dashboard['missingTeachers'] ? $dashboard['missingTeachers'].' tanpa pengajar' : 'Pengajar sudah lengkap', route('trainings.schedules', $training)],
            ['Evaluasi', $dashboard['evaluationProgress'].'%', 'bx-bar-chart-alt-2', 'warning', $evaluationDone.' peserta telah dievaluasi', route('evall12.dashboard', $training)],
            ['Sertifikat terbit', $dashboard['certificates'].'/'.$dashboard['approved'], 'bx-medal', 'secondary', 'Sertifikat final tersedia', route('training-certificates.index', $training)],
        ] as [$label,$value,$icon,$tone,$note,$url])
            <div class="col-6 col-xl"><a href="{{ $url }}" class="summary-card"><span class="summary-card__icon bg-label-{{ $tone }}"><i class="bx {{ $icon }}"></i></span><div class="min-w-0"><small>{{ $label }}</small><strong>{{ $value }}</strong><span>{{ $note }}</span></div></a></div>
        @endforeach
    </section>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom p-4"><div class="d-flex justify-content-between align-items-start gap-3"><div><h5 class="fw-bold mb-1"><i class="bx bx-bell text-warning me-2"></i>Perlu Perhatian Anda</h5><p class="text-muted small mb-0">Prioritas yang perlu ditindaklanjuti pada pelatihan ini.</p></div><span class="badge {{ $dashboard['attention']->isEmpty() ? 'bg-label-success' : 'bg-label-warning' }}">{{ $dashboard['attention']->count() }} pekerjaan</span></div></div>
                <div class="card-body p-0">
                    @forelse($dashboard['attention']->take(5) as $item)
                        <a href="{{ $item['url'] }}" class="attention-item"><span class="attention-item__icon bg-label-{{ $item['tone'] }}"><i class="bx {{ $item['icon'] }}"></i></span><div class="flex-grow-1 min-w-0"><h6 class="mb-1 text-break">{{ $item['title'] }}</h6><p class="mb-0">{{ $item['description'] }}</p></div><span class="attention-action">{{ $item['action'] }}<i class="bx bx-chevron-right"></i></span></a>
                    @empty
                        <div class="all-clear"><span><i class="bx bx-party"></i></span><div><h5 class="mb-1">Semua proses utama sudah tertangani!</h5><p class="mb-0">Tidak ada pekerjaan mendesak pada pelatihan ini.</p></div></div>
                    @endforelse
                </div>
            </section>
        </div>
        <div class="col-xl-4">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom p-4"><h5 class="fw-bold mb-1"><i class="bx bx-time-five text-primary me-2"></i>{{ $dashboard['scheduleState'] ?: 'Jadwal Terdekat' }}</h5><p class="text-muted small mb-0">Agenda yang perlu dipantau pengelola.</p></div>
                <div class="card-body p-4">
                    @if($dashboard['nextSchedule'])
                        @php $next = $dashboard['nextSchedule']; @endphp
                        <span class="schedule-date">{{ \Carbon\Carbon::parse($next->date)->translatedFormat('l, d F Y') }}</span><h5 class="fw-bold mt-3 mb-2">{{ $next->activity }}</h5><div class="schedule-detail"><span><i class="bx bx-time"></i>{{ substr($next->start_time,0,5) }}–{{ substr($next->end_time,0,5) }} WIB</span><span><i class="bx bx-user-voice"></i>{{ $next->pengajar?->name ?: ($next->pic ?: 'Pengajar belum ditentukan') }}</span><span><i class="bx bx-book-open"></i>{{ $next->schedule_type === 'break' ? 'Istirahat' : $next->duration_label }}</span></div><a href="{{ route('trainings.schedules', $training) }}" class="btn btn-sm btn-outline-primary w-100 mt-4">Lihat Seluruh Jadwal</a>
                    @else
                        <div class="empty-mini"><i class="bx bx-calendar-check"></i><h6>Tidak ada jadwal berikutnya</h6><p>{{ $dashboard['status'] === 'completed' ? 'Seluruh jadwal pelatihan telah selesai.' : 'Tambahkan jadwal agar dapat dipantau di sini.' }}</p><a href="{{ route('trainings.schedules', $training) }}" class="btn btn-sm btn-outline-primary">Kelola Jadwal</a></div>
                    @endif
                </div>
            </section>
        </div>
    </div>

    <section class="workflow-shell mb-4">
        <div class="workflow-head"><div><h5 class="fw-bold mb-1">Pusat Kendali Pelatihan</h5><p class="text-muted small mb-0">Menu disusun mengikuti tahapan kerja pengelola.</p></div><div class="workflow-scroll"><ul class="nav nav-pills workflow-tabs flex-nowrap" role="tablist">@foreach($sections as $key=>$section)<li class="nav-item"><button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#workflow-{{ $key }}" type="button"><span class="workflow-tab-number">{{ $loop->iteration }}</span><span>{{ $section['label'] }}</span></button></li>@endforeach</ul></div></div>
        <div class="tab-content workflow-content">@foreach($sections as $key=>$section)<div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="workflow-{{ $key }}"><div class="section-intro"><span><i class="bx {{ $section['icon'] }}"></i></span><div><h5>{{ $section['label'] }}</h5><p>{{ $section['description'] }}</p></div></div><div class="action-grid">@foreach($section['items'] as $item)<a href="{{ $item['url'] }}" @isset($item['modal']) data-bs-toggle="modal" data-bs-target="{{ $item['modal'] }}" @endisset class="action-card"><span class="action-card__icon bg-label-{{ $item['tone'] }}"><i class="bx {{ $item['icon'] }}"></i></span><div class="flex-grow-1 min-w-0"><div class="d-flex flex-wrap align-items-center gap-2 mb-1"><h6 class="mb-0">{{ $item['title'] }}</h6><span class="badge bg-label-{{ $item['status_tone'] }}">{{ $item['status'] }}</span></div><p>{{ $item['description'] }}</p></div><i class="bx bx-chevron-right action-card__arrow"></i></a>@endforeach</div></div>@endforeach</div>
    </section>

    <section class="row g-4 mb-4" id="organizerDocuments">
        <div class="col-xl-7"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-transparent border-bottom p-4"><div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3"><div><h5 class="fw-bold mb-1"><i class="bx bx-folder-open text-warning me-2"></i>Kelengkapan Penyelenggara</h5><p class="text-muted small mb-0">Bukti administrasi pelaksanaan pelatihan.</p></div><button class="btn btn-warning flex-shrink-0" data-bs-toggle="modal" data-bs-target="#modalOrganizerDocument"><i class="bx bx-plus me-1"></i>Tambah Dokumen</button></div></div><div class="card-body p-0">@if($dashboard['organizerDocuments']->isNotEmpty())<div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-light"><tr><th>Dokumen</th><th>Diunggah</th><th class="text-end">Aksi</th></tr></thead><tbody>@foreach($dashboard['organizerDocuments']->take(5) as $document)<tr><td><div class="d-flex align-items-center gap-2"><span class="file-icon"><i class="bx bx-file"></i></span><div><strong class="d-block text-break">{{ pathinfo($document->display_name, PATHINFO_FILENAME) }}</strong><small class="text-muted text-uppercase">{{ $document->file_type }}</small></div></div></td><td><span class="d-block">{{ $document->created_at->translatedFormat('d M Y') }}</span><small class="text-muted">{{ $document->user?->name ?: 'Sistem' }}</small></td><td><div class="d-flex justify-content-end gap-1"><a href="{{ route('documents.file.view',$document) }}" target="_blank" class="btn btn-sm btn-icon btn-outline-primary"><i class="bx bx-show"></i></a><a href="{{ route('documents.file.download',$document) }}" class="btn btn-sm btn-icon btn-outline-secondary"><i class="bx bx-download"></i></a></div></td></tr>@endforeach</tbody></table></div>@else<div class="empty-mini py-5"><i class="bx bx-folder-plus"></i><h6>Dokumen belum tersedia</h6><p>Tambahkan surat dan dokumen administrasi penyelenggaraan.</p></div>@endif</div></div></div>
        <div class="col-xl-5"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-transparent border-bottom p-4"><h5 class="fw-bold mb-1"><i class="bx bx-download text-primary me-2"></i>Unduh Data & Laporan</h5><p class="text-muted small mb-0">Dokumen pendukung untuk arsip dan pelaporan.</p></div><div class="card-body p-4"><div class="download-list">@foreach([
            [route('schedules.pdf',$training),'Jadwal Pelatihan','PDF','bx-calendar','danger'],
            [route('participants.export_data',$training),'Data Peserta','Excel','bx-group','success'],
            [route('attendance.excel.all',$training),'Rekap Kehadiran','Excel','bx-user-check','primary'],
            [route('trainings.export_evaluation',$training),'Rekap Evaluasi L1 & L2','Excel','bx-bar-chart','success'],
            [route('evall12.export_word',$training),'Laporan Evaluasi L1 & L2','Word','bx-file','primary'],
            [route('evall34.export',$training),'Rekap Evaluasi L3 & L4','Excel','bx-spreadsheet','success'],
            [route('evall34.export_word',$training),'Laporan Dampak 360°','Word','bx-file','info'],
            [route('evall34.export_invitation',$training),'Undangan Evaluasi 360°','Word','bx-envelope','danger'],
        ] as [$url,$title,$format,$icon,$tone])<a href="{{ $url }}" class="download-item"><span class="bg-label-{{ $tone }}"><i class="bx {{ $icon }}"></i></span><div><strong>{{ $title }}</strong><small>{{ $format }}</small></div><i class="bx bx-download ms-auto"></i></a>@endforeach</div></div></div></div>
    </section>
</div>

<div class="modal fade" id="modalOrganizerDocument" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form action="{{ route('trainings.organizer-documents.store',$training) }}" method="POST" enctype="multipart/form-data" class="modal-content">@csrf<div class="modal-header"><div><h5 class="modal-title fw-bold">Tambah Dokumen Penyelenggara</h5><small class="text-muted">Dokumen otomatis masuk ke folder kelengkapan.</small></div><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body p-4"><div class="mb-3"><label class="form-label fw-semibold">Nama dokumen <span class="text-danger">*</span></label><input name="document_name" value="{{ old('document_name') }}" class="form-control @error('document_name') is-invalid @enderror" placeholder="Contoh: Surat Undangan Peserta" required>@error('document_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div><label class="form-label fw-semibold">File dokumen <span class="text-danger">*</span></label><input type="file" name="document_file" class="form-control @error('document_file') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>@error('document_file')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">PDF, Word, Excel, JPG, atau PNG · maksimal 20 MB.</div></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-warning"><i class="bx bx-upload me-1"></i>Upload Dokumen</button></div></form></div></div>

<div class="modal fade" id="modalLms{{ $training->id }}" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form action="{{ route('trainings.set_lms',$training) }}" method="POST" class="modal-content">@csrf @method('PUT')<div class="modal-header"><div><h5 class="modal-title fw-bold">Akses LMS / Pertemuan</h5><small class="text-muted">Tautan akan ditampilkan kepada peserta.</small></div><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body p-4"><label class="form-label fw-semibold">Tautan URL</label><input type="url" name="link_lms" class="form-control" placeholder="https://..." value="{{ $training->link_lms }}" required><div class="form-text">Gunakan alamat lengkap yang diawali http:// atau https://.</div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Tautan</button></div></form></div></div>

@push('js')
<script>
@if(isset($errors) && ($errors->has('document_name') || $errors->has('document_file')))
document.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('modalOrganizerDocument')).show());
@endif
</script>
@endpush

<style>
.training-command-center{width:100%;min-width:0}.min-w-0{min-width:0}.training-hero{display:flex;align-items:stretch;justify-content:space-between;gap:2rem;padding:1.75rem 2rem;border-radius:1rem;background:linear-gradient(135deg,#696cff 0%,#4b52c4 100%);box-shadow:0 .65rem 1.75rem rgba(105,108,255,.2);overflow:hidden}.training-hero__main{display:flex;align-items:flex-start;gap:1.25rem;min-width:0}.training-hero__icon{display:grid;place-items:center;flex:0 0 64px;width:64px;height:64px;border-radius:17px;background:rgba(255,255,255,.16);color:#fff;font-size:2rem}.hero-badge{padding:.3rem .65rem;border-radius:50rem;background:rgba(255,255,255,.16);color:#fff;font-size:.75rem}.training-hero__meta{display:flex;flex-wrap:wrap;gap:.65rem 1.15rem;color:rgba(255,255,255,.85);font-size:.82rem}.training-hero__meta span{display:flex;align-items:center;gap:.35rem}.training-hero__side{display:flex;flex-direction:column;justify-content:space-between;gap:1.25rem;flex:0 0 390px}.hero-progress{padding:1rem;border-radius:.8rem;background:rgba(255,255,255,.13);color:#fff}.hero-progress__head{display:flex;justify-content:space-between;margin-bottom:.5rem}.hero-progress .progress{height:7px;background:rgba(255,255,255,.22)}.hero-progress small{display:block;margin-top:.45rem;opacity:.8}.hero-actions{display:flex;justify-content:flex-end;flex-wrap:wrap;gap:.5rem}.summary-card{display:flex;align-items:center;gap:.75rem;height:100%;min-width:0;padding:1rem;border:1px solid #ececf2;border-radius:.85rem;background:#fff;box-shadow:0 .2rem .7rem rgba(67,89,113,.06);color:inherit;transition:.2s}.summary-card:hover{color:inherit;transform:translateY(-2px);box-shadow:0 .45rem 1rem rgba(67,89,113,.12)}.summary-card__icon{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border-radius:12px;font-size:1.35rem}.summary-card small,.summary-card strong,.summary-card span{display:block}.summary-card small{color:#8592a3}.summary-card strong{font-size:1.35rem}.summary-card span{overflow:hidden;color:#8592a3;font-size:.68rem;text-overflow:ellipsis;white-space:nowrap}.attention-item{display:flex;align-items:center;gap:.9rem;padding:1rem 1.5rem;border-bottom:1px solid #eee;color:inherit}.attention-item:last-child{border-bottom:0}.attention-item:hover{background:#fafaff;color:inherit}.attention-item__icon{display:grid;place-items:center;flex:0 0 42px;width:42px;height:42px;border-radius:12px;font-size:1.25rem}.attention-item h6{font-weight:600}.attention-item p{color:#8592a3;font-size:.8rem}.attention-action{display:flex;align-items:center;color:#696cff;font-size:.8rem;white-space:nowrap}.all-clear{display:flex;align-items:center;gap:1rem;padding:2rem}.all-clear>span{display:grid;place-items:center;width:58px;height:58px;border-radius:50%;background:#eaf8f0;color:#28a66a;font-size:1.8rem}.all-clear p{color:#8592a3}.schedule-date{display:inline-flex;padding:.35rem .65rem;border-radius:.5rem;background:#eef0ff;color:#5663d7;font-size:.78rem;font-weight:600}.schedule-detail{display:grid;gap:.75rem}.schedule-detail span{display:flex;align-items:flex-start;gap:.55rem;color:#697a8d;font-size:.85rem}.schedule-detail i{color:#696cff;font-size:1.1rem}.empty-mini{text-align:center;padding:1.5rem}.empty-mini>i{font-size:2.5rem;color:#a8b1bb}.empty-mini p{color:#8592a3;font-size:.8rem}.workflow-shell{overflow:hidden;border:1px solid #e8e8ef;border-radius:1rem;background:#fff;box-shadow:0 .25rem 1rem rgba(67,89,113,.07)}.workflow-head{display:flex;align-items:center;justify-content:space-between;gap:2rem;padding:1.25rem 1.5rem;border-bottom:1px solid #ececf2}.workflow-scroll{overflow-x:auto}.workflow-tabs{width:max-content;gap:.4rem}.workflow-tabs .nav-link{display:flex;align-items:center;gap:.45rem;padding:.55rem .75rem;white-space:nowrap;color:#697a8d}.workflow-tab-number{display:grid;place-items:center;width:24px;height:24px;border-radius:50%;background:#f0f1f3;font-size:.72rem}.workflow-tabs .active .workflow-tab-number{background:rgba(255,255,255,.2)}.workflow-content{padding:1.5rem;background:#fbfbfd}.section-intro{display:flex;align-items:center;gap:.8rem;margin-bottom:1.25rem}.section-intro>span{display:grid;place-items:center;width:45px;height:45px;border-radius:12px;background:#eef0ff;color:#696cff;font-size:1.4rem}.section-intro h5,.section-intro p{margin:0}.section-intro p{color:#8592a3;font-size:.8rem}.action-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.85rem}.action-card{display:flex;align-items:center;gap:.8rem;min-width:0;padding:1rem;border:1px solid #e6e7eb;border-radius:.8rem;background:#fff;color:inherit;transition:.2s}.action-card:hover{border-color:#b9bdff;color:inherit;box-shadow:0 .3rem .8rem rgba(67,89,113,.08);transform:translateY(-1px)}.action-card__icon{display:grid;place-items:center;flex:0 0 46px;width:46px;height:46px;border-radius:12px;font-size:1.35rem}.action-card h6{font-weight:600}.action-card p{margin:0;color:#8592a3;font-size:.78rem}.action-card__arrow{color:#a1acb8;font-size:1.25rem}.file-icon{display:grid;place-items:center;flex:0 0 36px;width:36px;height:36px;border-radius:9px;background:#eef0ff;color:#696cff}.download-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem}.download-item{display:flex;align-items:center;gap:.65rem;min-width:0;padding:.7rem;border:1px solid #e8e8ef;border-radius:.7rem;color:inherit}.download-item:hover{border-color:#b9bdff;color:inherit;background:#fafaff}.download-item>span{display:grid;place-items:center;flex:0 0 34px;width:34px;height:34px;border-radius:9px}.download-item div{min-width:0}.download-item strong,.download-item small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.download-item strong{font-size:.76rem}.download-item small{color:#8592a3;font-size:.68rem}
@media(max-width:1199.98px){.training-hero{flex-direction:column}.training-hero__side{flex-basis:auto}.workflow-head{align-items:flex-start;flex-direction:column;gap:1rem}.workflow-scroll{width:100%}}
@media(max-width:767.98px){.training-hero{padding:1.25rem}.training-hero__icon{display:none}.hero-actions .btn{flex:1}.action-grid,.download-list{grid-template-columns:1fr}.attention-item{align-items:flex-start;padding:1rem}.attention-action{font-size:0}.attention-action i{font-size:1.3rem}.summary-card span{white-space:normal}.workflow-content{padding:1rem}.workflow-head{padding:1rem}.all-clear{align-items:flex-start;padding:1.5rem}}
@media(max-width:479.98px){.hero-actions{flex-direction:column}.hero-actions .btn{width:100%}.summary-card{align-items:flex-start;flex-direction:column}.training-hero h3{font-size:1.35rem}.action-card__arrow{display:none}}
</style>
@endsection
