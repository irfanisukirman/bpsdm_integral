@extends($widget ? 'layouts.complaint-widget' : ($guest ? 'layouts.auth' : 'layouts.master'))
@section('integral_theme', 'light')
@section('title', 'Tiket '.$ticket->reference())
@if($guest && !$widget)
    @include('complaints.guest-styles')
@endif

@section('content')
<main class="container py-4" style="max-width:900px"
    data-complaint-updates="{{ $guest ? route('complaints.guest.updates', $token) : route('complaints.updates', $ticket) }}"
    data-complaint-last-message="{{ $ticket->messages->last()?->id ?? 0 }}">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start mb-3">
        <div>
            <h1 class="h4 mb-1">{{ $ticket->chat_name ? 'Chat dengan Admin' : \App\Models\ComplaintTicket::CATEGORIES[$ticket->category] }}</h1>
            @if($staff && $ticket->chat_name)
                <p class="mb-1"><strong>{{ $ticket->chat_name }}</strong> · {{ $ticket->chat_institution }}</p>
            @endif
            <p class="text-muted mb-1">{{ $ticket->reference() }} @if($ticket->training) · {{ $ticket->training->nama_pelatihan }} @endif</p>
            <span class="badge bg-label-primary" data-complaint-ticket-status>{{ \App\Models\ComplaintTicket::STATUSES[$ticket->status] }}</span>
            @if(!$staff && !$reportOnly)
                <p class="small text-muted mb-0 mt-2" data-complaint-presence role="status">{{ $online ? 'Admin online · Chat tersedia' : 'Admin offline · Pengaduan tetap diterima' }}</p>
            @endif
        </div>
        <a class="btn btn-outline-secondary" href="{{ route($guest ? 'complaints.guest.track' : ($staff ? 'complaints.inbox' : 'complaints.index'), ['widget' => $widget ? 1 : null]) }}">
            {{ $guest ? 'Lacak Tiket Lain' : 'Kembali' }}
        </a>
    </div>
    @include('complaints.feedback')
    <div class="alert alert-warning" data-complaint-unavailable role="status" hidden>Tiket ini sudah tidak tersedia.</div>
    @if(($canManage ?? false) && !$widget)
        <section class="card mb-3"><div class="card-body">
            <h2 class="h6">Teruskan Pengaduan</h2>
            <form method="POST" action="{{ route('complaints.forward', $ticket) }}" class="d-flex flex-wrap align-items-end gap-2">
                @csrf
                @method('PATCH')
                <div>
                    <label for="complaint-assignee" class="form-label">Admin penanggung jawab</label>
                    <select id="complaint-assignee" name="assigned_to" class="form-select" required>
                        <option value="">Pilih admin</option>
                        @foreach($staffOptions as $admin)
                            <option value="{{ $admin->id }}" @selected((int) old('assigned_to', $ticket->assigned_to) === $admin->id)>{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-outline-primary">Teruskan</button>
            </form>
            @if(auth()->user()->role === 'superadmin')
                <form class="mt-3" method="POST" action="{{ route('complaints.destroy', $ticket) }}"
                    onsubmit="return confirm('Hapus tiket ini beserta percakapan dan lampirannya? Tiket juga hilang dari riwayat dan pelacakan pengirim.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger"><i class="bx bx-trash me-1" aria-hidden="true"></i>Hapus Pengaduan</button>
                </form>
            @endif
        </div></section>
    @endif
    @if($guest)
        <section class="card mb-3"><div class="card-body">
            <h2 class="h6">Simpan kode pelacakan</h2>
            <p class="text-muted">Simpan secara privat. Siapa pun yang memiliki kode ini dapat membuka tiket dan melihat balasannya.</p>
            <div class="input-group">
                <input class="form-control font-monospace" aria-label="Kode pelacakan" readonly value="{{ $token }}" id="complaint-tracking-code">
                <button class="btn btn-outline-primary" id="complaint-copy-link" type="button" data-url="{{ route('complaints.guest.show', $token) }}">Salin Tautan</button>
            </div>
            <small id="complaint-copy-feedback" class="d-block mt-2 text-muted" role="status"></small>
        </div></section>
        <script>
            document.getElementById('complaint-copy-link').addEventListener('click', async event => {
                const feedback = document.getElementById('complaint-copy-feedback');
                try {
                    await navigator.clipboard.writeText(event.currentTarget.dataset.url);
                    feedback.textContent = 'Tautan pelacakan disalin.';
                } catch {
                    document.getElementById('complaint-tracking-code').select();
                    feedback.textContent = 'Salin kode yang dipilih, atau salin tautan dari bilah alamat.';
                }
            });
        </script>
    @endif
    <section class="card mb-3">
        <div class="card-body">
            <h2 class="h5 mb-3">Percakapan</h2>
            <ol @class(['complaint-chat', 'complaint-chat--conversation' => $ticket->chat_name]) data-complaint-messages aria-live="polite">
                @include('complaints.messages')
            </ol>
        </div>
    </section>
    @include('complaints.ticket-ended')
    @unless($reportOnly)
    <section class="card"><div class="card-body">
        @unless($staff)
            <div data-complaint-offline @if($online || $ticket->isResolved()) hidden @endif>
                <p class="text-muted">Admin sedang offline. Anda bisa memantau tiket ini hingga ada balasan atau mengirim pengaduan baru.</p>
                <a href="{{ route($guest ? 'complaints.guest.create' : 'complaints.create', ['widget' => $widget ? 1 : null]) }}" class="btn btn-outline-primary">Buat Pengaduan</a>
            </div>
        @endunless
        <form method="POST" enctype="multipart/form-data" action="{{ $guest ? route('complaints.guest.reply', ['token' => $token, 'widget' => $widget ? 1 : null]) : route('complaints.reply', ['ticket' => $ticket, 'widget' => $widget ? 1 : null]) }}"
            data-complaint-reply @if($ticket->isResolved() || (!$staff && !$online)) hidden @endif>
            @csrf
            @include('complaints.message-fields', ['messageLabel' => 'Balasan'])
            @if($staff)
                <div class="mb-3">
                    <label for="complaint-status" class="form-label">Status tiket</label>
                    <select class="form-select" id="complaint-status" name="status">
                        @foreach(\App\Models\ComplaintTicket::STATUSES as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $ticket->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <button type="submit" class="btn btn-primary">{{ $staff ? 'Simpan Tanggapan' : 'Kirim Balasan' }}</button>
            <p class="small mb-0 mt-2" data-complaint-reply-feedback role="status"></p>
        </form>
    </div></section>
    @endunless
</main>
@endsection
