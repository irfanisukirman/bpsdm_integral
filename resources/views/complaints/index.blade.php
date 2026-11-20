@extends($widget ? 'layouts.complaint-widget' : 'layouts.master')
@section('title', $staff ? 'Kotak Masuk Pengaduan' : 'Riwayat Pengaduan')

@section('content')
@php
    $summary = [
        'received' => ['Masuk', 'warning', 'bx-inbox'],
        'in_progress' => ['Diproses', 'info', 'bx-time-five'],
        'resolved' => ['Selesai', 'success', 'bx-check-circle'],
    ];
@endphp
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">{{ $staff ? 'Kotak Masuk Pengaduan' : 'Riwayat Pengaduan' }}</h1>
    @if($staff)
        <a href="{{ route('complaints.export', ['status' => isset($summary[$status]) ? $status : null]) }}" class="btn btn-outline-primary">
            <i class="bx bx-download me-1" aria-hidden="true"></i>Ekspor Excel
        </a>
    @else
        <a href="{{ route('complaints.create', ['widget' => $widget ? 1 : null]) }}" class="btn btn-primary">Buat Pengaduan</a>
    @endif
</div>
@include('complaints.feedback')
@if($staff)
    <nav class="row g-2 g-sm-3 mb-3" aria-label="Ringkasan status pengaduan">
        @foreach($summary as $key => [$label, $tone, $icon])
            <div class="col-4">
                <a href="{{ route('complaints.inbox', ['status' => $status === $key ? null : $key]) }}"
                    @class(['card complaint-summary-card h-100 text-reset', 'border-primary' => $status === $key])
                    @if($status === $key) aria-current="page" @endif>
                    <div class="card-body p-2 p-sm-3 d-flex flex-wrap align-items-center gap-2">
                        <span class="badge bg-label-{{ $tone }} p-2"><i class="bx {{ $icon }} fs-5" aria-hidden="true"></i></span>
                        <div>
                            <span class="d-block small">{{ $label }}</span>
                            <strong class="fs-4 d-block lh-sm">{{ number_format($statusCounts[$key] ?? 0, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </nav>
@endif
<div class="card">
    <div class="card-body">
        <form method="GET" class="d-flex flex-wrap align-items-end gap-2">
            @if($widget)<input type="hidden" name="widget" value="1">@endif
            <div>
                <label class="form-label" for="complaints-status">Status</label>
                <select class="form-select" id="complaints-status" name="status">
                    <option value="">Semua status</option>
                    @foreach(\App\Models\ComplaintTicket::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-outline-primary" type="submit">Tampilkan</button>
        </form>
    </div>
    <div @class(['table-responsive', 'complaint-inbox-table' => $staff]) @if($staff) data-complaint-staff-inbox @endif>
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Tiket</th><th>Kategori</th>@if($staff)<th>Pengirim</th><th>Ditangani oleh</th>@endif<th>Status</th><th>Terakhir diperbarui</th>@if($staff)<th>Aksi</th>@endif</tr></thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr @class(['complaint-inbox-received' => $staff && $ticket->status === 'received'])>
                        <td>
                            <a href="{{ route('complaints.show', ['ticket' => $ticket, 'widget' => $widget ? 1 : null]) }}" class="fw-bold">
                                {{ $ticket->reference() }}
                                @if(!$staff && $ticket->chat_name)
                                    <small class="d-block">{{ $ticket->isResolved() ? 'Lihat Riwayat Chat' : 'Buka Chat' }}</small>
                                @endif
                            </a>
                        </td>
                        <td>{{ $ticket->chat_name ? 'Chat dengan Admin' : \App\Models\ComplaintTicket::CATEGORIES[$ticket->category] }}
                            @if($ticket->training)<small class="d-block text-muted">{{ $ticket->training->nama_pelatihan }}</small>@endif
                        </td>
                        @if($staff)
                            <td>
                                {{ $ticket->chat_name ?? ($ticket->user?->name ?: ($ticket->guest_name ?: 'Tamu')) }}
                                @if($ticket->chat_institution)
                                    <div class="small text-muted">{{ $ticket->chat_institution }}</div>
                                @endif
                            </td>
                            <td>{{ $ticket->assignee?->name ?? 'Belum diteruskan' }}</td>
                        @endif
                        <td>
                            @if($staff)
                                @php [$label, $tone, $icon] = $summary[$ticket->status]; @endphp
                                <span class="badge bg-label-{{ $tone }}"><i class="bx {{ $icon }} me-1" aria-hidden="true"></i>{{ $label }}</span>
                            @else
                                <span class="badge bg-label-primary">{{ \App\Models\ComplaintTicket::STATUSES[$ticket->status] }}</span>
                            @endif
                        </td>
                        <td>{{ $ticket->updated_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y H:i') }} WIB</td>
                        @if($staff)
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <a class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" href="{{ route('complaints.show', ['ticket' => $ticket, 'view' => $ticket->chat_name ? 'chat' : null]) }}">
                                        {{ $ticket->chat_name ? 'Chat' : 'Balas' }}
                                        @if($ticket->chat_name)
                                            <span class="complaint-chat-unread" data-complaint-unread-ticket="{{ $ticket->id }}"
                                                @unless(isset($unreadChatIds[$ticket->id])) hidden @endunless>Belum dibaca</span>
                                        @endif
                                    </a>
                                    @if($ticket->status !== 'resolved')
                                        <form method="POST" action="{{ route('complaints.resolve', $ticket) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Selesai</button>
                                        </form>
                                    @endif
                                    @if(auth()->user()->role === 'superadmin')
                                        <form method="POST" action="{{ route('complaints.destroy', $ticket) }}"
                                            onsubmit="return confirm('Hapus tiket ini beserta semua pesan dan lampirannya secara permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $staff ? 7 : 4 }}" class="py-4 text-center text-muted">Belum ada pengaduan untuk status ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tickets->hasPages())<div class="card-footer">{{ $tickets->links() }}</div>@endif
</div>
@endsection
