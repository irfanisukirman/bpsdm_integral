<table>
    <thead>
        {{-- Judul Utama --}}
        <tr>
            <th colspan="{{ count($dates) + 6 }}" style="text-align: center; font-weight: bold;">REKAPITULASI KEHADIRAN TOTAL PESERTA</th>
        </tr>
        <tr>
            <th colspan="{{ count($dates) + 6 }}" style="text-align: center; font-weight: bold;">{{ strtoupper($training->nama_pelatihan) }}</th>
        </tr>
        <tr></tr>
        {{-- Header Tabel --}}
        <tr>
            <th style="background-color: #2F5597; color: #FFFFFF; border: 1px solid #000; text-align: center; font-weight: bold;">NO</th>
            <th style="background-color: #2F5597; color: #FFFFFF; border: 1px solid #000; text-align: center; font-weight: bold;">NIP / NIK</th>
            <th style="background-color: #2F5597; color: #FFFFFF; border: 1px solid #000; text-align: center; font-weight: bold;">NAMA LENGKAP</th>
            @foreach($dates as $date)
                <th style="background-color: #2F5597; color: #FFFFFF; border: 1px solid #000; text-align: center; font-weight: bold;">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</th>
            @endforeach
            <th style="background-color: #C00000; color: #FFFFFF; border: 1px solid #000; text-align: center; font-weight: bold;">TOTAL HADIR</th>
            <th style="background-color: #C00000; color: #FFFFFF; border: 1px solid #000; text-align: center; font-weight: bold;">NILAI</th>
            <th style="background-color: #C00000; color: #FFFFFF; border: 1px solid #000; text-align: center; font-weight: bold;">PREDIKAT</th>
        </tr>
    </thead>
    <tbody>
        @foreach($participants as $index => $p)
            @php 
                $presentCount = 0; 
                $totalDays = count($dates);
            @endphp
            <tr>
                <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000;">'{{ $p->nip_nik }}</td>
                <td style="border: 1px solid #000;">{{ strtoupper($p->name) }}</td>
                
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
                    <td style="border: 1px solid #000; text-align: center;">{{ $status }}</td>
                @endforeach
                
                @php 
                    $score = ($totalDays > 0) ? round(($presentCount / $totalDays) * 100) : 0;
                    if($score == 100) $predicate = "SANGAT BAIK";
                    elseif($score >= 85) $predicate = "BAIK";
                    elseif($score >= 75) $predicate = "CUKUP";
                    else $predicate = "KURANG";
                @endphp

                {{-- Kolom angka: TIDAK BOLD --}}
                <td style="border: 1px solid #000; text-align: center;">{{ $presentCount }}</td>
                <td style="border: 1px solid #000; text-align: center;">{{ $score }}</td>
                {{-- Kolom Predikat: TETAP BOLD --}}
                <td style="border: 1px solid #000; text-align: center; font-weight: bold;">{{ $predicate }}</td>
            </tr>
        @endforeach
    </tbody>
</table>