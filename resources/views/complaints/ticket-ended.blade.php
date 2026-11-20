<div class="p-3 border-top" data-complaint-ticket-ended @unless($ticket->isResolved()) hidden @endunless>
    <p class="mb-0">{{ $ticket->chat_name ? 'Percakapan ini sudah berakhir.' : 'Pengaduan ini sudah selesai.' }}</p>
    @unless($staff)
        @if($ticket->chat_name)
            <a class="btn btn-primary btn-sm mt-2" data-complaint-chat-new @unless($online) hidden @endunless
                href="{{ route($guest ? 'complaints.guest.create' : 'complaints.create', ['widget' => $widget ? 1 : null, 'mode' => 'chat']) }}">Mulai Chat Baru</a>
            <a class="btn btn-outline-primary btn-sm mt-2" data-complaint-chat-report @if($online) hidden @endif
                href="{{ route($guest ? 'complaints.guest.create' : 'complaints.create', ['widget' => $widget ? 1 : null, 'mode' => 'report']) }}">Ajukan Pengaduan</a>
        @else
            <a class="btn btn-primary btn-sm mt-2"
                href="{{ route($guest ? 'complaints.guest.create' : 'complaints.create', ['widget' => $widget ? 1 : null, 'mode' => 'report']) }}">Ajukan Pengaduan Baru</a>
        @endif
    @endunless
</div>
