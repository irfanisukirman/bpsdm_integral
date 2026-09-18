@extends('layouts.master')

@section('title', 'Detail Tiket')
@section('content')
@php
    $user = Auth::user();
    $indicator = \App\Services\SlaService::calculateIndicator($ticket);
    $indicatorMap = [
        'aman' => ['label'=>'Tidak Terindikasi Mendekati SLA','icon'=>'bx-check-circle','color'=>'success'],
        'mendekati' => ['label'=>'Mendekati Jatuh Tempo','icon'=>'bx-time-five','color'=>'warning'],
        'terlewati' => ['label'=>'Melewati SLA','icon'=>'bx-error-circle','color'=>'danger'],
        'selesai' => ['label'=>'Selesai','icon'=>'bx-check-double','color'=>'secondary'],
        'tidak_ditentukan' => ['label'=>'Tidak Ditentukan','icon'=>'bx-help-circle','color'=>'secondary'],
    ];
    $slaInfo = $indicatorMap[$indicator] ?? $indicatorMap['tidak_ditentukan'];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Manajemen Layanan /</span> {{ $ticket->ticket_number }}</h4>
        <p class="text-muted small mb-0">Detail tiket Hotline.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('ticketing.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible shadow-sm border-0 mb-4" role="alert">
    <ul class="mb-0 small">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h6 class="mb-0 fw-bold"><i class="bx bx-info-circle me-2 text-primary"></i>Informasi Tiket</h6>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-label-{{ $slaInfo['color'] }} rounded-pill d-inline-flex align-items-center gap-1"><i class="bx {{ $slaInfo['icon'] }}"></i>SLA: {{ $slaInfo['label'] }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><div class="text-muted small">Pengaju</div><div class="fw-semibold">{{ $ticket->submitter_name }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Jenis Pengguna</div><div class="fw-semibold">{{ $ticket->user_type }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">NIP/NIK</div><div class="fw-semibold">{{ $ticket->nip_nik }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Perangkat Daerah</div><div class="fw-semibold">{{ $ticket->perangkat_daerah ?? '-' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Email</div><div class="fw-semibold">{{ $ticket->email }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Layanan</div><div class="fw-semibold">{{ ucwords(str_replace('-',' ',$ticket->service)) }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Kategori</div><div class="fw-semibold">{{ ucwords(str_replace('-',' ',$ticket->category)) }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Bidang</div><div class="fw-semibold">{{ $ticket->bidang }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">PIC</div><div class="fw-semibold">{{ $ticket->assignee?->name ?? 'Belum ditugaskan' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Isi Aduan</div><div class="alert alert-light border-0 bg-light mb-0" style="white-space:pre-wrap;">{{ $ticket->message }}</div>@if($ticket->attachment_path)<a href="javascript:void(0)" class="btn btn-sm btn-outline-primary mt-2" onclick="var p=document.getElementById('shotPreview');p.classList.toggle('d-none');this.querySelector('i').classList.toggle('bx-image');this.querySelector('i').classList.toggle('bx-x');"><i class="bx bx-image me-1"></i>Lihat Screenshot</a><div id="shotPreview" class="mt-2 d-none w-100"><img src="{{ asset('storage/'.$ticket->attachment_path) }}" alt="Screenshot" class="img-fluid rounded-3 border shadow-sm" style="cursor:pointer;" onclick="window.open(this.src,'_blank')"></div>@endif</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bx bx-message-rounded-dots me-2 text-primary"></i>Percakapan</h6></div>
            <div class="card-body">
                @php $allMessages = $userMessages->concat($internalMessages)->sortBy('created_at'); @endphp
                <div id="messagesWrapper" style="position:relative;">
                    <div id="messagesContainer">
                        @include('admin.ticketing.partials.messages', ['messages' => $allMessages, 'user' => $user])
                    </div>
                    <button type="button" id="newMessagesPill" class="btn btn-sm btn-primary rounded-pill shadow-sm" style="display:none;position:absolute;bottom:20px;left:50%;transform:translateX(-50%);z-index:5;border:0;"><i class="bx bx-down-arrow-alt me-1"></i>Pesan baru</button>
                </div>
            </div>
        </div>

        @if($canManage)
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bx bx-pencil me-2 text-primary"></i>Tambah Respons</h6></div>
            <div class="card-body">
                <form action="{{ route('ticketing.reply', $ticket) }}" method="POST" id="replyForm">
                    @csrf
                    <div class="mb-3">
                        <textarea name="message" class="form-control" rows="4" required maxlength="5000" placeholder="Tulis respons atau catatan..."></textarea>
                    </div>
                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="isInternal">
                                <label class="form-check-label" for="isInternal">Catatan Internal (hanya terlihat admin)</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" id="btnSendReply" class="btn btn-primary d-inline-flex align-items-center justify-content-center"><i class="bx bx-send me-1"></i><span>Kirim Respons</span></button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bx bx-cog me-2 text-primary"></i>Tindakan</h6></div>
            <div class="card-body">
                @if($canManage)
                @if($ticket->status==='CLOSED')
                <div class="alert alert-secondary small mb-3"><i class="bx bx-lock-alt me-1"></i>Tiket CLOSED bersifat final dan tidak dapat diubah.</div>
                @else
                <div class="mb-3">
                    <label class="form-label small fw-bold">Penutupan Tiket</label>
                    <form action="{{ route('ticketing.update-status', $ticket) }}" method="POST" onsubmit="return confirm('Tutup tiket ini? Tiket yang ditutup tidak dapat diubah lagi.')">
                        @csrf @method('PUT')
                        <input type="hidden" name="status" value="CLOSED">
                        <button type="submit" class="btn btn-danger btn-sm w-100"><i class="bx bx-lock-alt me-1"></i>Tutup Tiket</button>
                    </form>
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label small fw-bold">Assign PIC</label>
                    <form action="{{ route('ticketing.assign', $ticket) }}" method="POST" class="d-flex gap-2">
                        @csrf @method('PUT')
                        <select name="assigned_to" class="form-select form-select-sm" required>
                            <option value="">-- Pilih PIC --</option>
                            @foreach($pics as $p)<option value="{{ $p->id }}" {{ $ticket->assigned_to===$p->id?'selected':'' }}>{{ $p->name }} ({{ $p->bidang }})</option>@endforeach
                        </select>
                        <button type="submit" class="btn btn-info btn-sm text-white">Assign</button>
                    </form>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-bold">Alihkan Bidang</label>
                    <form action="{{ route('ticketing.transfer', $ticket) }}" method="POST">
                        @csrf @method('PUT')
                        <select name="bidang" class="form-select form-select-sm mb-2" required>
                            @foreach($bidangs as $b)<option value="{{ $b->name }}" {{ $ticket->bidang===$b->name?'selected':'' }}>{{ $b->name }}</option>@endforeach
                        </select>
                        <input type="text" name="note" class="form-control form-control-sm mb-2" placeholder="Catatan pengalihan (opsional)">
                        <button type="submit" class="btn btn-warning btn-sm w-100">Alihkan</button>
                    </form>
                </div>
                @else
                <div class="alert alert-secondary mb-0 small"><i class="bx bx-lock-alt me-1"></i>Anda tidak memiliki kewenangan untuk mengelola tiket ini.</div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bx bx-alarm me-2 text-primary"></i>Timeline SLA</h6></div>
            <div class="card-body small">
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2"><span class="text-muted">Dibuat</span><strong>{{ $ticket->created_at->format('d/m/Y H:i') }}</strong></div>
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2"><span class="text-muted">Respons Pertama</span><strong>{{ $ticket->first_response_at ? $ticket->first_response_at->format('d/m/Y H:i') : '-' }}</strong></div>
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2"><span class="text-muted">Resolved</span><strong>{{ $ticket->resolved_at ? $ticket->resolved_at->format('d/m/Y H:i') : '-' }}</strong></div>
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2"><span class="text-muted">Closed</span><strong>{{ $ticket->closed_at ? $ticket->closed_at->format('d/m/Y H:i') : '-' }}</strong></div>
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2"><span class="text-muted">Auto Close</span><strong>{{ $ticket->auto_close_at ? $ticket->auto_close_at->format('d/m/Y H:i') : '-' }}</strong></div>
                <div class="d-flex justify-content-between"><span class="text-muted">Indikator</span><strong class="text-{{ $slaInfo['color'] }}">{{ $slaInfo['label'] }}</strong></div>
            </div>
        </div>

        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    $('#replyForm').on('submit', function() {
        const btn = $('#btnSendReply');
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengirim...');
    });

    var $wrapper = $('#messagesWrapper');
    if (!$wrapper.length) return;
    var $container = $('#messagesContainer');
    var $pill = $('#newMessagesPill');
    var POLL_INTERVAL = 5000;
    var pollUrl = '{{ route('ticketing.messages', $ticket) }}';
    var lastId = null, lastCount = null, timer = null;

    function nearBottom() {
        var rect = $wrapper.closest('.card')[0].getBoundingClientRect();
        return (rect.bottom - 40) <= window.innerHeight;
    }
    function stickToBottom() {
        window.scrollTo(0, document.body.scrollHeight);
    }
    function sync() {
        if (document.hidden) return;
        $.ajax({
            url: pollUrl,
            dataType: 'json',
            cache: false,
            success: function(res) {
                if (res.count === lastCount && res.last_id === lastId) return;
                var sawChanges = lastId !== null;
                var wasBottom = nearBottom();
                $container.html(res.html);
                if (sawChanges && wasBottom) stickToBottom();
                else if (sawChanges) $pill.fadeIn(150);
                lastId = res.last_id;
                lastCount = res.count;
            }
        });
    }
    function start() {
        if (timer) return;
        timer = setInterval(sync, POLL_INTERVAL);
    }
    $(document).on('visibilitychange', function() {
        if (document.hidden) return;
        clearInterval(timer);
        timer = null;
        sync();
        start();
    });
    $pill.on('click', function() {
        stickToBottom();
        $pill.fadeOut(150);
    });
    sync();
    start();
});
</script>
@endpush