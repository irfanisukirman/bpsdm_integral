<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Pelatihan</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="header">
        <h2>JADWAL PELATIHAN</h2>
        <h3 style="margin-top: -10px;">{{ strtoupper($training->nama_pelatihan) }}</h3>
        <p>Lokasi: {{ $training->lokasi }} | Angkatan: {{ $training->angkatan }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Hari / Tanggal</th>
                <th width="15%">Waktu</th>
                <th width="35%">Materi / Kegiatan</th>
                <th width="25%">Penanggung Jawab</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedules as $index => $s)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($s->date)->translatedFormat('l, d F Y') }}</td>
                <td>{{ $s->start_time }} - {{ $s->end_time }}</td>
                <td>{{ $s->activity }}</td>
                <td>{{ $s->pic }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>