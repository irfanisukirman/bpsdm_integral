@extends('layouts.master')

@section('title', 'Jadwal Pengajar')

@section('content')
@php
    $now = now('Asia/Jakarta');
    $todayKey = $now->toDateString();
    $scheduleGroups = collect($groupedSchedules->all());
    $todaySchedules = $scheduleGroups->get($todayKey, collect());
    $otherGroups = $scheduleGroups->except([$todayKey]);
    $hasFilters = request()->filled('teacher_id') || request()->filled('bidang') || request()->filled('search') || request()->filled('date_from') || request()->filled('date_to');
    $exportQuery = request()->only(['date_from','date_to','teacher_id','bidang','search']);
    $locationOf = function ($schedule) {
        if ($schedule->external_place) return $schedule->external_place;
        if ($schedule->link_zoom) return 'Daring / Zoom';
        if ($schedule->venue_type) return str_replace('_', ' ', ucwords($schedule->venue_type));
        return $schedule->training?->lokasi ?: 'Lokasi belum ditentukan';
    };
    $statusMeta = [
        'upcoming' => ['label'=>'Akan Datang','tone'=>'info','icon'=>'bx-time-five'],
        'ongoing' => ['label'=>'Sedang Berlangsung','tone'=>'success','icon'=>'bx-broadcast'],
        'finished' => ['label'=>'Selesai','tone'=>'secondary','icon'=>'bx-check'],
    ];
@endphp

<div class="teacher-agenda-page">
    <div class="agenda-header mb-4">
        <div class="agenda-header__main"><span class="agenda-header__icon"><i class="bx bx-calendar-week"></i></span><div class="min-w-0"><span class="agenda-kicker">AGENDA LINTAS BIDANG</span><h3 class="fw-bold mb-1">Jadwal Pengajar</h3><p class="text-muted mb-0">Lihat siapa mengajar, kapan, di pelatihan apa, dan bertempat di mana.</p></div></div>
        <a href="{{ route('teacher-schedules.export',$exportQuery) }}" class="btn btn-success flex-shrink-0"><i class="bx bxs-spreadsheet me-1"></i>Export Jadwal</a>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['Hari Ini',$summary['today'],'sesi','bx-calendar-check','primary'],
            ['Minggu Ini',$summary['week'],'sesi','bx-calendar','info'],
            ['Pengajar Terjadwal',$summary['teachers'],'orang','bx-user-voice','success'],
            ['Indikasi Bentrok',$summary['conflicts'],'jadwal','bx-error-circle',$summary['conflicts']?'danger':'secondary'],
        ] as [$label,$value,$suffix,$icon,$tone])
            <div class="col-6 col-xl-3"><div class="agenda-stat"><span class="agenda-stat__icon bg-label-{{ $tone }}"><i class="bx {{ $icon }}"></i></span><div><small>{{ $label }}</small><strong>{{ number_format($value,0,',','.') }} <em>{{ $suffix }}</em></strong></div></div></div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm mb-4 filter-panel">
        <div class="card-body p-3 p-lg-4">
            <div class="quick-periods mb-3"><span class="small fw-semibold text-muted me-1">Tampilkan cepat:</span><a href="{{ route('teacher-schedules.index',['date_from'=>$todayKey,'date_to'=>$todayKey]) }}" class="quick-period {{ $dateFrom===$todayKey && $dateTo===$todayKey?'active':'' }}">Hari ini</a><a href="{{ route('teacher-schedules.index',['date_from'=>$todayKey,'date_to'=>$now->copy()->addDays(6)->toDateString()]) }}" class="quick-period">7 hari ke depan</a><a href="{{ route('teacher-schedules.index',['date_from'=>$now->copy()->startOfMonth()->toDateString(),'date_to'=>$now->copy()->endOfMonth()->toDateString()]) }}" class="quick-period">Bulan ini</a></div>
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label small fw-semibold">Dari tanggal</label><input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}"></div>
                <div class="col-6 col-md-3 col-xl-2"><label class="form-label small fw-semibold">Sampai tanggal</label><input type="date" name="date_to" class="form-control" value="{{ $dateTo }}"></div>
                <div class="col-md-6 col-xl-3"><label class="form-label small fw-semibold">WI / Pengajar</label><select name="teacher_id" class="form-select"><option value="">Semua pengajar</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected($teacherId===$teacher->id)>{{ $teacher->name }}{{ $teacher->nip_nik?' · '.$teacher->nip_nik:'' }}</option>@endforeach</select></div>
                <div class="col-md-6 col-xl-3"><label class="form-label small fw-semibold">Bidang penyelenggara</label><select name="bidang" class="form-select"><option value="">Semua bidang</option>@foreach($fields as $field)<option value="{{ $field }}" @selected($bidang===$field)>{{ $field }}</option>@endforeach</select></div>
                <div class="col-md-6 col-xl-2"><label class="form-label small fw-semibold">Cari</label><div class="input-group input-group-merge"><span class="input-group-text"><i class="bx bx-search"></i></span><input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Nama/materi..."></div></div>
                <div class="col-12"><div class="d-flex flex-column flex-sm-row justify-content-between gap-2"><div class="d-flex gap-2"><button class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i>Terapkan Filter</button>@if($hasFilters)<a href="{{ route('teacher-schedules.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset me-1"></i>Reset</a>@endif</div><small class="text-muted align-self-sm-center">{{ $schedules->count() }} sesi · {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }}–{{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}</small></div></div>
            </form>
        </div>
    </div>

    @if($todaySchedules->isNotEmpty())
        <section class="today-section mb-4">
            <div class="today-section__head"><div><span class="today-label"><span class="live-dot"></span>HARI INI</span><h4 class="fw-bold mb-1">{{ $now->translatedFormat('l, d F Y') }}</h4><p class="mb-0">{{ $todaySchedules->count() }} agenda mengajar perlu dipantau hari ini.</p></div><div class="today-clock"><i class="bx bx-time-five"></i><span>Waktu sekarang</span><strong>{{ $now->format('H:i') }} WIB</strong></div></div>
            <div class="today-agenda-list">
                @foreach($todaySchedules as $schedule)
                    @include('trainings.partials.teacher_schedule_item',['schedule'=>$schedule,'location'=>$locationOf($schedule),'meta'=>$statusMeta[$schedule->monitoring_status],'emphasis'=>true])
                @endforeach
            </div>
        </section>
    @elseif($dateFrom <= $todayKey && $dateTo >= $todayKey)
        <section class="today-empty mb-4"><span><i class="bx bx-coffee"></i></span><div><small>HARI INI · {{ $now->translatedFormat('d F Y') }}</small><h5 class="mb-1">Tidak ada jadwal mengajar hari ini</h5><p class="mb-0">Pengajar tidak memiliki agenda pada tanggal hari ini dalam filter yang dipilih.</p></div></section>
    @endif

    @if($otherGroups->isNotEmpty())
        <div class="d-flex justify-content-between align-items-end gap-3 mb-3"><div><h5 class="fw-bold mb-1">Agenda Berdasarkan Tanggal</h5><p class="text-muted small mb-0">Urutan jadwal dari tanggal terdekat.</p></div><span class="badge bg-label-primary">{{ $otherGroups->count() }} hari</span></div>
        <div class="date-groups">
            @foreach($otherGroups as $date=>$dailySchedules)
                @php $dateCarbon=\Carbon\Carbon::parse($date,'Asia/Jakarta'); $isPast=$dateCarbon->endOfDay()->lt($now); @endphp
                <section class="date-group {{ $isPast?'is-past':'' }}">
                    <aside class="date-marker"><span>{{ $dateCarbon->translatedFormat('D') }}</span><strong>{{ $dateCarbon->format('d') }}</strong><em>{{ $dateCarbon->translatedFormat('M Y') }}</em>@if(!$isPast)<small>{{ $dateCarbon->diffForHumans($now,['parts'=>1]) }}</small>@else<small>Riwayat</small>@endif</aside>
                    <div class="date-group__content"><div class="date-group__head"><div><h5>{{ $dateCarbon->translatedFormat('l, d F Y') }}</h5><span>{{ $dailySchedules->count() }} sesi mengajar</span></div>@if($dailySchedules->where('has_conflict',true)->count())<span class="badge bg-danger"><i class="bx bx-error me-1"></i>{{ $dailySchedules->where('has_conflict',true)->count() }} bentrok</span>@endif</div><div class="schedule-items">@foreach($dailySchedules as $schedule)@include('trainings.partials.teacher_schedule_item',['schedule'=>$schedule,'location'=>$locationOf($schedule),'meta'=>$statusMeta[$schedule->monitoring_status],'emphasis'=>false])@endforeach</div></div>
                </section>
            @endforeach
        </div>
    @endif

    @if($schedules->isEmpty())
        <div class="agenda-empty"><span><i class="bx bx-calendar-x"></i></span><h4>Jadwal tidak ditemukan</h4><p>Belum ada jadwal pengajar pada periode atau filter yang dipilih.</p><a href="{{ route('teacher-schedules.index',['date_from'=>$todayKey,'date_to'=>$now->copy()->endOfMonth()->toDateString()]) }}" class="btn btn-primary">Lihat Jadwal Terdekat</a></div>
    @endif

    <div class="coordination-note mt-4"><i class="bx bx-info-circle"></i><div><strong>Informasi lintas bidang</strong><span>Seluruh admin bidang dan superadmin dapat melihat agenda ini untuk koordinasi. Tombol buka jadwal hanya tersedia bagi akun yang membuat pelatihan tersebut.</span></div></div>
</div>

@push('css')
<style>
.teacher-agenda-page{width:100%;min-width:0}.min-w-0{min-width:0}.agenda-header{display:flex;align-items:center;justify-content:space-between;gap:2rem}.agenda-header__main{display:flex;align-items:center;gap:1rem;min-width:0}.agenda-header__icon{display:grid;place-items:center;flex:0 0 56px;width:56px;height:56px;border-radius:15px;background:#eef0ff;color:#696cff;font-size:1.75rem}.agenda-kicker{display:block;margin-bottom:.25rem;color:#696cff;font-size:.68rem;font-weight:700;letter-spacing:.1em}.agenda-stat{display:flex;align-items:center;gap:.8rem;height:100%;padding:1rem;border:1px solid #ececf2;border-radius:.85rem;background:#fff;box-shadow:0 .18rem .6rem rgba(67,89,113,.06)}.agenda-stat__icon{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border-radius:12px;font-size:1.3rem}.agenda-stat small,.agenda-stat strong{display:block}.agenda-stat small{color:#8592a3;font-size:.72rem}.agenda-stat strong{font-size:1.3rem}.agenda-stat em{color:#8592a3;font-size:.7rem;font-style:normal;font-weight:400}.quick-periods{display:flex;align-items:center;flex-wrap:wrap;gap:.45rem}.quick-period{padding:.35rem .65rem;border:1px solid #dfe1e6;border-radius:50rem;color:#697a8d;font-size:.72rem}.quick-period:hover,.quick-period.active{border-color:#696cff;background:#eef0ff;color:#696cff}.filter-panel .form-label{margin-bottom:.3rem}.today-section{overflow:hidden;border-radius:1rem;background:linear-gradient(135deg,#3446b8,#696cff);box-shadow:0 .6rem 1.5rem rgba(77,87,190,.2)}.today-section__head{display:flex;align-items:center;justify-content:space-between;gap:2rem;padding:1.5rem 1.75rem;color:#fff}.today-section__head p{color:rgba(255,255,255,.78)}.today-label{display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem;font-size:.68rem;font-weight:700;letter-spacing:.1em}.live-dot{display:inline-block;flex:0 0 8px;width:8px;height:8px;border-radius:50%;background:#71dd37;box-shadow:0 0 0 0 rgba(113,221,55,.55);animation:pulse 1.8s infinite}.today-clock{display:grid;grid-template-columns:auto auto;align-items:center;gap:0 .5rem;padding:.75rem 1rem;border-radius:.7rem;background:rgba(255,255,255,.14)}.today-clock i{grid-row:1/3;font-size:1.6rem}.today-clock span{font-size:.65rem;opacity:.75}.today-clock strong{font-size:.9rem}.today-agenda-list{display:grid;gap:.65rem;padding:0 1rem 1rem}.today-empty{display:flex;align-items:center;gap:1rem;padding:1.25rem 1.5rem;border:1px solid #dce0ff;border-radius:.9rem;background:#f7f7ff}.today-empty>span{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:13px;background:#eef0ff;color:#696cff;font-size:1.5rem}.today-empty small{color:#696cff;font-weight:700}.today-empty p{color:#8592a3;font-size:.8rem}.date-groups{display:grid;gap:1rem}.date-group{display:grid;grid-template-columns:115px minmax(0,1fr);gap:1rem}.date-marker{display:flex;align-items:center;flex-direction:column;align-self:start;padding:1rem .65rem;border:1px solid #dce0ff;border-radius:.85rem;background:#f7f7ff;color:#5663d7;text-align:center}.date-marker>span{font-size:.7rem;font-weight:700;text-transform:uppercase}.date-marker>strong{font-size:2rem;line-height:1.15}.date-marker>em{font-size:.68rem;font-style:normal}.date-marker>small{margin-top:.55rem;padding:.25rem .45rem;border-radius:50rem;background:#fff;font-size:.6rem}.date-group.is-past{opacity:.73}.date-group__content{min-width:0;overflow:hidden;border:1px solid #e7e7ed;border-radius:.9rem;background:#fff;box-shadow:0 .18rem .65rem rgba(67,89,113,.05)}.date-group__head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.85rem 1rem;border-bottom:1px solid #eee;background:#fafafa}.date-group__head h5{margin:0;font-size:.9rem;font-weight:700}.date-group__head span{color:#8592a3;font-size:.68rem}.schedule-items{display:grid}.schedule-item{display:grid;grid-template-columns:125px minmax(180px,.85fr) minmax(240px,1.4fr) minmax(180px,1fr) auto;align-items:center;gap:1rem;min-width:0;padding:1rem;border-bottom:1px solid #eee}.schedule-item:last-child{border-bottom:0}.schedule-item:hover{background:#fafaff}.schedule-item.is-conflict{background:#fff6f4}.schedule-time strong,.schedule-time span{display:block}.schedule-time strong{font-size:.95rem}.schedule-time span{color:#8592a3;font-size:.68rem}.schedule-teacher{display:flex;align-items:center;gap:.65rem;min-width:0}.teacher-avatar{display:grid;place-items:center;flex:0 0 38px;width:38px;height:38px;border-radius:50%;background:#eef0ff;color:#696cff;font-weight:700}.schedule-teacher strong,.schedule-teacher small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.schedule-teacher strong{font-size:.78rem}.schedule-teacher small{color:#8592a3;font-size:.64rem}.schedule-main{min-width:0}.schedule-main>strong{display:block;margin-bottom:.25rem;font-size:.8rem;overflow-wrap:anywhere}.training-reference{display:flex;align-items:flex-start;gap:.35rem;color:#697a8d;font-size:.68rem}.schedule-place{min-width:0}.schedule-place strong,.schedule-place span{display:block;overflow-wrap:anywhere}.schedule-place strong{font-size:.7rem}.schedule-place span{color:#8592a3;font-size:.64rem}.schedule-place a{font-size:.65rem}.schedule-actions{display:flex;align-items:center;justify-content:flex-end;gap:.4rem}.schedule-actions .badge{white-space:nowrap}.today-agenda-list .schedule-item{border:0;border-radius:.75rem;background:#fff}.today-agenda-list .schedule-item.is-conflict{background:#fff2ef}.conflict-note{display:block;margin-top:.25rem;color:#ff3e1d;font-size:.62rem;font-weight:700}.agenda-empty{padding:4.5rem 1rem;border:1px dashed #d9dbe0;border-radius:1rem;background:#fff;text-align:center}.agenda-empty>span{display:grid;place-items:center;width:72px;height:72px;margin:0 auto 1rem;border-radius:50%;background:#eef0ff;color:#696cff;font-size:2.2rem}.agenda-empty p{color:#8592a3}.coordination-note{display:flex;align-items:flex-start;gap:.75rem;padding:1rem;border:1px solid #dce0ff;border-radius:.8rem;background:#f7f7ff;color:#566a7f}.coordination-note>i{color:#696cff;font-size:1.3rem}.coordination-note strong,.coordination-note span{display:block}.coordination-note span{font-size:.75rem}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(113,221,55,.55)}70%{box-shadow:0 0 0 8px rgba(113,221,55,0)}100%{box-shadow:0 0 0 0 rgba(113,221,55,0)}}
@media(max-width:1399.98px){.schedule-item{grid-template-columns:115px minmax(170px,.8fr) minmax(220px,1.3fr) minmax(160px,.8fr) auto}}
@media(max-width:1199.98px){.schedule-item{grid-template-columns:105px minmax(180px,.8fr) minmax(240px,1.4fr) auto}.schedule-place{grid-column:2/4;padding-top:.65rem;border-top:1px dashed #e6e6ec}.schedule-actions{grid-column:4;grid-row:1/3}}
@media(max-width:991.98px){.date-group{grid-template-columns:90px minmax(0,1fr)}.schedule-item{grid-template-columns:95px minmax(170px,1fr) auto}.schedule-main{grid-column:2/4}.schedule-place{grid-column:2/3}.schedule-actions{grid-column:3;grid-row:1}.date-group__head{display:none}}
@media(max-width:767.98px){.agenda-header{align-items:flex-start;flex-direction:column;gap:1rem}.agenda-header>a{width:100%}.agenda-header__icon{display:none}.today-section__head{align-items:flex-start;flex-direction:column;padding:1.25rem}.today-clock{width:100%}.date-group{grid-template-columns:1fr}.date-marker{align-items:center;flex-direction:row;gap:.45rem;padding:.65rem 1rem;text-align:left}.date-marker>strong{font-size:1.25rem}.date-marker>small{margin-top:0;margin-left:auto}.schedule-item{grid-template-columns:1fr auto;gap:.65rem}.schedule-time{grid-column:1}.schedule-teacher{grid-column:1}.schedule-main{grid-column:1/3}.schedule-place{grid-column:1/3}.schedule-actions{grid-column:2;grid-row:1/3;align-self:start}.today-agenda-list{padding:.75rem}.agenda-stat{align-items:flex-start;flex-direction:column}.quick-periods>span{width:100%}}
@media(max-width:479.98px){.schedule-actions .btn{display:none}.schedule-actions{grid-row:1}.schedule-teacher{grid-column:1/3}.schedule-time{grid-column:1}.schedule-main,.schedule-place{grid-column:1/3}}
</style>
@endpush
@endsection
