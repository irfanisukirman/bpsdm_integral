@extends('layouts.master')

@php
    $title = $downloadsOnly ? 'Download Laporan Monitoring' : 'Instrumen Monitoring';
    $listUrl = route($downloadsOnly ? 'monitoring.downloads' : 'monitoring.index');
    $hasFilters = $search !== '' || in_array($status, ['filled', 'unfilled'], true);
@endphp

@section('title', $title)

@push('css')
<style>
    .monitoring-training-title {
        overflow-wrap: anywhere;
    }

    .monitoring-training-meta {
        color: var(--integral-muted);
    }

    .monitoring-training-meta i {
        color: var(--integral-brand-ink);
    }
</style>
@endpush

@section('content')
<section class="training-hero rounded-4 p-4 p-md-5 mb-4">
    <h1 class="h3 fw-bold mb-2">{{ $title }}</h1>
    <p class="mb-0">{{ $downloadsOnly
        ? 'Pilih pelatihan untuk mengunduh laporan monitoring, laporan tindak lanjut, atau ceklis.'
        : 'Cari pelatihan untuk mengisi instrumen atau melihat hasil monitoring yang sudah tersimpan.' }}</p>
</section>

<nav class="d-flex flex-wrap gap-2 mb-4" aria-label="Navigasi monitoring">
    <a href="{{ route('monitoring.index') }}" class="btn {{ !$downloadsOnly ? 'btn-primary' : 'btn-outline-primary' }}" @if(!$downloadsOnly) aria-current="page" @endif>Instrumen Monitoring</a>
    <a href="{{ route('monitoring.downloads') }}" class="btn {{ $downloadsOnly ? 'btn-primary' : 'btn-outline-primary' }}" @if($downloadsOnly) aria-current="page" @endif>Download Laporan</a>
    <a href="{{ route('monitoring.dashboard') }}" class="btn btn-outline-secondary">Dashboard Monitoring</a>
</nav>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ $listUrl }}" class="row g-3 align-items-end">
            <div class="col-md">
                <label for="monitoring-search" class="form-label">Cari pelatihan</label>
                <input type="search" id="monitoring-search" name="q" class="form-control" value="{{ $search }}" placeholder="Nama pelatihan, bidang, atau angkatan">
            </div>
            <div class="col-md-4 col-lg-3">
                <label for="monitoring-status" class="form-label">Status instrumen</label>
                <select id="monitoring-status" name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="filled" @selected($status === 'filled')>Sudah diisi</option>
                    <option value="unfilled" @selected($status === 'unfilled')>Belum diisi</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bx bx-search me-1" aria-hidden="true"></i>Cari</button>
                @if($hasFilters)
                    <a href="{{ $listUrl }}" class="btn btn-outline-secondary">Hapus Filter</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h2 class="h5 mb-0">Daftar Pelatihan</h2>
    <span class="text-muted">
        @if($trainings->count())
            {{ $trainings->firstItem() }}–{{ $trainings->lastItem() }} dari {{ $trainings->total() }} pelatihan
        @else
            {{ $trainings->total() }} pelatihan
        @endif
    </span>
</div>

<div class="row g-3">
    @forelse($trainings as $training)
        <div class="col-12 col-xl-6">
            <article class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                        <h3 class="h5 mb-0 monitoring-training-title">{{ $training->nama_pelatihan }}</h3>
                        <span class="badge {{ $training->monitoring_results_exists ? 'bg-label-success' : 'bg-label-secondary' }}">
                            {{ $training->monitoring_results_exists ? 'Sudah Diisi' : 'Belum Diisi' }}
                        </span>
                    </div>
                    <div class="monitoring-training-meta d-grid gap-2 mb-4">
                        <span><i class="bx bx-buildings me-2" aria-hidden="true"></i>{{ $training->bidang ?: 'Bidang belum tercatat' }}</span>
                        <div class="d-flex flex-wrap gap-3">
                            @if(filled($training->tgl_mulai))
                                <span><i class="bx bx-calendar me-2" aria-hidden="true"></i>{{ \Carbon\Carbon::parse($training->tgl_mulai)->locale('id')->translatedFormat('j M Y') }}</span>
                            @endif
                            @if(filled($training->angkatan))
                                <span><i class="bx bx-group me-2" aria-hidden="true"></i>Angkatan {{ $training->angkatan }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-auto">
                        @if($downloadsOnly)
                            <a href="{{ route('monitoring.download-options', $training) }}" class="btn btn-primary">
                                <i class="bx bx-download me-1" aria-hidden="true"></i>Pilih Dokumen
                            </a>
                        @else
                            <a href="{{ route('monitoring.fill', $training) }}" class="btn btn-primary">
                                <i class="bx bx-edit-alt me-1" aria-hidden="true"></i>{{ $training->monitoring_results_exists ? 'Buka Instrumen' : 'Isi Instrumen' }}
                            </a>
                            <a href="{{ route('followup.index', ['training_id' => $training->id]) }}" class="btn btn-outline-secondary">Tindak Lanjut</a>
                            <a href="{{ route('monitoring.download-options', $training) }}" class="btn btn-outline-secondary">Laporan</a>
                        @endif
                    </div>
                </div>
            </article>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <h3 class="h5">{{ $hasFilters ? 'Pelatihan tidak ditemukan' : 'Belum ada pelatihan di halaman ini' }}</h3>
                    @if($hasFilters || $trainings->currentPage() > 1)
                        <a href="{{ $listUrl }}" class="btn btn-outline-primary mt-2">Lihat Semua Pelatihan</a>
                    @endif
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($trainings->hasPages())
    <div class="mt-4">{{ $trainings->links() }}</div>
@endif
@endsection
