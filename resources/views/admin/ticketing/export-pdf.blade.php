<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Tiket Hotline - {{ now()->format('d/m/Y') }}</title>
    <style>
        body{font-family:'Segoe UI',Arial,sans-serif;color:#22304a;font-size:11px;}
        h1{font-size:18px;margin:0 0 4px;}
        .sub{color:#718096;margin-bottom:16px;}
        table{width:100%;border-collapse:collapse;margin-top:12px;}
        th,td{border:1px solid #d9dee3;padding:6px 8px;text-align:left;}
        th{background:#f1f3fa;font-size:10px;text-transform:uppercase;}
        .badge{padding:2px 6px;border-radius:4px;font-size:9px;font-weight:700;}
        .foot{margin-top:16px;color:#718096;font-size:10px;}
    </style>
</head>
<body>
    <h1>Laporan Tiket Hotline</h1>
    <div class="sub">BPSDM Provinsi Jawa Barat · Dicetak {{ now()->translatedFormat('d F Y H:i') }}</div>
    <p>Total tiket: <strong>{{ $tickets->count() }}</strong></p>
    <table>
        <thead>
            <tr>
                <th>No</th><th>No. Tiket</th><th>Tanggal</th><th>Pengaju</th><th>Layanan</th><th>Kategori</th><th>Status</th><th>Bidang</th><th>PIC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $i => $t)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $t->ticket_number }}</td>
                <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $t->submitter_name }}</td>
                <td>{{ ucwords(str_replace('-',' ',$t->service)) }}</td>
                <td>{{ ucwords(str_replace('-',' ',$t->category)) }}</td>
                <td><span class="badge" style="background:#eef1f6;">{{ $t->status_label }}</span></td>
                <td>{{ $t->bidang }}</td>
                <td>{{ $t->assignee?->name ?? '-' }}</td>
            </tr>
            @endforeach
            @if($tickets->isEmpty())
            <tr><td colspan="9" style="text-align:center;color:#718096;">Tidak ada data.</td></tr>
            @endif
        </tbody>
    </table>
    <div class="foot">Dicetak melalui Sistem Hotline Integral.</div>
</body>
</html>