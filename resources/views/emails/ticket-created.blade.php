<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tiket Hotline Diterima</title>
    <style>
        body{margin:0;padding:0;background:#f4f6fb;font-family:'Segoe UI',Arial,sans-serif;color:#22304a;}
        .wrap{max-width:600px;margin:24px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(34,48,74,.1);}
        .header{background:linear-gradient(135deg,#293c9e,#5966e9);padding:32px;color:#fff;text-align:center;}
        .header h1{margin:0;font-size:22px;}
        .header p{margin:8px 0 0;color:rgba(255,255,255,.8);font-size:13px;}
        .body{padding:32px;}
        .ticket-box{display:block;border:2px dashed #5966e9;border-radius:12px;padding:20px;text-align:center;background:#f8f9ff;margin:24px 0;}
        .ticket-box .label{font-size:12px;color:#718096;text-transform:uppercase;letter-spacing:1px;}
        .ticket-box .number{font-size:28px;font-weight:800;color:#293c9e;margin-top:6px;}
        .meta{margin:20px 0;}
        .meta .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #eef1f6;}
        .meta .k{color:#718096;font-size:13px;}
        .meta .v{font-weight:600;font-size:13px;text-align:right;}
        .btn{display:inline-block;background:#5966e9;color:#fff!important;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;font-size:14px;}
        .footer{padding:20px 32px;background:#f8f9ff;color:#718096;font-size:12px;text-align:center;border-top:1px solid #eef1f6;}
    </style>
</head>
<body>
    <div class="wrap">
        <div class="header">
            <h1>Hotline Integral</h1>
            <p>Tiket aduan Anda telah diterima</p>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $ticket->submitter_name }}</strong>,</p>
            <p>Aduan Anda berhasil kami terima dan akan segera ditangani oleh tim yang berwenang.</p>
            <div class="ticket-box">
                <div class="label">Nomor Tiket</div>
                <div class="number">{{ $ticket->ticket_number }}</div>
            </div>
            <div class="meta">
                <div class="row"><span class="k">Layanan</span><span class="v">{{ ucwords(str_replace('-',' ',$ticket->service)) }}</span></div>
                <div class="row"><span class="k">Kategori</span><span class="v">{{ ucwords(str_replace('-',' ',$ticket->category)) }}</span></div>
                <div class="row"><span class="k">Status</span><span class="v">Baru</span></div>
                <div class="row"><span class="k">Bidang</span><span class="v">{{ $ticket->bidang }}</span></div>
            </div>
            <p>Anda dapat melacak perkembangan tiket melalui tautan di bawah ini:</p>
            <p style="text-align:center;margin:24px 0;">
                <a class="btn" href="{{ url('hotline/tracking/'.$ticket->tracking_token) }}">Lacak Tiket</a>
            </p>
            <p style="font-size:13px;color:#718096;">Jika tombol tidak berfungsi, salin tautan berikut:<br><code>{{ url('hotline/tracking/'.$ticket->tracking_token) }}</code></p>
        </div>
        <div class="footer">
            Sistem Hotline Integral · BPSDM Provinsi Jawa Barat
        </div>
    </div>
</body>
</html>