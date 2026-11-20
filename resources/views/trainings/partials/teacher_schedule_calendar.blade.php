@php
    $firstMonth = \Carbon\CarbonImmutable::parse($dateFrom, 'Asia/Jakarta')->startOfMonth();
    $lastMonth = \Carbon\CarbonImmutable::parse($dateTo, 'Asia/Jakarta')->startOfMonth();
    $requestedMonth = request()->string('calendar_month')->toString();
    $visibleMonth = $firstMonth;
    if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/D', $requestedMonth)) {
        $monthStart = \Carbon\CarbonImmutable::parse($requestedMonth.'-01', 'Asia/Jakarta');
        if ($monthStart->betweenIncluded($firstMonth, $lastMonth)) {
            $visibleMonth = $monthStart;
        }
    }
    $calendarStart = $visibleMonth->startOfWeek(\Carbon\CarbonImmutable::MONDAY);
    $calendarEnd = $visibleMonth->endOfMonth()->endOfWeek(\Carbon\CarbonImmutable::SUNDAY)->startOfDay();
    $calendarLink = function ($month) use ($exportQuery, $firstMonth, $lastMonth) {
        if ($month->betweenIncluded($firstMonth, $lastMonth)) {
            return route('teacher-schedules.index', array_merge($exportQuery, ['calendar_month' => $month->format('Y-m')]));
        }

        return route('teacher-schedules.index', array_merge($exportQuery, [
            'date_from' => $month->toDateString(),
            'date_to' => $month->endOfMonth()->toDateString(),
        ]));
    };
@endphp

<section class="card teacher-calendar mb-4" aria-labelledby="teacher-calendar-month">
    <div class="card-body d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <div>
            <p class="text-muted small mb-1">Kalender Pengajar</p>
            <h2 id="teacher-calendar-month" class="h4 mb-0">{{ $visibleMonth->locale('id')->translatedFormat('F Y') }}</h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a class="btn btn-icon btn-outline-secondary" href="{{ $calendarLink($visibleMonth->subMonth()) }}" aria-label="Bulan sebelumnya" title="Bulan sebelumnya">
                <i class="bx bx-chevron-left fs-5" aria-hidden="true"></i>
            </a>
            <a class="btn btn-outline-primary" href="{{ $calendarLink($now->toImmutable()->startOfMonth()) }}">Bulan Ini</a>
            <a class="btn btn-icon btn-outline-secondary" href="{{ $calendarLink($visibleMonth->addMonth()) }}" aria-label="Bulan berikutnya" title="Bulan berikutnya">
                <i class="bx bx-chevron-right fs-5" aria-hidden="true"></i>
            </a>
        </div>
    </div>
    <div class="teacher-calendar-grid">
        @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
            <div class="teacher-calendar-weekday">{{ $weekday }}</div>
        @endforeach
        @for($day = $calendarStart; $day->lte($calendarEnd); $day = $day->addDay())
            @php
                $key = $day->toDateString();
                $belongsToMonth = $day->month === $visibleMonth->month;
                $sessions = $belongsToMonth ? $scheduleGroups->get($key, collect()) : collect();
                $dayLabel = $day->locale('id')->translatedFormat('l, j F Y');
            @endphp
            <div @class(['teacher-calendar-cell', 'is-adjacent' => !$belongsToMonth, 'is-today' => $key === $todayKey])>
                @if($sessions->isNotEmpty())
                    <a class="teacher-calendar-day" href="#jadwal-{{ $key }}" data-calendar-date="{{ $dayLabel }}"
                        aria-label="{{ $dayLabel }}, {{ $sessions->count() }} sesi" @if($key === $todayKey) aria-current="date" @endif>
                        <span class="teacher-calendar-number">{{ $day->day }}</span>
                        <span class="teacher-calendar-count">{{ $sessions->count() }}<span class="teacher-calendar-count-label"> sesi</span></span>
                        <span class="teacher-calendar-events">
                            @foreach($sessions->take(2) as $session)
                                <span @class(['teacher-calendar-event', 'has-conflict' => $session->has_conflict])>
                                    <time>{{ substr((string) $session->start_time, 0, 5) }}</time>
                                    <span>{{ $session->pengajar?->name ?: 'Pengajar' }}</span>
                                </span>
                            @endforeach
                            @if($sessions->count() > 2)
                                <span class="small">+{{ $sessions->count() - 2 }} sesi lainnya</span>
                            @endif
                        </span>
                    </a>
                @else
                    <time class="teacher-calendar-number" datetime="{{ $key }}" @if($key === $todayKey) aria-current="date" @endif>{{ $day->day }}</time>
                @endif
            </div>
        @endfor
    </div>
</section>

<div class="modal fade" id="teacherCalendarDetails" tabindex="-1" aria-labelledby="teacherCalendarDetailsTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="teacherCalendarDetailsTitle"></h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body teacher-agenda-page"></div>
        </div>
    </div>
</div>

@push('css')
<style>
    .teacher-calendar-grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); border-top:1px solid var(--integral-border); }
    .teacher-calendar-weekday { padding:.6rem .25rem; text-align:center; font-weight:700; color:var(--integral-muted); background:var(--integral-raised); }
    .teacher-calendar-cell { min-width:0; min-height:7.1rem; padding:.45rem; border-top:1px solid var(--integral-border); border-right:1px solid var(--integral-border); }
    .teacher-calendar-cell:nth-child(7n) { border-right:0; }
    .teacher-calendar-cell.is-adjacent { background:var(--integral-raised); color:var(--integral-muted); }
    .teacher-calendar-number { display:inline-grid; place-items:center; min-width:1.6rem; height:1.6rem; font-weight:700; }
    .teacher-calendar-cell.is-today .teacher-calendar-number { border-radius:50%; background:var(--integral-brand); color:#fff; }
    html[data-integral-theme="dark"] .teacher-calendar-cell.is-today .teacher-calendar-number { color:#09291a; background:#50d990; }
    .teacher-calendar-day { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.15rem; min-height:100%; border-radius:.45rem; }
    .teacher-calendar-day:hover { background:var(--integral-raised); }
    .teacher-calendar-day:focus-visible { outline:2px solid var(--integral-brand-ink); outline-offset:2px; }
    .teacher-calendar-count { font-size:.7rem; font-weight:700; }
    .teacher-calendar-events { display:grid; align-self:start; flex-basis:100%; min-width:0; gap:.2rem; }
    .teacher-calendar-event { display:flex; align-items:center; gap:.25rem; padding:.17rem .25rem; border-radius:.3rem; background:var(--integral-brand-soft); color:var(--integral-brand-ink); font-size:.7rem; }
    .teacher-calendar-event > span { overflow:hidden; white-space:nowrap; text-overflow:ellipsis; }
    .teacher-calendar-event.has-conflict { border-inline-start:2px solid #dc664f; }
    #teacherCalendarDetails .schedule-item { grid-template-columns:1fr 1fr; }
    #teacherCalendarDetails :is(.schedule-main,.schedule-place,.schedule-actions) { grid-column:auto; grid-row:auto; }
    #teacherCalendarDetails .schedule-main { grid-column:1/-1; }
    #teacherCalendarDetails .schedule-actions .btn { display:inline-flex; }
    @media(max-width:767.98px) {
        .teacher-calendar-cell { min-height:3.2rem; padding:.2rem; }
        .teacher-calendar-day { flex-direction:column; justify-content:flex-start; }
        .teacher-calendar-events,.teacher-calendar-count-label { display:none; }
        .teacher-calendar-count { min-width:1rem; border-radius:1rem; text-align:center; background:var(--integral-brand-soft); }
    }
</style>
@endpush

@push('js')
<script>
(() => {
    const modal = document.getElementById('teacherCalendarDetails');
    let trigger;

    document.querySelector('.teacher-calendar-grid').addEventListener('click', event => {
        const day = event.target.closest('a[data-calendar-date]');
        if (!day || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const agenda = document.getElementById(day.hash.slice(1));
        if (!agenda) return;

        const details = [...agenda.querySelectorAll('.schedule-item')].map(item => item.cloneNode(true));
        modal.querySelector('.modal-title').textContent = day.dataset.calendarDate;
        modal.querySelector('.modal-body').replaceChildren(...details);
        trigger = day;
        event.preventDefault();
        bootstrap.Modal.getOrCreateInstance(modal).show(day);
    });

    modal.addEventListener('hidden.bs.modal', () => trigger?.focus());
})();
</script>
@endpush
