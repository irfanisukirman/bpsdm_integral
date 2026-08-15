<!DOCTYPE html>
<html>
<head>
    <title>Rekap Kehadiran Total</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; word-wrap: break-word; }
        
        /* Header Biru: Teks Putih & Bold */
    .header-blue { 
        background-color: #2F5597; 
        color: #FFFFFF; 
        font-weight: bold; 
        text-transform: uppercase; 
    }
    /* Header Merah: Teks Putih & Bold */
    .header-red { 
        background-color: #C00000; 
        color: #FFFFFF; 
        font-weight: bold; 
        text-transform: uppercase; 
    }
    
    /* Baris isi: Default Hitam & Tidak Bold */
    td { 
        border: 1px solid #000; 
        padding: 5px; 
        text-align: center; 
        color: #000000;
        font-weight: normal; 
    }
    /* Khusus untuk kolom predikat di PDF */
    /* Khusus untuk kolom predikat di PDF */
    .bold-text {
        font-weight: bold;
    }
        .text-left { text-align: left; }
        .summary { margin-top: 15px; font-size: 8px; }
        .footer-sign { margin-top: 30px; float: right; width: 200px; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0; font-size: 14px;">REKAPITULASI KEHADIRAN TOTAL PESERTA</h2>
        <h3 style="margin:5px 0; font-size: 12px;">{{ strtoupper($training->nama_pelatihan) }}</h3>
        <p style="margin:0;">Penyelenggara: {{ $training->bidang }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="header-blue" width="15px">NO</th>
                <th class="header-blue" width="80px">NIP / NIK</th>
                <th class="header-blue" width="140px">NAMA LENGKAP</th>
                {{-- Kolom Tanggal Dinamis --}}
                @foreach($dates as $date)
                    <th class="header-blue">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</th>
                @endforeach
                <th class="header-red" width="40px">TOTAL HADIR</th>
                <th class="header-red" width="35px">NILAI</th>
                <th class="header-red" width="70px">PREDIKAT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($participants as $index => $p)
                @php 
                    $presentCount = 0; 
                    $totalDays = count($dates);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $p->nip_nik }}</td>
                    <td class="text-left fw-bold">{{ strtoupper($p->name) }}</td>
                    
                    @foreach($dates as $date)
                        @php
                            $attendance = \App\Models\Attendance::whereHas('schedule', function($q) use ($date, $training) {
                                            $q->where('training_id', $training->id)->where('date', $date);
                                        })->where('participant_id', $p->id)->first();
                            
                            $status = "-";
                            if($attendance) {
                                $status = strtoupper(substr($attendance->status, 0, 1));
                                if($attendance->status == 'hadir') $presentCount++;
                            }
                        @endphp
                        <td>{{ $status }}</td>
                    @endforeach
                    
                    {{-- Perhitungan Statistik --}}
                    @php 
                        $score = ($totalDays > 0) ? round(($presentCount / $totalDays) * 100) : 0;
                        
                        if($score == 100) $predicate = "SANGAT BAIK";
                        elseif($score >= 85) $predicate = "BAIK";
                        elseif($score >= 75) $predicate = "CUKUP";
                        else $predicate = "KURANG";
                    @endphp

                    <td class="fw-bold">{{ $presentCount }}</td>
                    <td class="fw-bold">{{ $score }}</td>
                    <td class="fw-bold">{{ $predicate }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Keterangan:</strong> H = Hadir, I = Izin, S = Sakit, - = Tanpa Keterangan</p>
    </div>

    <div class="footer-sign">
        <p>Cimahi, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
        <p>Panitia Penyelenggara,</p>
        <br><br><br>
        <p><strong>{{ Auth::user()->name }}</strong></p>
    </div>
</body>
</html>