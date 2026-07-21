@php
    $cols = count($stages);
    $totalHeaderCols = $cols + 2;
@endphp
<table>
    {{-- INFORMASI ATAS --}}
    <tr>
        <th colspan="{{ $totalHeaderCols }}" style="font-weight: bold; font-size: 14pt;">LAPORAN REKAPITULASI MONITORING</th>
    </tr>
    <tr>
        <th colspan="2" style="text-align: left;">Nama Pelatihan</th>
        <th colspan="{{ $cols }}">: {{ strtoupper($training->nama_pelatihan) }}</th>
    </tr>
    <tr>
        <th colspan="2" style="text-align: left;">LPP / Unit Kerja</th>
        <th colspan="{{ $cols }}">: {{ $training->bidang }}</th>
    </tr>
    <tr>
        <th colspan="2" style="text-align: left;">Periode Global</th>
        <th colspan="{{ $cols }}">: {{ \Carbon\Carbon::parse($training->tgl_mulai)->format('d/m/Y') }} s.d {{ \Carbon\Carbon::parse($training->tgl_selesai)->format('d/m/Y') }}</th>
    </tr>
    <tr><td colspan="{{ $totalHeaderCols }}"></td></tr>

    {{-- HEADER TABEL BIRU --}}
    <thead>
        <tr>
            <th style="background-color: #2F5597; color: #FFFFFF; border: 1px solid #000000; font-weight: bold; text-align: center;">NO</th>
            <th style="background-color: #2F5597; color: #FFFFFF; border: 1px solid #000000; font-weight: bold; text-align: center;">BUTIR INDIKATOR / INSTRUMEN</th>
            @foreach($stages as $st)
                <th style="background-color: #2F5597; color: #FFFFFF; border: 1px solid #000000; font-weight: bold; text-align: center;">
                    {{ strtoupper($st->nama_tahapan) }}<br>
                    ({{ \Carbon\Carbon::parse($st->tgl_mulai)->format('d/m') }}-{{ \Carbon\Carbon::parse($st->tgl_selesai)->format('d/m') }})
                </th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @foreach($categories as $cat)
            {{-- BARIS KATEGORI --}}
            <tr>
                <td style="background-color: #D9D9D9; border: 1px solid #000000; font-weight: bold; text-align: center;"></td>
                <td colspan="{{ $cols + 1 }}" style="background-color: #D9D9D9; border: 1px solid #000000; font-weight: bold;">
                    {{ strtoupper($cat) }}
                </td>
            </tr>

            @php
                $catQuestions = $questions->where('category', $cat);
                $no = 1;
            @endphp

            @foreach($catQuestions as $q)
                <tr>
                    <td style="border: 1px solid #000000; text-align: center;">{{ $no++ }}</td>
                    <td style="border: 1px solid #000000;">{{ $q->question_text }}</td>
                    @foreach($stages as $st)
                        <td style="border: 1px solid #000000; text-align: center;">
                            @php
                                // Cek relevansi metode indikator dengan metode tahapan
                                $isRelevant = ($q->metode == 'semua' || strtolower($q->metode) == strtolower($st->metode));
                                
                                $res = null;
                                if($isRelevant) {
                                    $res = $training->monitoringResults
                                        ->where('question_id', $q->id)
                                        ->where('training_stage_id', $st->id)
                                        ->first();
                                }
                            @endphp

                            @if(!$isRelevant)
                                -
                            @elseif($res && $res->answer == 'ya')
                                ✓
                            @elseif($res && $res->answer == 'tidak')
                                X
                            @else
                                ?
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach

        <tr><td colspan="{{ $totalHeaderCols }}"></td></tr>

        {{-- FOOTER KESIMPULAN --}}
        <tr>
            <td colspan="2" style="background-color: #D9D9D9; border: 1px solid #000000; font-weight: bold;">KESIMPULAN & REKOMENDASI:</td>
            @foreach($stages as $st)
                @php
                    $sum = $training->summaries
                        ->where('training_stage_id', $st->id)
                        ->where('category', 'STAGE_FINAL_SUMMARY')
                        ->first();
                @endphp
                <td style="border: 1px solid #000000; font-size: 9pt;">{{ $sum->conclusion ?? '-' }}</td>
            @endforeach
        </tr>

        <tr><td colspan="{{ $totalHeaderCols }}"></td></tr>

        {{-- TANDA TANGAN --}}
        <tr>
            <td colspan="{{ $cols }}"></td>
            <td colspan="2" style="text-align: center;">
                Cimahi, {{ \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y') }}<br>
                Tim Monitoring BPSDM Provinsi Jawa Barat<br><br><br><br>
                <strong>{{ Auth::user()->name }}</strong>
            </td>
        </tr>
    </tbody>
</table>