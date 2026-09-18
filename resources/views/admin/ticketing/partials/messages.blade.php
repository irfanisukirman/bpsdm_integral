@php $user = $user ?? Auth::user(); @endphp
@forelse($messages as $msg)
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