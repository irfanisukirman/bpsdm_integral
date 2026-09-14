<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kwitansi Booking {{$reservation->booking_code}}</title>
    <style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;color:#25324a;line-height:1.6}h1{font-size:23px;margin-bottom:0}.header{border-bottom:3px solid #344b8e;padding-bottom:16px}.muted{color:#65748b}.status{padding:14px;background:#eef2ff;margin:20px 0}table{width:100%;border-collapse:collapse}td{padding:9px 0;border-bottom:1px solid #e5e7eb;vertical-align:top}.label{width:36%;color:#65748b}.total{font-size:18px;font-weight:bold}.footer{margin-top:30px;font-size:11px;overflow-wrap:break-word}</style>
</head>
<body>
    <div class="header"><strong>BPSDM PROVINSI JAWA BARAT</strong><h1>KWITANSI BOOKING FASILITAS</h1><div>{{$reservation->booking_code}}</div></div>
    <div class="status"><strong>Status reservasi: {{$reservation->status_label}}</strong><br>
        @if(in_array($reservation->status,['confirmed','completed']))
            Pembayaran telah diverifikasi admin.
        @elseif(in_array($reservation->status,['rejected','cancelled']))
            Booking ini tidak berlaku. Hubungi admin untuk tindak lanjut pembayaran.
        @else
            Kwitansi ini merupakan bukti pengajuan booking. Pembayaran belum dinyatakan terverifikasi dan jadwal belum dikonfirmasi admin.
        @endif
    </div>
    <table>
        <tr><td class="label">Tanggal booking</td><td>{{$reservation->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i')}} WIB</td></tr>
        <tr><td class="label">Nama pemohon</td><td>{{$reservation->full_name}}</td></tr>
        <tr><td class="label">Komunitas / instansi</td><td>{{$reservation->organization ?: '-'}}</td></tr>
        <tr><td class="label">Fasilitas</td><td>{{$reservation->asset?->name}}</td></tr>
        <tr><td class="label">Tanggal penggunaan</td><td>{{$reservation->rental_date->translatedFormat('d F Y')}}</td></tr>
        <tr><td class="label">Jam penggunaan</td><td>{{substr($reservation->start_time,0,5)}} – {{substr($reservation->end_time,0,5)}} WIB ({{$reservation->duration_hours}} jam)</td></tr>
        <tr><td class="label">Rincian tarif</td><td>
            @forelse($reservation->rate_breakdown ?? [] as $rate)
                <div>{{substr($rate['start'],0,5)}}–{{substr($rate['end'],0,5)}} WIB: {{$rate['hours']}} jam × Rp {{number_format($rate['rate'],0,',','.')}} = Rp {{number_format($rate['subtotal'],0,',','.')}}</div>
            @empty
                Rp {{number_format($reservation->hourly_rate,0,',','.')}} per jam
            @endforelse
        </td></tr>
        <tr><td class="label">Total booking</td><td class="total">Rp {{number_format($reservation->total_amount,0,',','.')}}</td></tr>
        <tr><td class="label">Bukti pembayaran diunggah</td><td>{{$reservation->payment_uploaded_at?->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') ?: 'Belum tersedia'}}</td></tr>
    </table>
    <div class="footer">Simpan dan bawa kwitansi ini saat datang menggunakan fasilitas. Periksa status terbaru melalui tiket {{$reservation->booking_code}} pada halaman pelacakan reservasi.<br><a href="{{route('public.asset-rentals.status',$reservation->public_token)}}">Lacak status booking</a><p class="muted">Dicetak: {{now()->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i')}} WIB</p></div>
</body>
</html>
