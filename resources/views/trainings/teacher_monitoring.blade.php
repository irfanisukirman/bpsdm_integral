@extends('layouts.master')

@section('title', 'Monitoring Pengajar')

@section('content')
@php
    $selectedMonthLabel = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F');
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Monitoring Pengajar</h4>
            <p class="text-muted mb-0">Rekap menyeluruh beban mengajar dan sumber perolehan JP setiap narasumber.</p>
        </div>
        <form method="GET" class="card border-0 shadow-sm">
            <div class="card-body p-2 d-flex flex-wrap align-items-end gap-2">
                <div>
                    <label class="form-label small mb-1">Bulan</label>
                    <select name="month" class="form-select form-select-sm">
                        @foreach(range(1, 12) as $monthOption)
                            <option value="{{ $monthOption }}" @selected($month === $monthOption)>
                                {{ \Carbon\Carbon::create($year, $monthOption, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">Tahun</label>
                    <select name="year" class="form-select form-select-sm">
                        @foreach($availableYears as $yearOption)
                            <option value="{{ $yearOption }}" @selected($year === $yearOption)>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="bx bx-filter-alt me-1"></i>Terapkan</button>
                <a href="{{ route('teacher-monitoring.export', ['year' => $year, 'month' => $month]) }}" class="btn btn-success btn-sm">
                    <i class="bx bxs-spreadsheet me-1"></i>Export Data
                </a>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-4 bg-primary text-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <small class="text-white-50 text-uppercase fw-bold">Cakupan Monitoring</small>
                    <h4 class="text-white fw-bold mb-1 mt-1">{{ $scopeLabel }}</h4>
                    <p class="mb-0 text-white-50">Menghitung seluruh jadwal dan pelatihan dalam cakupan ini.</p>
                </div>
                <div class="text-md-end">
                    <span class="badge bg-white text-primary px-3 py-2">Periode {{ $selectedMonthLabel }} {{ $year }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['icon' => 'bx-chalkboard', 'color' => 'primary', 'label' => 'Pengajar Terlibat', 'value' => $summary['teachers'], 'suffix' => 'orang'],
            ['icon' => 'bx-calendar', 'color' => 'warning', 'label' => 'Unit '.$selectedMonthLabel, 'value' => $summary['month_units'], 'suffix' => 'JP/OJ'],
            ['icon' => 'bx-calendar-check', 'color' => 'success', 'label' => 'Unit Tahun '.$year, 'value' => $summary['year_units'], 'suffix' => 'JP/OJ'],
            ['icon' => 'bx-history', 'color' => 'info', 'label' => 'Seluruh Riwayat', 'value' => $summary['lifetime_units'], 'suffix' => 'JP/OJ'],
        ] as $stat)
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3 p-md-4">
                        <span class="avatar-initial rounded bg-label-{{ $stat['color'] }} p-2 d-inline-flex mb-3"><i class="bx {{ $stat['icon'] }} fs-4"></i></span>
                        <small class="text-muted d-block">{{ $stat['label'] }}</small>
                        <h3 class="fw-bold mb-0">{{ number_format($stat['value'], 0, ',', '.') }} <small class="fs-6 text-muted">{{ $stat['suffix'] }}</small></h3>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="alert alert-info border-0 d-flex gap-2 mb-4">
        <i class="bx bx-info-circle fs-5 flex-shrink-0"></i>
        <div class="small">Total unit menggabungkan jumlah JP dan OJ (contoh: 1 JP + 1 OJ = 2 unit). Rincian JP dan OJ tetap dipisahkan karena durasi waktunya berbeda.</div>
    </div>
    @if($summary['reached_32'] > 0)
        <div class="alert alert-warning border-0 shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
            <div><i class="bx bx-error-circle fs-5 me-2"></i><strong>{{ $summary['reached_32'] }} pengajar telah mencapai atau melebihi 32 unit JP/OJ pada tahun {{ $year }}.</strong><div class="small mt-1">Periksa pemerataan beban mengajar serta rincian jumlah JP dan OJ masing-masing pengajar.</div></div>
            <span class="badge bg-warning text-dark fs-6">&ge; 32 JP/OJ</span>
        </div>
    @endif

    @forelse($teacherMonitoring as $item)
        @php
            $teacher = $item['teacher'];
            $accordionId = 'teacher-monitoring-'.$teacher->id;
        @endphp
        <div class="card border-0 shadow-sm mb-4 teacher-card">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-xl-row justify-content-between gap-4">
                    <div class="d-flex align-items-start gap-3 teacher-identity">
                        <span class="avatar avatar-lg flex-shrink-0">
                            <span class="avatar-initial rounded-circle bg-label-primary fs-4">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($teacher->name, 0, 1)) }}</span>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $teacher->name }}</h5>
                            @if($item['year_units'] >= 32)<span class="badge bg-danger mb-2"><i class="bx bx-error me-1"></i>Beban {{ $item['year_units'] }} unit (&ge;32 JP/OJ)</span>@endif
                            <div class="text-muted small">{{ $teacher->jabatan ?: 'Jabatan belum diisi' }}</div>
                            <div class="text-muted small"><i class="bx bx-buildings me-1"></i>{{ $teacher->instansi ?: $teacher->pengajar?->instansi ?: 'Instansi belum diisi' }}</div>
                        </div>
                    </div>
                    <div class="teacher-stat-grid flex-grow-1">
                        <div class="teacher-stat bg-label-warning"><small>{{ $selectedMonthLabel }}</small><strong>{{ $item['month_units'] }} JP/OJ</strong><span>{{ $item['month_jp'] }} JP | {{ $item['month_oj'] }} OJ &middot; {{ $item['month_sessions'] }} sesi</span></div>
                        <div class="teacher-stat {{ $item['year_units'] >= 32 ? 'bg-label-danger' : 'bg-label-success' }}"><small>Tahun {{ $year }}</small><strong>{{ $item['year_units'] }} JP/OJ</strong><span>{{ $item['year_jp'] }} JP | {{ $item['year_oj'] }} OJ &middot; {{ $item['year_trainings'] }} pelatihan</span></div>
                        <div class="teacher-stat bg-label-info"><small>Seluruh Riwayat</small><strong>{{ $item['lifetime_units'] }} JP/OJ</strong><span>{{ $item['lifetime_jp'] }} JP | {{ $item['lifetime_oj'] }} OJ &middot; {{ $item['lifetime_trainings'] }} pelatihan</span></div>
                    </div>
                </div>

                <button class="btn btn-outline-primary btn-sm mt-4" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $accordionId }}" aria-expanded="false">
                    <i class="bx bx-list-ul me-1"></i>Lihat Sumber JP Tahun {{ $year }}
                </button>
            </div>

            <div class="collapse" id="{{ $accordionId }}">
                <div class="card-body border-top bg-light p-3 p-md-4">
                    <h6 class="fw-bold mb-3">Pelatihan yang Diajar Tahun {{ $year }}</h6>
                    <div class="table-responsive border rounded bg-white">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 250px">Pelatihan</th>
                                    <th style="min-width: 150px">Tanggal Mengajar</th>
                                    <th>Sesi</th>
                                    <th>Unit {{ $selectedMonthLabel }}</th>
                                    <th>Total Unit {{ $year }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item['training_breakdown'] as $breakdown)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold d-block">{{ $breakdown['training']?->nama_pelatihan ?: 'Pelatihan telah dihapus' }}</span>
                                            <small class="text-muted">{{ $breakdown['training']?->bidang ?: '-' }}</small>
                                            <details class="session-details mt-2">
                                                <summary class="text-primary small fw-semibold">Lihat {{ $breakdown['sessions'] }} sesi mengajar</summary>
                                                <div class="mt-2 d-flex flex-column gap-2">
                                                    @foreach($breakdown['details'] as $detail)
                                                        <div class="border rounded p-2 bg-white small">
                                                            <strong>{{ \Carbon\Carbon::parse($detail->date)->translatedFormat('d M Y') }}</strong>
                                                            &middot; {{ substr($detail->start_time, 0, 5) }}&ndash;{{ substr($detail->end_time, 0, 5) }}
                                                            <span class="badge bg-label-info ms-1">{{ $detail->duration_label }}</span>
                                                            <span class="d-block text-muted mt-1">{{ $detail->activity }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($breakdown['first_date'])->translatedFormat('d M') }}
                                            @if($breakdown['last_date'] !== $breakdown['first_date'])
                                                &ndash; {{ \Carbon\Carbon::parse($breakdown['last_date'])->translatedFormat('d M Y') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($breakdown['first_date'])->format('Y') }}
                                            @endif
                                        </td>
                                        <td>{{ $breakdown['sessions'] }}</td>
                                        <td><strong class="text-warning">{{ $breakdown['month_units'] }} JP/OJ</strong><small class="d-block text-muted">{{ $breakdown['month_jp'] }} JP | {{ $breakdown['month_oj'] }} OJ</small></td>
                                        <td><strong class="text-success">{{ $breakdown['units'] }} JP/OJ</strong><small class="d-block text-muted">{{ $breakdown['jp'] }} JP | {{ $breakdown['oj'] }} OJ</small></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-5 text-muted"><i class="bx bx-calendar-x fs-1 d-block mb-2"></i>Belum ada jadwal mengajar pada tahun {{ $year }}.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bx bx-user-x display-5 text-muted d-block mb-3"></i>
                <h5 class="fw-bold">Belum ada data pengajar</h5>
                <p class="text-muted mb-0">Belum ada pengajar yang memiliki jadwal dalam cakupan monitoring Anda.</p>
            </div>
        </div>
    @endforelse
</div>

<style>
    .teacher-identity { min-width: 260px; }
    .teacher-stat-grid { display: grid; grid-template-columns: repeat(3, minmax(140px, 1fr)); gap: .75rem; }
    .teacher-stat { border-radius: .65rem; padding: .8rem 1rem; display: flex; flex-direction: column; }
    .teacher-stat small { color: #697a8d; }
    .teacher-stat strong { color: #233446; font-size: 1.25rem; }
    .teacher-stat span { color: #8592a3; font-size: .75rem; }
    .teacher-card { transition: transform .2s ease, box-shadow .2s ease; }
    .teacher-card:hover { transform: translateY(-2px); }
    .session-details summary { cursor: pointer; }
    @media (max-width: 1199.98px) { .teacher-stat-grid { grid-template-columns: repeat(2, minmax(130px, 1fr)); } }
    @media (max-width: 575.98px) { .teacher-stat-grid { grid-template-columns: 1fr 1fr; } .teacher-stat { padding: .7rem; } }
</style>
@endsection
