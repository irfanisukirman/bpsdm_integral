@extends('layouts.master')

@section('content')
<div class="row">
    <!-- Card Statistik Dasar -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Total Pelatihan</span>
                <h3 class="card-title mb-2 text-primary">{{ $totalTraining }}</h3>
                <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> Aktif</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <span class="fw-semibold d-block mb-1">Total Peserta</span>
                <h3 class="card-title mb-2 text-info">{{ $totalParticipant }}</h3>
                <small class="text-muted">Dari seluruh angkatan</small>
            </div>
        </div>
    </div>

    <!-- Grafik Level 2: Learning (Pretest vs Postest) -->
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0 me-2">Level 2: Learning</h5>
            </div>
            <div class="card-body">
                <canvas id="chartL2" style="max-height: 250px;"></canvas>
                <div class="mt-3 text-center">
                    <small class="text-muted">Peningkatan Kompetensi (Pre vs Post)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Level 1, 3, 4: Impact Score -->
    <div class="col-md-12 col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title m-0">Ringkasan Dampak Kirkpatrick (Skala 10-100)</h5>
            </div>
            <div class="card-body">
                <canvas id="chartImpact" style="max-height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Chart Level 2 (Bar Chart)
    const ctxL2 = document.getElementById('chartL2').getContext('2d');
    new Chart(ctxL2, {
        type: 'bar',
        data: {
            labels: ['Pre-Test', 'Post-Test'],
            datasets: [{
                label: 'Rata-rata Nilai',
                data: [{{ $avgPre }}, {{ $avgPost }}],
                backgroundColor: ['#ffab00', '#71dd37'],
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, max: 100 } }
        }
    });

    // 2. Chart Impact Level 1, 3, 4 (Radar atau Polar Area)
    const ctxImpact = document.getElementById('chartImpact').getContext('2d');
    new Chart(ctxImpact, {
        type: 'line',
        data: {
            labels: ['Level 1 (Reaksi)', 'Level 3 (Perilaku)', 'Level 4 (Hasil/Dampak)'],
            datasets: [{
                label: 'Skor Kepuasan & Dampak',
                data: [{{ $avgL1 }}, {{ $avgL34 }}, {{ $avgL34 - 5 }}], // Contoh Level 4 sedikit di bawah L3
                borderColor: '#696cff',
                backgroundColor: 'rgba(105, 108, 255, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            scales: { y: { beginAtZero: true, max: 100 } }
        }
    });
</script>
@endpush