<table>
    {{-- BAGIAN 1 - INFORMASI UMUM --}}
    <tr><th colspan="7" style="font-weight: bold; background-color: #FFA500; border: 1px solid #000;">BAGIAN 1 - INFORMASI UMUM</th></tr>
    <tr></tr> {{-- Row 2 --}}
    <tr> {{-- Row 3 --}}
        <td style="font-weight: bold; border: 1px solid #000; background-color: #f2f2f2;">1. TINGKAT PENDIDIKAN</td>
        <td style="font-weight: bold; border: 1px solid #000; background-color: #f2f2f2;">Saat Pelatihan</td>
        <td style="font-weight: bold; border: 1px solid #000; background-color: #f2f2f2;">Saat Ini</td>
    </tr>
    @foreach(['S2/S3', 'D4/S1', 'D3', 'SMA/K', 'SD/SMP'] as $edu)
    <tr> {{-- Row 4-8 --}}
        <td style="border: 1px solid #000;">{{ $edu }}</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $profiles->where('edu_during_training', $edu)->count() }}</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $profiles->where('edu_current', $edu)->count() }}</td>
    </tr>
    @endforeach

    @for($i=0; $i<7; $i++) <tr></tr> @endfor {{-- Row 9-15 (Spasi untuk Chart) --}}

    <tr> {{-- Row 16 --}}
        <td style="font-weight: bold; border: 1px solid #000; background-color: #f2f2f2;">2. GOLONGAN</td>
        <td style="font-weight: bold; border: 1px solid #000; background-color: #f2f2f2;">Saat Pelatihan</td>
        <td style="font-weight: bold; border: 1px solid #000; background-color: #f2f2f2;">Saat Ini</td>
    </tr>
    @foreach(['Gol IV', 'Gol III', 'Gol II', 'Gol I'] as $gol)
    <tr> {{-- Row 17-20 --}}
        <td style="border: 1px solid #000;">{{ $gol }}</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $profiles->filter(fn($p) => str_contains($p->rank_during_training ?? '', str_replace('Gol ', '', $gol)))->count() }}</td>
        <td style="border: 1px solid #000; text-align: center;">{{ $profiles->filter(fn($p) => str_contains($p->rank_current ?? '', str_replace('Gol ', '', $gol)))->count() }}</td>
    </tr>
    @endforeach

    @for($i=0; $i<7; $i++) <tr></tr> @endfor {{-- Spasi --}}

    <tr><td style="font-weight: bold; border: 1px solid #000; background-color: #f2f2f2;">3. JABATAN</td><td style="font-weight: bold; border: 1px solid #000; text-align: center;">Jumlah</td></tr> {{-- Row 28 --}}
    <tr><td style="border: 1px solid #000;">Berubah</td><td style="border: 1px solid #000; text-align: center;">{{ $profiles->filter(fn($p) => $p->pos_during_training != $p->pos_current)->count() }}</td></tr>
    <tr><td style="border: 1px solid #000;">Tetap</td><td style="border: 1px solid #000; text-align: center;">{{ $profiles->filter(fn($p) => $p->pos_during_training == $p->pos_current)->count() }}</td></tr>

    @for($i=0; $i<5; $i++) <tr></tr> @endfor {{-- Spasi --}}

    <tr><td style="font-weight: bold; border: 1px solid #000; background-color: #f2f2f2;">4. UNIT KERJA</td><td style="font-weight: bold; border: 1px solid #000; text-align: center;">Jumlah</td></tr> {{-- Row 37 --}}
    <tr><td style="border: 1px solid #000;">Berubah</td><td style="border: 1px solid #000; text-align: center;">{{ $profiles->filter(fn($p) => $p->unit_during_training != $p->unit_current)->count() }}</td></tr>
    <tr><td style="border: 1px solid #000;">Tetap</td><td style="border: 1px solid #000; text-align: center;">{{ $profiles->filter(fn($p) => $p->unit_during_training == $p->unit_current)->count() }}</td></tr>

    @for($i=0; $i<10; $i++) <tr></tr> @endfor {{-- Row 40-50 --}}

    {{-- BAGIAN 2 - PENUGASAN --}}
    <tr><th colspan="7" style="font-weight: bold; background-color: #FFA500; border: 1px solid #000;">BAGIAN 2 - PENUGASAN SAAT INI</th></tr>
    @php
        $taskQs = [
            1 => 'Apakah saat ini Ybs sedang bertugas yang berkaitan dengan pelatihan?',
            2 => 'Apakah pengetahuan yang diperoleh membantu Ybs dalam menjalankan tugas?',
            3 => 'Jika Tidak, apakah pengetahuan membantu menjalankan tugas?',
            4 => 'Apakah pelatihan memiliki keterkaitan dengan bidang tugas?',
            5 => 'Apakah Ybs melakukan transfer ilmu?'
        ];
    @endphp

    @foreach($taskQs as $num => $txt)
        <tr><td colspan="7" style="font-weight: bold;">{{ $num + 5 }}. {{ $txt }}</td></tr>
        <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
            <td style="border: 1px solid #000;">Pilihan</td>
            <td style="border: 1px solid #000;">Peserta</td><td style="border: 1px solid #000;">Atasan</td><td style="border: 1px solid #000;">Rekan</td>
            <td style="border: 1px solid #000;">Peserta (%)</td><td style="border: 1px solid #000;">Atasan (%)</td><td style="border: 1px solid #000;">Rekan (%)</td>
        </tr>
        @foreach(['Ya', 'Tidak'] as $ans)
        <tr>
            <td style="border: 1px solid #000;">{{ $ans }}</td>
            @foreach(['mandiri', 'atasan', 'rekan'] as $role)
                @php $c = $results->where('evaluator_role', $role)->filter(fn($r) => str_contains($r->note, "Tugas ke-$num") && str_contains($r->note, $ans))->count(); @endphp
                <td style="border: 1px solid #000; text-align: center;">{{ $c }}</td>
            @endforeach
            @foreach(['mandiri', 'atasan', 'rekan'] as $role)
                @php 
                    $total = $totalResponden[$role] > 0 ? $totalResponden[$role] : 1;
                    $c = $results->where('evaluator_role', $role)->filter(fn($r) => str_contains($r->note, "Tugas ke-$num") && str_contains($r->note, $ans))->count();
                @endphp
                <td style="border: 1px solid #000; text-align: center;">{{ round(($c/$total)*100, 1) }}</td>
            @endforeach
        </tr>
        @endforeach
        @for($i=0; $i<7; $i++) <tr></tr> @endfor {{-- Jarak antar chart penugasan --}}
    @endforeach

    {{-- BAGIAN 3 & 4 - PERILAKU & DAMPAK --}}
    @foreach(['PERUBAHAN PERILAKU' => $questionsL3, 'DAMPAK PELATIHAN' => $questionsL4] as $title => $qs)
    <tr><th colspan="7" style="font-weight: bold; background-color: #FFA500; border: 1px solid #000;">{{ $title }}</th></tr>
    @foreach($qs as $idx => $q)
        <tr><td colspan="7" style="font-weight: bold;">{{ $idx + 1 }}. {{ $q->question_text }}</td></tr>
        <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
            <td style="border: 1px solid #000;">Skala</td>
            <td style="border: 1px solid #000;">Peserta</td><td style="border: 1px solid #000;">Atasan</td><td style="border: 1px solid #000;">Rekan</td>
            <td style="border: 1px solid #000;">P (%)</td><td style="border: 1px solid #000;">A (%)</td><td style="border: 1px solid #000;">R (%)</td>
        </tr>
        @foreach([['l'=>'Sangat Baik','min'=>91,'max'=>100],['l'=>'Baik','min'=>81,'max'=>90],['l'=>'Cukup','min'=>71,'max'=>80],['l'=>'Kurang','min'=>61,'max'=>70],['l'=>'Sangat Kurang','min'=>10,'max'=>60]] as $s)
        <tr>
            <td style="border: 1px solid #000;">{{ $s['l'] }}</td>
            @foreach(['mandiri', 'atasan', 'rekan'] as $role)
                <td style="border: 1px solid #000; text-align: center;">{{ $results->where('evaluator_role', $role)->where('question_id', $q->id)->whereBetween('score', [$s['min'], $s['max']])->count() }}</td>
            @endforeach
            @foreach(['mandiri', 'atasan', 'rekan'] as $role)
                @php 
                    $total = $totalResponden[$role] > 0 ? $totalResponden[$role] : 1;
                    $c = $results->where('evaluator_role', $role)->where('question_id', $q->id)->whereBetween('score', [$s['min'], $s['max']])->count();
                @endphp
                <td style="border: 1px solid #000; text-align: center;">{{ round(($c/$total)*100, 1) }}</td>
            @endforeach
        </tr>
        @endforeach
        @for($i=0; $i<5; $i++) <tr></tr> @endfor
    @endforeach
    @endforeach
</table>