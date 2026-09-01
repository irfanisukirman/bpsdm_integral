@extends('layouts.master')

@section('title', 'Jadwal Mengajar')

@section('content')
@php
    $now = \Carbon\Carbon::now('Asia/Jakarta');
    $allSchedules = $myTrainings->flatMap->schedules->values();
    $todaySchedules = $allSchedules->filter(fn ($schedule) => \Carbon\Carbon::parse($schedule->date, 'Asia/Jakarta')->isSameDay($now));
    $upcomingSchedules = $allSchedules->filter(fn ($schedule) => \Carbon\Carbon::parse($schedule->date.' '.$schedule->start_time, 'Asia/Jakarta')->isFuture());
    $completedSchedules = $allSchedules->filter(fn ($schedule) => \Carbon\Carbon::parse($schedule->date.' '.($schedule->end_time ?: $schedule->start_time), 'Asia/Jakarta')->isPast());
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Jadwal Mengajar</h4>
        <p class="text-muted mb-0">Seluruh sesi yang ditugaskan kepada {{ $user->name }}.</p>
    </div>
    <a href="{{ route('pengajar.index') }}" class="btn btn-outline-primary">
        <i class="bx bx-home-alt me-1"></i>Dashboard Narasumber
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible"><i class="bx bx-check-circle me-1"></i>{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible"><i class="bx bx-info-circle me-1"></i>{{ session('warning') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-3 mb-4">
    @foreach([
        ['Pelatihan Diampu', $myTrainings->count(), 'bx-book-open', 'primary'],
        ['Sesi Hari Ini', $todaySchedules->count(), 'bx-calendar-check', 'info'],
        ['Sesi Mendatang', $upcomingSchedules->count(), 'bx-time-five', 'warning'],
        ['Total JP', $allSchedules->sum('jp').' JP', 'bx-bar-chart-alt-2', 'success'],
    ] as [$label, $value, $icon, $color])
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-initial rounded bg-label-{{ $color }} p-3"><i class="bx {{ $icon }} fs-4"></i></span>
                    <div><small class="text-muted d-block">{{ $label }}</small><h4 class="mb-0 fw-bold">{{ $value }}</h4></div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="bx bx-search"></i></span>
                    <input type="search" id="scheduleSearch" class="form-control" placeholder="Cari nama pelatihan, materi, bidang, atau lokasi...">
                </div>
            </div>
            <div class="col-md-4">
                <select id="scheduleStatus" class="form-select">
                    <option value="all">Semua sesi</option>
                    <option value="ongoing">Sedang berlangsung</option>
                    <option value="upcoming">Akan datang</option>
                    <option value="completed">Sudah selesai</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div id="scheduleList" class="d-grid gap-4">
    @forelse($myTrainings as $training)
        @php
            $trainingSchedules = $training->schedules;
            $trainingJp = $trainingSchedules->sum('jp');
            $searchText = strtolower(collect([$training->nama_pelatihan, $training->bidang, $training->angkatan])
                ->merge($trainingSchedules->pluck('activity'))
                ->merge($trainingSchedules->pluck('external_place'))
                ->filter()->implode(' '));
        @endphp
        <section class="card border-0 shadow-sm training-card" data-search="{{ $searchText }}">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div class="d-flex gap-3">
                        <span class="avatar-initial rounded bg-label-primary p-3 flex-shrink-0"><i class="bx bx-book-content fs-4"></i></span>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $training->nama_pelatihan }}</h5>
                            <div class="d-flex flex-wrap gap-2 text-muted small">
                                <span><i class="bx bx-buildings me-1"></i>{{ $training->bidang ?: 'Bidang belum ditentukan' }}</span>
                                <span><i class="bx bx-layer me-1"></i>Angkatan {{ $training->angkatan ?: '-' }}</span>
                                <span><i class="bx bx-time me-1"></i>{{ $trainingJp }} JP</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('pengajar.manage', $training) }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-cog me-1"></i>Kelola Administrasi
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="schedule-timeline">
                    @foreach($trainingSchedules as $schedule)
                        @php
                            $start = \Carbon\Carbon::parse($schedule->date.' '.$schedule->start_time, 'Asia/Jakarta');
                            $end = \Carbon\Carbon::parse($schedule->date.' '.($schedule->end_time ?: $schedule->start_time), 'Asia/Jakarta');
                            $status = $now->between($start, $end) ? 'ongoing' : ($start->isFuture() ? 'upcoming' : 'completed');
                            $statusMeta = match($status) {
                                'ongoing' => ['Sedang Berlangsung', 'success', 'bx-play-circle'],
                                'upcoming' => ['Akan Datang', 'warning', 'bx-time-five'],
                                default => ['Selesai', 'secondary', 'bx-check-circle'],
                            };
                            $internalAssets = $schedule->bookings->pluck('asset.name')->filter()->join(', ');
                            $location = $schedule->venue_type === 'internal'
                                ? ($internalAssets ?: 'Ruangan internal belum ditentukan')
                                : ($schedule->external_place ?: ($schedule->link_zoom ? 'Pertemuan daring' : 'Lokasi belum ditentukan'));
                            $documentsComplete = $schedule->pengajarDocuments?->isComplete() ?? false;
                            $rowSearch = strtolower(collect([$training->nama_pelatihan, $training->bidang, $schedule->activity, $location])->filter()->implode(' '));
                        @endphp
                        <article class="schedule-row px-4 py-4" data-status="{{ $status }}" data-search="{{ $rowSearch }}">
                            <div class="timeline-marker bg-{{ $statusMeta[1] }}"><i class="bx {{ $statusMeta[2] }}"></i></div>
                            <div class="row g-3 align-items-center w-100">
                                <div class="col-lg-3">
                                    <div class="fw-bold text-primary">{{ $start->translatedFormat('l, d F Y') }}</div>
                                    <div class="fs-5 fw-semibold">{{ $start->format('H:i') }}–{{ $end->format('H:i') }} WIB</div>
                                    <span class="badge bg-label-{{ $statusMeta[1] }} mt-1">{{ $statusMeta[0] }}</span>
                                </div>
                                <div class="col-lg-4">
                                    <h6 class="fw-bold mb-1">{{ $schedule->activity ?: 'Materi belum ditentukan' }}</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-label-primary">{{ $schedule->jp ?? 0 }} JP</span>
                                        <span class="badge bg-label-info">{{ ucwords(str_replace('_', ' ', $training->metode ?: $training->model ?: '-')) }}</span>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="small text-muted mb-1"><i class="bx bx-map me-1"></i>Tempat</div>
                                    <div class="fw-semibold text-break">{{ $location }}</div>
                                    @if($schedule->link_zoom)
                                        <a href="{{ $schedule->link_zoom }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info mt-2"><i class="bx bx-video me-1"></i>Join Zoom</a>
                                    @endif
                                </div>
                                <div class="col-lg-2 text-lg-end">
                                    <div class="mb-2">
                                        <span class="badge bg-label-{{ $documentsComplete ? 'success' : 'danger' }}">
                                            <i class="bx {{ $documentsComplete ? 'bx-check' : 'bx-file' }} me-1"></i>{{ $documentsComplete ? 'Dokumen Lengkap' : 'Dokumen Belum Lengkap' }}
                                        </span>
                                    </div>
                                    <a href="{{ route('pengajar.manage', $training) }}" class="btn btn-sm btn-outline-primary">Buka Sesi</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <span class="avatar-initial rounded-circle bg-label-primary d-inline-flex p-4 mb-3"><i class="bx bx-calendar-x fs-1"></i></span>
                <h5 class="fw-bold">Belum Ada Jadwal Mengajar</h5>
                <p class="text-muted mb-0">Jadwal akan muncul setelah admin menugaskan Anda sebagai pengajar pada sesi pelatihan.</p>
            </div>
        </div>
    @endforelse
</div>

<div id="scheduleEmptyFilter" class="card border-0 shadow-sm d-none">
    <div class="card-body text-center py-5 text-muted"><i class="bx bx-filter-alt fs-1 d-block mb-2"></i>Tidak ada jadwal yang sesuai dengan pencarian atau filter.</div>
</div>
@endsection

@push('css')
<style>
    .schedule-timeline { position: relative; }
    .schedule-row { display: flex; position: relative; padding-left: 4.5rem !important; }
    .schedule-row:not(:last-child) { border-bottom: 1px solid #eceef1; }
    .schedule-row:not(:last-child)::before { content: ''; position: absolute; left: 2rem; top: 3.2rem; bottom: -1rem; width: 2px; background: #e6e8ed; }
    .timeline-marker { position: absolute; left: 1.25rem; top: 1.75rem; width: 1.55rem; height: 1.55rem; border-radius: 50%; color: #fff; display: grid; place-items: center; z-index: 1; }
    .timeline-marker i { font-size: .95rem; }
    @media (max-width: 767.98px) {
        .schedule-row { padding-left: 3.6rem !important; }
        .timeline-marker { left: 1rem; }
        .schedule-row:not(:last-child)::before { left: 1.72rem; }
    }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('scheduleSearch');
    const status = document.getElementById('scheduleStatus');
    const cards = Array.from(document.querySelectorAll('.training-card'));
    const empty = document.getElementById('scheduleEmptyFilter');

    function applyScheduleFilter() {
        const keyword = search.value.trim().toLowerCase();
        const selectedStatus = status.value;
        let visibleCards = 0;

        cards.forEach(card => {
            let visibleRows = 0;
            card.querySelectorAll('.schedule-row').forEach(row => {
                const matchesKeyword = !keyword || row.dataset.search.includes(keyword);
                const matchesStatus = selectedStatus === 'all' || row.dataset.status === selectedStatus;
                const visible = matchesKeyword && matchesStatus;
                row.classList.toggle('d-none', !visible);
                if (visible) visibleRows++;
            });
            card.classList.toggle('d-none', visibleRows === 0);
            if (visibleRows > 0) visibleCards++;
        });

        empty.classList.toggle('d-none', visibleCards > 0 || cards.length === 0);
    }

    search.addEventListener('input', applyScheduleFilter);
    status.addEventListener('change', applyScheduleFilter);
});
</script>
@endpush
