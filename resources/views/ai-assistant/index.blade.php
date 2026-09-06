@extends('layouts.master')

@section('title', 'Asisten AI Internal')

@push('css')
<style>
    .ai-hero { background: linear-gradient(135deg, #25286f, #696cff 58%, #8c8eff); overflow: hidden; }
    .ai-search { background: rgba(255,255,255,.96); border-radius: 16px; padding: .45rem; box-shadow: 0 1rem 2.5rem rgba(20,24,80,.2); }
    .ai-search .form-control { border: 0; box-shadow: none; min-height: 48px; }
    .suggestion-chip { border-radius: 999px; white-space: normal; text-align: left; }
    .result-card { border: 1px solid #e7e7ff; transition: transform .18s ease, box-shadow .18s ease; }
    .result-card:hover { transform: translateY(-2px); box-shadow: 0 .55rem 1.35rem rgba(67,89,113,.12); }
    .result-icon { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 21px; }
    .capability { background: #f8f8ff; border-radius: 14px; padding: 1rem; height: 100%; }
</style>
@endpush

@section('content')
@php
    $role = Auth::user()->role;
    $scopeLabel = $role === 'superadmin' ? 'Semua bidang' : ($role === 'admin_aset' ? 'Jadwal dan aset' : (Auth::user()->bidang ?: 'Bidang Anda'));
    $suggestions = $role === 'admin_aset'
        ? ['Aset yang dipakai hari ini', 'Jadwal ruangan besok']
        : ['Pelatihan terbaru', 'Pengajar yang mencapai 32 JP', 'Aset yang dipakai hari ini', 'Laporan yang belum lengkap', 'Evaluasi terendah', 'Peserta berulang tahun ini'];
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card ai-hero border-0 shadow-sm mb-4">
        <div class="card-body p-4 p-lg-5 text-white position-relative">
            <div class="row align-items-center">
                <div class="col-lg-9 position-relative" style="z-index:1">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-white text-primary"><i class="bx bx-shield-quarter me-1"></i>Data internal</span>
                        <span class="badge bg-label-light">Cakupan: {{ $scopeLabel }}</span>
                    </div>
                    <h2 class="text-white mb-2">Apa yang ingin Anda temukan?</h2>
                    <p class="mb-4 opacity-75">Cari ringkasan data operasional dengan bahasa sehari-hari. Jawaban tetap mengikuti hak akses akun Anda.</p>
                    <form method="GET" action="{{ route('ai-assistant.index') }}" class="ai-search d-flex align-items-center">
                        <i class="bx bx-search fs-3 text-primary ms-2"></i>
                        <input type="search" name="q" value="{{ $question }}" maxlength="300" class="form-control px-3" placeholder="Contoh: laporan pelatihan yang belum lengkap" autofocus>
                        <button class="btn btn-primary px-3 px-sm-4" type="submit"><span class="d-none d-sm-inline">Cari</span><i class="bx bx-right-arrow-alt ms-sm-1"></i></button>
                    </form>
                </div>
                <div class="col-lg-3 d-none d-lg-block text-center"><i class="bx bx-bot" style="font-size:145px;opacity:.2"></i></div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-4">
        @foreach($suggestions as $suggestion)
            <a class="btn btn-sm btn-outline-primary suggestion-chip" href="{{ route('ai-assistant.index', ['q' => $suggestion]) }}">{{ $suggestion }}</a>
        @endforeach
    </div>

    @if($result)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between">
                    <div>
                        <small class="text-uppercase text-primary fw-bold">Ringkasan hasil</small>
                        <h4 class="mt-1 mb-2">{{ $result['answer'] }}</h4>
                        <p class="text-muted mb-0"><i class="bx bx-info-circle me-1"></i>{{ $result['caveat'] }}</p>
                    </div>
                    <small class="text-muted text-nowrap"><i class="bx bx-time-five me-1"></i>{{ $result['generated_at']->format('H:i') }} WIB</small>
                </div>
            </div>
        </div>

        <div class="row g-3">
            @forelse($result['items'] as $item)
                <div class="col-md-6 col-xl-4">
                    <a href="{{ $item['url'] }}" class="card result-card h-100 text-body text-decoration-none">
                        <div class="card-body">
                            <div class="d-flex gap-3">
                                <span class="result-icon bg-label-{{ $item['tone'] }} flex-shrink-0"><i class="bx bx-data"></i></span>
                                <div class="min-w-0">
                                    <h6 class="mb-1">{{ $item['title'] }}</h6>
                                    <small class="text-muted d-block mb-3">{!! $item['subtitle'] !!}</small>
                                    <span class="badge bg-label-{{ $item['tone'] }}">{{ $item['metric'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top d-flex justify-content-between"><small>Buka sumber data</small><i class="bx bx-right-arrow-alt"></i></div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bx bx-search-alt fs-1 text-muted"></i><h5 class="mt-3">Tidak ada data yang sesuai</h5><p class="text-muted mb-0">Coba gunakan salah satu contoh pencarian di atas.</p></div></div></div>
            @endforelse
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-1">Pencarian yang tersedia</h5>
                <p class="text-muted mb-4">Asisten membaca data melalui pencarian yang telah dibatasi, bukan menjalankan perintah database bebas.</p>
                <div class="row g-3">
                    <div class="col-md-4"><div class="capability"><i class="bx bx-calendar-check fs-3 text-primary"></i><h6 class="mt-2 mb-1">Pelaksanaan</h6><small class="text-muted">Pelatihan, jadwal pengajar, dan pemakaian aset.</small></div></div>
                    <div class="col-md-4"><div class="capability"><i class="bx bx-line-chart fs-3 text-success"></i><h6 class="mt-2 mb-1">Evaluasi</h6><small class="text-muted">Ringkasan hasil evaluasi dan data yang perlu perhatian.</small></div></div>
                    <div class="col-md-4"><div class="capability"><i class="bx bx-file fs-3 text-warning"></i><h6 class="mt-2 mb-1">Tindak lanjut</h6><small class="text-muted">Laporan belum selesai dan pemerataan alumni.</small></div></div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
