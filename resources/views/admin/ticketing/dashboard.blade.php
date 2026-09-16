@extends('layouts.master')

@section('title', 'Dashboard Ticketing')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Manajemen Layanan /</span> Dashboard</h4>
        <p class="text-muted small mb-0">Ringkasan tiket Hotline semua status.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('ticketing.index') }}" class="btn btn-outline-primary btn-sm shadow-sm"><i class="bx bx-list-ul me-1"></i>Semua Tiket</a>
        <a href="{{ route('ticketing.export.excel') }}" class="btn btn-outline-success btn-sm shadow-sm"><i class="bx bx-file me-1"></i>Excel</a>
        <a href="{{ route('ticketing.export.pdf') }}" class="btn btn-outline-danger btn-sm shadow-sm"><i class="bx bxs-file-pdf me-1"></i>PDF</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-primary p-3"><i class="bx bx-ticket fs-3"></i></div>
                <div><div class="text-muted small">Total Tiket</div><div class="fs-4 fw-bold">{{ $stats['total'] }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-primary p-3"><i class="bx bx-plus-circle fs-3"></i></div>
                <div><div class="text-muted small">Tiket Baru</div><div class="fs-4 fw-bold text-primary">{{ $stats['baru'] }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-info p-3"><i class="bx bx-loader-circle fs-3"></i></div>
                <div><div class="text-muted small">Diproses</div><div class="fs-4 fw-bold text-info">{{ $stats['diproses'] }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-warning p-3"><i class="bx bx-hourglass fs-3"></i></div>
                <div><div class="text-muted small">Menunggu Respons Pengguna</div><div class="fs-4 fw-bold text-warning">{{ $stats['menunggu'] }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-success p-3"><i class="bx bx-check-circle fs-3"></i></div>
                <div><div class="text-muted small">Resolved</div><div class="fs-4 fw-bold text-success">{{ $stats['resolved'] }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-secondary p-3"><i class="bx bx-x-circle fs-3"></i></div>
                <div><div class="text-muted small">Closed</div><div class="fs-4 fw-bold text-secondary">{{ $stats['closed'] }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 border-start border-warning border-2">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-warning p-3"><i class="bx bx-time-five fs-3"></i></div>
                <div><div class="text-muted small">Mendekati SLA</div><div class="fs-4 fw-bold text-warning">{{ $stats['sla_approaching'] }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card shadow-sm border-0 border-start border-danger border-2">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-label-danger p-3"><i class="bx bx-error-circle fs-3"></i></div>
                <div><div class="text-muted small">Melewati SLA</div><div class="fs-4 fw-bold text-danger">{{ $stats['sla_overdue'] }}</div></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-7 col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bx bx-line-chart me-2 text-primary"></i>Tren Tiket per Bulan ({{ now()->year }})</h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-5 col-lg-6">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bx bx-pie-chart-alt-2 me-2 text-primary"></i>Distribusi per Kategori</h6>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="120"></canvas>
            </div>
        </div>
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bx bx-buildings me-2 text-primary"></i>Distribusi per Bidang</h6>
            </div>
            <div class="card-body">
                <canvas id="bidangChart" height="120"></canvas>
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->role === 'superadmin')
<div class="row g-3 mb-4">
    @foreach($bidangData as $nama => $count)
    <div class="col-md-4 col-lg-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small text-truncate">{{ $nama }}</div>
                <div class="fs-4 fw-bold">{{ $count }} tiket</div>
                <a href="{{ route('ticketing.index', ['bidang' => $nama]) }}" class="btn btn-sm btn-outline-primary mt-2">Lihat Tiket</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: { labels, datasets: [{ label: 'Jumlah Tiket', data: @json($monthlyData), borderColor: '#696cff', backgroundColor: 'rgba(105,108,255,.15)', fill: true, tension: .35 }] }
});
const cats = @json($categoryData);
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: { labels: Object.keys(cats).map(c => c.replace(/-/g,' ')), datasets: [{ data: Object.values(cats), backgroundColor: ['#696cff','#03c3ec','#ffab00','#71dd37','#ff3e1d'] }] }
});
const bidangs = @json($bidangData);
new Chart(document.getElementById('bidangChart'), {
    type: 'bar',
    data: { labels: Object.keys(bidangs).map(b => b.length > 25 ? b.substring(0,24)+'â€¦' : b), datasets: [{ label: 'Tiket', data: Object.values(bidangs), backgroundColor: '#03c3ec' }] },
    options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
</script>
@endpush