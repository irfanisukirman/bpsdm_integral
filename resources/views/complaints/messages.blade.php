@php
    $chatConversation = filled($ticket->chat_name);
    $previousDay = $previousDay ?? null;
@endphp
@foreach($ticket->messages as $message)
    @php
        $sentAt = $message->created_at->timezone('Asia/Jakarta')->locale('id');
    @endphp
    @if($chatConversation && $previousDay !== $sentAt->toDateString())
        @include('complaints.chat-date', ['date' => $sentAt])
        @php $previousDay = $sentAt->toDateString(); @endphp
    @endif
    <li @class(['complaint-bubble', 'is-own' => $message->from_staff === $staff])>
        @if($chatConversation)
            <span class="complaint-message-avatar" aria-hidden="true">
                <i @class(['bx', 'bx-support' => $message->from_staff, 'bx-user' => !$message->from_staff])></i>
            </span>
            <span class="visually-hidden">{{ $message->from_staff === $staff ? 'Anda' : ($staff ? 'Pengguna' : 'Admin') }}</span>
        @else
        <div class="fw-semibold small">
            @if($message->from_staff)
                {{ $staff ? ($message->author?->name ?? 'Admin Integral') : 'Admin Integral' }}
            @else
                {{ $staff ? ($ticket->chat_name ?? ($ticket->user?->name ?: ($ticket->guest_name ?: 'Tamu'))) : 'Anda' }}
            @endif
        </div>
        @endif
        @if(filled($message->body))
            <p class="complaint-message-body mb-1">{{ $message->body }}</p>
        @endif
        @if($message->attachment)
            @php
                $attachmentRoute = $guest ? 'complaints.guest.attachment' : 'complaints.attachment';
                $attachmentParams = [$guest ? $token : $ticket, $message->id];
            @endphp
            <a class="complaint-image-link mb-1" href="{{ route($attachmentRoute, $attachmentParams) }}" aria-label="Unduh gambar">
                <img src="{{ route($attachmentRoute, [...$attachmentParams, 'preview' => 1]) }}" alt="Gambar lampiran" loading="lazy">
            </a>
        @endif
        <time class="complaint-message-time" datetime="{{ $sentAt->toIso8601String() }}">
            {{ $chatConversation ? $sentAt->format('H:i') : $sentAt->translatedFormat('d M Y H:i').' WIB' }}
        </time>
    </li>
@endforeach
