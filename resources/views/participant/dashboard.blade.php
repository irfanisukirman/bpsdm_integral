@extends('layouts.master')

@section('title', 'Dashboard Peserta')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <!-- Banner Selamat Datang -->
        <div class="col-lg-8 mb-4">
            <div class="card bg-label-primary border-0">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Selamat Datang, {{ $user->name }}! 👋</h5>
                            <p class="mb-4">
                                Anda login menggunakan NIP: <span class="fw-bold">{{ $user->nip_nik }}</span>. 
                                Pantau terus riwayat pelatihan dan capaian kompetensi Anda di sini.
                            </p>
                            <a href="{{ route('participant.trainings') }}" class="btn btn-sm btn-primary">Lihat Pelatihan Baru</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4 text-center">
                            {{-- LOGIKA FOTO PROFIL SINKRON --}}
                            <div class="mb-3">
                                @if($user->profile_photo)
                                    {{-- Jika ada foto yang diunggah manual --}}
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                         alt="Avatar" 
                                         class="rounded-circle shadow-lg border border-3 border-white" 
                                         style="width: 120px; height: 120px; object-fit: cover;" />
                                @elseif($user->avatar)
                                    {{-- Jika tidak ada unggahan manual, gunakan avatar Google --}}
                                    <img src="{{ $user->avatar }}" 
                                         alt="Avatar" 
                                         class="rounded-circle shadow-lg border border-3 border-white" 
                                         style="width: 120px; height: 120px; object-fit: cover;" />
                                @else
                                    {{-- Jika tidak ada keduanya, gunakan ilustrasi default Sneat --}}
                                    <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" 
                                         height="140" alt="Default Illustration" />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Target JP Tahun Ini -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0">Target JP Tahun {{ date('Y') }}</h6>
                        <span class="badge {{ $myJpThisYear >= 20 ? 'bg-label-success' : 'bg-label-warning' }}">
                            {{ $myJpThisYear >= 20 ? 'Target Terpenuhi' : 'On Progress' }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center mt-3">
                        <h2 class="mb-0 me-2">{{ $myJpThisYear }}</h2>
                        <span class="text-muted">/ 20 JP Minimal</span>
                    </div>
                    
                    @php
                        $target = 20;
                        $percent = ($myJpThisYear / $target) * 100;
                        if($percent > 100) $percent = 100; 
                    @endphp

                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-semibold">Progress Capaian</small>
                            <small class="fw-bold">{{ round($percent) }}%</small>
                        </div>
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated {{ $myJpThisYear >= 20 ? 'bg-success' : 'bg-primary' }}" 
                                 role="progressbar" 
                                 style="width: {{ $percent }}%" 
                                 aria-valuenow="{{ $percent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100"></div>
                        </div>
                    </div>
                    <p class="mt-3 small text-muted italic">
                        <i class="bx bx-info-circle me-1"></i> 
                        {{ $myJpThisYear >= 20 
                           ? 'Selamat! Anda telah memenuhi syarat minimal 20 JP tahun ini.' 
                           : 'Anda butuh ' . (20 - $myJpThisYear) . ' JP lagi untuk memenuhi target tahunan.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if($isPengajar)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-label-info d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1 text-info"><i class="bx bx-chalkboard me-2"></i>Dashboard Pengajar</h5>
                <small class="text-muted">Ringkasan penugasan mengajar Anda.</small>
            </div>
            <a href="{{ route('pengajar.index') }}" class="btn btn-info btn-sm">Kelola Administrasi Pengajar</a>
        </div>
        <div class="card-body pt-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block mb-1">JP Mengajar Tahun {{ date('Y') }}</small>
                        <h3 class="mb-0 text-info">{{ $teachingJpThisYear }} <small class="fs-6 text-muted">JP</small></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block mb-1">Total Seluruh JP Mengajar</small>
                        <h3 class="mb-0 text-primary">{{ $teachingJpTotal }} <small class="fs-6 text-muted">JP</small></h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <small class="text-muted d-block mb-1">Pelatihan yang Diajar</small>
                        <h3 class="mb-0 text-success">{{ $teachingCount }} <small class="fs-6 text-muted">Pelatihan</small></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Statistik Kecil -->
        <div class="col-md-6 col-6 mb-4">
            <div class="card shadow-none border hover-shadow transition-all">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar bg-label-info p-2 rounded me-2">
                            <i class="bx bx-collection text-info h4 mb-0"></i>
                        </div>
                        <span class="d-block fw-semibold text-muted">Pelatihan Tersedia</span>
                    </div>
                    <h3 class="card-title mb-2 text-info">{{ \App\Models\Training::where('tgl_selesai', '>=', now())->count() }}</h3>
                    <a href="{{ route('participant.trainings') }}" class="small text-decoration-none">Cek Pelatihan Baru <i class="bx bx-right-arrow-alt"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-6 mb-4">
            <div class="card shadow-none border hover-shadow transition-all">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar bg-label-success p-2 rounded me-2">
                            <i class="bx bx-history text-success h4 mb-0"></i>
                        </div>
                        <span class="d-block fw-semibold text-muted">Riwayat Pelatihan</span>
                    </div>
                    <h3 class="card-title mb-2 text-success">{{ $totalFollowed }}</h3>
                    <a href="{{ route('participant.history') }}" class="small text-decoration-none">Lihat Semua Riwayat <i class="bx bx-right-arrow-alt"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .transition-all { transition: all 0.3s ease; }
    .hover-shadow:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 10px 20px rgba(105, 108, 255, 0.1) !important;
    }
    /* Mencegah gambar lonjong jika aspek rasio tidak 1:1 */
    .rounded-circle {
        object-fit: cover;
    }
</style>
@endsection
