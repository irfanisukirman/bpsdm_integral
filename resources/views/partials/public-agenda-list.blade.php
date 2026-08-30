@php $publicAgendaGroups = $publicCalendarItems->groupBy(fn($item) => $item['starts_at']->format('Y-m-d')); @endphp
<div class="mx-auto mb-5" style="max-width:1040px">
    @forelse($publicAgendaGroups as $date => $items)
        <div class="d-flex align-items-center gap-3 mt-4 mb-2">
            <div class="rounded-3 bg-primary text-white text-center px-3 py-2 shadow-sm" style="min-width:82px">
                <div class="small text-uppercase">{{\Carbon\Carbon::parse($date)->translatedFormat('M')}}</div>
                <div class="fs-4 fw-bold lh-1">{{\Carbon\Carbon::parse($date)->format('d')}}</div>
            </div>
            <div><h5 class="mb-0 fw-bold">{{\Carbon\Carbon::parse($date)->translatedFormat('l')}}</h5><small class="text-muted">{{\Carbon\Carbon::parse($date)->translatedFormat('d F Y')}} · {{$items->count()}} kegiatan</small></div>
        </div>
        <div class="card border-0 shadow-sm mb-4 overflow-hidden"><div class="list-group list-group-flush">
            @foreach($items as $item)
                <div class="list-group-item p-3 p-md-4"><div class="row g-3 align-items-start">
                    <div class="col-3 col-md-2 text-center"><div class="fw-bold fs-5 text-primary">{{$item['starts_at']->format('H:i')}}</div><small class="text-muted">s.d. {{$item['ends_at']->format('H:i')}}</small></div>
                    <div class="col-9 col-md-10 border-start">
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-1"><h5 class="fw-bold mb-0">{{$item['title']}}</h5><span class="badge bg-label-{{$item['color']}}">{{$item['source_label']}}</span></div>
                        @if($item['description'])<p class="mb-2 text-dark">{{\Illuminate\Support\Str::limit($item['description'],220)}}</p>@endif
                        <div class="small text-muted d-flex flex-wrap gap-3"><span><i class="bx bx-map me-1"></i>{{$item['location']}}</span>@if($item['person'])<span><i class="bx bx-user me-1"></i>{{$item['person']}}</span>@endif</div>
                    </div>
                </div></div>
            @endforeach
        </div></div>
    @empty
        <div class="text-center text-muted py-5"><i class="bx bx-calendar-x display-4 opacity-50"></i><p class="mt-3">Belum ada agenda atau jadwal pelatihan mendatang.</p></div>
    @endforelse
    @if($publicCalendarItems->count() >= 60)<p class="text-center text-muted small">Menampilkan 60 kegiatan terdekat.</p>@endif
</div>