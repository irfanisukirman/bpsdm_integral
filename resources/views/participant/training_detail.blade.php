@extends('layouts.master')

@section('title', 'Detail Pelatihan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Pelatihan Saya /</span> {{ $training->nama_pelatihan }}
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="nav-align-top mb-4">
                {{-- 4 BAR MENU (TAB) --}}
                <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-info">
                            <i class="bx bx-info-circle me-1"></i> 1. INFO UMUM
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#kelengkapan">
                            <i class="bx bx-file me-1"></i> 2. KELENGKAPAN 
                            @php
                                $isComplete = $participant->biodata_file_id && $participant->surat_tugas_file_id;
                            @endphp
                            @if(!$isComplete)
                                <span class="badge badge-dot bg-danger ms-1"></span>
                            @else
                                <i class="bx bxs-check-circle text-success ms-1"></i>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-evaluasi">
                            <i class="bx bx-bar-chart-alt-2 me-1"></i> 3. EVALUASI
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-pills-sertifikat">
                            <i class="bx bx-medal me-1"></i> 4. SERTIFIKAT
                        </button>
                    </li>
                </ul>

                <div class="tab-content shadow-sm border-0 p-4 bg-white rounded">
                    {{-- TAB 1: INFO UMUM --}}
                    <div class="tab-pane fade show active" id="navs-pills-info" role="tabpanel">
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
                        </div>
                    </div>

                    {{-- TAB 2: KELENGKAPAN (LOGIKA TERKUNCI & USER FRIENDLY) --}}
                    <div class="tab-pane fade" id="kelengkapan" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">Unggah Berkas Administrasi</h5>
                            @if($isComplete)
                                <span class="badge bg-label-success"><i class="bx bx-check-double me-1"></i> Semua Berkas Lengkap</span>
                            @endif
                        </div>

                        <div class="row">
                            @php
                                $docs = [
                                    ['label' => 'Biodata Peserta', 'key' => 'biodata', 'file_id' => $participant->biodata_file_id],
                                    ['label' => 'Surat Tugas', 'key' => 'surat_tugas', 'file_id' => $participant->surat_tugas_file_id]
                                ];
                            @endphp

                            @foreach($docs as $doc)
                            <div class="col-md-6 mb-4">
                                <div class="card border {{ $doc['file_id'] ? 'border-success bg-label-success' : 'border-dashed bg-label-secondary' }} h-100 shadow-none transition-all">
                                    <div class="card-body text-center py-4">
                                        
                                        @if($doc['file_id'])
                                            {{-- TAMPILAN JIKA SUDAH UPLOAD --}}
                                            <div class="avatar avatar-lg bg-success mx-auto mb-3 shadow">
                                                <i class="bx bx-check-shield text-white h3 mb-0"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">{{ $doc['label'] }}</h6>
                                            <p class="small text-success fw-bold mb-3">BERKAS TERSIMPAN</p>
                                            
                                            @php $file = \App\Models\File::find($doc['file_id']); @endphp
                                            
                                            <div class="d-grid gap-2 col-8 mx-auto">
                                                <a href="{{ asset('storage/'.($file->file_path ?? '')) }}" target="_blank" class="btn btn-primary btn-sm">
                                                    <i class="bx bx-show me-1"></i> Lihat Berkas
                                                </a>
                                            </div>

                                            <div class="mt-4 p-2 bg-white rounded border border-success">
                                                <small class="text-muted d-block italic">
                                                    <i class="bx bx-lock-alt me-1"></i> Ingin mengganti berkas?
                                                </small>
                                                <small class="fw-bold text-primary">Silakan hubungi Admin Bidang.</small>
                                            </div>
                                        @else
                                            {{-- TAMPILAN JIKA BELUM UPLOAD --}}
                                            <div class="avatar avatar-lg bg-secondary mx-auto mb-3">
                                                <i class="bx bx-upload text-white h3 mb-0"></i>
                                            </div>
                                            <h6 class="fw-bold text-dark mb-1">{{ $doc['label'] }}</h6>
                                            <p class="small text-muted mb-4">Belum ada berkas yang diunggah</p>

                                            <form action="{{ route('participant.training.upload', $training->id) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                                @csrf
                                                <input type="hidden" name="type" value="{{ $doc['key'] }}">
                                                <div class="mb-3">
                                                    <input type="file" name="file" class="form-control form-control-sm" accept="application/pdf" required>
                                                </div>
                                                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                                    <i class="bx bx-cloud-upload me-1"></i> Unggah Sekarang
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
                    <div class="tab-pane fade" id="navs-pills-evaluasi" role="tabpanel">
                        <div class="card shadow-none border mb-4">
                            <div class="card-body text-center py-4">
                                <h6 class="fw-bold text-start mb-3 text-primary"><i class="bx bx-star me-2"></i>A. Evaluasi Level 1 (Selama Pelatihan)</h6>
                                <div class="list-group list-group-flush text-start">
                                    @forelse($formsL1 as $form)
                                        <a href="{{ route('public.evall1.form', ['training_id' => $training->id, 'type' => $form->type, 'sid' => $form->schedule_id]) }}" 
                                           target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center rounded mb-2 border shadow-sm">
                                            <div>
                                                <span class="fw-bold d-block">{{ $form->name }}</span>
                                                <small class="text-muted">Objek: {{ $form->target_name }}</small>
                                            </div>
                                            <i class="bx bx-chevron-right text-primary"></i>
                                        </a>
                                    @empty
                                        <div class="bg-light p-3 rounded text-center">Belum ada evaluasi yang tersedia.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-none border">
                            <div class="card-body text-center py-4">
                                <h6 class="fw-bold text-start mb-3 text-primary"><i class="bx bx-line-chart me-2"></i>B. Evaluasi Level 3 & 4 (Pasca Pelatihan)</h6>
                                @if($training->sisa_hari_sebar <= 0)
                                    <div class="bg-label-success p-4 rounded mb-3 border border-success">
                                        <h6 class="fw-bold mb-2">Evaluasi Dampak Telah Dibuka!</h6>
                                        <p class="small mb-3">Silakan berikan penilaian Anda mengenai perubahan perilaku dan dampak pelatihan di tempat kerja.</p>
                                        <a href="{{ route('public.l34.gateway', $training->id) }}" target="_blank" class="btn btn-success shadow-sm">
                                            <i class="bx bx-paper-plane me-1"></i> Mulai Penilaian
                                        </a>
                                    </div>
                                @else
                                    <div class="bg-light p-4 rounded text-muted border border-dashed">
                                        <i class="bx bx-lock-alt h2 d-block mb-2"></i>
                                        <p class="mb-0">Kuesioner Dampak belum tersedia saat ini.</p>
                                        <small>Akan dibuka pada: <strong>{{ $training->tgl_sebar_l34->translatedFormat('d F Y') }}</strong></small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- TAB 4: SERTIFIKAT --}}
                    <div class="tab-pane fade" id="navs-pills-sertifikat" role="tabpanel">
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