@extends('layouts.master')
@section('title','Monitoring Jadwal Harian')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Monitoring Jadwal Harian</h4>
            <p class="text-muted mb-0">Seluruh jadwal lintas bidang dan aset yang digunakan pada satu hari.</p>
        </div>
        <form method="GET" action="{{ route('daily-schedule.index') }}" class="d-flex gap-2 align-items-end">
            <div><label class="form-label small fw-bold mb-1">Tanggal monitoring</label><input type="date" name="date" value="{{ $date }}" class="form-control" onchange="this.form.submit()"></div>
            @if($date !== now()->toDateString())<a href="{{ route('daily-schedule.index') }}" class="btn btn-outline-primary">Hari Ini</a>@endif
        </form>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['icon'=>'bx-calendar-event','color'=>'primary','label'=>'Total Kegiatan','value'=>$stats['total']],
            ['icon'=>'bx-book-open','color'=>'info','label'=>'Sesi Pelatihan','value'=>$stats['trainings']],
            ['icon'=>'bx-calendar-star','color'=>'success','label'=>'Agenda Lainnya','value'=>$stats['agendas']],
            ['icon'=>'bx-cube','color'=>'warning','label'=>'Aset Terpakai','value'=>$stats['assets']],
        ] as $stat)
            <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body">
                <span class="avatar-initial rounded bg-label-{{ $stat['color'] }} p-2 d-inline-flex mb-2"><i class="bx {{ $stat['icon'] }} fs-4"></i></span>
                <small class="d-block text-muted">{{ $stat['label'] }}</small><h3 class="fw-bold mb-0">{{ $stat['value'] }}</h3>
            </div></div></div>
        @endforeach
    </div>

    @if($stats['unassigned'] > 0)
        <div class="alert alert-warning border-0 shadow-sm"><i class="bx bx-error-circle me-2"></i><strong>{{ $stats['unassigned'] }} kegiatan belum memiliki lokasi atau aset.</strong> Koordinasikan dengan bidang penyelenggara sebelum kegiatan dimulai.</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div><h5 class="fw-bold mb-1">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</h5><small class="text-muted">Urut berdasarkan waktu mulai kegiatan.</small></div>
            <div class="d-flex gap-2"><span class="badge bg-label-primary">Pelatihan</span><span class="badge bg-label-success">Agenda</span></div>
        </div>
        <div class="card-body p-0">
            @forelse($schedules as $item)
                @php
                    $isRunning=$isToday && $currentTime >= $item['start'] && $currentTime <= $item['end'];
                    $isPast=$isToday && $currentTime > $item['end'];
                    $typeColor=$item['type']==='Istirahat'?'warning':($item['type']==='Pelatihan'?'primary':'success');
                @endphp
                <div class="schedule-row p-3 p-md-4 border-bottom {{ $isRunning ? 'running' : '' }}">
                    <div class="row g-3 align-items-start">
                        <div class="col-md-2 col-xl-1">
                            <div class="time-box text-center rounded p-2">
                                <strong class="d-block">{{ $item['start'] }}</strong><small class="text-muted">{{ $item['end'] }}</small>
                            </div>
                        </div>
                        <div class="col-md-5 col-xl-4">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge bg-label-{{ $typeColor }}">{{ $item['type'] }}</span>
                                @if($isRunning)<span class="badge bg-danger pulse">Sedang Berlangsung</span>
                                @elseif($isPast)<span class="badge bg-label-secondary">Selesai</span>
                                @elseif($isToday)<span class="badge bg-label-info">Akan Datang</span>@endif
                            </div>
                            <h6 class="fw-bold mb-1">{{ $item['title'] }}</h6>
                            <p class="small text-muted mb-1">{{ $item['parent'] }}</p>
                            <span class="badge bg-label-secondary"><i class="bx bx-buildings me-1"></i>{{ $item['bidang'] }}</span>
                        </div>
                        <div class="col-md-5 col-xl-3">
                            <small class="text-muted d-block mb-1">Penanggung Jawab / Pengajar</small>
                            <div class="fw-semibold mb-2"><i class="bx bx-user me-1 text-primary"></i>{{ $item['pic'] }}</div>
                            <small class="text-muted"><i class="bx bx-group me-1"></i>{{ is_numeric($item['participants']) ? $item['participants'].' peserta' : $item['participants'] }}</small>
                        </div>
                        <div class="col-xl-3">
                            <small class="text-muted d-block mb-2">Lokasi & Aset Digunakan</small>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($item['assets'] as $asset)
                                    <span class="badge bg-label-warning text-start" title="{{ $asset->location }}"><i class="bx bx-cube me-1"></i>{{ $asset->name }}</span>
                                @endforeach
                                @if($item['assets']->isEmpty() && $item['requested_assets']->isNotEmpty())
                                    @foreach($item['requested_assets'] as $asset)
                                        <span class="badge bg-label-info text-start" title="{{ $asset->location }}"><i class="bx bx-time-five me-1"></i>{{ $asset->name }} (diajukan)</span>
                                    @endforeach
                                    <span class="badge bg-label-{{ ['pending'=>'warning','revision'=>'info','rejected'=>'danger','approved'=>'success'][$item['loan_status']] ?? 'secondary' }}">{{ ['pending'=>'Menunggu Persetujuan','revision'=>'Perlu Perbaikan','rejected'=>'Ditolak','approved'=>'Disetujui'][$item['loan_status']] ?? ucfirst($item['loan_status'] ?? '') }}</span>
                                @endif
                                @if($item['place'])<span class="badge bg-label-info text-start"><i class="bx bx-map me-1"></i>{{ $item['place'] }}</span>@endif
                                @if($item['zoom'])<span class="badge bg-label-success"><i class="bx bx-video me-1"></i>Virtual</span>@endif
                                @if($item['assets']->isEmpty() && $item['requested_assets']->isEmpty() && !$item['place'] && !$item['zoom'])<span class="badge bg-label-danger"><i class="bx bx-error me-1"></i>Belum ditentukan</span>@endif
                            </div>
                        </div>
                        <div class="col-xl-1 text-xl-end">
                            @if($item['manage_url'])<a href="{{ $item['manage_url'] }}" class="btn btn-sm btn-icon btn-outline-primary" title="{{ $item['manage_label'] }}"><i class="bx bx-link-external"></i></a>@endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 px-3"><span class="avatar-initial rounded-circle bg-label-secondary p-3 d-inline-flex mb-3"><i class="bx bx-calendar-x fs-2"></i></span><h5 class="fw-bold">Tidak ada jadwal</h5><p class="text-muted mb-0">Belum ada pelatihan atau agenda pada tanggal yang dipilih.</p></div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.schedule-row{transition:.2s;background:#fff}.schedule-row:hover{background:#f8f9ff}.schedule-row.running{background:#fff8f0;border-left:4px solid #ff6b35}.time-box{background:#f2f3f7;min-width:72px}.pulse{animation:pulse 1.8s infinite}@keyframes pulse{0%,100%{opacity:1}50%{opacity:.65}}
</style>
@endpush
