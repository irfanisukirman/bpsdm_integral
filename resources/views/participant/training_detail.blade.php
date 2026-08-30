@extends('layouts.master')

@section('title', 'Detail Pelatihan')

@section('content')
@php
    $activeTab = in_array(request('tab'), ['info', 'kelengkapan', 'evaluasi', 'sertifikat'])
        ? request('tab')
        : 'info';
    $completedDocuments = collect([$participant->pas_foto_file_id, $participant->biodata_file_id, $participant->surat_tugas_file_id])->filter()->count();
    $forumUnread = app(\App\Services\NotificationCenter::class)->unreadCountForTraining(auth()->user(), $training);
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <a href="{{ route('participant.trainings') }}" class="small text-muted text-decoration-none"><i class="bx bx-arrow-back me-1"></i>Kembali ke Pelatihan Saya</a>
            <h4 class="fw-bold mt-2 mb-1">{{ $training->nama_pelatihan }}</h4>
            <div class="d-flex flex-wrap gap-2 text-muted small">
                <span><i class="bx bx-calendar me-1"></i>{{ \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d M Y') }}</span>
                <span><i class="bx bx-map me-1"></i>{{ $training->lokasi }}</span>
                <span><i class="bx bx-time-five me-1"></i>{{ $training->jp }} JP</span>
            </div>
        </div>
        <div class="text-md-end">
            <a href="{{ route('training.forum.index', $training) }}" class="btn btn-primary me-2 position-relative">
                <i class="bx bx-conversation me-1"></i> Forum Pelatihan
                @if($forumUnread > 0)
                    <span class="badge rounded-pill bg-danger ms-1">{{ $forumUnread > 99 ? '99+' : $forumUnread }}</span>
                @endif
            </a>
            <span class="badge {{ $completedDocuments === 3 ? 'bg-label-success' : 'bg-label-warning' }} px-3 py-2">
                Kelengkapan {{ $completedDocuments }}/3
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible"><i class="bx bx-check-circle me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error') || $errors->any())
        <div class="alert alert-danger alert-dismissible"><i class="bx bx-error-circle me-1"></i>{{ session('error') ?? $errors->first() }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="nav-align-top mb-4">
                {{-- 4 BAR MENU (TAB) --}}
                <ul class="nav nav-pills training-tabs mb-3 flex-nowrap" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $activeTab === 'info' ? 'active' : '' }}" role="tab" data-tab="info" data-bs-toggle="tab" data-bs-target="#navs-pills-info">
                            <i class="bx bx-info-circle me-1"></i> Ringkasan
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $activeTab === 'kelengkapan' ? 'active' : '' }}" role="tab" data-tab="kelengkapan" data-bs-toggle="tab" data-bs-target="#kelengkapan">
                            <i class="bx bx-file me-1"></i> Kelengkapan
                            @if($completedDocuments < 3)
                                <span class="badge badge-dot bg-danger ms-1"></span>
                            @else
                                <i class="bx bxs-check-circle text-success ms-1"></i>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $activeTab === 'evaluasi' ? 'active' : '' }}" role="tab" data-tab="evaluasi" data-bs-toggle="tab" data-bs-target="#navs-pills-evaluasi">
                            <i class="bx bx-bar-chart-alt-2 me-1"></i> Evaluasi
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $activeTab === 'sertifikat' ? 'active' : '' }}" role="tab" data-tab="sertifikat" data-bs-toggle="tab" data-bs-target="#navs-pills-sertifikat">
                            <i class="bx bx-medal me-1"></i> Sertifikat
                        </button>
                    </li>
                </ul>

                <div class="tab-content shadow-sm border-0 p-4 bg-white rounded">
                    {{-- TAB 1: INFO UMUM --}}
                    <div class="tab-pane fade {{ $activeTab === 'info' ? 'show active' : '' }}" id="navs-pills-info" role="tabpanel">
                        <div class="row">
                            <div class="col-md-7">
                                <h5 class="fw-bold text-primary mb-3">Informasi Pelatihan</h5>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tr><td class="ps-0 py-1" width="180">Metode</td><td>: <span class="badge bg-label-primary">{{ strtoupper($training->metode) }}</span></td></tr>
                                        <tr><td class="ps-0 py-1">Tanggal Mulai</td><td>: {{ \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d F Y') }}</td></tr>
                                        <tr><td class="ps-0 py-1">Tanggal Selesai</td><td>: {{ \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y') }}</td></tr>
                                        <tr><td class="ps-0 py-1">Lokasi</td><td>: {{ $training->lokasi }}</td></tr>
                                        <tr><td class="ps-0 py-1">Durasi</td><td>: {{ $training->jp }} JP</td></tr>
                                    </table>
                                    @if($training->link_lms)
                                        <div class="mt-4 p-4 border rounded bg-label-info animate__animated animate__fadeIn">
                                            <div class="d-flex align-items-start">
                                                <div class="avatar bg-info p-2 rounded me-3">
                                                    <i class="bx bx-laptop text-white h3 mb-0"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-bold text-dark">Akses Ruang Belajar Digital</h6>
                                                    <p class="small text-muted mb-3">Silakan klik tombol di bawah untuk masuk ke platform LMS Pelatihan.</p>
                                                    <a href="{{ $training->link_lms }}" target="_blank" class="btn btn-info shadow">
                                                        <i class="bx bx-rocket me-1"></i> MASUK KE LMS / AKSES PELATIHIAN
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-4 p-3 border rounded bg-light">
                                            <p class="small text-muted mb-0 italic">
                                                <i class="bx bx-info-circle me-1"></i> Tautan akses kelas online belum tersedia. Silakan hubungi admin atau tunggu hingga pelatihan dimulai.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-5 mt-4 mt-md-0">
                                <div class="card border shadow-none h-100">
                                    <div class="card-header bg-light border-bottom">
                                        <h6 class="fw-bold mb-1"><i class="bx bx-calendar-check text-primary me-2"></i>Presensi Kehadiran</h6>
                                        <small class="text-muted">Presensi dicatat satu kali untuk setiap hari pelatihan.</small>
                                    </div>
                                    <div class="card-body">
                                        @forelse($attendanceDays as $day)
                                            @php
                                                $attendance = $day['attendance'];
                                                $isFilled = $attendance !== null;
                                                $dateLabel = \Carbon\Carbon::parse($day['date'])->translatedFormat('l, d F Y');
                                                $statusLabel = $isFilled ? ucfirst($attendance->status) : 'Belum Presensi';
                                            @endphp
                                            <div class="border rounded p-3 mb-3 {{ $isFilled ? 'border-success bg-label-success' : 'border-danger bg-label-danger' }}">
                                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                    <div>
                                                        <small class="text-muted d-block">Tanggal Presensi</small>
                                                        <span class="fw-bold text-dark">{{ $dateLabel }}</span>
                                                    </div>
                                                    <i class="bx {{ $isFilled ? 'bxs-check-circle text-success' : 'bx-error-circle text-danger' }} fs-4"></i>
                                                </div>

                                                @if($isFilled)
                                                    <button type="button" class="btn btn-success btn-sm w-100" disabled>
                                                        <i class="bx bx-check-double me-1"></i>
                                                        Sudah Presensi - {{ $statusLabel }}
                                                        @if($attendance->check_in_at)
                                                            ({{ \Carbon\Carbon::parse($attendance->check_in_at)->format('H:i') }})
                                                        @endif
                                                    </button>
                                                @else
                                                    <a href="{{ route('public.attendance.daily', ['training_id' => $training->id, 'date' => $day['date'], 'participant_id' => $participant->id]) }}"
                                                       class="btn btn-danger btn-sm w-100" target="_blank">
                                                        <i class="bx bx-fingerprint me-1"></i> Belum Presensi - Isi Sekarang
                                                    </a>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-center text-muted py-4">
                                                <i class="bx bx-calendar-x fs-1 d-block mb-2"></i>
                                                Jadwal pelatihan belum tersedia.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 2: KELENGKAPAN (LOGIKA TERKUNCI & USER FRIENDLY) --}}
                    <div class="tab-pane fade {{ $activeTab === 'kelengkapan' ? 'show active' : '' }}" id="kelengkapan" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Unggah Berkas Administrasi</h5>
                            @php
                                $isComplete = $participant->biodata_file_id && $participant->surat_tugas_file_id && $participant->pas_foto_file_id;
                            @endphp
                            @if($isComplete)
                                <span class="badge bg-label-success"><i class="bx bx-check-double me-1"></i> Semua Berkas Lengkap</span>
                            @endif
                        </div>

                        <div class="row">
                            @php
                                $docs = [
                                    ['label' => 'Pas Foto (Berwarna)', 'key' => 'pas_foto', 'file_id' => $participant->pas_foto_file_id, 'accept' => 'image/*'],
                                    ['label' => 'Biodata Peserta', 'key' => 'biodata', 'file_id' => $participant->biodata_file_id, 'accept' => '.pdf'],
                                    ['label' => 'Surat Tugas', 'key' => 'surat_tugas', 'file_id' => $participant->surat_tugas_file_id, 'accept' => '.pdf']
                                ];
                            @endphp

                            @foreach($docs as $doc)
                            <div class="col-md-4 mb-4"> {{-- Ubah ke col-md-4 agar 3 card sejajar --}}
                                <div class="card border {{ $doc['file_id'] ? 'border-success bg-label-success' : 'border-dashed bg-label-secondary' }} h-100 shadow-none transition-all">
                                    <div class="card-body text-center py-4">
                                        
                                        @if($doc['file_id'])
                                            {{-- TAMPILAN JIKA SUDAH UPLOAD --}}
                                            <div class="avatar avatar-lg bg-success mx-auto mb-3 shadow">
                                                <i class="bx {{ $doc['key'] == 'pas_foto' ? 'bx-image' : 'bx-check-shield' }} text-white h3 mb-0"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">{{ $doc['label'] }}</h6>
                                            <p class="small text-success fw-bold mb-3">BERKAS TERSIMPAN</p>
                                            
                                            @php $file = \App\Models\File::find($doc['file_id']); @endphp
                                            
                                            <div class="d-grid gap-2 col-10 mx-auto">
                                                <a href="{{ asset('storage/'.($file->file_path ?? '')) }}" target="_blank" class="btn btn-primary btn-sm">
                                                    <i class="bx bx-show me-1"></i> Lihat Berkas
                                                </a>
                                            </div>

                                            <div class="mt-4 p-2 bg-white rounded border border-success">
                                                <small class="text-muted d-block italic">
                                                    <i class="bx bx-lock-alt me-1"></i> Terkunci.
                                                </small>
                                                <small class="fw-bold text-primary" style="font-size: 10px;">Hubungi Admin untuk ganti.</small>
                                            </div>
                                        @else
                                            {{-- TAMPILAN JIKA BELUM UPLOAD --}}
                                            <div class="avatar avatar-lg bg-secondary mx-auto mb-3">
                                                <i class="bx {{ $doc['key'] == 'pas_foto' ? 'bx-camera' : 'bx-upload' }} text-white h3 mb-0"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">{{ $doc['label'] }}</h6>
                                            <p class="small text-muted mb-4">{{ $doc['key'] == 'pas_foto' ? 'Format JPG/PNG' : 'Format PDF, Maks 5MB' }}</p>

                                            <form action="{{ route('participant.training.upload', ['id' => $training->id, 'tab' => 'kelengkapan']) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                                @csrf
                                                <input type="hidden" name="type" value="{{ $doc['key'] }}">
                                                <div class="mb-3">
                                                    <input type="file" name="file" class="form-control form-control-sm" accept="{{ $doc['accept'] }}" required>
                                                </div>
                                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                                    <i class="bx bx-cloud-upload me-1"></i> Unggah
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- TAB 3: EVALUASI --}}
                    <div class="tab-pane fade {{ $activeTab === 'evaluasi' ? 'show active' : '' }}" id="navs-pills-evaluasi" role="tabpanel">
    
                        {{-- BAGIAN A: LEVEL 1 (REAKSI) --}}
                        <div class="card shadow-none border mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-3"><i class="bx bx-star me-2"></i>A. Evaluasi Selama Pelatihan (Level 1)</h6>
                                
                                <div class="list-group list-group-flush mt-3">
                                    @forelse($formsL1 as $form)
                                        @php
                                            // Cek status pengisian per form
                                            $isFilledL1 = $participant->hasFilledL1($form->id, $form->schedule_id);
                                            $opensAtL1 = $form->opensAt();
                                            $isOpenL1 = $form->isOpen();
                                        @endphp
                                        
                                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center rounded mb-2 border {{ $isFilledL1 ? 'border-info bg-label-info' : ($isOpenL1 ? 'border-danger bg-label-danger' : 'border-secondary bg-light') }} shadow-sm">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar flex-shrink-0 me-3">
                                                    <span class="avatar-initial rounded bg-white {{ $isFilledL1 ? 'text-info' : ($isOpenL1 ? 'text-danger' : 'text-secondary') }}">
                                                        <i class="bx {{ $isFilledL1 ? 'bx-check-circle' : ($isOpenL1 ? 'bx-error-circle' : 'bx-lock-alt') }}"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block text-dark">{{ $form->name }}</span>
                                                    <small class="text-muted d-block">Objek: {{$form->target_name}}</small>
                                                    @if($form->type==='narasumber' && $form->schedule)<small class="text-muted d-block"><i class="bx bx-calendar me-1"></i>{{\Carbon\Carbon::parse($form->schedule->date)->translatedFormat('d M Y')}} · {{substr($form->schedule->start_time,0,5)}}–{{substr($form->schedule->end_time,0,5)}} · {{$form->materi}}</small>@endif
                                                </div>
                                            </div>

                                            <div class="text-end">
                                                @if($isFilledL1)
                                                    <span class="badge bg-info mb-1">Sudah Mengisi</span><button class="btn btn-xs btn-outline-info d-block w-100" disabled>Terkunci</button>
                                                @elseif(!$isOpenL1)
                                                    <span class="badge bg-label-secondary mb-1">Belum Dibuka</span><button class="btn btn-xs btn-outline-secondary d-block w-100" disabled><i class="bx bx-lock-alt me-1"></i>{{$opensAtL1?->translatedFormat('d M, H:i')?:'Menunggu jadwal'}}</button>
                                                @else
                                                    <span class="badge bg-danger mb-1">Belum Mengisi</span><a href="{{route('public.evall1.form',['training_id'=>$training->id,'type'=>$form->type,'sid'=>$form->schedule_id??'null'])}}" target="_blank" class="btn btn-xs btn-danger d-block w-100">Isi Sekarang</a>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="bg-light p-3 rounded text-center">Belum ada instrumen evaluasi Level 1.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- BAGIAN B: LEVEL 3 & 4 (DAMPAK) --}}
                        @php
                            $isFilledL34 = $participant->hasFilledL34Mandiri();
                        @endphp
                        <div class="card shadow-none border {{ $isFilledL34 ? 'border-info' : 'border-danger' }}">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-3"><i class="bx bx-line-chart me-2"></i>B. Evaluasi Pasca Pelatihan (Level 3 & 4)</h6>
                                
                                @if($training->sisa_hari_sebar <= 0)
                                    {{-- Waktu sebar sudah tiba --}}
                                    <div class="p-4 rounded border {{ $isFilledL34 ? 'bg-label-info border-info' : 'bg-label-danger border-danger' }}">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <div>
                                                <h6 class="fw-bold {{ $isFilledL34 ? 'text-info' : 'text-danger' }} mb-1">
                                                    {{ $isFilledL34 ? 'Evaluasi Dampak Selesai' : 'Waktunya Mengisi Evaluasi Dampak' }}
                                                </h6>
                                                <p class="small mb-0">Pengisian penilaian perubahan perilaku dan hasil pelatihan.</p>
                                            </div>
                                            
                                            @if($isFilledL34)
                                                <div class="text-center">
                                                    <span class="badge bg-info d-block mb-2">Selesai</span>
                                                    <i class="bx bxs-badge-check text-info h2 mb-0"></i>
                                                </div>
                                            @else
                                                <a href="{{ route('public.l34.form', [$training->id, 'mandiri']) }}" class="btn btn-danger shadow-sm">
                                                    <i class="bx bx-edit me-1"></i> Isi Evaluasi Sekarang
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    {{-- Belum waktunya --}}
                                    <div class="bg-light p-4 rounded text-muted border border-dashed text-center">
                                        <i class="bx bx-lock-alt h2 d-block mb-2"></i>
                                        <p class="mb-0">Kuesioner Dampak (L3 & L4) belum tersedia.</p>
                                        <small>Akan dibuka otomatis pada: <strong>{{ $training->tgl_sebar_l34->translatedFormat('d F Y') }}</strong></small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- TAB 4: SERTIFIKAT --}}
                    <div class="tab-pane fade {{ $activeTab === 'sertifikat' ? 'show active' : '' }}" id="navs-pills-sertifikat" role="tabpanel">
                        <div class="text-center py-5 bg-light rounded border border-dashed">
                            <div class="avatar avatar-xl bg-label-warning mx-auto mb-4" style="width: 100px; height: 100px;">
                                <i class="bx bx-medal" style="font-size: 50px;"></i>
                            </div>
                            <h4 class="fw-bold text-dark">{{ strtoupper(auth()->user()->name) }}</h4>
                            <p class="text-muted px-lg-5">Sertifikat elektronik akan muncul di sini jika Anda dinyatakan <strong>LULUS</strong> <br> dan telah melengkapi seluruh administrasi serta evaluasi.</p>
                            <button class="btn btn-secondary disabled px-5 shadow-none"><i class="bx bx-lock-alt me-1"></i> Belum Tersedia</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f5f5f9; }
    .nav-pills .nav-link.active { box-shadow: 0 2px 10px rgba(105, 108, 255, 0.4); }
    .training-tabs {
        gap: .5rem;
        overflow-x: auto;
        padding: .25rem;
        scrollbar-width: thin;
    }
    .training-tabs .nav-item { flex: 1 0 auto; }
    .training-tabs .nav-link {
        width: 100%;
        min-width: 145px;
        white-space: nowrap;
        padding: .8rem 1rem;
    }
    .card { transition: all 0.3s ease; }
    .bg-label-success { background-color: #eafbea !important; }
    .transition-all:hover { transform: translateY(-3px); }
    .border-dashed { border-style: dashed !important; border-width: 2px !important; }
    .italic { font-style: italic; }
</style>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        document.querySelectorAll('.training-tabs [data-bs-toggle="tab"]').forEach(function (tabButton) {
            tabButton.addEventListener('shown.bs.tab', function (event) {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', event.target.dataset.tab);
                window.history.replaceState({}, '', url);
            });
        });

        @if(session('success_enroll'))
            Swal.fire({
                title: 'Selamat Bergabung!',
                text: "{{ session('success_enroll') }}",
                icon: 'success',
                confirmButtonText: 'Lengkapi Berkas',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        @endif
    });
</script>
@endpush
