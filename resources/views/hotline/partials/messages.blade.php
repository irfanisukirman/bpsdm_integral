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