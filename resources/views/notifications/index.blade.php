@extends('layouts.master')
@section('title', 'Notifikasi')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Pusat Notifikasi</h4>
        <p class="text-muted mb-0">Daftar kewajiban dan aktivitas yang masih memerlukan tindakan Anda.</p>
    </div>
    <span class="badge bg-label-primary fs-6">{{ $notifications->count() }} perlu ditindaklanjuti</span>
</div>

<div class="card shadow-sm border-0">
    <div class="list-group list-group-flush">
        @forelse($notifications as $notification)
            <a href="{{ $notification['url'] }}" class="list-group-item list-group-item-action p-4">
                <div class="d-flex gap-3">
                    <span class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded-circle bg-label-{{ $notification['level'] }}">
                            <i class="bx {{ $notification['icon'] }} fs-4"></i>
                        </span>
                    </span>
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap justify-content-between gap-2">
                            <h6 class="fw-bold mb-1">{{ $notification['title'] }}</h6>
                            <span class="badge bg-label-{{ $notification['level'] }}">{{ $notification['action'] }}</span>
                        </div>
                        <p class="text-muted mb-0">{{ $notification['message'] }}</p>
                    </div>
                    <i class="bx bx-chevron-right fs-4 align-self-center text-muted"></i>
                </div>
            </a>
        @empty
            <div class="text-center py-5 px-3">
                <i class="bx bx-check-circle text-success mb-3" style="font-size:4rem"></i>
                <h5 class="fw-bold">Semua sudah selesai</h5>
                <p class="text-muted mb-0">Tidak ada aktivitas yang memerlukan tindakan Anda saat ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
