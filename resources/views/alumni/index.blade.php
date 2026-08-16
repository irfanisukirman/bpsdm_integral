@extends('layouts.master')

@section('title', 'Statistik Alumni')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">INTEGRAL /</span> Analitik Data Alumni
        </h4>
        <div class="d-flex gap-2">
            <a href="{{ route('alumni.export') }}" class="btn btn-success shadow-sm">
                <i class="bx bxs-file-export me-1"></i> Export Statistik Excel
            </a>
            <span class="badge bg-primary">Total: {{ $totalAlumni }} Alumni</span>
        </div>
    </div>

    <div class="row">
        <!-- 1. GENDER & 3T (Pie & Donut) -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0">Komposisi Gender</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartGender" height="250"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0">Wilayah 3T vs Non-3T</h5>
                    <small class="text-muted">Ref: Kota Cimahi</small>
                </div>
                <div class="card-body">
                    <canvas id="chart3T" height="250"></canvas>
                    <div class="mt-3 text-center small text-muted">
                        Berdasarkan domisili peserta terhadap indeks wilayah terpencil.
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. STATUS KEPEGAWAIAN -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title m-0">Status Kepegawaian</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartStatus" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- 3. SEBARAN WILAYAH (PROVINSI) -->
        <div class="col-md-12 col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-label-primary py-3">
                    <h5 class="card-title mb-0">Sebaran Alumni Seluruh Indonesia</h5>
                </div>
                <div class="card-body mt-3">
                    <canvas id="chartProvinsi" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- 4. DATA PENDIDIKAN (BAR) -->
        <div class="col-md-12 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title m-0">Tingkat Pendidikan Terakhir</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartEdu" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Konfigurasi Warna
    const colors = ['#696cff', '#03c3ec', '#71dd37', '#ffab00', '#ff3e1d', '#233446'];

    // 1. Chart Gender
    new Chart(document.getElementById('chartGender'), {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($genderStats)) !!},
            datasets: [{
                data: {!! json_encode(array_values($genderStats)) !!},
                backgroundColor: ['#696cff', '#ff3e1d']
            }]
        }
    });

    // 2. Chart 3T
    new Chart(document.getElementById('chart3T'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($stats3T)) !!},
            datasets: [{
                data: {!! json_encode(array_values($stats3T)) !!},
                backgroundColor: ['#ffab00', '#71dd37']
            }]
        },
        options: { cutout: '70%' }
    });

    // 3. Chart Provinsi
    new Chart(document.getElementById('chartProvinsi'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($provinsiStats->keys()) !!},
            datasets: [{
                label: 'Jumlah Alumni',
                data: {!! json_encode($provinsiStats->values()) !!},
                backgroundColor: '#03c3ec'
            }]
        },
        options: { indexAxis: 'y' }
    });

    // 4. Chart Pendidikan
    new Chart(document.getElementById('chartEdu'), {
        type: 'polarArea',
        data: {
            labels: {!! json_encode($eduStats->pluck('edu_current')) !!},
            datasets: [{
                data: {!! json_encode($eduStats->pluck('total')) !!},
                backgroundColor: colors
            }]
        }
    });

    // 5. Chart Status
    new Chart(document.getElementById('chartStatus'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($statusStats->keys()) !!},
            datasets: [{
                label: 'Peserta',
                data: {!! json_encode($statusStats->values()) !!},
                backgroundColor: '#696cff'
            }]
        }
    });
</script>
@endpush
@endsection