@extends('layouts.master')
@section('title', 'Dashboard Narasumber')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Dashboard Narasumber</h4>
        <p class="text-muted mb-0">Pelatihan yang Anda ampu beserta akses administrasi, jadwal, dan forum.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('pengajar.schedule') }}" class="btn btn-outline-primary"><i class="bx bx-calendar me-1"></i>Jadwal Mengajar</a>
        <a href="{{ route('pengajar.history') }}" class="btn btn-outline-secondary"><i class="bx bx-history me-1"></i>Riwayat</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Nama Pelatihan</th><th>Materi / Kegiatan</th><th>Tanggal dan Jam Mengajar</th><th>Total JP</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($trainings as $training)
                @php
                    $forumUnread = app(\App\Services\NotificationCenter::class)->unreadCountForTraining(auth()->user(), $training);
                @endphp
                <tr>
                    <td><strong>{{ $training->nama_pelatihan }}</strong><br><small class="text-muted">Angkatan {{ $training->angkatan }} · {{ $training->bidang }}</small></td>
                    <td>
                        @foreach($training->schedules as $schedule)
                            <div class="{{ !$loop->last ? 'mb-2 pb-2 border-bottom' : '' }}"><strong>{{ $schedule->activity }}</strong><br><small>{{ $schedule->jp ?? 0 }} JP</small></div>
                        @endforeach
                    </td>
                    <td>
                        @foreach($training->schedules as $schedule)
                            <div class="{{ !$loop->last ? 'mb-2 pb-2 border-bottom' : '' }}">{{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('d M Y') }}<br><small>{{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}</small></div>
                        @endforeach
                    </td>
                    <td><span class="badge bg-label-primary">{{ $training->schedules->sum('jp') }} JP</span></td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <a class="btn btn-primary btn-sm" href="{{ route('pengajar.manage', $training) }}"><i class="bx bx-cog me-1"></i>Kelola</a>
                            <a class="btn btn-outline-primary btn-sm position-relative" href="{{ route('training.forum.index', $training) }}">
                                <i class="bx bx-conversation me-1"></i>Forum
                                @if($forumUnread > 0)
                                    <span class="badge rounded-pill bg-danger ms-1">{{ $forumUnread > 99 ? '99+' : $forumUnread }}</span>
                                @endif
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center py-5 text-muted"><i class="bx bx-calendar-x fs-1 d-block mb-2"></i>Belum ada pelatihan yang ditugaskan kepada Anda.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
