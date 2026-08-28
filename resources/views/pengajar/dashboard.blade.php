@extends('layouts.master')

@section('title', 'Dashboard Pengajar')

@push('css')
<style>
    .transition-all { 
        transition: all 0.3s ease; 
    }
    .hover-shadow:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 8px 18px rgba(105, 108, 255, 0.12) !important;
    }
    .rounded-circle {
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- ======================================================== -->
    <!-- 1. BANNER SAMBUTAN & STATUS PROFIL KEUANGAN              -->
    <!-- ======================================================== -->
    <div class="row mb-4">
        <!-- Banner Sambutan -->
        <div class="col-12 col-lg-8 mb-4 mb-lg-0">
            <div class="card bg-label-info border-0 h-100 shadow-sm">
                <div class="d-flex align-items-end row h-100 g-0">
                    <div class="col-12 col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-info mb-1 fw-bold">Selamat Datang, {{ $user->name }}! 👨‍🏫</h5>
                            <p class="text-muted small mb-2">
                                <span class="badge bg-info text-white me-1">PENGAJAR / FASILITATOR</span>
                                {{ $user->jabatan ?? 'Tenaga Pengajar' }}
                            </p>
                            <p class="mb-4 text-dark small">
                                NIP/NIK: <span class="fw-bold">{{ $user->nip_nik ?? '-' }}</span><br>
                                Instansi: <span class="fw-bold">{{ $user->pengajar->instansi ?? $user->instansi ?? '-' }}</span>
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-info text-white shadow-sm">
                                    <i class="bx bx-user me-1"></i> Edit Profil & Berkas
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-5 text-center text-sm-end pe-sm-4 pb-3 pb-sm-0">
                        <div class="card-body pb-0 px-0 px-md-2 text-center">
                            <div class="mb-3">
                                @if($user->profile_photo)
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" 
                                         alt="Avatar" 
                                         class="rounded-circle shadow border border-3 border-white" 
                                         style="width: 105px; height: 105px; object-fit: cover;" />
                                @elseif($user->avatar)
                                    <img src="{{ $user->avatar }}" 
                                         alt="Avatar" 
                                         class="rounded-circle shadow border border-3 border-white" 
                                         style="width: 105px; height: 105px; object-fit: cover;" />
                                @else
                                    <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" 
                                         height="115" alt="Illustration" />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Profile Keuangan -->
        <div class="col-12 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-wallet text-warning me-1"></i> Profile Keuangan</h6>
                        @if($user->pengajar && $user->pengajar->nomor_rekening)
                            <span class="badge bg-label-success">Lengkap</span>
                        @else
                            <span class="badge bg-label-danger">Belum Lengkap</span>
                        @endif
                    </div>

                    <div class="bg-light p-3 rounded mb-3 border">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-muted">Bank:</span>
                            <span class="small fw-bold text-dark text-truncate ms-2" style="max-width: 170px;">{{ $user->pengajar->nama_bank ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-muted">No. Rek:</span>
                            <span class="small fw-bold text-primary">{{ $user->pengajar->nomor_rekening ?? '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="small text-muted">A/N:</span>
                            <span class="small fw-bold text-dark text-truncate ms-2" style="max-width: 170px;">{{ $user->pengajar->nama_rekening ?? '-' }}</span>
                        </div>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-warning w-100">
                        <i class="bx bx-edit-alt me-1"></i> Perbarui Rekening
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 2. SECTION CAPAIANKU (DESAIN TEMPLATE SNEAT)             -->
    <!-- ======================================================== -->
    <h5 class="fw-bold mb-3 text-dark">
        <i class="bx bx-chart me-1 text-primary"></i> Capaianku
    </h5>

    <div class="row">
        <!-- 1. Total JP -->
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-0 hover-shadow transition-all h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-primary p-2 rounded me-3">
                        <i class="bx bx-time-five text-primary h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Total JP</small>
                        <h4 class="mb-0 text-primary fw-bold">{{ $totalJp ?? 0 }} <span class="fs-6 text-muted fw-normal">JP</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. JP Tahun Ini -->
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-0 hover-shadow transition-all h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-success p-2 rounded me-3">
                        <i class="bx bx-calendar text-success h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">JP Tahun Ini</small>
                        <h4 class="mb-0 text-success fw-bold">{{ $jpTahunIni ?? 0 }} <span class="fs-6 text-muted fw-normal">JP</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Jumlah Pelatihan Diajarkan -->
        <div class="col-12 col-sm-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-0 hover-shadow transition-all h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-info p-2 rounded me-3">
                        <i class="bx bx-chalkboard text-info h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Jumlah Pelatihan Diajarkan</small>
                        <h4 class="mb-0 text-info fw-bold">{{ $totalPelatihan ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Pelatihan Diajarkan Tahun Ini -->
        <div class="col-12 col-sm-6 col-lg-6 mb-4">
            <div class="card shadow-sm border-0 hover-shadow transition-all h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-warning p-2 rounded me-3">
                        <i class="bx bx-calendar-check text-warning h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Pelatihan Diajarkan Tahun Ini</small>
                        <h4 class="mb-0 text-warning fw-bold">{{ $pelatihanTahunIni ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Persentase Capaian JP -->
        <div class="col-12 col-sm-12 col-lg-6 mb-4">
            <div class="card shadow-sm border-0 hover-shadow transition-all h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar bg-label-danger p-2 rounded me-3">
                        <i class="bx bx-tachometer text-danger h4 mb-0"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-semibold">Persentase Capaian JP</small>
                        <h4 class="mb-0 text-danger fw-bold">{{ $persentaseJp ?? 0 }}%</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection