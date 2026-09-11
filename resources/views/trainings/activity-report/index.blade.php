@extends('layouts.master')

@section('title', 'Laporan Kegiatan Pelatihan')

@section('content')
@php
    $aiDraft = session('activity_report_ai_draft', []);
    $completeCount = collect($checks)->where('complete', true)->count();
    $percentage = (int) round(($completeCount / max(1, count($checks))) * 100);
    $selectedPhotos = $photos->where('include_in_report', true)->count();
    $narrativeGroups = [
        'Dasar penyusunan' => [
            'background' => ['Latar Belakang', 'Jelaskan alasan dan konteks pelaksanaan kegiatan.'],
            'legal_basis' => ['Dasar Hukum', 'Tuliskan regulasi, surat tugas, atau dasar pelaksanaan.'],
            'objectives' => ['Maksud dan Tujuan', 'Jelaskan sasaran dan hasil yang ingin dicapai.'],
        ],
        'Pelaksanaan kegiatan' => [
            'implementation' => ['Uraian Pelaksanaan', 'Gambarkan rangkaian pelaksanaan kegiatan secara utuh.'],
            'achievements' => ['Capaian Kegiatan', 'Tuliskan capaian utama berdasarkan data dan hasil kegiatan.'],
            'constraints' => ['Kendala', 'Catat kendala penting yang terjadi selama pelaksanaan.'],
        ],
        'Penutup dan perbaikan' => [
            'follow_up' => ['Rencana Tindak Lanjut', 'Jelaskan tindakan perbaikan dan penanggung jawabnya.'],
            'conclusion' => ['Kesimpulan', 'Simpulkan pelaksanaan dan hasil kegiatan secara keseluruhan.'],
            'recommendations' => ['Rekomendasi', 'Tuliskan rekomendasi untuk penyelenggaraan berikutnya.'],
        ],
    ];
    $categories = ['pembukaan','pembelajaran','diskusi','kegiatan_lapangan','evaluasi','penutupan','foto_bersama','sarana','lainnya'];
@endphp

<div class="activity-report-page">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('trainings.index') }}">Pelatihan</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trainings.manage', $training) }}">Kelola</a></li>
            <li class="breadcrumb-item active">Laporan Kegiatan</li>
        </ol>
    </nav>

    <section class="report-hero mb-4">
        <div class="report-hero__content">
            <div class="report-hero__icon"><i class="bx bx-file"></i></div>
            <div class="min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-white text-primary">Angkatan {{ $training->angkatan }}</span>
                    <span class="badge {{ $report->status === 'final' ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ $report->status === 'final' ? 'Laporan Final' : 'Masih Draft' }}
                    </span>
                </div>
                <h3 class="text-white fw-bold mb-1 text-break">Laporan Kegiatan Pelatihan</h3>
                <p class="text-white mb-0 report-hero__subtitle">{{ $training->nama_pelatihan }}</p>
                <div class="report-hero__meta mt-3">
                    <span><i class="bx bx-calendar"></i>{{ $data['values']['periode_pelatihan'] }}</span>
                    <span><i class="bx bx-map"></i>{{ $data['values']['lokasi_pelatihan'] }}</span>
                    <span><i class="bx bx-buildings"></i>{{ $training->bidang }}</span>
                </div>
            </div>
        </div>
        <div class="report-hero__actions">
            <a href="{{ route('training-activity-report.template.download', $training) }}" class="btn btn-light">
                <i class="bx bx-download me-1"></i>Template & Kode
            </a>
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="bx bx-file me-1"></i>Generate Laporan
            </button>
        </div>
    </section>

    @foreach(['success' => 'success', 'error' => 'danger'] as $key => $type)
        @if(session($key))
            <div class="alert alert-{{ $type }} alert-dismissible border-0 shadow-sm" role="alert">
                <i class="bx {{ $type === 'success' ? 'bx-check-circle' : 'bx-error-circle' }} me-2"></i>{{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <div class="fw-semibold mb-1"><i class="bx bx-error-circle me-1"></i>Data belum dapat disimpan</div>
            <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mb-3">
                        <div><h5 class="fw-bold mb-1">Kesiapan Laporan</h5><p class="text-muted small mb-0">Periksa kelengkapan sebelum laporan ditetapkan sebagai final.</p></div>
                        <div class="completion-score {{ $percentage === 100 ? 'is-complete' : '' }}">{{ $percentage }}%</div>
                    </div>
                    <div class="progress report-progress mb-4"><div class="progress-bar {{ $percentage === 100 ? 'bg-success' : 'bg-warning' }}" style="width: {{ $percentage }}%"></div></div>
                    <div class="check-grid">
                        @foreach($checks as $check)
                            <div class="check-item {{ $check['complete'] ? 'is-complete' : 'is-pending' }}">
                                <span class="check-item__icon"><i class="bx {{ $check['complete'] ? 'bx-check' : 'bx-time-five' }}"></i></span>
                                <div class="min-w-0"><span class="d-block fw-semibold text-break">{{ $check['label'] }}</span><small>{{ $check['complete'] ? 'Sudah siap' : 'Perlu dilengkapi' }}</small></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1">Data Otomatis</h5>
                    <p class="text-muted small mb-3">Ringkasan langsung dari proses pelatihan.</p>
                    <div class="metric-grid">
                        <div class="metric-card metric-primary"><i class="bx bx-group"></i><div><strong>{{ $data['values']['jumlah_peserta'] }}</strong><span>Peserta</span></div></div>
                        <div class="metric-card metric-info"><i class="bx bx-calendar-event"></i><div><strong>{{ count($data['schedules']) }}</strong><span>Jadwal</span></div></div>
                        <div class="metric-card metric-success"><i class="bx bx-user-check"></i><div><strong>{{ $data['values']['rata_rata_kehadiran'] }}</strong><span>Kehadiran</span></div></div>
                        <div class="metric-card metric-warning"><i class="bx bx-images"></i><div><strong>{{ $selectedPhotos }}</strong><span>Foto terpilih</span></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="report-tabs-shell mb-4">
        <div class="report-tabs-scroll">
            <ul class="nav nav-pills report-tabs flex-nowrap" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#narrative" type="button"><i class="bx bx-edit-alt"></i><span>Narasi & Pengesahan</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#documentation" type="button"><i class="bx bx-images"></i><span>Dokumentasi</span><span class="tab-count">{{ $photos->count() }}</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#template" type="button"><i class="bx bx-code-block"></i><span>Template & Kode</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#history" type="button"><i class="bx bx-history"></i><span>Riwayat Versi</span><span class="tab-count">{{ $report->versions->count() }}</span></button></li>
            </ul>
        </div>
    </div>

    <div class="tab-content report-tab-content p-0 bg-transparent shadow-none">
        <div class="tab-pane fade show active" id="narrative">
            <div class="activity-report-ai-panel mb-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="d-flex align-items-start gap-3"><span class="activity-report-ai-icon"><i class="bx bx-bot"></i></span><div><h5 class="fw-bold mb-1">Susun Draf Laporan dengan AI</h5><p class="small text-muted mb-0">AI menggunakan data agregat pelatihan, jadwal tanpa identitas, serta kesimpulan evaluasi anonim. Hasil tetap harus ditelaah admin.</p></div></div>
                    <form method="POST" action="{{ route('training-activity-report.ai-draft', $training) }}" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').innerHTML='Menyusun draf...';">@csrf<button class="btn btn-primary text-nowrap"><i class="bx bx-sparkles me-1"></i>Buat Draf Semua Narasi</button></form>
                </div>
                @if(session('activity_report_ai_error'))<div class="alert alert-danger small mb-0 mt-3"><i class="bx bx-error-circle me-1"></i>{{ session('activity_report_ai_error') }}</div>@endif
                @if($aiDraft)<div class="alert alert-warning small mb-0 mt-3"><i class="bx bx-check-shield me-1"></i><strong>Draf AI telah dimasukkan ke seluruh editor.</strong> Periksa fakta, dasar hukum, angka, dan redaksi sebelum menyimpan.</div>@endif
            </div>
            <form method="POST" action="{{ route('training-activity-report.update', $training) }}">
                @csrf @method('PUT')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-bottom p-4"><h5 class="fw-bold mb-1">Identitas Laporan</h5><p class="text-muted small mb-0">Nomor laporan akan ditampilkan pada dokumen hasil generate.</p></div>
                    <div class="card-body p-4"><div class="row g-3"><div class="col-lg-6"><label class="form-label fw-semibold">Nomor laporan</label><input class="form-control" name="report_number" value="{{ old('report_number', $report->report_number) }}" placeholder="Contoh: 800/123/BPSDM/2026"><div class="form-text">Kode template: <code>${nomor_laporan}</code></div></div><div class="col-lg-6"><div class="info-box"><i class="bx bx-info-circle"></i><span>Data peserta, jadwal, presensi, pengajar, dan evaluasi diisi otomatis oleh sistem.</span></div></div></div></div>
                </div>

                @foreach($narrativeGroups as $group => $fields)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-bottom p-4"><div class="d-flex align-items-center gap-3"><span class="section-number">{{ $loop->iteration }}</span><div><h5 class="fw-bold mb-1">{{ $group }}</h5><p class="text-muted small mb-0">Lengkapi narasi agar laporan mudah dipahami dan siap disampaikan.</p></div></div></div>
                        <div class="card-body p-4"><div class="row g-4">@foreach($fields as $field => [$label, $hint])<div class="col-xl-6 {{ $loop->last && count($fields) % 2 === 1 ? 'col-xl-12' : '' }}"><div class="narrative-field"><div class="d-flex flex-column flex-sm-row justify-content-between gap-1 mb-2"><label class="form-label fw-semibold mb-0">{{ $label }}</label><button type="button" class="copy-inline-code" data-code="${narasi_{{ $field }}}"><i class="bx bx-copy"></i> ${narasi_{{ $field }}}</button></div><p class="small text-muted mb-2">{{ $hint }}</p><textarea name="{{ $field }}" rows="6" class="form-control" placeholder="Tuliskan {{ strtolower($label) }}...">{{ old($field, $aiDraft[$field] ?? $report->{$field}) }}</textarea></div></div>@endforeach</div></div>
                    </div>
                @endforeach

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-bottom p-4"><div class="d-flex align-items-center gap-3"><span class="section-number">4</span><div><h5 class="fw-bold mb-1">Pengesahan Laporan</h5><p class="text-muted small mb-0">Identitas pejabat yang mengesahkan laporan kegiatan.</p></div></div></div>
                    <div class="card-body p-4"><div class="row g-3"><div class="col-lg-4"><label class="form-label fw-semibold">Nama penandatangan</label><input class="form-control" name="signatory_name" value="{{ old('signatory_name', $report->signatory_name) }}"></div><div class="col-lg-3"><label class="form-label fw-semibold">NIP</label><input class="form-control" name="signatory_nip" value="{{ old('signatory_nip', $report->signatory_nip) }}"></div><div class="col-lg-3"><label class="form-label fw-semibold">Jabatan</label><input class="form-control" name="signatory_position" value="{{ old('signatory_position', $report->signatory_position) }}"></div><div class="col-lg-2"><label class="form-label fw-semibold">Tanggal</label><input type="date" class="form-control" name="approval_date" value="{{ old('approval_date', $report->approval_date?->format('Y-m-d')) }}"></div></div></div>
                    <div class="card-footer bg-white border-top p-4"><div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3"><small class="text-muted"><i class="bx bx-save me-1"></i>Perubahan narasi akan mengembalikan status laporan menjadi draft.</small><button class="btn btn-primary px-4"><i class="bx bx-save me-1"></i>Simpan Semua Narasi</button></div></div>
                </div>
            </form>
        </div>

        <div class="tab-pane fade" id="documentation">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom p-4"><div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3"><div><h5 class="fw-bold mb-1">Dokumentasi Kegiatan</h5><p class="text-muted small mb-0">{{ $selectedPhotos }} dari {{ $photos->count() }} foto akan masuk ke laporan. Maksimal 20 foto ditampilkan.</p></div><button class="btn btn-primary flex-shrink-0" data-bs-toggle="modal" data-bs-target="#photoModal"><i class="bx bx-plus me-1"></i>Tambah Dokumentasi</button></div></div>
                <div class="card-body p-4">
                    @if($photos->isEmpty())
                        <div class="empty-state"><span><i class="bx bx-images"></i></span><h5>Belum ada dokumentasi</h5><p>Unggah foto pembukaan, pembelajaran, praktik, evaluasi, dan penutupan kegiatan.</p><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#photoModal">Upload Foto Pertama</button></div>
                    @else
                        <div class="photo-grid">@foreach($photos as $photo)<article class="photo-card {{ !$photo->include_in_report ? 'is-excluded' : '' }}"><a href="{{ route('training-activity-report.photos.view', $photo) }}" target="_blank" class="photo-card__image"><img src="{{ route('training-activity-report.photos.view', $photo) }}" alt="{{ $photo->title }}"><div class="photo-card__badges"><span class="badge bg-dark">{{ str_replace('_', ' ', Str::title($photo->category)) }}</span>@if($photo->is_featured)<span class="badge bg-warning text-dark"><i class="bx bx-star"></i> Utama</span>@endif</div>@unless($photo->include_in_report)<span class="photo-card__excluded">Tidak masuk laporan</span>@endunless</a><div class="photo-card__body"><h6 class="fw-bold mb-1 text-break">{{ $photo->title }}</h6><p class="small text-muted mb-3">{{ $photo->caption ?: 'Belum diberi keterangan foto.' }}</p><div class="photo-card__meta"><span><i class="bx bx-calendar"></i>{{ $photo->taken_at?->translatedFormat('d M Y') ?: 'Tanpa tanggal' }}</span><span><i class="bx bx-sort"></i>Urutan {{ $photo->sort_order }}</span></div></div><div class="photo-card__footer"><button class="btn btn-sm btn-outline-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#editPhoto{{ $photo->id }}"><i class="bx bx-slider-alt me-1"></i>Atur</button><form method="POST" action="{{ route('training-activity-report.photos.destroy', $photo) }}" onsubmit="return confirm('Hapus foto dokumentasi ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bx bx-trash"></i></button></form></div></article>@endforeach</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="template">
            <div class="row g-4"><div class="col-xl-4"><div class="card border-0 shadow-sm template-panel"><div class="card-body p-4"><span class="template-icon"><i class="bx bxs-file-doc"></i></span><h5 class="fw-bold mt-3 mb-1">Template Word</h5><p class="text-muted small">{{ $report->template_path ? 'Template khusus sedang digunakan.' : 'Saat ini menggunakan template standar sistem.' }}</p><div class="template-status mb-4"><i class="bx {{ $report->template_path ? 'bx-check-circle text-success' : 'bx-info-circle text-primary' }}"></i><span>{{ $report->template_path ? 'Template khusus aktif' : 'Template standar aktif' }}</span></div><a href="{{ route('training-activity-report.template.download', $training) }}" class="btn btn-outline-primary w-100 mb-3"><i class="bx bx-download me-1"></i>Download Template Contoh</a><form method="POST" enctype="multipart/form-data" action="{{ route('training-activity-report.template.upload', $training) }}">@csrf<label class="form-label fw-semibold">Upload template .docx</label><input type="file" name="template" accept=".docx" class="form-control mb-2" required><div class="form-text mb-3">Maksimal 20 MB dan wajib memuat kode ${...}.</div><button class="btn btn-primary w-100"><i class="bx bx-upload me-1"></i>Gunakan Template</button></form>@if($report->template_path)<form method="POST" class="mt-2" action="{{ route('training-activity-report.template.reset', $training) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger w-100">Kembali ke Template Standar</button></form>@endif</div></div></div><div class="col-xl-8"><div class="card border-0 shadow-sm"><div class="card-header bg-transparent border-bottom p-4"><h5 class="fw-bold mb-1">Daftar Kode Template</h5><p class="text-muted small mb-3">Klik kode untuk menyalin. Kode tabel harus diletakkan dalam satu baris tabel Word.</p><div class="input-group"><span class="input-group-text"><i class="bx bx-search"></i></span><input type="search" id="codeSearch" class="form-control" placeholder="Cari kode atau nama data..."></div></div><div class="table-responsive code-table-wrap"><table class="table table-hover align-middle mb-0" id="codeTable"><thead class="table-light"><tr><th style="width:45%">Kode</th><th>Data yang ditampilkan</th></tr></thead><tbody>@foreach($codes as $item)<tr><td><button type="button" class="code-chip copy-code" data-code="${{ '{'.$item['code'].'}' }}"><code>${{ '{'.$item['code'].'}' }}</code><i class="bx bx-copy"></i></button></td><td>{{ $item['description'] }}</td></tr>@endforeach</tbody></table></div></div></div></div>
        </div>

        <div class="tab-pane fade" id="history">
            <div class="card border-0 shadow-sm"><div class="card-header bg-transparent border-bottom p-4"><h5 class="fw-bold mb-1">Riwayat Versi Laporan</h5><p class="text-muted small mb-0">Setiap proses generate menyimpan snapshot sehingga versi terdahulu tidak berubah.</p></div><div class="table-responsive"><table class="table align-middle mb-0"><thead class="table-light"><tr><th>Versi</th><th>Waktu dibuat</th><th>Dibuat oleh</th><th class="text-end">File</th></tr></thead><tbody>@forelse($report->versions as $version)<tr><td><span class="version-badge">v{{ $version->version }}</span></td><td><span class="fw-semibold d-block">{{ $version->created_at->translatedFormat('d M Y') }}</span><small class="text-muted">{{ $version->created_at->format('H:i') }} WIB</small></td><td>{{ $version->generator?->name ?: 'Sistem' }}</td><td><div class="d-flex justify-content-end flex-wrap gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('training-activity-report.versions.download', [$version, 'docx']) }}"><i class="bx bxs-file-doc me-1"></i>Word</a>@if($version->pdf_path)<a class="btn btn-sm btn-outline-danger" href="{{ route('training-activity-report.versions.download', [$version, 'pdf']) }}"><i class="bx bxs-file-pdf me-1"></i>PDF</a>@endif</div></td></tr>@empty<tr><td colspan="4"><div class="empty-state py-5"><span><i class="bx bx-history"></i></span><h5>Belum ada versi laporan</h5><p>Versi pertama akan muncul setelah laporan digenerate.</p></div></td></tr>@endforelse</tbody></table></div></div>
        </div>
    </div>
</div>

<div class="modal fade" id="photoModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><form class="modal-content" method="POST" enctype="multipart/form-data" action="{{ route('training-activity-report.photos.store', $training) }}">@csrf<div class="modal-header"><div><h5 class="modal-title fw-bold">Tambah Dokumentasi</h5><small class="text-muted">Satu informasi kegiatan dapat digunakan untuk beberapa foto sekaligus.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body p-4"><div class="upload-zone mb-4"><i class="bx bx-cloud-upload"></i><label class="form-label fw-semibold d-block">Pilih foto kegiatan</label><input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="form-control" required><small>JPG, PNG, atau WebP · maksimal 20 foto · 10 MB per foto</small></div><div class="row g-3"><div class="col-12"><label class="form-label fw-semibold">Judul kegiatan</label><input name="title" class="form-control" required placeholder="Contoh: Pembukaan pelatihan oleh Kepala BPSDM"></div><div class="col-12"><label class="form-label fw-semibold">Keterangan foto</label><textarea name="caption" class="form-control" rows="3" placeholder="Jelaskan siapa, kegiatan apa, dan konteks foto."></textarea></div><div class="col-md-7"><label class="form-label fw-semibold">Kategori</label><select name="category" class="form-select">@foreach($categories as $category)<option value="{{ $category }}">{{ str_replace('_', ' ', Str::title($category)) }}</option>@endforeach</select></div><div class="col-md-5"><label class="form-label fw-semibold">Tanggal kegiatan</label><input type="date" name="taken_at" class="form-control"></div></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="bx bx-upload me-1"></i>Upload Dokumentasi</button></div></form></div></div>

@foreach($photos as $photo)
<div class="modal fade" id="editPhoto{{ $photo->id }}" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><form class="modal-content" method="POST" action="{{ route('training-activity-report.photos.update', $photo) }}">@csrf @method('PUT')<div class="modal-header"><div><h5 class="modal-title fw-bold">Atur Dokumentasi</h5><small class="text-muted">Tentukan informasi dan posisi foto di laporan.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body p-4"><div class="row g-4"><div class="col-md-5"><img src="{{ route('training-activity-report.photos.view', $photo) }}" class="w-100 rounded border edit-photo-preview" alt="{{ $photo->title }}"></div><div class="col-md-7"><div class="mb-3"><label class="form-label fw-semibold">Judul</label><input name="title" value="{{ $photo->title }}" class="form-control" required></div><div class="mb-3"><label class="form-label fw-semibold">Keterangan</label><textarea name="caption" rows="3" class="form-control">{{ $photo->caption }}</textarea></div><div class="row g-2"><div class="col-sm-6"><label class="form-label fw-semibold">Kategori</label><select name="category" class="form-select">@foreach($categories as $category)<option value="{{ $category }}" @selected($photo->category === $category)>{{ str_replace('_', ' ', Str::title($category)) }}</option>@endforeach</select></div><div class="col-sm-6"><label class="form-label fw-semibold">Tanggal</label><input type="date" name="taken_at" value="{{ $photo->taken_at?->format('Y-m-d') }}" class="form-control"></div><div class="col-sm-6"><label class="form-label fw-semibold">Urutan tampil</label><input type="number" min="0" name="sort_order" value="{{ $photo->sort_order }}" class="form-control" required></div></div><div class="option-box mt-3"><div class="form-check"><input type="hidden" name="include_in_report" value="0"><input class="form-check-input" type="checkbox" name="include_in_report" value="1" id="include{{ $photo->id }}" @checked($photo->include_in_report)><label class="form-check-label" for="include{{ $photo->id }}"><strong>Masukkan ke laporan</strong><small class="d-block text-muted">Foto akan disertakan pada halaman dokumentasi.</small></label></div><div class="form-check mt-3"><input type="hidden" name="is_featured" value="0"><input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featured{{ $photo->id }}" @checked($photo->is_featured)><label class="form-check-label" for="featured{{ $photo->id }}"><strong>Jadikan foto utama</strong><small class="d-block text-muted">Foto ditempatkan pada urutan pertama.</small></label></div></div></div></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Pengaturan</button></div></form></div></div>
@endforeach

<div class="modal fade" id="generateModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" method="POST" action="{{ route('training-activity-report.generate', $training) }}">@csrf<div class="modal-header"><div><h5 class="modal-title fw-bold">Generate Laporan</h5><small class="text-muted">Pilih format dokumen yang diperlukan.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body p-4"><label class="form-label fw-semibold">Format dokumen</label><div class="row g-3"><div class="col-6"><input class="btn-check" type="radio" name="format" id="docx" value="docx" checked><label class="format-option" for="docx"><i class="bx bxs-file-doc text-primary"></i><strong>Word</strong><small>Dapat diedit kembali</small></label></div><div class="col-6"><input class="btn-check" type="radio" name="format" id="pdf" value="pdf"><label class="format-option" for="pdf"><i class="bx bxs-file-pdf text-danger"></i><strong>PDF</strong><small>Siap dibagikan</small></label></div></div><div class="finalize-box mt-4"><div class="form-check"><input class="form-check-input" type="checkbox" value="1" name="finalize" id="finalize"><label class="form-check-label" for="finalize"><strong>Tetapkan sebagai versi final</strong><small class="d-block">Sistem akan memastikan seluruh komponen wajib sudah lengkap.</small></label></div></div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="bx bx-cog me-1"></i>Generate & Download</button></div></form></div></div>

@push('js')
<script>
document.querySelectorAll('.copy-code, .copy-inline-code').forEach((button) => {
    button.addEventListener('click', () => navigator.clipboard.writeText(button.dataset.code).then(() => {
        const original = button.innerHTML;
        button.innerHTML = '<i class="bx bx-check"></i> Tersalin';
        setTimeout(() => button.innerHTML = original, 1200);
    }));
});
document.getElementById('codeSearch')?.addEventListener('input', function () {
    const keyword = this.value.toLowerCase().trim();
    document.querySelectorAll('#codeTable tbody tr').forEach(row => row.classList.toggle('d-none', !row.textContent.toLowerCase().includes(keyword)));
});
</script>
@endpush

<style>
.activity-report-page{width:100%;min-width:0}.min-w-0{min-width:0}.report-hero{display:flex;align-items:center;justify-content:space-between;gap:2rem;padding:1.75rem 2rem;border-radius:1rem;background:linear-gradient(135deg,#696cff 0%,#4851c8 100%);box-shadow:0 .5rem 1.5rem rgba(105,108,255,.2);overflow:hidden}.report-hero__content{display:flex;align-items:flex-start;gap:1.25rem;min-width:0}.report-hero__icon{display:grid;place-items:center;flex:0 0 64px;width:64px;height:64px;border-radius:16px;background:rgba(255,255,255,.16);color:#fff;font-size:2rem}.report-hero__subtitle{font-size:1rem;opacity:.88;overflow-wrap:anywhere}.report-hero__meta{display:flex;flex-wrap:wrap;gap:.65rem 1.25rem;color:rgba(255,255,255,.85);font-size:.82rem}.report-hero__meta span{display:inline-flex;align-items:center;gap:.4rem}.report-hero__actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:.75rem;flex:0 0 auto}.completion-score{display:grid;place-items:center;min-width:68px;height:42px;padding:0 .75rem;border-radius:12px;background:#fff3cd;color:#9a6700;font-weight:700}.completion-score.is-complete{background:#d1e7dd;color:#0f5132}.report-progress{height:9px;border-radius:10px}.check-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.check-item{display:flex;align-items:center;gap:.75rem;min-width:0;padding:.8rem;border:1px solid;border-radius:.75rem}.check-item.is-complete{border-color:#cfe8dc;background:#f4fbf7}.check-item.is-pending{border-color:#f4dfad;background:#fffaf0}.check-item__icon{display:grid;place-items:center;flex:0 0 34px;width:34px;height:34px;border-radius:50%;font-size:1.2rem}.is-complete .check-item__icon{background:#d1e7dd;color:#198754}.is-pending .check-item__icon{background:#fff0c2;color:#b77900}.check-item small{color:#8592a3}.metric-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.metric-card{display:flex;align-items:center;gap:.65rem;min-width:0;padding:.9rem;border-radius:.75rem}.metric-card>i{font-size:1.5rem;flex:0 0 auto}.metric-card strong,.metric-card span{display:block}.metric-card strong{font-size:1.15rem;line-height:1.2}.metric-card span{font-size:.72rem;margin-top:.2rem}.metric-primary{background:#eef0ff;color:#5663d7}.metric-info{background:#e8f7fa;color:#168da0}.metric-success{background:#eaf8f0;color:#238854}.metric-warning{background:#fff5dc;color:#aa7200}.report-tabs-shell{position:sticky;top:76px;z-index:10;padding:.5rem;background:rgba(255,255,255,.96);border:1px solid #e7e7ff;border-radius:.85rem;box-shadow:0 .25rem .8rem rgba(67,89,113,.08);backdrop-filter:blur(8px)}.report-tabs-scroll{overflow-x:auto;scrollbar-width:thin}.report-tabs{gap:.4rem;width:max-content;min-width:100%}.report-tabs .nav-link{display:flex;align-items:center;justify-content:center;gap:.45rem;min-height:44px;padding:.65rem 1rem;white-space:nowrap;border-radius:.65rem;color:#697a8d}.report-tabs .nav-link.active{box-shadow:none}.tab-count{min-width:22px;padding:.1rem .4rem;border-radius:20px;background:rgba(105,108,255,.1);font-size:.7rem}.nav-link.active .tab-count{background:rgba(255,255,255,.2)}.report-tab-content{border:0!important;min-width:0}.info-box{display:flex;align-items:flex-start;gap:.75rem;height:100%;padding:1rem;border:1px solid #dce0ff;border-radius:.75rem;background:#f5f6ff;color:#566a7f}.info-box i{font-size:1.25rem;color:#696cff}.section-number{display:grid;place-items:center;flex:0 0 40px;width:40px;height:40px;border-radius:12px;background:#eef0ff;color:#696cff;font-weight:700}.activity-report-ai-panel{padding:1.25rem;border:1px solid #d9dcff;border-radius:.9rem;background:linear-gradient(135deg,#f5f6ff,#fff)}.activity-report-ai-icon{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:14px;background:#696cff;color:#fff;font-size:1.55rem}.narrative-field{height:100%;padding:1rem;border:1px solid #e8e8ef;border-radius:.8rem;background:#fcfcfd}.narrative-field textarea{min-height:145px;resize:vertical}.copy-inline-code{padding:0;border:0;background:transparent;color:#696cff;font-size:.74rem;text-align:left}.photo-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}.photo-card{display:flex;flex-direction:column;min-width:0;overflow:hidden;border:1px solid #e6e7eb;border-radius:.9rem;background:#fff}.photo-card.is-excluded{opacity:.72}.photo-card__image{position:relative;display:block;height:210px;background:#f1f2f4;overflow:hidden}.photo-card__image img{width:100%;height:100%;object-fit:cover;transition:transform .25s}.photo-card__image:hover img{transform:scale(1.03)}.photo-card__badges{position:absolute;left:.75rem;top:.75rem;display:flex;flex-wrap:wrap;gap:.35rem}.photo-card__excluded{position:absolute;right:.75rem;bottom:.75rem;padding:.3rem .55rem;border-radius:.4rem;background:rgba(33,37,41,.82);color:#fff;font-size:.7rem}.photo-card__body{flex:1;padding:1rem}.photo-card__body p{display:-webkit-box;overflow:hidden;-webkit-box-orient:vertical;-webkit-line-clamp:2}.photo-card__meta{display:flex;flex-wrap:wrap;gap:.5rem 1rem;color:#8592a3;font-size:.72rem}.photo-card__meta span{display:inline-flex;align-items:center;gap:.3rem}.photo-card__footer{display:flex;gap:.5rem;padding:.75rem 1rem;border-top:1px solid #eee}.empty-state{text-align:center;padding:4rem 1rem}.empty-state>span{display:grid;place-items:center;width:70px;height:70px;margin:0 auto 1rem;border-radius:50%;background:#eef0ff;color:#696cff;font-size:2rem}.empty-state p{max-width:500px;margin:0 auto 1.25rem;color:#8592a3}.template-panel{position:sticky;top:150px}.template-icon{display:grid;place-items:center;width:60px;height:60px;border-radius:15px;background:#eef0ff;color:#696cff;font-size:2rem}.template-status{display:flex;align-items:center;gap:.5rem;padding:.75rem;border-radius:.65rem;background:#f7f7fa}.template-status i{font-size:1.2rem}.code-table-wrap{max-height:650px}.code-table-wrap thead{position:sticky;top:0;z-index:2}.code-chip{display:inline-flex;align-items:center;justify-content:space-between;gap:.5rem;max-width:100%;padding:.42rem .6rem;border:1px solid #d9dcff;border-radius:.45rem;background:#f4f5ff;color:#5663d7}.code-chip code{overflow:hidden;color:inherit;text-overflow:ellipsis}.version-badge{display:inline-flex;padding:.35rem .7rem;border-radius:.5rem;background:#eef0ff;color:#5663d7;font-weight:700}.upload-zone{padding:1.25rem;border:1px dashed #b7bcff;border-radius:.85rem;background:#f8f8ff;text-align:center}.upload-zone>i{font-size:2rem;color:#696cff}.upload-zone small{display:block;margin-top:.5rem;color:#8592a3}.edit-photo-preview{height:280px;object-fit:cover}.option-box{padding:1rem;border:1px solid #e8e8ef;border-radius:.75rem;background:#fafafa}.format-option{display:flex;flex-direction:column;align-items:center;gap:.25rem;height:100%;padding:1.25rem;border:2px solid #e6e7eb;border-radius:.8rem;text-align:center;cursor:pointer}.format-option i{font-size:2.25rem}.format-option small{color:#8592a3}.btn-check:checked+.format-option{border-color:#696cff;background:#f5f5ff}.finalize-box{padding:1rem;border:1px solid #ffe2a8;border-radius:.75rem;background:#fffaf0}
@media(max-width:1199.98px){.photo-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.template-panel{position:static}}
@media(max-width:767.98px){.report-hero{align-items:stretch;padding:1.25rem;flex-direction:column}.report-hero__icon{display:none}.report-hero__actions{justify-content:stretch}.report-hero__actions .btn{flex:1}.check-grid,.metric-grid{grid-template-columns:1fr}.photo-grid{grid-template-columns:1fr}.report-tabs-shell{top:68px}.card-header,.card-body,.card-footer{overflow-wrap:anywhere}.code-table-wrap{max-height:none}.copy-inline-code{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}}
@media(max-width:479.98px){.report-hero__actions{flex-direction:column}.report-hero__actions .btn{width:100%}.report-tabs .nav-link{padding:.6rem .8rem}.photo-card__image{height:190px}}
</style>
@endsection
