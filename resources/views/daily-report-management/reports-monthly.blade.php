@extends('layouts.master')

@section('title', 'Monitoring Laporan Harian')

@section('content')
<div class="container-xxl container-p-y">
    @if(session('error'))<div class="alert alert-danger"><i class="bx bx-error-circle me-1"></i>{{ session('error') }}</div>@endif
    <div class="monitor-hero mb-4">
        <div>
            <small>MONITORING KASUBAG</small>
            <h3 class="text-white fw-bold mb-1">Laporan Harian PPPK-PW</h3>
            <p class="text-white-50 mb-0">Buka periode bulanan, pilih pegawai, lalu lihat seluruh kegiatan hariannya.</p>
        </div>
        @if(auth()->user()->role === 'superadmin')
            <a href="{{ route('daily-report-management.index') }}" class="btn btn-light text-primary">Kelola Pegawai</a>
        @endif
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tahun laporan</label>
                    <select name="year" class="form-select">
                        @foreach($availableYears->sortDesc() as $availableYear)
                            <option value="{{ $availableYear }}" @selected($year === $availableYear)>{{ $availableYear }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bx bx-filter-alt me-1"></i>Tampilkan</button></div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h5 class="fw-bold mb-1">Periode Tahun {{ $year }}</h5><small class="text-muted">{{ $months->count() }} bulan memiliki laporan</small></div>
    </div>

    <div class="row g-4">
        @forelse($months as $monthKey => $month)
            @php
                $activityCount = $month['reports']->sum(fn($report) => $report->items->count());
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="month-summary-card h-100 shadow-sm">
                    <div class="month-icon"><i class="bx bx-calendar"></i></div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">{{ $month['label'] }}</h5>
                        <p class="text-muted small mb-3">Ringkasan laporan PPPK-PW periode ini.</p>
                        <div class="month-stats mb-3"><span><strong>{{ $month['employees']->count() }}</strong> Pegawai</span><span><strong>{{ $month['reports']->count() }}</strong> Hari</span><span><strong>{{ $activityCount }}</strong> Kegiatan</span></div>
                        <div class="d-grid gap-2"><a href="{{ route('daily-report-management.month', $monthKey) }}" class="btn btn-primary"><i class="bx bx-show me-1"></i>Lihat Laporan Bulan Ini</a><a href="{{ route('daily-report-management.monthly-zip', $monthKey) }}" class="btn btn-outline-danger"><i class="bx bx-download me-1"></i>Unduh ZIP Semua Pegawai</a></div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bx bx-calendar-x fs-1 text-muted"></i><h5 class="mt-3">Belum ada laporan pada tahun {{ $year }}</h5><p class="text-muted mb-0">Laporan yang disimpan pegawai akan otomatis muncul di sini.</p></div></div></div>
        @endforelse
    </div>
</div>
@endsection

@push('css')
<style>
.monitor-hero{display:flex;align-items:center;justify-content:space-between;gap:2rem;padding:2rem;border-radius:20px;background:linear-gradient(120deg,#25478e,#617ee9)}
.month-summary-card{display:flex;gap:16px;padding:20px;border:1px solid #e3e8f2;border-radius:16px;background:#fff;transition:.2s}.month-summary-card:hover{transform:translateY(-3px);box-shadow:0 14px 30px rgba(35,63,125,.12)!important}.month-stats{display:flex;gap:8px;flex-wrap:wrap}.month-stats span{padding:7px 9px;border-radius:9px;background:#f1f5ff;color:#66728a;font-size:11px}.month-stats strong{color:#294c9c}
.month-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:13px;background:#e8efff;color:#4169d8;font-size:24px;flex:0 0 auto}
.employee-row{background:#fff}.employee-avatar{width:42px;height:42px;display:grid;place-items:center;border-radius:50%;background:#eef3ff;color:#4169d8;font-size:21px}.employee-detail{background:#f7f9fc}
.timeline-report{position:relative;padding-left:8px}.report-day{display:grid;grid-template-columns:58px 1fr;gap:14px;padding:14px 0;border-bottom:1px solid #e3e8f0}.report-day:last-child{border-bottom:0}.report-date{width:52px;height:56px;border-radius:12px;background:#4169d8;color:#fff;text-align:center;padding-top:5px}.report-date span{display:block;font-size:20px;font-weight:700;line-height:25px}.report-date small{text-transform:uppercase;font-size:10px}.report-content{min-width:0;background:#fff;border:1px solid #e4e9f2;border-radius:12px;padding:14px}
@media(max-width:600px){.monitor-hero{align-items:flex-start;flex-direction:column}.employee-row .text-end{display:none}.report-day{grid-template-columns:1fr}.report-date{width:100%;height:auto;padding:7px 10px;text-align:left}.report-date span,.report-date small{display:inline;font-size:13px}}
</style>
@endpush
