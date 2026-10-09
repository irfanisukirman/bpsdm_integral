<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 18mm 15mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h2 { margin: 0 0 4px; text-align: center; font-size: 16px; }
        .subtitle { text-align: center; margin-bottom: 14px; }
        .meta { width: 100%; margin-bottom: 15px; }
        .meta td { padding: 3px; }
        .date-title { margin: 14px 0 5px; padding: 7px 9px; background: #e9eefc; border-left: 4px solid #355ec9; font-size: 11px; }
        .data { width: 100%; border-collapse: collapse; page-break-inside: auto; }
        .data th, .data td { border: 1px solid #555; padding: 6px; vertical-align: top; }
        .data th { background: #f0f3f8; }
        .data tr { page-break-inside: avoid; }
        .footer { width: 100%; margin-top: 24px; text-align: center; page-break-inside: avoid; }
        .footer td { width: 50%; vertical-align: top; }
        .signature-space { height: 52px; }
    </style>
</head>
<body>
    <h2>LAPORAN KEGIATAN BULANAN PPPK-PW</h2>
    <div class="subtitle">BPSDM Provinsi Jawa Barat<br><strong>{{ $month->translatedFormat('F Y') }}</strong></div>

    <table class="meta">
        <tr><td width="18%">Nama</td><td>: {{ $a->user->name }}</td><td width="18%">NIP/NIK</td><td>: {{ $a->user->nip_nik ?: '-' }}</td></tr>
        <tr><td>Jabatan</td><td>: {{ $a->user->jabatan ?: '-' }}</td><td>Jumlah Hari</td><td>: {{ $reports->count() }} hari laporan</td></tr>
    </table>

    @foreach($reports as $report)
        <div class="date-title">{{ $report->report_date->translatedFormat('l, d F Y') }}</div>
        <table class="data">
            <thead><tr><th width="5%">No</th><th width="14%">Waktu</th><th width="35%">Kegiatan</th><th>Hasil / Output / Kesimpulan Kegiatan</th></tr></thead>
            <tbody>
                @foreach($report->items as $index => $item)
                    <tr><td>{{ $index + 1 }}</td><td>{{ substr($item->start_time, 0, 5) }}–{{ substr($item->end_time, 0, 5) }}</td><td>{{ $item->activity }}</td><td>{{ $item->output }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <table class="footer">
        <tr>
            <td>
                PPPK-PW
                <div class="signature-space"></div>
                <strong>{{ $a->user->name }}</strong><br>
                <span>NIP/NIK. {{ $a->user->nip_nik ?: '-' }}</span>
            </td>
            <td>
                Mengetahui,<br>Kasubag/Atasan
                <div class="signature-space"></div>
                <strong>{{ $a->supervisor->name }}</strong><br>
                <span>NIP/NIK. {{ $a->supervisor->nip_nik ?: '-' }}</span>
            </td>
        </tr>
    </table>
</body>
</html>
