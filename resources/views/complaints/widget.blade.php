@php
    $complaintGuest = auth()->guest();
    $complaintReplyAlerts = !$complaintGuest && !in_array(auth()->user()->role, \App\Models\ComplaintTicket::STAFF_ROLES, true);
    $complaintTraining = request()->route('training');
    if ($complaintTraining instanceof \App\Models\Training) {
        $complaintTraining = $complaintTraining->id;
    } elseif (request()->routeIs('trainings.manage', 'trainings.schedules')) {
        $complaintTraining = request()->route('id');
    }
    $complaintCreateRoute = $complaintGuest ? 'complaints.guest.create' : 'complaints.create';
    $complaintCreateParams = ['widget' => 1, 'training_id' => !$complaintGuest && is_numeric($complaintTraining) ? $complaintTraining : null];
@endphp
<details class="complaint-widget" @if($complaintReplyAlerts) data-complaint-unread="{{ route('complaints.unread-replies') }}" @endif
    data-complaint-availability="{{ route($complaintGuest ? 'complaints.guest.availability' : 'complaints.availability') }}">
    <summary class="btn btn-primary complaint-launcher shadow-sm" aria-label="Buka bantuan Integral" title="Bantuan Integral">
        <i class="bx bx-message-rounded-dots" aria-hidden="true"></i>
        @if($complaintReplyAlerts)
            <span class="badge bg-danger rounded-pill" data-complaint-reply-count hidden></span>
        @endif
    </summary>
    <div class="card complaint-widget-panel">
        <div class="complaint-widget-head">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="complaint-window-button" data-complaint-back aria-label="Kembali ke pilihan bantuan" hidden>
                    <i class="bx bx-chevron-left" aria-hidden="true"></i>
                </button>
                <div>
                    <strong>Bantuan Integral</strong>
                    <div class="small" data-complaint-presence role="status">Memeriksa ketersediaan admin…</div>
                </div>
            </div>
            <button type="button" class="complaint-window-button" data-complaint-close aria-label="Tutup bantuan">
                <i class="bx bx-x" aria-hidden="true"></i>
            </button>
        </div>
        <div class="card-body" data-complaint-menu>
            <p class="fw-semibold mb-3">Ada yang bisa kami bantu?</p>
            <div class="d-grid gap-2">
                <a href="{{ route($complaintCreateRoute, $complaintCreateParams + ['mode' => 'chat']) }}" target="integral-complaint-frame"
                    class="btn btn-primary" data-complaint-frame-link data-complaint-chat-option hidden>
                    <i class="bx bx-message-rounded-dots me-1" aria-hidden="true"></i>Chat dengan Admin
                </a>
                <a href="{{ route($complaintCreateRoute, $complaintCreateParams + ['mode' => 'report']) }}" target="integral-complaint-frame"
                    class="btn btn-primary" data-complaint-frame-link data-complaint-report-option>
                    <i class="bx bx-edit me-1" aria-hidden="true"></i>Ajukan Pengaduan
                </a>
            </div>
            <div class="d-flex flex-wrap gap-3 mt-3">
                <a href="{{ route($complaintGuest ? 'complaints.guest.track' : 'complaints.index', ['widget' => 1]) }}" target="integral-complaint-frame"
                    class="small" data-complaint-frame-link>{{ $complaintGuest ? 'Lacak Tiket' : 'Riwayat Pengaduan' }}</a>
            </div>
        </div>
        <iframe name="integral-complaint-frame" title="Percakapan Bantuan Integral" class="complaint-widget-frame" hidden></iframe>
    </div>
</details>
