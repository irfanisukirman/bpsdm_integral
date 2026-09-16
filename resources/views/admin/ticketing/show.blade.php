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
                    <span class="badge bg-{{ $ticket->status_color }} rounded-pill">{{ $ticket->status_label }}</span>
                    <span class="badge bg-label-{{ $slaInfo['color'] }} rounded-pill d-inline-flex align-items-center gap-1"><i class="bx {{ $slaInfo['icon'] }}"></i>SLA: {{ $slaInfo['label'] }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><div class="text-muted small">Pengaju</div><div class="fw-semibold">{{ $ticket->submitter_name }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Jenis Pengguna</div><div class="fw-semibold">{{ $ticket->user_type }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">NIP/NIK</div><div class="fw-semibold">{{ $ticket->nip_nik }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Perangkat Daerah</div><div class="fw-semibold">{{ $ticket->perangkat_daerah ?? '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Email</div><div class="fw-semibold">{{ $ticket->email }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Telepon</div><div class="fw-semibold">+{{ $ticket->phone }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Layanan</div><div class="fw-semibold">{{ ucwords(str_replace('-',' ',$ticket->service)) }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Kategori</div><div class="fw-semibold">{{ ucwords(str_replace('-',' ',$ticket->category)) }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Bidang</div><div class="fw-semibold">{{ $ticket->bidang }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">PIC</div><div class="fw-semibold">{{ $ticket->assignee?->name ?? 'Belum ditugaskan' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Isi Aduan</div><div class="alert alert-light border-0 bg-light mb-0" style="white-space:pre-wrap;">{{ $ticket->message }}</div>@if($ticket->attachment_path)<a href="javascript:void(0)" class="btn btn-sm btn-outline-primary mt-2" onclick="var p=document.getElementById('shotPreview');p.classList.toggle('d-none');this.querySelector('i').classList.toggle('bx-image');this.querySelector('i').classList.toggle('bx-x');"><i class="bx bx-image me-1"></i>Lihat Screenshot</a><div id="shotPreview" class="mt-2 d-none w-100"><img src="{{ asset('storage/'.$ticket->attachment_path) }}" alt="Screenshot" class="img-fluid rounded-3 border shadow-sm" style="cursor:pointer;" onclick="window.open(this.src,'_blank')"></div>@endif</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bx bx-message-rounded-dots me-2 text-primary"></i>Percakapan</h6></div>
            <div class="card-body">
                @php $allMessages = $userMessages->concat($internalMessages)->sortBy('created_at'); @endphp
                @forelse($allMessages as $msg)
                    @php
                        $isUser = $msg->sender_role === 'user';
                        $isInternal = $msg->is_internal && in_array($user->role, ['superadmin','admin_bidang']);
                    @endphp
                    <div class="d-flex mb-3 {{ $isUser ? '' : 'justify-content-end' }}">
                        <div class="rounded-3 p-3 shadow-sm {{ $isUser ? 'bg-label-primary' : ($isInternal ? 'bg-label-secondary border-start border-2 border-warning' : 'bg-label-success') }}" style="max-width:80%;">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                <div class="fw-bold small {{ $isUser ? 'text-primary' : ($isInternal ? 'text-secondary' : 'text-success') }}">
                                    <i class="bx {{ $isUser ? 'bx-user' : 'bx-briefcase' }} me-1"></i>{{ $msg->sender_name }}
                                    @if(!$isUser){!! $msg->is_internal ? '<span class="badge bg-warning ms-1">Internal</span>' : '<span class="badge bg-label-success ms-1">PIC</span>' !!}@endif
                                </div>
                                <small class="text-muted">{{ $msg->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <div style="white-space:pre-wrap;" class="small">{{ $msg->message }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted"><i class="bx bx-chat fs-1 d-block mb-2"></i>Belum ada percakapan.</div>
                @endforelse
            </div>
        </div>

        @if($canManage)
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bx bx-pencil me-2 text-primary"></i>Tambah Respons</h6></div>
            <div class="card-body">
                <form action="{{ route('ticketing.reply', $ticket) }}" method="POST">
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
                    <button type="submit" class="btn btn-primary"><i class="bx bx-send me-1"></i>Kirim Respons</button>
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
                    <label class="form-label small fw-bold">Ubah Status</label>
                    <form action="{{ route('ticketing.update-status', $ticket) }}" method="POST" class="d-flex gap-2">
                        @csrf @method('PUT')
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="DIPROSES" {{ $ticket->status==='DIPROSES'?'selected':'' }}>Diproses</option>
                            <option value="MENUNGGU_PENGGUNA" {{ $ticket->status==='MENUNGGU_PENGGUNA'?'selected':'' }}>Menunggu Respons Pengguna</option>
                            <option value="RESOLVED" {{ $ticket->status==='RESOLVED'?'selected':'' }}>Resolved</option>
                            <option value="CLOSED" {{ $ticket->status==='CLOSED'?'selected':'' }}>Closed</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
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

        @if($ticket->statusHistories->count())
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bx bx-history me-2 text-primary"></i>Riwayat Status</h6></div>
            <div class="card-body small">
                <ul class="timeline mb-0" style="list-style:none;padding-left:0;">
                    @foreach($ticket->statusHistories as $sh)
                    <li class="mb-3">
                        <div class="d-flex align-items-center gap-2">
                            @if($sh->from_status)
                                <span class="badge bg-secondary">{{ $sh->from_status }}</span>
                                <i class="bx bx-right-arrow-alt"></i>
                            @endif
                            <span class="badge bg-primary">{{ $sh->to_status }}</span>
                        </div>
                        <div class="mt-1">
                            <i class="bx bx-user me-1"></i>{{ $sh->changed_by_name }}
                            <span class="text-muted">Â· {{ $sh->created_at ? $sh->created_at->format('d/m/Y H:i') : '-' }}</span>
                        </div>
                        @if($sh->note)<div class="text-muted">{{ $sh->note }}</div>@endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection