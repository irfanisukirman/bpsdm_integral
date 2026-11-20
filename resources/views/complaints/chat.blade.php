@extends(($staff ?? false) ? 'layouts.master' : 'layouts.complaint-widget')
@section('title', ($staff ?? false) ? 'Chat Pengaduan' : 'Chat dengan Admin')

@section('content')
@php
    $started = isset($ticket);
    $staff = $staff ?? false;
    $questions = [
        'chat_name' => 'Siapa nama Kaka?',
        'chat_institution' => 'Baik, Kaka {nama}. Selanjutnya, sebutkan instansi Kaka.',
        'message' => 'Silakan ketik keluhan Kaka. Admin akan melanjutkan percakapan di sini.',
    ];
    $answers = [
        'chat_name' => $started ? $ticket->chat_name : old('chat_name'),
        'chat_institution' => $started ? $ticket->chat_institution : old('chat_institution'),
    ];
    $step = !$answers['chat_name'] ? 'chat_name' : (!$answers['chat_institution'] ? 'chat_institution' : 'message');
@endphp
@if($staff)
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">{{ $ticket->chat_name }}</h1>
            <p class="text-muted mb-0">{{ $ticket->chat_institution }} · {{ $ticket->reference() }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" href="{{ route('complaints.show', $ticket) }}">Detail Tiket</a>
            <a class="btn btn-outline-secondary" href="{{ route('complaints.inbox') }}">Kembali ke Kotak Masuk</a>
        </div>
    </div>
@endif
<main @class(['complaint-live', 'complaint-live--staff card' => $staff])
    @if($staff) data-complaint-staff-room="{{ $ticket->id }}" @endif
    @if($started)
        data-complaint-updates="{{ $guest ? route('complaints.guest.updates', $token) : route('complaints.updates', $ticket) }}"
        data-complaint-last-message="{{ $ticket->messages->last()?->id ?? 0 }}"
    @endif>
    <div class="complaint-live-meta small">
        @if($started)
            <span data-complaint-ticket-status>{{ \App\Models\ComplaintTicket::STATUSES[$ticket->status] }}</span>
            @if($guest)
                <details>
                    <summary>Simpan kode pelacakan</summary>
                    <input class="form-control form-control-sm" aria-label="Kode pelacakan" value="{{ $token }}" readonly>
                    <a href="{{ route('complaints.guest.show', $token) }}" target="_blank" rel="noopener">Buka tiket di tab baru</a>
                </details>
            @endif
        @else
            <span>Chat dengan Admin</span>
        @endif
    </div>
    <ol class="complaint-chat complaint-chat--conversation complaint-live-scroll" data-complaint-messages aria-live="polite" aria-label="Percakapan">
        @php $firstChatDate = $started ? $ticket->messages->first()?->created_at?->timezone('Asia/Jakarta') : null; @endphp
        @if($firstChatDate)
            @include('complaints.chat-date', ['date' => $firstChatDate])
        @endif
        @unless($staff)
        <li class="complaint-bubble" @if(!$started && $step === 'chat_name') data-complaint-intro hidden @endif>
            <span class="complaint-message-avatar" aria-hidden="true"><i class="bx bx-support"></i></span>
            <p class="mb-0">Selamat datang, Kaka! Saya akan meminta nama dan instansi Kaka agar admin bisa menindaklanjuti keluhan Kaka.</p>
        </li>
        @foreach($questions as $key => $question)
            <li class="complaint-bubble" @if(!$started && $step === 'chat_name' && $key === 'chat_name') data-complaint-first-question hidden @endif>
                <span class="complaint-message-avatar" aria-hidden="true"><i class="bx bx-support"></i></span>
                <p class="mb-0">{{ str_replace('{nama}', $answers['chat_name'] ?? '', $question) }}</p>
            </li>
            @if($key === $step)
                @break
            @endif
            <li class="complaint-bubble is-own">
                <span class="complaint-message-avatar" aria-hidden="true"><i class="bx bx-user"></i></span>
                <p class="mb-0">{{ $answers[$key] }}</p>
            </li>
        @endforeach
        @endunless
        @if($started)
            @include('complaints.messages', ['previousDay' => $firstChatDate?->toDateString()])
        @endif
    </ol>
    @include('complaints.feedback')
    <div class="alert alert-warning m-2" data-complaint-unavailable role="status" hidden>Tiket ini sudah tidak tersedia.</div>
    @if($started)
        @include('complaints.ticket-ended')
    @endif
    @if($started && !$staff)
        <p class="small m-2" data-complaint-offline @if($online || $ticket->isResolved()) hidden @endif>Admin sedang offline. Balasan akan muncul di sini.</p>
    @endif
    @php
        $action = $started
            ? ($guest ? route('complaints.guest.reply', ['token' => $token, 'widget' => 1]) : route('complaints.reply', ['ticket' => $ticket, 'widget' => $staff ? null : 1, 'view' => $staff ? 'chat' : null]))
            : route($guest ? 'complaints.guest.store' : 'complaints.store', ['widget' => 1, 'mode' => 'chat']);
        $placeholder = $started ? 'Ketik pesan…' : match ($step) {
            'chat_name' => 'Ketik nama Kaka…',
            'chat_institution' => 'Ketik nama instansi Kaka…',
            default => 'Ketik keluhan Kaka…',
        };
        $ready = $step === 'message';
    @endphp
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="complaint-live-form"
        @if($started)
            data-complaint-reply @if($ticket->isResolved() || (!$staff && !$online)) hidden @endif
        @else
            data-complaint-chat-start="{{ $step }}" data-complaint-chat-questions="{{ json_encode($questions) }}"
        @endif>
        @csrf
        @unless($started)
            <input type="hidden" name="chat_name" value="{{ $answers['chat_name'] }}">
            <input type="hidden" name="chat_institution" value="{{ $answers['chat_institution'] }}">
        @endunless
        <div class="complaint-image-preview" data-complaint-image-preview hidden>
            <img alt="Pratinjau gambar">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-complaint-image-remove>Hapus gambar</button>
        </div>
        <div class="small text-danger" data-complaint-image-error role="status"></div>
        <div class="complaint-emoji-picker" data-complaint-emoji-picker aria-label="Pilihan emoji" role="group" hidden>
            @foreach(['😊', '🙏', '👍', '🙂', '👋', '❤️', '🎉', '😂', '🤔', '😅', '😢', '✅'] as $emoji)
                <button class="btn btn-sm" type="button" data-complaint-emoji="{{ $emoji }}">{{ $emoji }}</button>
            @endforeach
        </div>
        <div class="complaint-live-input">
            <textarea name="message" class="form-control" rows="1" maxlength="{{ $ready ? 5000 : ($step === 'chat_name' ? 100 : 150) }}"
                aria-label="{{ $placeholder }}" placeholder="{{ $placeholder }}" data-complaint-image-paste>{{ $ready ? old('message') : '' }}</textarea>
            <div class="complaint-live-tools" data-complaint-chat-tools @unless($ready) hidden @endunless>
                <button type="button" class="btn btn-icon btn-outline-primary" title="Lampirkan gambar" aria-label="Lampirkan gambar" data-complaint-chat-attachment>
                    <i class="bx bx-paperclip" aria-hidden="true"></i>
                </button>
                <input type="file" name="attachment" aria-label="Lampirkan gambar" accept="image/jpeg,image/png,image/webp" hidden @unless($ready) disabled @endunless>
                <button type="button" class="btn btn-icon btn-outline-primary" title="Pilih emoji" aria-label="Pilih emoji"
                    aria-expanded="false" data-complaint-emoji-toggle>
                    <i class="bx bx-smile" aria-hidden="true"></i>
                </button>
            </div>
            <button type="submit" class="btn btn-icon btn-primary" aria-label="Kirim" title="Kirim"><i class="bx bx-send" aria-hidden="true"></i></button>
        </div>
        <div class="small" data-complaint-reply-feedback role="status"></div>
    </form>
</main>
@endsection
