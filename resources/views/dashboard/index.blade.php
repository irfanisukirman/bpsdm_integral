@extends('layouts.master')

@section('title', 'Dashboard Monitoring & Evaluasi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <!-- WELCOME & KPI -->
        <div class="col-lg-8 mb-4 order-0">
            <div class="card bg-primary text-white">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-white">Halo, {{ Auth::user()->name }}! 🎉</h5>
                            <p class="mb-4">
                                Saat ini terdapat <span class="fw-bold">{{ $stats['total_training'] }}</span> pelatihan terpantau di bidang Anda. 
                                Pantau dampak pelatihan secara real-time melalui sistem Kirkpatrick.
                            </p>
                            <a href="{{ route('trainings.index') }}" class="btn btn-sm btn-outline-white border-white text-white">Lihat Detail Pelatihan</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" height="140" alt="View Badge User" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 order-1">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <span class="fw-semibold d-block mb-1 text-muted">Total Peserta</span>
                            <h3 class="card-title mb-2">{{ $stats['total_participants'] }}</h3>
                            <small class="text-success fw-semibold"><i class="bx bx-user me-1"></i>Orang</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <span class="fw-semibold d-block mb-1 text-muted">Kehadiran</span>
                            @php 
                                $attRate = $stats['total_attendance_logs'] > 0 ? round(($stats['avg_attendance'] / $stats['total_attendance_logs']) * 100) : 0;
                            @endphp
                            <h3 class="card-title mb-2">{{ $attRate }}%</h3>
                            <small class="text-info fw-semibold"><i class="bx bx-check-double me-1"></i>Rata-rata</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- RADAR CHART: KIRKPATRICK 360° -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-title mb-0">
                        <h5 class="m-0 me-2">Kirkpatrick Impact</h5>
                        <small class="text-muted">Skor Performa L1 - L4</small>
                    </div>
                </div>
                <div class="card-body mt-3">
                    <canvas id="radarKirkpatrick" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- BAR CHART: LEVEL 2 LEARNING -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h5 class="card-title mb-0">Level 2: Learning</h5>
                    <small class="text-muted">Perbandingan Pre-test vs Post-test</small>
                </div>
                <div class="card-body mt-3">
                    <canvas id="barLearning" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- PIE CHART: MONITORING COMPLIANCE -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h5 class="card-title mb-0">Kepatuhan Monitoring</h5>
                    <small class="text-muted">Indikator YA vs TIDAK</small>
                </div>
                <div class="card-body mt-3 d-flex flex-column align-items-center">
                    <canvas id="pieMonitoring" height="200"></canvas>
                    <div class="mt-4 w-100">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Indikator Terpenuhi (YA)</small>
                            <small class="fw-bold">{{ $monYa }}</small>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small>Butuh Tindak Lanjut (TIDAK)</small>
                            <small class="fw-bold text-danger">{{ $monTidak }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- PELATIHAN TERBARU & STATUS -->
        <div class="col-md-12">
            <div class="card h-100">
                <h5 class="card-header">Daftar Pelatihan Berjalan & Terbaru</h5>
                <div class="table-responsive">
                    <table class="table table-hover border-top">
                        <thead>
                            <tr>
                                <th>Pelatihan</th>
                                <th>Metode</th>
                                <th>Sedang Berlangsung</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestTrainings as $lt)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $lt->nama_pelatihan }}</span><br>
                                    <small class="text-muted">Angkatan {{ $lt->angkatan }}</small>
                                </td>
                                <td><span class="badge bg-label-info">{{ $lt->metode }}</span></td>
                                <td>
                                    @php $nowAct = $lt->current_activity; @endphp
                                    @if($nowAct)
                                        <small class="text-success fw-bold"><i class="bx bx-play me-1"></i> {{ $nowAct->activity }}</small>
                                    @else
                                        <small class="text-muted">Tidak ada jadwal saat ini</small>
                                    @endif
                                </td>
                                <td>
                                    @if($lt->sisa_hari < 0)
                                        <span class="badge bg-label-danger">Selesai</span>
                                    @else
                                        <span class="badge bg-label-success">Aktif ({{ $lt->sisa_hari }} Hari lagi)</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Radar Chart Kirkpatrick
    const ctxRadar = document.getElementById('radarKirkpatrick').getContext('2d');
    new Chart(ctxRadar, {
        type: 'radar',
        data: {
            labels: ['L1: Reaksi', 'L3: Perilaku', 'L4: Hasil'],
            datasets: [{
                label: 'Skor Agregat',
                data: [{{ round($avgL1) }}, {{ round($avgL3) }}, {{ round($avgL4) }}],
                backgroundColor: 'rgba(105, 108, 255, 0.2)',
                borderColor: '#696cff',
                pointBackgroundColor: '#696cff',
            }]
        },
        options: {
            scales: { r: { beginAtZero: true, max: 100 } },
            plugins: { legend: { display: false } }
        }
    });

    // 2. Bar Chart Learning
    const ctxBar = document.getElementById('barLearning').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Pre-Test', 'Post-Test'],
            datasets: [{
                label: 'Nilai',
                data: [{{ round($avgL2Pre) }}, {{ round($avgL2Post) }}],
                backgroundColor: ['#ffab00', '#71dd37'],
                borderRadius: 5
            }]
        },
        options: {
            scales: { y: { beginAtZero: true, max: 100 } },
            plugins: { legend: { display: false } }
        }
    });

    // 3. Pie Chart Monitoring
    const ctxPie = document.getElementById('pieMonitoring').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['YA', 'TIDAK'],
            datasets: [{
                data: [{{ $monYa }}, {{ $monTidak }}],
                backgroundColor: ['#71dd37', '#ff3e1d'],
                hoverOffset: 4
            }]
        },
        options: {
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
</script>
@endpush