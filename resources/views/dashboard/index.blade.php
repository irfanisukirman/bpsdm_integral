@extends('layouts.master')

@section('title', 'Dashboard Admin')

@push('css')
<style>
    .dashboard-hero { background: linear-gradient(135deg, #696cff 0%, #4f52d9 55%, #2f3192 100%); overflow: hidden; }
    .metric-card { border: 0; box-shadow: 0 .25rem 1rem rgba(67, 89, 113, .08); transition: transform .2s ease, box-shadow .2s ease; }
    .metric-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1.4rem rgba(67, 89, 113, .13); }
    .metric-icon { width: 46px; height: 46px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 24px; }
    .chart-box { position: relative; min-height: 285px; }
    .training-name { max-width: 300px; white-space: normal; }
    .empty-chart { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #8592a3; pointer-events: none; }
    .dashboard-ai-search { max-width: 680px; padding: .35rem; border-radius: 14px; background: rgba(255,255,255,.96); }
    .dashboard-ai-search .form-control { border: 0; box-shadow: none; min-height: 43px; }
</style>
@endpush

@section('content')
@php
    $scopeLabel = Auth::user()->role === 'superadmin' ? 'Semua bidang' : (Auth::user()->bidang ?: 'Bidang belum ditentukan');
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card dashboard-hero border-0 shadow-sm mb-4">
        <div class="card-body p-4 p-lg-5 text-white">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-white text-primary mb-3">{{ $scopeLabel }}</span>
                    <h3 class="text-white mb-2">Selamat datang, {{ Auth::user()->name }}</h3>
                    <p class="mb-4 opacity-75">
                        Ringkasan pelaksanaan, peserta, kehadiran, evaluasi, dan dokumen diperbarui dari data sistem.
                    </p>
                    <form action="{{ route('ai-assistant.index') }}" method="GET" class="dashboard-ai-search d-flex align-items-center mb-3">
                        <i class="bx bx-bot fs-4 text-primary ms-2"></i>
                        <input type="search" name="q" maxlength="300" class="form-control px-2" placeholder="Tanyakan data pelatihan, evaluasi, laporan, pengajar, atau aset...">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-search me-sm-1"></i><span class="d-none d-sm-inline">Cari</span></button>
                    </form>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('trainings.index') }}" class="btn btn-light text-primary">
                            <i class="bx bx-calendar-event me-1"></i>Kelola Pelatihan
                        </a>
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-light">
                            <i class="bx bx-folder-open me-1"></i>Manajemen Dokumen
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 d-none d-lg-block text-end">
                    <i class="bx bx-bar-chart-alt-2" style="font-size: 130px; opacity: .18;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100"><div class="card-body">
                <div class="d-flex justify-content-between">
                    <div><small class="text-muted">Total Pelatihan</small><h3 class="mb-1 mt-1">{{ number_format($stats['total_training']) }}</h3><small>{{ $stats['ongoing_training'] }} sedang berlangsung</small></div>
                    <span class="metric-icon bg-label-primary"><i class="bx bx-book-open"></i></span>
                </div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100"><div class="card-body">
                <div class="d-flex justify-content-between">
                    <div><small class="text-muted">Total Peserta</small><h3 class="mb-1 mt-1">{{ number_format($stats['total_participants']) }}</h3><small>{{ $stats['approved_participants'] }} disetujui</small></div>
                    <span class="metric-icon bg-label-success"><i class="bx bx-group"></i></span>
                </div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100"><div class="card-body">
                <div class="d-flex justify-content-between">
                    <div><small class="text-muted">Tingkat Kehadiran</small><h3 class="mb-1 mt-1">{{ number_format($stats['attendance_rate'], 1) }}%</h3><small>{{ $stats['attendance_present'] }} dari {{ $stats['attendance_total'] }} catatan</small></div>
                    <span class="metric-icon bg-label-info"><i class="bx bx-check-double"></i></span>
                </div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card metric-card h-100"><div class="card-body">
                <div class="d-flex justify-content-between">
                    <div><small class="text-muted">Total Jam Pelajaran</small><h3 class="mb-1 mt-1">{{ number_format($stats['total_jp']) }} <small class="fs-6">JP</small></h3><small>Dari seluruh jadwal</small></div>
                    <span class="metric-icon bg-label-warning"><i class="bx bx-time-five"></i></span>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><h5 class="mb-1">Tren Pelatihan {{ $year }}</h5><small class="text-muted">Berdasarkan tanggal mulai pelatihan</small></div>
                    <span class="badge bg-label-primary">{{ $stats['total_training'] }} total</span>
                </div>
                <div class="card-body chart-box"><canvas id="trainingTrend"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header"><h5 class="mb-1">Status Pelatihan</h5><small class="text-muted">Posisi per hari ini</small></div>
                <div class="card-body chart-box"><canvas id="trainingStatus"></canvas></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header"><h5 class="mb-1">Evaluasi Kirkpatrick</h5><small class="text-muted">Rata-rata skor L1, L3, dan L4</small></div>
                <div class="card-body chart-box"><canvas id="radarKirkpatrick"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header"><h5 class="mb-1">Level 2: Pembelajaran</h5><small class="text-muted">Rata-rata pre-test dan post-test</small></div>
                <div class="card-body chart-box"><canvas id="barLearning"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header d-flex justify-content-between"><div><h5 class="mb-1">Kepatuhan Monitoring</h5><small class="text-muted">Jawaban indikator monitoring</small></div><span class="badge bg-label-success">{{ number_format($monitoringRate, 1) }}%</span></div>
                <div class="card-body chart-box"><canvas id="pieMonitoring"></canvas></div>
                <div class="card-footer border-top">
                    <div class="d-flex justify-content-between"><span><i class="bx bxs-circle text-success me-1"></i>Terpenuhi</span><strong>{{ $monYa }}</strong></div>
                    <div class="d-flex justify-content-between mt-2"><span><i class="bx bxs-circle text-danger me-1"></i>Perlu tindak lanjut</span><strong>{{ $monTidak }}</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><h5 class="mb-1">Pelatihan Terbaru</h5><small class="text-muted">Enam pelatihan berdasarkan tanggal mulai</small></div>
                    <a href="{{ route('trainings.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Pelatihan</th><th>Pelaksanaan</th><th>Peserta / Sesi</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @forelse($latestTrainings as $training)
                            @php
                                $start = \Carbon\Carbon::parse($training->tgl_mulai);
                                $end = \Carbon\Carbon::parse($training->tgl_selesai);
                                $now = \Carbon\Carbon::today('Asia/Jakarta');
                                $status = $start->gt($now) ? 'Akan datang' : ($end->lt($now) ? 'Selesai' : 'Berlangsung');
                                $statusClass = $status === 'Berlangsung' ? 'success' : ($status === 'Akan datang' ? 'warning' : 'secondary');
                            @endphp
                            <tr>
                                <td class="training-name"><strong>{{ $training->nama_pelatihan }}</strong><br><small class="text-muted">{{ $training->bidang }} · Angkatan {{ $training->angkatan }}</small></td>
                                <td><small>{{ $start->translatedFormat('d M Y') }}<br>{{ $end->translatedFormat('d M Y') }}</small></td>
                                <td><span class="badge bg-label-primary">{{ $training->participants_count }} peserta</span><br><small class="text-muted">{{ $training->schedules_count }} sesi</small></td>
                                <td><span class="badge bg-label-{{ $statusClass }}">{{ $status }}</span></td>
                                <td><a href="{{ route('trainings.manage', $training->id) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Kelola"><i class="bx bx-right-arrow-alt"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5"><i class="bx bx-calendar-x d-block fs-1 mb-2"></i>Belum ada pelatihan pada lingkup ini.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header"><h5 class="mb-1">Perlu Perhatian</h5><small class="text-muted">Ringkasan pekerjaan admin</small></div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <span class="metric-icon bg-label-warning me-3"><i class="bx bx-user-check"></i></span>
                        <div class="flex-grow-1"><small class="text-muted">Pendaftaran menunggu</small><h5 class="mb-0">{{ $stats['pending_participants'] }} peserta</h5></div>
                    </div>
                    <div class="d-flex align-items-center mb-4">
                        <span class="metric-icon bg-label-info me-3"><i class="bx bx-calendar-plus"></i></span>
                        <div class="flex-grow-1"><small class="text-muted">Pelatihan akan datang</small><h5 class="mb-0">{{ $stats['upcoming_training'] }} pelatihan</h5></div>
                    </div>
                    <div class="d-flex align-items-center mb-4">
                        <span class="metric-icon bg-label-secondary me-3"><i class="bx bx-check-circle"></i></span>
                        <div class="flex-grow-1"><small class="text-muted">Pelatihan selesai</small><h5 class="mb-0">{{ $stats['completed_training'] }} pelatihan</h5></div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="metric-icon bg-label-primary me-3"><i class="bx bx-file"></i></span>
                        <div class="flex-grow-1"><small class="text-muted">Dokumen dalam lingkup akses</small><h5 class="mb-0">{{ $stats['total_documents'] }} file</h5></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = 'Public Sans, sans-serif';
    Chart.defaults.color = '#697a8d';
    const commonOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } };

    new Chart(document.getElementById('trainingTrend'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [{ label: 'Pelatihan', data: @json($monthlyTrainings), borderColor: '#696cff', backgroundColor: 'rgba(105,108,255,.12)', fill: true, tension: .35, pointRadius: 4 }]
        },
        options: { ...commonOptions, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } }, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('trainingStatus'), {
        type: 'doughnut',
        data: { labels: ['Berlangsung', 'Akan datang', 'Selesai'], datasets: [{ data: [{{ $stats['ongoing_training'] }}, {{ $stats['upcoming_training'] }}, {{ $stats['completed_training'] }}], backgroundColor: ['#71dd37', '#ffab00', '#8592a3'], borderWidth: 0 }] },
        options: { ...commonOptions, cutout: '68%' }
    });

    new Chart(document.getElementById('radarKirkpatrick'), {
        type: 'radar',
        data: { labels: ['L1 Reaksi', 'L3 Perilaku', 'L4 Hasil'], datasets: [{ data: [{{ round($avgL1, 1) }}, {{ round($avgL3, 1) }}, {{ round($avgL4, 1) }}], backgroundColor: 'rgba(105,108,255,.16)', borderColor: '#696cff', pointBackgroundColor: '#696cff' }] },
        options: { ...commonOptions, scales: { r: { beginAtZero: true, max: 100 } }, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('barLearning'), {
        type: 'bar',
        data: { labels: ['Pre-test', 'Post-test'], datasets: [{ data: [{{ round($avgL2Pre, 1) }}, {{ round($avgL2Post, 1) }}], backgroundColor: ['#ffab00', '#71dd37'], borderRadius: 7 }] },
        options: { ...commonOptions, scales: { y: { beginAtZero: true, max: 100 }, x: { grid: { display: false } } }, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('pieMonitoring'), {
        type: 'doughnut',
        data: { labels: ['Terpenuhi', 'Perlu tindak lanjut'], datasets: [{ data: [{{ $monYa }}, {{ $monTidak }}], backgroundColor: ['#71dd37', '#ff3e1d'], borderWidth: 0 }] },
        options: { ...commonOptions, cutout: '70%' }
    });
});
</script>
@endpush
