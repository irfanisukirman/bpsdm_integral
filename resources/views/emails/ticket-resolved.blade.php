<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tiket Hotline {{ $statusLabel }}</title>
    <style>
        body{margin:0;padding:0;background:#f4f6fb;font-family:'Segoe UI',Arial,sans-serif;color:#22304a;}
        .wrap{max-width:600px;margin:24px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(34,48,74,.1);}
        .header{background:linear-gradient(135deg,#16a34a,#4ade80);padding:32px;color:#fff;text-align:center;}
        .header h1{margin:0;font-size:22px;}
        .header p{margin:8px 0 0;color:rgba(255,255,255,.9);font-size:13px;}
        .body{padding:32px;}
        .ticket-box{display:block;border:2px dashed #16a34a;border-radius:12px;padding:20px;text-align:center;background:#f0fdf4;margin:24px 0;}
        .ticket-box .label{font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:1px;}
        .ticket-box .number{font-size:24px;font-weight:800;color:#166534;margin-top:6px;}
        .btn{display:inline-block;background:#16a34a;color:#fff!important;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;font-size:14px;}
        .footer{padding:20px 32px;background:#f8f9ff;color:#718096;font-size:12px;text-align:center;border-top:1px solid #eef1f6;}
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <h1>Hotline Integral</h1>
            <p>Tiket Anda telah {{ strtolower($statusLabel) }}</p>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $ticket->submitter_name }}</strong>,</p>
            <p>Kami informasikan bahwa tiket aduan Anda telah di-<strong>{{ strtolower($statusLabel) }}</strong> oleh tim kami.</p>
            <div class="ticket-box">
                <div class="label">Nomor Tiket</div>
                <div class="number">{{ $ticket->ticket_number }}</div>
            </div>
            <p>Terima kasih telah menggunakan layanan Hotline Integral.</p>
            <p style="text-align:center;margin:24px 0;">
                <a class="btn" href="{{ url('hotline/tracking/'.$ticket->tracking_token) }}">Lihat Detail Tiket</a>
            </p>
        </div>
        <div class="footer">
            Sistem Hotline Integral · BPSDM Provinsi Jawa Barat
        </div>
    </div>
</body>
</html>