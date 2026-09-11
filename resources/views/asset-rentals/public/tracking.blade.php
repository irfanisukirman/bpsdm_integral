<section class="border rounded p-4 mb-4" aria-label="Perjalanan reservasi">
    <h5 class="fw-bold">Tracking Booking</h5>
    <p class="text-muted small">Booking & bukti pembayaran dikirim → Verifikasi admin → Dikonfirmasi → Selesai</p>
    <ol class="booking-timeline list-unstyled mb-0">
        <li class="pb-3"><strong>Booking diterima</strong><small class="d-block text-muted">{{$reservation->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i')}} WIB</small></li>
        @foreach($reservation->trackingTimeline() as $event)
            <li class="pb-3 {{$loop->last ? 'current' : ''}}">
                <strong>{{$event['label']}}</strong>
                @if($event['at'])<small class="d-block text-muted">{{\Carbon\Carbon::parse($event['at'])->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i')}} WIB</small>@endif
                @if($event['note'])<p class="small mb-0 mt-1">{{$event['note']}}</p>@endif
                @if($loop->last)<span class="badge bg-label-{{$reservation->status_color}} mt-1">Status saat ini</span>@endif
            </li>
        @endforeach
    </ol>
</section>
<style>.booking-timeline{margin-left:8px;padding-left:24px;border-left:2px solid #d9dee3}.booking-timeline li{position:relative}.booking-timeline li:before{content:'';position:absolute;left:-31px;top:5px;width:12px;height:12px;border-radius:50%;background:#8592a3;border:2px solid white}.booking-timeline li.current:before{background:#696cff;box-shadow:0 0 0 3px #e7e7ff}.booking-timeline li:last-child{padding-bottom:0!important}</style>
