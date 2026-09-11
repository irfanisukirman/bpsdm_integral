@extends('layouts.master')
@section('title', 'Asisten AI Integral')

@push('css')
<style>
.integral-search-page{min-height:calc(100vh - 150px);display:flex;flex-direction:column;align-items:center;justify-content:{{ $result ? 'flex-start' : 'center' }};padding:{{ $result ? '2.25rem 1rem 3rem' : '2rem 1rem 8rem' }}}
.integral-search-shell{width:min(100%,760px);text-align:center}.integral-ai-logo{display:inline-flex;align-items:center;justify-content:center;gap:1rem;margin-bottom:1.7rem;text-decoration:none}.integral-ai-logo img{width:76px;height:76px;object-fit:contain;filter:drop-shadow(0 9px 18px rgba(105,108,255,.22))}.integral-ai-name{text-align:left;line-height:.88}.integral-ai-name strong{display:block;color:#566a7f;font-size:clamp(2.4rem,7vw,4rem);font-weight:800;letter-spacing:-.065em}.integral-ai-name span{display:block;margin-top:.7rem;padding-left:.15rem;color:#696cff;font-size:.78rem;font-weight:700;letter-spacing:.42em;text-transform:uppercase}
.integral-search-form{display:flex;align-items:center;width:100%;min-height:60px;padding:.35rem .45rem .35rem 1.15rem;background:#fff;border:1px solid #dfe3e7;border-radius:999px;box-shadow:0 3px 12px rgba(67,89,113,.08);transition:.2s}.integral-search-form:hover,.integral-search-form:focus-within{border-color:transparent;box-shadow:0 7px 24px rgba(67,89,113,.17)}.integral-search-form>i{color:#8592a3;font-size:1.55rem}.integral-search-form .form-control{min-width:0;min-height:48px;padding-inline:.9rem;border:0;box-shadow:none;background:transparent;font-size:1rem}.integral-search-submit{width:48px;height:48px;flex:0 0 48px;display:inline-grid;place-items:center;padding:0;border:0;border-radius:50%;color:#fff;background:#696cff;font-size:1.35rem;transition:.18s}.integral-search-submit:hover{color:#fff;background:#5659db;transform:scale(1.04)}
.integral-results{width:min(100%,980px);margin-top:2.5rem}.result-summary{border:0;border-radius:18px;box-shadow:0 .3rem 1.2rem rgba(67,89,113,.09)}.result-card{border:1px solid #e7e7f4;border-radius:16px;transition:.18s}.result-card:hover{transform:translateY(-2px);box-shadow:0 .55rem 1.35rem rgba(67,89,113,.12)}.result-icon{width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 42px;border-radius:12px;font-size:21px}
@media(max-width:575.98px){.integral-search-page{min-height:calc(100vh - 125px);padding-inline:.25rem}.integral-ai-logo img{width:58px;height:58px}.integral-ai-name span{margin-top:.55rem;font-size:.65rem}.integral-search-form{min-height:56px}.integral-search-submit{width:44px;height:44px;flex-basis:44px}}
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1">
<main class="integral-search-page">
    <div class="integral-search-shell">
        <a href="{{ route('ai-assistant.index') }}" class="integral-ai-logo" aria-label="Integral AI">
            <img src="{{ asset('assets/img/favicon/inte.png') }}" alt="Logo Integral">
            <span class="integral-ai-name"><strong>INTEGRAL</strong><span>AI Assistant</span></span>
        </a>
        <form method="GET" action="{{ route('ai-assistant.index') }}" class="integral-search-form">
            <i class="bx bx-search" aria-hidden="true"></i>
            <input type="search" name="q" value="{{ $question }}" maxlength="300" class="form-control" placeholder="Telusuri data di Integral..." aria-label="Cari data Integral" autocomplete="off" autofocus>
            <button class="integral-search-submit" type="submit" aria-label="Cari"><i class="bx bx-right-arrow-alt"></i></button>
        </form>
    </div>

    @if($result)
    <section class="integral-results">
        <div class="card result-summary mb-4"><div class="card-body p-4">
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between">
                <div><small class="text-primary fw-bold text-uppercase">Hasil pencarian</small><h4 class="mt-1 mb-2">{{ $result['answer'] }}</h4><p class="text-muted mb-0">{{ $result['caveat'] }}</p></div>
                <small class="text-muted text-nowrap"><i class="bx bx-time-five me-1"></i>{{ $result['generated_at']->format('H:i') }} WIB</small>
            </div>
        </div></div>
        <div class="row g-3">
        @forelse($result['items'] as $item)
            <div class="col-md-6 col-xl-4">
                <a href="{{ $item['url'] }}" class="card result-card h-100 text-body text-decoration-none">
                    <div class="card-body"><div class="d-flex gap-3">
                        <span class="result-icon bg-label-{{ $item['tone'] }}"><i class="bx bx-data"></i></span>
                        <div class="min-w-0"><h6 class="mb-1">{{ $item['title'] }}</h6><small class="text-muted d-block mb-3">{!! $item['subtitle'] !!}</small><span class="badge bg-label-{{ $item['tone'] }}">{{ $item['metric'] }}</span></div>
                    </div></div>
                    <div class="card-footer bg-transparent border-top d-flex justify-content-between"><small>Buka sumber data</small><i class="bx bx-right-arrow-alt"></i></div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center py-5"><i class="bx bx-search-alt fs-1 text-muted"></i><h5 class="mt-3">Data tidak ditemukan</h5><p class="text-muted mb-0">Coba gunakan kata pencarian yang berbeda.</p></div>
        @endforelse
        </div>
    </section>
    @endif
</main>
</div>
@endsection