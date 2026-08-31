@extends('layouts.master')
@section('title','Detail Pengajuan')
@section('content')
@php $isOwner=(int)$submission->user_id===auth()->id();$canEdit=$isOwner&&in_array($submission->status,['draft','revision_requested']);$statusColor=$submission->status==='final'?'success':($submission->status==='revision_requested'?'warning':($submission->status==='rejected'?'danger':($submission->status==='draft'?'secondary':'primary'))); @endphp
<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4"><div><a href="{{$admin?route('mitra.admin.index'):route('mitra.dashboard')}}" class="text-muted text-decoration-none"><i class="bx bx-arrow-back"></i> Kembali</a><h4 class="fw-bold mt-2 mb-1">{{$submission->title}}</h4><div class="d-flex flex-wrap gap-2"><span class="badge bg-label-info">{{$submission->type_label}}</span><span class="badge bg-label-{{$statusColor}}">{{$submission->status_label}}</span></div></div>@if($canEdit)<form action="{{route('mitra.submissions.submit',$submission)}}" method="POST" onsubmit="return confirm('Kirim pengajuan ini kepada bidang tujuan?')">@csrf @method('PUT')<button class="btn btn-primary"><i class="bx bx-send me-1"></i>{{$submission->status==='revision_requested'?'Kirim Revisi':'Kirim Pengajuan'}}</button></form>@endif</div>
@if(session('success'))<div class="alert alert-success alert-dismissible">{{session('success')}}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif @if($errors->any())<div class="alert alert-danger">{{$errors->first()}}</div>@endif
<div class="row g-4"><div class="col-xl-7"><div class="card border-0 shadow-sm mb-4"><div class="card-header border-bottom d-flex justify-content-between"><h5 class="mb-0">Informasi Pengajuan</h5>@if($canEdit)<button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editSubmission"><i class="bx bx-edit"></i> Edit Draft</button>@endif</div><div class="card-body"><dl class="row mb-0"><dt class="col-sm-4">Mitra</dt><dd class="col-sm-8">{{$submission->partner->instansi?:$submission->partner->name}}</dd><dt class="col-sm-4">Bidang Tujuan</dt><dd class="col-sm-8">{{$submission->target_bidang}}</dd><dt class="col-sm-4">PIC</dt><dd class="col-sm-8">{{$submission->pic_name}} · {{$submission->pic_contact}}</dd>@if($submission->type==='training')<dt class="col-sm-4">Sasaran/Jumlah</dt><dd class="col-sm-8">{{$submission->participant_target?:'-'}} / {{$submission->estimated_participants?:'-'}} orang</dd><dt class="col-sm-4">Rencana Waktu</dt><dd class="col-sm-8">{{$submission->preferred_start?->translatedFormat('d M Y')?:'-'}} s.d. {{$submission->preferred_end?->translatedFormat('d M Y')?:'-'}}</dd><dt class="col-sm-4">Metode/Lokasi</dt><dd class="col-sm-8">{{$submission->method?:'-'}} / {{$submission->location?:'-'}}</dd>@else<dt class="col-sm-4">Ruang Lingkup</dt><dd class="col-sm-8">{!!nl2br(e($submission->scope?:'-'))!!}</dd>@endif<dt class="col-sm-4">Latar Belakang</dt><dd class="col-sm-8">{!!nl2br(e($submission->background?:'-'))!!}</dd><dt class="col-sm-4">Tujuan</dt><dd class="col-sm-8">{!!nl2br(e($submission->objective?:'-'))!!}</dd><dt class="col-sm-4">PIC Pengelola</dt><dd class="col-sm-8">{{$submission->assignee?->name?:'Belum ditentukan'}}</dd></dl></div></div>
@if($canEdit)<div class="collapse mb-4" id="editSubmission"><div class="card border-primary"><div class="card-header">Edit Data Draft</div><form action="{{route('mitra.submissions.update',$submission)}}" method="POST">@csrf @method('PUT')<input type="hidden" name="type" value="{{$submission->type}}"><div class="card-body row g-3"><div class="col-12"><label class="form-label">Judul</label><input name="title" value="{{$submission->title}}" class="form-control" required></div>@if($submission->type==='training')<div class="col-12"><label class="form-label">Bidang Tujuan</label><select name="target_bidang" class="form-select">@foreach($trainingFields as $field)<option value="{{$field}}" @selected($submission->target_bidang===$field)>{{$field}}</option>@endforeach</select></div><input type="hidden" name="participant_target" value="{{$submission->participant_target}}"><input type="hidden" name="estimated_participants" value="{{$submission->estimated_participants}}"><input type="hidden" name="preferred_start" value="{{$submission->preferred_start?->format('Y-m-d')}}"><input type="hidden" name="preferred_end" value="{{$submission->preferred_end?->format('Y-m-d')}}"><input type="hidden" name="method" value="{{$submission->method}}"><input type="hidden" name="location" value="{{$submission->location}}"><input type="hidden" name="competency" value="{{$submission->competency}}">@elseperiod_end?->format('Y-m-d')}}"><div class="col-12"><label class="form-label">Ruang Lingkup</label><textarea name="scope" class="form-control">{{$submission->scope}}</textarea></div>@endif<div class="col-md-6"><input name="pic_name" value="{{$submission->pic_name}}" class="form-control" required></div><div class="col-md-6"><input name="pic_contact" value="{{$submission->pic_contact}}" class="form-control" required></div><div class="col-12"><textarea name="background" class="form-control" rows="3">{{$submission->background}}</textarea></div><div class="col-12"><textarea name="objective" class="form-control" rows="3">{{$submission->objective}}</textarea></div></div><div class="card-footer text-end"><button class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>@endif
<div class="card border-0 shadow-sm"><div class="card-header border-bottom"><h5 class="mb-0">Riwayat Draft Dokumen</h5></div><div class="card-body">@if($submission->status!=='final'&&$submission->status!=='rejected')<form action="{{route('mitra.submissions.upload',$submission)}}" method="POST" enctype="multipart/form-data" class="border rounded p-3 mb-4">@csrf<div class="row g-2"><div class="col-md-5"><input type="file" name="document" class="form-control" required></div><div class="col-md-5"><input name="change_note" class="form-control" placeholder="Catatan perubahan versi"></div><div class="col-md-2"><button class="btn btn-outline-primary w-100">Unggah</button></div></div></form>@endif
@forelse($submission->documents->sortByDesc('version_number') as $doc)<div class="d-flex justify-content-between gap-3 border-bottom py-3"><div><strong>Versi {{$doc->version_number}} · {{$doc->display_name}}</strong>@if($doc->is_final)<span class="badge bg-success ms-1">FINAL</span>@endif<small class="text-muted d-block">{{$doc->uploader->name}} · {{$doc->created_at->translatedFormat('d M Y H:i')}}</small>@if($doc->change_note)<small>{{$doc->change_note}}</small>@endif</div><a href="{{route('mitra.documents.download',$doc)}}" class="btn btn-sm btn-outline-secondary align-self-center"><i class="bx bx-download"></i></a></div>@empty<p class="text-muted text-center py-3">Belum ada draft dokumen.</p>@endforelse</div></div></div>
<div class="col-xl-5">@if($admin)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-label-primary"><h5 class="mb-1"><i class="bx bx-cog me-1"></i>Aksi Pengelola</h5><small class="text-muted">Kesepakatan dan koreksi dibahas langsung melalui chat.</small></div>
    <div class="card-body">
        @if($submission->status === 'final')
            <div class="alert alert-success small"><i class="bx bx-check-shield me-1"></i>Usulan sudah final dan dokumen terakhir sudah masuk ke Manajemen Dokumen.</div>
            <form action="{{ route('mitra.admin.reopen', $submission) }}" method="POST" onsubmit="return confirm('Kembalikan pengajuan final menjadi draft? Dokumen final akan dilepas dari Manajemen Dokumen dan chat dibuka kembali.')">
                @csrf @method('PUT')
                <button class="btn btn-outline-warning w-100"><i class="bx bx-undo me-1"></i>Kembalikan Menjadi Draft</button>
            </form>
        @else
            <p class="small text-muted">Jika hasil pembahasan di chat sudah disepakati, tetapkan versi dokumen terakhir sebagai final.</p>
            <form action="{{ route('mitra.admin.finalize', $submission) }}" method="POST" onsubmit="return confirm('Finalisasi usulan dan tetapkan dokumen versi terakhir sebagai dokumen final?')">
                @csrf @method('PUT')
                <button class="btn btn-success w-100" {{ $submission->documents->isEmpty() ? 'disabled' : '' }}><i class="bx bx-check-shield me-1"></i>Finalisasi Usulan</button>
            </form>
            @if($submission->documents->isEmpty())<small class="text-danger d-block mt-2"><i class="bx bx-error-circle me-1"></i>Belum ada dokumen yang dapat difinalisasi.</small>@endif
        @endif
    </div>
    <div class="card-footer border-top">
        <form action="{{ route('mitra.admin.destroy', $submission) }}" method="POST" onsubmit="return confirm('Hapus pengajuan ini secara permanen? Seluruh chat, draft, dokumen final, dan file terkait akan ikut dihapus.')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger w-100"><i class="bx bx-trash me-1"></i>Hapus Pengajuan</button>
        </form>
    </div>
</div>
@endif<div class="card border-0 shadow-sm partner-chat-card">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between py-3">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar-initial rounded-circle bg-label-primary chat-header-icon"><i class="bx bx-message-rounded-dots"></i></span>
            <div>
                <h5 class="mb-0">Diskusi Pengajuan</h5>
                <small class="text-muted">Ruang komunikasi Mitra dan pengelola bidang</small>
            </div>
        </div>
        <span class="badge bg-label-{{ in_array($submission->status, ['final','rejected']) ? 'secondary' : 'success' }}">
            <i class="bx bxs-circle me-1 chat-status-dot"></i>{{ in_array($submission->status, ['final','rejected']) ? 'Ditutup' : 'Aktif' }}
        </span>
    </div>

    <div class="card-body partner-chat-messages" id="partnerChatMessages" data-comments-url="{{ route('mitra.submissions.comments', $submission) }}" data-current-user="{{ auth()->id() }}">
        @forelse($submission->comments->sortBy('created_at') as $comment)
            @php
                $isMine = (int) $comment->user_id === (int) auth()->id();
                $senderRole = match($comment->user->role) {
                    'mitra' => 'Mitra',
                    'superadmin' => 'Superadmin',
                    'admin_bidang' => 'Admin Bidang',
                    default => 'Pengelola',
                };
            @endphp
            <div class="d-flex mb-3 chat-message-row {{ $isMine ? 'justify-content-end' : 'justify-content-start' }}" data-comment-id="{{ $comment->id }}">
                @unless($isMine)
                    <div class="avatar avatar-sm flex-shrink-0 me-2 mt-1">
                        <span class="avatar-initial rounded-circle bg-label-info">{{ strtoupper(substr($comment->user->name, 0, 1)) }}</span>
                    </div>
                @endunless
                <div class="chat-message-wrap {{ $isMine ? 'align-items-end' : 'align-items-start' }}">
                    <div class="d-flex align-items-center gap-2 mb-1 {{ $isMine ? 'flex-row-reverse' : '' }}">
                        <small class="fw-semibold text-dark">{{ $isMine ? 'Anda' : $comment->user->name }}</small>
                        <span class="badge rounded-pill bg-label-{{ $comment->user->role === 'mitra' ? 'warning' : 'primary' }} chat-role-badge">{{ $senderRole }}</span>
                    </div>
                    <div class="chat-bubble {{ $isMine ? 'chat-bubble-mine' : 'chat-bubble-other' }}">
                        <div>{!! nl2br(e($comment->message)) !!}</div>
                        <div class="chat-time {{ $isMine ? 'text-white-50' : 'text-muted' }}">
                            {{ $comment->created_at->translatedFormat('d M, H:i') }}
                            @if($isMine)<i class="bx bx-check-double ms-1"></i>@endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="chat-empty text-center">
                <span class="avatar-initial rounded-circle bg-label-primary d-inline-flex p-3 mb-3"><i class="bx bx-conversation fs-2"></i></span>
                <h6 class="fw-bold mb-1">Belum ada percakapan</h6>
                <p class="text-muted small mb-0">Mulai diskusi mengenai pengajuan, revisi, atau dokumen kerja sama.</p>
            </div>
        @endforelse
    </div>

    @if(!in_array($submission->status, ['final','rejected']))
        <form action="{{ route('mitra.submissions.comment', $submission) }}" method="POST" class="card-footer border-top bg-white p-3" id="partnerChatForm">
            @csrf
            <div class="d-flex align-items-end gap-2">
                <div class="flex-grow-1 chat-composer">
                    <textarea name="message" id="partnerChatInput" class="form-control border-0 shadow-none" rows="1" maxlength="3000" placeholder="Tulis pesan..." required></textarea>
                    <small class="text-muted d-block px-2 pb-1">Enter untuk baris baru</small>
                </div>
                <button class="btn btn-primary btn-icon rounded-circle flex-shrink-0 chat-send-button" type="submit" title="Kirim pesan">
                    <i class="bx bx-send"></i>
                </button>
            </div>
        </form>
    @else
        <div class="card-footer text-center text-muted bg-light"><i class="bx bx-lock-alt me-1"></i>Percakapan ditutup karena pengajuan berstatus {{ strtolower($submission->status_label) }}.</div>
    @endif
</div></div></div>

@push('css')
<style>
    .partner-chat-card { overflow: hidden; border-radius: 14px; }
    .chat-header-icon { width: 40px; height: 40px; font-size: 1.25rem; }
    .chat-status-dot { font-size: .45rem; vertical-align: middle; }
    .partner-chat-messages { height: 480px; overflow-y: auto; padding: 1.25rem; background: linear-gradient(180deg, #f7f8ff 0%, #fff 100%); scroll-behavior: smooth; }
    .partner-chat-messages::-webkit-scrollbar { width: 6px; }
    .partner-chat-messages::-webkit-scrollbar-thumb { background: #d9dee3; border-radius: 10px; }
    .chat-message-wrap { display: flex; flex-direction: column; max-width: 82%; }
    .chat-bubble { padding: .75rem .9rem .45rem; border-radius: 15px; line-height: 1.45; overflow-wrap: anywhere; box-shadow: 0 2px 7px rgba(67, 89, 113, .08); }
    .chat-bubble-mine { color: #fff; background: #696cff; border-bottom-right-radius: 4px; }
    .chat-bubble-other { color: #566a7f; background: #fff; border: 1px solid #e7e7ff; border-bottom-left-radius: 4px; }
    .chat-time { margin-top: .35rem; font-size: .66rem; text-align: right; }
    .chat-role-badge { font-size: .58rem; padding: .25rem .45rem; }
    .chat-empty { margin: auto; padding: 5rem 1rem; }
    .chat-composer { border: 1px solid #d9dee3; border-radius: 14px; background: #f8f9fa; padding: .25rem .45rem 0; transition: border-color .2s, box-shadow .2s; }
    .chat-composer:focus-within { border-color: #696cff; box-shadow: 0 0 0 .18rem rgba(105,108,255,.12); background: #fff; }
    .chat-composer textarea { resize: none; min-height: 38px; max-height: 120px; background: transparent; }
    .chat-send-button { width: 44px; height: 44px; }
    @media (max-width: 575.98px) { .partner-chat-messages { height: 420px; padding: 1rem .75rem; } .chat-message-wrap { max-width: 88%; } }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const messages = document.getElementById('partnerChatMessages');
    const input = document.getElementById('partnerChatInput');
    const form = document.getElementById('partnerChatForm');
    if (!messages) return;

    const currentUserId = Number(messages.dataset.currentUser);
    let lastCommentId = Math.max(0, ...Array.from(messages.querySelectorAll('[data-comment-id]')).map(item => Number(item.dataset.commentId)));
    let polling = false;
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
    const scrollToLatest = behavior => messages.scrollTo({top: messages.scrollHeight, behavior: behavior || 'smooth'});

    function appendMessage(comment) {
        if (messages.querySelector(`[data-comment-id="${comment.id}"]`)) return;
        const mine = Number(comment.user_id) === currentUserId;
        const row = document.createElement('div');
        row.className = `d-flex mb-3 chat-message-row ${mine ? 'justify-content-end' : 'justify-content-start'}`;
        row.dataset.commentId = comment.id;
        const avatar = mine ? '' : `<div class="avatar avatar-sm flex-shrink-0 me-2 mt-1"><span class="avatar-initial rounded-circle bg-label-info">${escapeHtml(comment.name).charAt(0).toUpperCase()}</span></div>`;
        const roleColor = comment.role === 'mitra' ? 'warning' : 'primary';
        row.innerHTML = `${avatar}<div class="chat-message-wrap ${mine ? 'align-items-end' : 'align-items-start'}"><div class="d-flex align-items-center gap-2 mb-1 ${mine ? 'flex-row-reverse' : ''}"><small class="fw-semibold text-dark">${mine ? 'Anda' : escapeHtml(comment.name)}</small><span class="badge rounded-pill bg-label-${roleColor} chat-role-badge">${escapeHtml(comment.role_label)}</span></div><div class="chat-bubble ${mine ? 'chat-bubble-mine' : 'chat-bubble-other'}"><div>${escapeHtml(comment.message).replace(/\n/g, '<br>')}</div><div class="chat-time ${mine ? 'text-white-50' : 'text-muted'}">${escapeHtml(comment.time)}${mine ? '<i class="bx bx-check-double ms-1"></i>' : ''}</div></div></div>`;
        const empty = messages.querySelector('.chat-empty'); if (empty) empty.remove();
        messages.appendChild(row); lastCommentId = Math.max(lastCommentId, Number(comment.id)); scrollToLatest();
    }

    async function fetchMessages() {
        if (polling || document.hidden) return; polling = true;
        try {
            const response = await fetch(`${messages.dataset.commentsUrl}?after=${lastCommentId}`, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, cache:'no-store'});
            if (response.ok) (await response.json()).comments.forEach(appendMessage);
        } catch (error) { /* koneksi berikutnya akan mencoba kembali */ }
        finally { polling = false; }
    }

    if (form && input) {
        const resizeInput = () => { input.style.height = 'auto'; input.style.height = Math.min(input.scrollHeight, 120) + 'px'; };
        input.addEventListener('input', resizeInput); resizeInput();
        form.addEventListener('submit', async function (event) {
            event.preventDefault(); const message = input.value.trim(); if (!message) return;
            const button = form.querySelector('button[type="submit"]'); button.disabled = true;
            try {
                const response = await fetch(form.action, {method:'POST', headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body:new FormData(form)});
                if (!response.ok) throw new Error('Pesan gagal dikirim.');
                appendMessage((await response.json()).comment); input.value=''; resizeInput(); input.focus();
            } catch (error) { alert(error.message); }
            finally { button.disabled = false; }
        });
    }

    scrollToLatest('auto');
    window.setInterval(fetchMessages, 3000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) fetchMessages(); });
});
</script>
@endpush
@endsection