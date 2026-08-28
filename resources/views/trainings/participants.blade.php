@extends('layouts.master')

@section('title', 'Manajemen Peserta')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header dengan Tombol Aksi Terkelompok -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Pelatihan /</span> Peserta: {{ $training->nama_pelatihan }}
                </h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <!-- FORM PENCARIAN PESERTA -->
                <form action="{{ route('trainings.participants', $training->id) }}" method="GET" style="min-width: 250px;">
                    <div class="input-group input-group-merge shadow-sm">
                        <span class="input-group-text" id="basic-addon-search31"><i class="bx bx-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama / NIP..."
                            aria-label="Search..." aria-describedby="basic-addon-search31" value="{{ $search ?? '' }}">
                        @if ($search)
                            <a href="{{ route('trainings.participants', $training->id) }}"
                                class="btn btn-outline-secondary px-2">
                                <i class="bx bx-x"></i>
                            </a>
                        @endif
                    </div>
                </form>
                <a href="{{ route('trainings.manage', $training->id) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
                    <i class="bx bx-plus me-1"></i> Tambah
                </button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
                    <i class="bx bx-file me-1"></i> Import
                </button>
            </div>
        </div>

        <!-- Alert Notifikasi -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible border-0 shadow-sm mb-4 animate__animated animate__fadeIn" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bx bx-check-circle me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex">
                    <i class="bx bx-error-circle me-2"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Tabel Peserta Modern -->
        <div class="card shadow-sm border-0">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
                <h5 class="card-title mb-0 text-primary fw-bold">Daftar Peserta Pelatihan</h5>
                <div class="d-flex align-items-center">
                    @if(isset($search) && $search)
                        <small class="text-muted me-3 text-uppercase">Hasil Pencarian: <strong>"{{ $search }}"</strong></small>
                    @endif
                    <span class="badge bg-label-primary">{{ $participants->total() }} ORANG</span>
                </div>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover" style="table-layout: fixed; width: 100%; min-width: 1250px;">
                    <thead>
                        <tr class="text-nowrap bg-light">
                            <th style="width: 170px;" class="fw-bold">NIP / NIK</th>
                            <th style="width: 220px;" class="fw-bold">NAMA LENGKAP</th>
                            <th style="width: 130px;" class="fw-bold">GENDER/STATUS</th>
                            <th style="width: 180px;" class="fw-bold">JABATAN</th>
                            <th style="width: 220px;" class="fw-bold">INSTANSI & WILAYAH</th>
                            <th style="width: 120px;" class="fw-bold text-center">KONTAK</th>
                            <th style="width: 110px;" class="fw-bold text-center">STATUS</th>
                            <th style="width: 130px;" class="text-center fw-bold">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($participants as $p)
                            <tr class="participant-row">
                                <!-- Kolom NIP -->
                                <td class="align-top">
                                    <div class="d-flex align-items-center mt-1">
                                        <code class="fw-bold text-danger me-2" style="font-size: 0.8rem;">{{ $p->nip_nik }}</code>
                                        <button class="btn btn-xs btn-icon btn-outline-secondary border-0" 
                                                onclick="copyToClipboard('{{ $p->nip_nik }}', this)" 
                                                title="Salin NIP">
                                            <i class="bx bx-copy"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Kolom Nama & Avatar (Prioritas Foto Profile User) -->
                                <td class="align-top text-wrap">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xs me-2">
                                            @if($p->user && $p->user->profile_photo)
                                                <img src="{{ asset('storage/' . $p->user->profile_photo) }}" class="rounded-circle" style="object-fit: cover">
                                            @elseif($p->user && $p->user->avatar)
                                                <img src="{{ $p->user->avatar }}" class="rounded-circle">
                                            @else
                                                <span class="avatar-initial rounded-circle bg-label-primary" style="font-size: 10px;">
                                                    {{ substr($p->name, 0, 1) }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="fw-bold text-dark" style="font-size: 0.85rem;">{{ strtoupper($p->user->name ?? $p->name) }}</span>
                                    </div>
                                </td>

                                <!-- Kolom Gender & Status -->
                                <td class="align-top">
                                    <small class="d-block mb-1 text-muted">
                                        <i class="bx {{ ($p->user->gender ?? $p->gender) == 'Laki-Laki' ? 'bx-male-sign text-info' : 'bx-female-sign text-danger' }} me-1"></i>
                                        {{ $p->user->gender ?? $p->gender }}
                                    </small>
                                    @php
                                        $currentStatus = strtoupper($p->user->status_kepegawaian ?? $p->status_kepegawaian ?? 'NON-ASN');
                                        $statusColor = 'bg-label-secondary';
                                        if(str_contains($currentStatus, 'PNS')) $statusColor = 'bg-label-success';
                                        elseif(str_contains($currentStatus, 'PPPK')) $statusColor = 'bg-label-warning';
                                        elseif(str_contains($currentStatus, 'ASN')) $statusColor = 'bg-label-primary';
                                    @endphp
                                    <span class="badge {{ $statusColor }} btn-xs fw-bold" style="font-size: 0.6rem;">
                                        {{ $currentStatus }}
                                    </span>
                                </td>
                                
                                <!-- Kolom Jabatan -->
                                <td class="align-top text-wrap">
                                    <div style="line-height: 1.3; font-size: 0.8rem; white-space: normal;" class="text-dark">
                                        {{ $p->user->jabatan ?? $p->jabatan }}
                                    </div>
                                </td>

                                <!-- Kolom Instansi & Wilayah -->
                                <td class="align-top text-wrap">
                                    <div class="fw-bold text-dark mb-1" style="font-size: 0.8rem; white-space: normal;">
                                        {{ $p->user->instansi ?? $p->instansi }}
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        <i class="bx bx-map-pin me-1" style="font-size: 9px;"></i>
                                        {{ $p->user->kota ?? $p->kabupaten_kota }}, {{ $p->user->provinsi ?? $p->provinsi }}
                                    </small>
                                </td>

                                <!-- Kolom Kontak (WA) -->
                                <td class="align-top text-center">
                                    @php $phone = $p->user->whatsapp ?? $p->phone; @endphp
                                    @if($phone)
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $phone) }}" 
                                        target="_blank" 
                                        class="btn btn-xs btn-outline-success rounded-pill px-3 shadow-xs">
                                            <i class="bx bxl-whatsapp me-1"></i> WhatsApp
                                        </a>
                                    @else
                                        <span class="text-light small italic">N/A</span>
                                    @endif
                                </td>

                                <!-- Kolom Status Approval -->
                                <td class="align-top text-center">
                                    @if($p->registration_status == 'pending')
                                        <span class="badge bg-label-warning animate__animated animate__flash animate__infinite">Pending</span>
                                    @elseif($p->registration_status == 'approved')
                                        <span class="badge bg-label-success shadow-none">Approved</span>
                                    @else
                                        <span class="badge bg-label-danger">Rejected</span>
                                    @endif
                                </td>

                                <!-- Kolom Aksi -->
                                <td class="align-top text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if($p->registration_status == 'pending')
                                            <form action="{{ route('participants.approve', $p->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <button type="submit" class="btn btn-xs btn-icon btn-success shadow-xs" title="Setujui">
                                                    <i class="bx bx-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('participants.reject', $p->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <button type="submit" class="btn btn-xs btn-icon btn-danger shadow-xs" title="Tolak">
                                                    <i class="bx bx-x"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('participants.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus peserta ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-label-danger border-0" title="Hapus">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <img src="{{ asset('assets/img/illustrations/empty-box.png') }}" width="100" class="mb-3 opacity-50">
                                <p class="text-muted fw-light">Tidak ada data peserta yang ditemukan.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Modern -->
            <div class="card-footer border-top bg-light py-3">
                <div class="d-flex justify-content-center">
                    {{ $participants->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Include Modals (Add & Import) -->
    {{-- @include('trainings.partials.modal_add_participant') --}}
    {{-- @include('trainings.partials.modal_import_participant') --}}

@endsection

@push('css')
    <style>
        .table-responsive {
            overflow-x: auto !important;
            scrollbar-width: thin;
        }
        .table-responsive::-webkit-scrollbar { height: 8px; }
        .table-responsive::-webkit-scrollbar-thumb { background: #d9dee3; border-radius: 10px; }
        
        .participant-row { transition: all 0.2s ease; }
        .participant-row:hover { background-color: #f8f9ff !important; }
        
        .text-wrap { white-space: normal !important; word-wrap: break-word; }
        .shadow-xs { box-shadow: 0 2px 4px rgba(0,0,0,0.08); }
        
        /* Sneat Label Danger Style */
        .btn-label-danger { background: #ffebee; color: #ff3e1d; }
        .btn-label-danger:hover { background: #ff3e1d; color: #fff; }
    </style>
@endpush

@push('js')
<script>
    /**
     * Fungsi Copy NIP ke Clipboard
     */
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="bx bx-check text-success"></i>';
            setTimeout(() => {
                btn.innerHTML = originalIcon;
            }, 2000);
        });
    }
</script>
@endpush