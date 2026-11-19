@extends('layouts.master')

@section('title', 'Dashboard Monitoring')

@section('content')
@php
    $coverage = $totalTrainings ? round(100 * $monitoredTrainings / $totalTrainings) : 0;
    $completion = $totalFollowUps ? round(100 * $verifiedFollowUps / $totalFollowUps) : 0;
    $metrics = [
        ['Total Pelatihan', $totalTrainings, 'bx-collection', 'primary'],
        ['Sudah Dimonitor', $monitoredTrainings, 'bx-check-square', 'success'],
        ['Belum Dimonitor', $totalTrainings - $monitoredTrainings, 'bx-time', 'secondary'],
        ['Total Tindak Lanjut', $totalFollowUps, 'bx-task', 'primary'],
        ['Selesai Terverifikasi', $verifiedFollowUps, 'bx-check-shield', 'success'],
        ['Terlambat', $overdueFollowUps, 'bx-alarm-exclamation', 'danger'],
    ];
    $statuses = [
        'open' => 'Belum Dikerjakan',
        'in_progress' => 'Sedang Dikerjakan',
        'submitted' => 'Menunggu Verifikasi',
        'rejected' => 'Perlu Revisi',
        'verified' => 'Selesai Terverifikasi',
    ];
@endphp

<div class="mb-4">
    <h4 class="fw-bold mb-1">Dashboard Monitoring</h4>
    <p class="text-muted mb-0">Cakupan monitoring pelatihan dan progres penyelesaian temuan.</p>
</div>

<div class="row g-3 mb-4">
    @foreach($metrics as [$label, $count, $icon, $tone])
        <div class="col-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <span class="badge bg-label-{{ $tone }} mb-2"><i class="bx {{ $icon }} fs-5" aria-hidden="true"></i></span>
                    <div class="h3 mb-1">{{ number_format($count, 0, ',', '.') }}</div>
                    <div class="text-muted">{{ $label }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <section class="card h-100">
            <div class="card-body">
                <h5 class="mb-4">Progres Monitoring</h5>
                @foreach([
                    ['Cakupan pelatihan', $coverage, $monitoredTrainings, $totalTrainings],
                    ['Penyelesaian tindak lanjut', $completion, $verifiedFollowUps, $totalFollowUps],
                ] as [$label, $percent, $done, $total])
                    <div class="mb-4">
                        <div class="d-flex justify-content-between gap-2 mb-2">
                            <span>{{ $label }}</span><strong>{{ $percent }}%</strong>
                        </div>
                        <div class="progress" role="progressbar" aria-label="{{ $label }}" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-primary" style="width: {{ $percent }}%"></div>
                        </div>
                        <small class="text-muted">{{ $done }} dari {{ $total }}</small>
                    </div>
                @endforeach
                <a href="{{ route('monitoring.index') }}" class="btn btn-outline-primary">Lihat Instrumen Monitoring</a>
            </div>
        </section>
    </div>
    <div class="col-lg-7">
        <section class="card h-100">
            <div class="card-body">
                <h5>Status Tindak Lanjut</h5>
                @if($totalFollowUps)
                    <ul class="list-group list-group-flush mb-3">
                        @foreach($statuses as $status => $label)
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                                <a href="{{ route('followup.index', ['status' => $status]) }}">{{ $label }}</a>
                                <span class="badge bg-label-primary rounded-pill">{{ $statusCounts->get($status, 0) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">Belum ada temuan yang memerlukan tindak lanjut.</p>
                @endif
                <a href="{{ route('followup.index') }}" class="btn btn-outline-primary">Lihat Semua Tindak Lanjut</a>
            </div>
        </section>
    </div>
</div>
@endsection
