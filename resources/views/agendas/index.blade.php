@extends('layouts.master')
@section('title','Kelola Agenda')
@section('content')
@php
    $agendaGroups = $agendas->getCollection()->groupBy(fn($agenda) => optional($agenda->schedules->first()?->starts_at)->format('Y-m-d') ?? 'tanpa-jadwal');
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Kelola Agenda</h4>
        <p class="text-muted mb-0">Daftar kegiatan di luar pelatihan, diurutkan dari jadwal terbaru.</p>
    </div>
    <a href="{{ route('agendas.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>Buat Agenda</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

@forelse($agendaGroups as $date => $items)
    <div class="d-flex align-items-center gap-3 mt-4 mb-2">
        <div class="badge bg-primary rounded-pill px-3 py-2">
            {{ $date === 'tanpa-jadwal' ? 'Tanpa jadwal' : \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
        </div>
        <div class="border-top flex-grow-1"></div>
        <small class="text-muted">{{ $items->count() }} kegiatan</small>
    </div>
    <div class="card mb-3">
        <div class="list-group list-group-flush">
            @foreach($items as $agenda)
                @php
                    $schedule = $agenda->schedules->first();
                    $location = $agenda->scope === 'internal'
                        ? $schedule?->bookings->pluck('asset.name')->filter()->join(', ')
                        : $schedule?->external_place;
                    $isPast = $schedule?->ends_at?->isPast();
                    $isOngoing = $schedule && $schedule->starts_at->isPast() && $schedule->ends_at->isFuture();
                @endphp
                <div class="list-group-item p-3 p-lg-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-2 col-xl-1 text-md-center">
                            <div class="fw-bold fs-5 text-primary">{{ $schedule?->starts_at?->format('H:i') ?? '--:--' }}</div>
                            <small class="text-muted">s.d. {{ $schedule?->ends_at?->format('H:i') ?? '--:--' }}</small>
                        </div>
                        <div class="col-md-7 col-xl-8 border-start-md">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h5 class="mb-0 fw-bold">{{ $agenda->name }}</h5>
                                <span class="badge bg-label-{{ $agenda->agenda_type === 'pimpinan' ? 'warning' : 'primary' }}">{{ $agenda->agenda_type === 'pimpinan' ? 'Agenda Pimpinan' : 'Agenda Bidang' }}</span>
                                <span class="badge bg-label-{{ $isOngoing ? 'success' : ($isPast ? 'secondary' : 'info') }}">{{ $isOngoing ? 'Sedang berlangsung' : ($isPast ? 'Selesai' : 'Akan datang') }}</span>
                            </div>
                            <div class="text-muted small d-flex flex-wrap gap-3">
                                <span><i class="bx bx-map me-1"></i>{{ $location ?: 'Lokasi belum ditentukan' }}</span>
                                <span><i class="bx bx-user me-1"></i>{{ $schedule?->participants_info ?: 'Pelaksana belum dicantumkan' }}</span>
                                <span><i class="bx bx-buildings me-1"></i>{{ $agenda->bidang }}</span>
                            </div>
                            @if($agenda->description)<p class="mb-0 mt-2 text-muted">{{ \Illuminate\Support\Str::limit($agenda->description, 180) }}</p>@endif
                        </div>
                        <div class="col-md-3 col-xl-3">
                            <div class="d-flex justify-content-md-end gap-2">
                                <a href="{{ route('agendas.edit', $agenda) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-edit me-1"></i>Edit</a>
                                <form method="POST" action="{{ route('agendas.destroy', $agenda) }}" onsubmit="return confirm('Hapus agenda ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash me-1"></i>Hapus</button>
                                </form>
                            </div>
                            <div class="text-md-end mt-2"><small class="text-muted">Dibuat oleh {{ $agenda->creator?->name ?? '-' }}</small></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="card"><div class="card-body text-center py-5"><i class="bx bx-calendar-x display-4 text-muted"></i><h5 class="mt-3">Belum ada agenda</h5><p class="text-muted">Buat agenda pertama untuk mulai mengatur kegiatan.</p></div></div>
@endforelse

@if($agendas->hasPages())<div class="mt-4">{{ $agendas->links() }}</div>@endif
@endsection
