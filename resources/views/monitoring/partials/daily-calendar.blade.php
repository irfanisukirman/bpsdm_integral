@php
    $calendarStart = $calendarMonth->startOfWeek(\Carbon\CarbonImmutable::MONDAY);
    $calendarEnd = $calendarMonth->endOfMonth()->endOfWeek(\Carbon\CarbonImmutable::SUNDAY)->startOfDay();
    $todayKey = $now->toDateString();
    $dailyCalendarUrl = fn ($day) => route('daily-schedule.index', ['date' => $day]);
@endphp

<section class="card daily-calendar mb-4" aria-labelledby="daily-calendar-month">
    <div class="card-body d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <h2 class="h4 mb-0" id="daily-calendar-month">{{ $calendarMonth->locale('id')->translatedFormat('F Y') }}</h2>
        <nav class="d-flex align-items-center gap-2" aria-label="Navigasi kalender">
            <a href="{{ $dailyCalendarUrl($calendarMonth->subMonth()->toDateString()) }}" class="btn btn-icon btn-outline-secondary" aria-label="Bulan sebelumnya">
                <i class="bx bx-chevron-left fs-5" aria-hidden="true"></i>
            </a>
            <a href="{{ route('daily-schedule.index') }}" class="btn btn-outline-primary">Hari Ini</a>
            <a href="{{ $dailyCalendarUrl($calendarMonth->addMonth()->toDateString()) }}" class="btn btn-icon btn-outline-secondary" aria-label="Bulan berikutnya">
                <i class="bx bx-chevron-right fs-5" aria-hidden="true"></i>
            </a>
        </nav>
    </div>
    <div class="daily-calendar-grid">
        @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
            <div class="daily-calendar-weekday">{{ $weekday }}</div>
        @endforeach
        @for($day = $calendarStart; $day->lte($calendarEnd); $day = $day->addDay())
            @php
                $key = $day->toDateString();
                $counts = $calendarCounts[$key] ?? null;
                $total = array_sum($counts ?? []);
            @endphp
            @if($counts === null)
                <div class="daily-calendar-day is-adjacent" aria-hidden="true">{{ $day->day }}</div>
            @else
                <a href="{{ $dailyCalendarUrl($key) }}#agenda-harian"
                    @class(['daily-calendar-day', 'is-selected' => $key === $date, 'is-today' => $key === $todayKey])
                    aria-label="{{ $day->locale('id')->translatedFormat('l, j F Y') }}, {{ $total }} kegiatan{{ $key === $date ? ', tanggal dipilih' : '' }}"
                    @if($key === $todayKey) aria-current="date" @endif>
                    <div class="d-flex justify-content-between align-items-center gap-1">
                        <span class="daily-calendar-number">{{ $day->day }}</span>
                        @if($total)
                            <span class="daily-calendar-total">{{ $total }}</span>
                        @endif
                    </div>
                    @if($total)
                        <div class="daily-calendar-counts">
                            @foreach($counts as $label => $count)
                                @if($count)
                                    <span>{{ $count }} {{ $label }}</span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </a>
            @endif
        @endfor
    </div>
</section>

@push('styles')
<style>
    .daily-calendar-grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); border-top:1px solid var(--integral-border); }
    .daily-calendar-weekday { padding:.65rem .125rem; text-align:center; background:var(--integral-raised); color:var(--integral-muted); font-weight:700; }
    .daily-calendar-day {
        min-width:0; min-height:6rem; padding:.5rem; border-top:1px solid var(--integral-border);
        border-inline-end:1px solid var(--integral-border); color:var(--integral-text);
    }
    .daily-calendar-day:nth-child(7n) { border-inline-end:0; }
    .daily-calendar-day.is-adjacent { background:var(--integral-raised); color:var(--integral-muted); }
    a.daily-calendar-day:is(:hover,:focus-visible,.is-selected) { background:var(--integral-brand-soft); }
    a.daily-calendar-day.is-selected { box-shadow:inset 0 0 0 2px var(--integral-brand-accent); }
    a.daily-calendar-day:focus-visible { outline:2px solid var(--integral-brand-ink); outline-offset:-3px; }
    .daily-calendar-number { display:inline-grid; place-items:center; width:1.6rem; height:1.6rem; font-weight:700; }
    .is-today .daily-calendar-number { border-radius:50%; background:#008444; color:#fff; }
    .daily-calendar-total { padding:.1rem .4rem; border-radius:1rem; background:var(--integral-brand-soft); color:var(--integral-brand-ink); font-size:.75rem; font-weight:700; }
    .daily-calendar-counts { display:grid; gap:.15rem; margin-top:.3rem; color:var(--integral-muted); font-size:.75rem; }
    @media(max-width:767.98px) {
        .daily-calendar-day { min-height:3.25rem; padding:.25rem .125rem; text-align:center; }
        .daily-calendar-day > div:first-child { flex-direction:column; }
        .daily-calendar-counts { display:none; }
        .daily-calendar-weekday { font-size:.75rem; }
    }
</style>
@endpush
