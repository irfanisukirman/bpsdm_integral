@extends('layouts.master')

@section('title', 'Daftar Pelatihan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Peserta /</span> Daftar Pelatihan
        </h4>

        <!-- KOLOM SEARCH MODERN -->
        <div class="col-md-4">
            <form action="{{ route('participant.trainings') }}" method="GET">
                <div class="input-group input-group-merge shadow-sm">
                    <span class="input-group-text" id="basic-addon-search31"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari Pelatihan atau Bidang..." 
                           value="{{ $search ?? '' }}" aria-label="Search..." aria-describedby="basic-addon-search31">
                    @if($search)
                        <a href="{{ route('participant.trainings') }}" class="btn btn-outline-secondary px-2">
                            <i class="bx bx-x"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($search)
        <div class="mb-4">
            <small class="text-muted">Menampilkan hasil pencarian untuk: <span class="fw-bold">"{{ $search }}"</span></small>
        </div>
    @endif

    <div class="row">
        @forelse($trainings as $t)
            @php
                $isFinished = \Carbon\Carbon::parse($t->tgl_selesai)->isPast();
                $percent = ($t->jumlah_peserta > 0) ? ($t->participants_count / $t->jumlah_peserta) * 100 : 0;
            @endphp
            
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0 transition-all {{ $isFinished ? 'opacity-75 bg-light' : 'hover-shadow' }}">
                    
                    <div class="card-header pb-2">
                        <span class="badge bg-label-primary text-wrap w-100 p-2" 
                              style="font-size: 11px; line-height: 1.5; text-align: left; display: block; min-height: 40px;">
                            <i class="bx bx-buildings me-1"></i> {{ $t->bidang }}
                        </span>
                    </div>

                    <div class="card-body pt-2">
                        <h5 class="card-title fw-bold {{ $isFinished ? 'text-muted' : 'text-dark' }} mb-3">
                            {{ $t->nama_pelatihan }}
                        </h5>
                        
                        <div class="d-flex justify-content-between mb-3 bg-white p-2 rounded border">
                            <div class="text-center flex-fill border-end border-2">
                                <small class="text-muted d-block small">Durasi</small>
                                <span class="fw-bold text-primary">{{ $t->jp }} JP</span>
                            </div>
                            <div class="text-center flex-fill">
                                <small class="text-muted d-block small">Angkatan</small>
                                <span class="fw-bold text-dark">{{ $t->angkatan }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block mb-1"><i class="bx bx-calendar me-1"></i> Pelaksanaan:</small>
                            <span class="fw-semibold small {{ $isFinished ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                {{ \Carbon\Carbon::parse($t->tgl_mulai)->translatedFormat('d M') }} - 
                                {{ \Carbon\Carbon::parse($t->tgl_selesai)->translatedFormat('d M Y') }}
                            </span>
                        </div>

                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-bold">Pendaftar</small>
                                <small class="fw-bold">{{ $t->participants_count }} / {{ $t->jumlah_peserta }}</small>
                            </div>
                            
                            @php
                                $barColor = $isFinished ? 'bg-secondary' : ($percent >= 100 ? 'bg-danger' : ($percent >= 80 ? 'bg-warning' : 'bg-success'));
                            @endphp
                            
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped {{ $barColor }}" 
                                     role="progressbar" 
                                     style="width: {{ $percent }}%" 
                                     aria-valuenow="{{ $percent }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>

                            @if($isFinished)
                                <small class="text-danger mt-1 d-block fw-bold"><i class="bx bx-calendar-x me-1"></i> PELATIHAN SELESAI</small>
                            @elseif($percent >= 100)
                                <small class="text-danger mt-1 d-block fw-bold"><i class="bx bx-error-circle me-1"></i>Kuota Penuh</small>
                            @else
                                <small class="text-muted mt-1 d-block italic">Tersisa {{ $t->jumlah_peserta - $t->participants_count }} slot</small>
                            @endif
                        </div>
                    </div>

                    <div class="card-footer border-top bg-transparent pt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge {{ $t->model == 'blended' ? 'bg-label-warning' : 'bg-label-info' }}">
                                {{ strtoupper($t->model) }}
                            </span>

                            @if($isFinished)
                                <button class="btn btn-secondary btn-sm px-4 opacity-50" disabled>Tutup</button>
                            @else
                                <button class="btn btn-primary btn-sm px-4 shadow-sm" {{ $percent >= 100 ? 'disabled' : '' }}>
                                    Lihat Detail
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bx bx-search-alt display-1 text-light"></i>
                <h5 class="text-muted mt-3">Tidak ditemukan pelatihan dengan kata kunci "{{ $search }}"</h5>
                <a href="{{ route('participant.trainings') }}" class="btn btn-primary btn-sm mt-2">Lihat Semua Pelatihan</a>
            </div>
        @endforelse
    </div>
</div>

<style>
    .transition-all { transition: all 0.3s ease; }
    .hover-shadow:hover { 
        transform: translateY(-8px); 
        box-shadow: 0 15px 30px rgba(105, 108, 255, 0.15) !important;
        border-color: #696cff !important;
    }
    .card-title {
        min-height: 50px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection