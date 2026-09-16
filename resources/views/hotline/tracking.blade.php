@extends('layouts.form')

@section('form_title', 'Tracking Tiket')
@section('module_name', 'Hotline')
@section('page_title', 'Tracking Tiket')
@section('page_description', 'Pantau status aduan Anda.')
@section('back_url', 'javascript:history.back()')
@section('form_footer') @overwrite

@php
    $indicator = \App\Services\SlaService::calculateIndicator($ticket);
    $indicatorMap = [
        'aman' => ['label' => 'Tidak Terindikasi Mendekati SLA', 'icon' => 'bx-check-circle', 'color' => 'success'],
        'mendekati' => ['label' => 'Mendekati Jatuh Tempo', 'icon' => 'bx-time-five', 'color' => 'warning'],
        'terlewati' => ['label' => 'Melewati SLA', 'icon' => 'bx-error-circle', 'color' => 'danger'],
        'selesai' => ['label' => 'Selesai', 'icon' => 'bx-check-double', 'color' => 'success'],
        'tidak_ditentukan' => ['label' => 'Tidak Ditentukan', 'icon' => 'bx-help-circle', 'color' => 'secondary'],
    ];
    $slaInfo = $indicatorMap[$indicator] ?? $indicatorMap['tidak_ditentukan'];
@endphp

@section('form_content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="mb-0 fw-bold"><i class="bx bx-barcode-reader me-2 text-primary"></i>{{ $ticket->ticket_number }}</h6>
            <small class="text-muted">Dibuat {{ $ticket->created_at->translatedFormat('d F Y H:i') }}</small>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-{{ $ticket->status_color }} rounded-pill">{{ $ticket->status_label }}</span>
            <span class="badge bg-label-{{ $slaInfo['color'] }} rounded-pill d-inline-flex align-items-center gap-1"><i class="bx {{ $slaInfo['icon'] }}"></i>SLA: {{ $slaInfo['label'] }}</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-muted small">Layanan</div>
                <div class="fw-semibold">{{ ucwords(str_replace('-', ' ', $ticket->service)) }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Kategori</div>
                <div class="fw-semibold">{{ ucwords(str_replace('-', ' ', $ticket->category)) }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Bidang Penanggung Jawab</div>
                <div class="fw-semibold">{{ $ticket->bidang }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">PIC</div>
                <div class="fw-semibold">{{ $ticket->assignee?->name ?? '-' }}</div>
            </div>
            <div class="col-12">
                <div class="text-muted small">Isi Aduan</div>
                <div class="alert alert-light border-0 bg-light mb-0" style="white-space:pre-wrap;">{{ $ticket->message }}</div>
                @if($ticket->attachment_path)
                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary mt-2" onclick="var p=document.getElementById('shotPreview');p.classList.toggle('d-none');this.querySelector('i').classList.toggle('bx-image');this.querySelector('i').classList.toggle('bx-x');"><i class="bx bx-image me-1"></i>Lihat Screenshot</a>
                    <div id="shotPreview" class="mt-2 d-none w-100">
                        <img src="{{ asset('storage/'.$ticket->attachment_path) }}" alt="Screenshot" class="img-fluid rounded-3 border shadow-sm" style="cursor:pointer;" onclick="window.open(this.src,'_blank')">
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bx bx-message-rounded-dots me-2 text-primary"></i>Riwayat Percakapan</h6>
    </div>
    <div class="card-body">
        @forelse($messages as $msg)
            @php $isUser = $msg->sender_role === 'user'; @endphp
            <div class="d-flex mb-3 {{ $isUser ? '' : 'justify-content-end' }}">
                <div class="rounded-3 p-3 shadow-sm" style="{{ $isUser ? 'max-width:80%;background:#f1f3ff;' : 'max-width:80%;background:#e8f5e9;' }}">
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-1">
                        <div class="fw-bold small {{ $isUser ? 'text-primary' : 'text-success' }}">
                            <i class="bx {{ $isUser ? 'bx-user' : 'bx-briefcase' }} me-1"></i>{{ $msg->sender_name }}
                            @if(!$isUser)<span class="badge bg-label-success ms-1">PIC</span>@endif
                        </div>
                        <small class="text-muted">{{ $msg->created_at->translatedFormat('d F Y H:i') }}</small>
                    </div>
                    <div style="white-space:pre-wrap;" class="small">{{ $msg->message }}</div>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">
                <i class="bx bx-chat fs-1 d-block mb-2"></i>
                Belum ada percakapan.
            </div>
        @endforelse
    </div>
</div>

@if($canReply)
<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bx bx-pencil me-2 text-primary"></i>Balas Pesan</h6>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <textarea name="message" class="form-control" rows="4" required maxlength="5000" placeholder="Tulis balasan Anda..."></textarea>
            @error('message')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>
        <button type="submit" id="btnSendReply" class="btn btn-primary d-inline-flex align-items-center justify-content-center"><i class="bx bx-send me-1"></i><span>Kirim Balasan</span></button>
    </div>
</div>
@else
<div class="alert alert-secondary mb-5">
    <i class="bx bx-lock-alt me-2"></i>
    Tiket ini sudah <strong>{{ $ticket->status_label }}</strong> dan tidak dapat dibalas lagi.
</div>
@endif
@endsection

@push('form_js')
<script>
$(document).ready(function() {
    if (!$('#btnSendReply').length) return;
    $('#reusableForm').on('submit', function() {
        const btn = $('#btnSendReply');
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengirim...');
    });
});
</script>
@endpush