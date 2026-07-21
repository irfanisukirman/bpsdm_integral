@extends('layouts.master')

@section('title', 'Isi Instrumen Monitoring')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Monitoring /</span> Isi Instrumen
        </h4>
        <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- Informasi Pelatihan -->
    <div class="card mb-4 shadow-none border">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8 border-end">
                    <h6 class="mb-1 text-muted small text-uppercase">Nama Pelatihan:</h6>
                    <h5 class="fw-bold text-primary mb-3">{{ $training->nama_pelatihan }}</h5>
                    <div class="d-flex gap-3">
                        <div>
                            <small class="text-muted d-block">Model:</small>
                            <span class="badge bg-label-warning text-uppercase">{{ $training->model }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Metode Utama:</small>
                            <span class="badge bg-label-primary text-uppercase">{{ $training->metode }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 ps-md-4 mt-3 mt-md-0">
                    <small class="text-muted d-block text-uppercase small">Instansi Penyelenggara:</small>
                    <span class="fw-bold text-dark">{{ $training->bidang }}</span>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible border-0 shadow-sm mb-4" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Form Monitoring -->
    <div class="nav-align-top mb-4">
        @if($training->model == 'blended')
            <!-- TAB HEADER (Hanya untuk Blended) -->
            <ul class="nav nav-tabs" role="tablist">
                @foreach($training->stages as $index => $st)
                    @php
                        // Cek apakah ada record di monitoring_results untuk stage ini
                        // Kita gunakan (int) agar tipe datanya sama
                        $isFilled = $training->monitoringResults->where('training_stage_id', (int)$st->id)->count() > 0;
                    @endphp
                    <li class="nav-item">
                        <button type="button" 
                            class="nav-link {{ $index == 0 ? 'active' : '' }} {{ $isFilled ? 'text-success fw-bold' : '' }}" 
                            data-bs-toggle="tab" 
                            data-bs-target="#navs-stage-{{ $st->id }}">
                            
                            @if($isFilled)
                                <i class="bx bxs-check-circle me-1"></i> {{-- Centang Hijau --}}
                            @else
                                <i class="bx bx-circle me-1 opacity-50"></i> {{-- Lingkaran Kosong --}}
                            @endif
                            
                            {{ strtoupper($st->nama_tahapan) }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <!-- TAB CONTENT -->
            <div class="tab-content border-0 p-0 pt-4 bg-transparent">
                @foreach($training->stages as $index => $st)
                <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="navs-stage-{{ $st->id }}" role="tabpanel">
                    <form action="{{ route('monitoring.store', $training->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="stage_id" value="{{ $st->id }}">
                        
                        @include('monitoring.partials.instrument_loop', [
                            'questions' => $questionsByStage[$st->id], 
                            'stage_id' => $st->id,
                            'stage_name' => $st->nama_tahapan
                        ])
                    </form>
                </div>
                @endforeach
            </div>
        @else
            <!-- TAMPILAN STANDAR (Tanpa Tab) -->
            <form action="{{ route('monitoring.store', $training->id) }}" method="POST">
                @csrf
                <input type="hidden" name="stage_id" value="std">
                @include('monitoring.partials.instrument_loop', [
                    'questions' => $questionsByStage['standar'], 
                    'stage_id' => 'std',
                    'stage_name' => 'Pelatihan'
                ])
            </form>
        @endif
    </div>
</div>

{{-- CSS Custom untuk menandai Tab yang sudah diisi --}}
<style>
    .nav-tabs .nav-link.tab-filled {
        color: #71dd37 !important; /* Hijau Sneat */
        font-weight: 700;
    }
    .nav-tabs .nav-link.active.tab-filled {
        border-bottom: 3px solid #71dd37 !important;
    }
    .nav-tabs .nav-item .tab-filled:not(.active) {
        background-color: #eafbea;
        border-radius: 5px 5px 0 0;
    }
    .question-card:hover {
        border-color: #696cff !important;
        box-shadow: 0 2px 8px rgba(105, 108, 255, 0.05);
    }
</style>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Toggle box tindak lanjut saat pilihan YA/TIDAK berubah
        $(document).on('change', '.select-ans', function() {
            let targetId = $(this).data('target');
            if($(this).val() === 'tidak') {
                $(`#${targetId}`).fadeIn();
            } else {
                $(`#${targetId}`).fadeOut();
                // Opsional: kosongkan input saat dikembalikan ke YA
                $(`#${targetId} select`).val('');
                $(`#${targetId} input`).val('');
            }
        });
    });
</script>
@endpush