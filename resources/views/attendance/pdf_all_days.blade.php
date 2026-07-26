<!DOCTYPE html>
<html>
<head>
    <title>Rekap Kehadiran Total</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-left { text-align: left; }
        .bg-gray { background-color: #f9f9f9; }
        .summary { margin-top: 20px; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0;">REKAPITULASI KEHADIRAN PESERTA</h2>
        <h3 style="margin:5px 0;">{{ strtoupper($training->nama_pelatihan) }}</h3>
        <p style="margin:0;">Penyelenggara: {{ $training->bidang }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">NO</th>
                <th width="15%">NIP / NIK</th>
                <th width="20%">NAMA LENGKAP</th>
                {{-- Generate Kolom Tanggal --}}
                @foreach($dates as $date)
                    <th>{{ \Carbon\Carbon::parse($date)->format('d/m') }}</th>
                @endforeach
                <th width="5%">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach($participants as $index => $p)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $p->nip_nik }}</td>
                    <td class="text-left">{{ strtoupper($p->name) }}</td>
                    
                    @php $presentCount = 0; @endphp
                    @foreach($dates as $date)
                        @php
                            // Cari apakah ada kehadiran di tanggal ini (Hadir/Izin/Sakit)
                            $attendance = \App\Models\Attendance::whereHas('schedule', function($q) use ($date, $training) {
                                            $q->where('training_id', $training->id)->where('date', $date);
                                        })->where('participant_id', $p->id)->first();
                            
                            if($attendance && $attendance->status == 'hadir') $presentCount++;
                        @endphp
                        
                        <td class="{{ !$attendance ? 'bg-gray' : '' }}">
                            @if($attendance)
                                {{ strtoupper(substr($attendance->status, 0, 1)) }}
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                    
                    {{-- Hitung Persentase --}}
                    <td style="font-weight: bold;">
                        {{ round(($presentCount / count($dates)) * 100) }}%
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Keterangan:</strong> H = Hadir, I = Izin, S = Sakit, - = Tanpa Keterangan</p>
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>
</body>
</html>