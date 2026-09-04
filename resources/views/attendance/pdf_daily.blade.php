<!DOCTYPE html>
<html>
<head>
    <title>Laporan Presensi Harian</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .info { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
        .badge { padding: 2px 5px; border-radius: 3px; font-weight: bold; font-size: 9px; }
        .status-hadir { color: #28a745; }
        .status-izin { color: #ffc107; }
        .status-sakit { color: #dc3545; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0;">DAFTAR HADIR PESERTA</h2>
        <h3 style="margin:5px 0;">{{ strtoupper($training->nama_pelatihan) }}</h3>
    </div>

    <div class="info">
        <table style="border:none; width: 50%;">
            <tr style="border:none;">
                <td style="border:none; width: 30%;">Hari / Tanggal</td>
                <td style="border:none;">: <strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</strong></td>
            </tr>
            <tr style="border:none;">
                <td style="border:none;">Penyelenggara</td>
                <td style="border:none;">: {{ $training->bidang }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="18%">NIP / NIK</th>
                <th width="25%">Nama Lengkap</th>
                <th width="15%" class="text-center">Status</th>
                <th width="17%" class="text-center">Waktu</th>
                <th width="20%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($participants as $index => $p)
            @php 
                $att = $attendances->where('participant_id', $p->id)->first(); 
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $p->nip_nik }}</td>
                <td>{{ $p->name }}</td>
                <td class="text-center">
                    @if($att)
                        <span class="status-{{ $att->status }}">{{ strtoupper($att->status) }}</span>
                    @else
                        <span style="color: #999;">BELUM ABSEN</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($att)
                        {{ \Carbon\Carbon::parse($att->check_in_at)->format('H:i:s') }} {{ $att->timezone_label }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $att?->keterangan ?: '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; float: right; width: 200px; text-align: center;">
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
        <br><br><br>
        <p>_________________________<br>Panitia Penyelenggara</p>
    </div>
</body>
</html>
