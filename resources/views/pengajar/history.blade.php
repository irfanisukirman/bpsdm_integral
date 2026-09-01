@extends('layouts.master')

@section('title', 'Riwayat Mengajar')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Riwayat Mengajar</h4>
        <p class="text-muted mb-0">Rekap pelatihan dan sesi yang telah selesai Anda ampu.</p>
    </div>
    <a href="{{ route('pengajar.schedule') }}" class="btn btn-outline-primary">
        <i class="bx bx-calendar me-1"></i>Jadwal Mengajar
    </a>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['Pelatihan Selesai', $totalPelatihanRiwayat, 'bx-book-open', 'primary'],
        ['Sesi Mengajar', $totalSesiRiwayat, 'bx-chalkboard', 'info'],
        ['Akumulasi JP', $totalJpRiwayat.' JP', 'bx-bar-chart-alt-2', 'success'],
    ] as [$label, $value, $icon, $color])
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-initial rounded bg-label-{{ $color }} p-3"><i class="bx {{ $icon }} fs-4"></i></span>
                    <div><small class="text-muted d-block">{{ $label }}</small><h3 class="fw-bold mb-0">{{ $value }}</h3></div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('pengajar.history') }}" class="row g-2 align-items-center">
            <div class="col-md">
                <div class="input-group">
                    <span class="input-group-text bg-transparent"><i class="bx bx-search"></i></span>
                    <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Cari pelatihan, materi, bidang, atau lokasi...">
                </div>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button class="btn btn-primary"><i class="bx bx-search me-1"></i>Cari</button>
                @if($search !== '')
                    <a href="{{ route('pengajar.history') }}" class="btn btn-outline-secondary"><i class="bx bx-reset me-1"></i>Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="d-grid gap-4">
    @forelse($trainings as $training)
        @php
            $schedules = $training->schedules;
            $completeDocuments = $schedules->filter(fn ($schedule) => $schedule->pengajarDocuments?->isComplete() ?? false)->count();
        @endphp
        <article class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-4">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-5">
                        <div class="d-flex gap-3">
                            <span class="avatar-initial rounded bg-label-success p-3 flex-shrink-0"><i class="bx bx-check-circle fs-4"></i></span>
                            <div>
                                <span class="badge bg-label-success mb-2">Pelatihan Selesai</span>
                                <h5 class="fw-bold mb-1">{{ $training->nama_pelatihan }}</h5>
                                <div class="text-muted small">Angkatan {{ $training->angkatan ?: '-' }} · {{ $training->bidang ?: 'Bidang belum ditentukan' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <small class="text-muted d-block mb-1">Periode Pelatihan</small>
                        <strong>{{ \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d M Y') }}</strong>
                        <small class="d-block text-muted">s.d. {{ \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d M Y') }}</small>
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <small class="text-muted d-block mb-1">Kontribusi Mengajar</small>
                        <strong>{{ $schedules->count() }} sesi · {{ $schedules->sum('jp') }} JP</strong>
                        <small class="d-block text-muted">{{ $completeDocuments }}/{{ $schedules->count() }} administrasi lengkap</small>
                    </div>
                    <div class="col-lg-3 text-lg-end">
                        <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#historySessions{{ $training->id }}" aria-expanded="false">
                                <i class="bx bx-detail me-1"></i>Detail Sesi
                            </button>
                            <a href="{{ route('pengajar.manage', $training) }}" class="btn btn-sm btn-primary"><i class="bx bx-folder-open me-1"></i>Administrasi</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="collapse" id="historySessions{{ $training->id }}">
                <div class="border-top bg-light p-3 p-md-4">
                    <h6 class="fw-bold mb-3"><i class="bx bx-list-ul me-1"></i>Rincian Sesi yang Diampu</h6>
                    <div class="table-responsive rounded border bg-white">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Tanggal & Jam</th><th>Materi / Kegiatan</th><th>JP</th><th>Tempat</th><th>Administrasi</th></tr>
                            </thead>
                            <tbody>
                                @foreach($schedules as $schedule)
                                    @php
                                        $assets = $schedule->bookings->pluck('asset.name')->filter()->join(', ');
                                        $location = $schedule->venue_type === 'internal'
                                            ? ($assets ?: 'Ruangan internal tidak tercatat')
                                            : ($schedule->external_place ?: ($schedule->link_zoom ? 'Pertemuan daring' : ($training->lokasi ?: '-')));
                                        $documentComplete = $schedule->pengajarDocuments?->isComplete() ?? false;
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">
                                            <strong>{{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('d M Y') }}</strong>
                                            <small class="d-block text-muted">{{ substr($schedule->start_time, 0, 5) }}–{{ substr($schedule->end_time, 0, 5) }} WIB</small>
                                        </td>
                                        <td><strong>{{ $schedule->activity ?: 'Materi tidak tercatat' }}</strong></td>
                                        <td><span class="badge bg-label-primary">{{ $schedule->jp ?? 0 }} JP</span></td>
                                        <td>
                                            <span class="text-break">{{ $location }}</span>
                                            @if($schedule->link_zoom)<small class="d-block text-info"><i class="bx bx-video me-1"></i>Sesi menggunakan Zoom</small>@endif
                                        </td>
                                        <td><span class="badge bg-label-{{ $documentComplete ? 'success' : 'danger' }}">{{ $documentComplete ? 'Lengkap' : 'Belum Lengkap' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </article>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <span class="avatar-initial rounded-circle bg-label-primary d-inline-flex p-4 mb-3"><i class="bx bx-history fs-1"></i></span>
                <h5 class="fw-bold">{{ $search !== '' ? 'Riwayat Tidak Ditemukan' : 'Belum Ada Riwayat Mengajar' }}</h5>
                <p class="text-muted mb-0">
                    {{ $search !== '' ? 'Coba gunakan kata kunci yang berbeda atau reset pencarian.' : 'Pelatihan akan masuk ke halaman ini setelah tanggal pelaksanaannya berakhir.' }}
                </p>
            </div>
        </div>
    @endforelse
</div>

@if($trainings->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-4">
        <small class="text-muted">Menampilkan {{ $trainings->firstItem() }}–{{ $trainings->lastItem() }} dari {{ $trainings->total() }} pelatihan</small>
        {{ $trainings->onEachSide(1)->links() }}
    </div>
@endif
@endsection
