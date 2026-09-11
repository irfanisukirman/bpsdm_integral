@extends('layouts.auth')
@section('title', 'Verifikasi Tanda Tangan Elektronik')
@section('content')
@php
    $isCompleted = $document->status === 'completed';
    $orderedActions = $document->actions->sortByDesc(fn ($item) => $item->actor?->sequence ?? 0);
@endphp
<div class="container py-5" style="max-width:900px">
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-{{ $isCompleted ? 'success' : 'warning' }} text-white mb-3" style="width:82px;height:82px"><i class="bx bx-{{ $isCompleted ? 'check-shield' : 'time-five' }}" style="font-size:42px"></i></div>
        <h2 class="fw-bold">{{ $isCompleted ? 'Dokumen Telah Ditandatangani' : 'Dokumen Masih Dalam Proses' }}</h2>
        <p class="text-muted">Verifikasi resmi Tanda Tangan Elektronik INTEGRAL</p>
    </div>
    <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
        <small class="text-uppercase text-muted">Dokumen</small><h4 class="mt-1">{{ $document->participantCertificate?->participant?->name ?: $document->original_name }}</h4>
        <div class="row g-3 mt-2"><div class="col-md-6"><small class="text-muted d-block">Kegiatan/Pengajuan</small><strong>{{ $document->request->title }}</strong></div><div class="col-md-3"><small class="text-muted d-block">Sumber</small><strong>{{ ['training_certificates'=>'Pelatihan Integral','jct_certificates'=>'JCT','other_documents'=>'Dokumen Lain'][$document->request->source_type] ?? 'Dokumen' }}</strong></div><div class="col-md-3"><small class="text-muted d-block">Status</small><span class="badge bg-{{ $isCompleted ? 'success' : 'warning' }}">{{ $isCompleted ? 'VALID / SELESAI' : 'DALAM PROSES' }}</span></div></div>
    </div></div>
    <div class="d-flex align-items-center justify-content-between mb-3"><div><h5 class="fw-bold mb-1">Riwayat Persetujuan</h5><small class="text-muted">Penandatangan akhir ditampilkan paling atas, dilanjutkan pemaraf sebelumnya.</small></div><span class="badge bg-label-primary">{{ $orderedActions->count() }} tahapan</span></div>
    <div class="d-flex flex-column gap-3">
        @foreach($orderedActions as $action)
            @php
                $user = $action->actor?->user;
                $isSigner = $action->actor?->role === 'signer';
                $photo = $user?->profile_photo ? asset('storage/'.$user->profile_photo) : $user?->avatar;
            @endphp
            <div class="card border-0 shadow-sm {{ $isSigner ? 'border-start border-primary border-4' : '' }}"><div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                <div class="avatar flex-shrink-0" style="width:68px;height:68px">
                    @if($photo)<img src="{{ $photo }}" alt="Foto {{ $user?->name }}" class="rounded-circle w-100 h-100" style="object-fit:cover">@else<span class="avatar-initial rounded-circle bg-label-{{ $isSigner ? 'primary' : 'secondary' }} w-100 h-100 fs-4">{{ mb_strtoupper(mb_substr($user?->name ?: '?', 0, 1)) }}</span>@endif
                </div>
                <div class="flex-grow-1 min-w-0"><div class="d-flex flex-wrap align-items-center gap-2 mb-1"><strong class="fs-5">{{ $user?->name ?: 'Akun tidak tersedia' }}</strong>@if($isSigner)<span class="badge bg-primary"><i class="bx bx-pen me-1"></i>Penandatangan Akhir</span>@else<span class="badge bg-label-secondary">Pemaraf tahap {{ $action->actor?->sequence }}</span>@endif</div><small class="text-muted d-block">{{ $user?->jabatan ?: 'Jabatan belum tersedia' }}</small></div>
                <div class="text-end flex-shrink-0"><span class="badge bg-label-{{ $action->status === 'completed' ? 'success' : 'secondary' }}">{{ $action->status === 'completed' ? 'Selesai' : 'Menunggu' }}</span><small class="d-block text-muted mt-2">{{ $action->signed_at?->translatedFormat('d M Y, H:i') ?: 'Belum diproses' }}</small></div>
            </div></div>
        @endforeach
    </div>
    <p class="text-center text-muted small mt-4">ID Verifikasi: {{ $document->verification_token }}</p>
</div>
@endsection
