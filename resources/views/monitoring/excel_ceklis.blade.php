@php
    // Hitung total kolom: 3 kolom tetap (No, Kategori, Pertanyaan) + Jumlah Tahapan
    $totalCols = count($stages) + 3;
@endphp

<table>
    {{-- HEADER INFORMASI --}}
    <tr>
        <th colspan="{{ $totalCols }}" style="font-weight: bold; font-size: 14pt; text-align: center;">
            LAPORAN REKAPITULASI MONITORING: {{ strtoupper($training->nama_pelatihan) }}
        </th>
    </tr>
    <tr>
        <th style="font-weight: bold;">MODEL:</th>
        <th colspan="{{ $totalCols - 1 }}">{{ strtoupper($training->model) }}</th>
    </tr>
    <tr>
        <th style="font-weight: bold;">LPP / Unit Kerja:</th>
        <th colspan="{{ $totalCols - 1 }}">{{ $training->bidang }}</th>
    </tr>
    <tr>
        <th style="font-weight: bold;">Periode Global:</th>
        <th colspan="{{ $totalCols - 1 }}">
            {{ \Carbon\Carbon::parse($training->tgl_mulai)->format('d/m/Y') }} s.d 
            {{ \Carbon\Carbon::parse($training->tgl_selesai)->format('d/m/Y') }}
        </th>
    </tr>
    
    {{-- Baris Kosong --}}
    <tr><td colspan="{{ $totalCols }}"></td></tr>

    {{-- HEADER TABEL --}}
    <thead>
        <tr>
            <th rowspan="2" style="font-weight: bold; border: 1px solid #000; background-color: #D3D3D3; text-align: center; vertical-align: center;">NO</th>
            <th rowspan="2" style="font-weight: bold; border: 1px solid #000; background-color: #D3D3D3; text-align: center; vertical-align: center;">BUTIR INDIKATOR</th>
            <th rowspan="2" style="font-weight: bold; border: 1px solid #000; background-color: #D3D3D3; text-align: center; vertical-align: center;">INSTRUMEN / PERTANYAAN</th>
            {{-- Kolom Dinamis Per Tahapan --}}
            @foreach($stages as $st)
                <th style="font-weight: bold; border: 1px solid #000; background-color: #E7E7FF; text-align: center;">
                    {{ strtoupper($st->nama_tahapan) }} ({{ strtoupper($st->metode) }})
                </th>
            @endforeach
        </tr>
        <tr>
            @foreach($stages as $st)
                <th style="font-weight: bold; border: 1px solid #000; background-color: #E7E7FF; text-align: center;">
                    {{ \Carbon\Carbon::parse($st->tgl_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($st->tgl_selesai)->format('d/m/Y') }}
                </th>
            @endforeach
        </tr>
    </thead>

    {{-- ISI DATA --}}
    <tbody>
        @php $no = 1; @endphp
        @foreach($questions as $cat => $items)
            @foreach($items as $q)
                <tr>
                    <td style="border: 1px solid #000; text-align: center;">{{ $no++ }}</td>
                    <td style="border: 1px solid #000;">{{ $cat }}</td>
                    <td style="border: 1px solid #000;">{{ $q->question_text }}</td>
                    @foreach($stages as $st)
                        @php
                            // Ambil hasil monitoring untuk pertanyaan ini pada tahapan ini
                            $res = $training->monitoringResults
                                   ->where('question_id', $q->id)
                                   ->where('training_stage_id', $st->id)
                                   ->first();
                            $val = $res ? strtoupper($res->answer) : '-';
                        @endphp
                        <td style="border: 1px solid #000; text-align: center; font-weight: bold; color: {{ $val == 'TIDAK' ? '#FF0000' : '#000000' }}">
                            {{ $val }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach
    </tbody>

    {{-- FOOTER --}}
    <tr><td colspan="{{ $totalCols }}"></td></tr>
    <tr>
        <td colspan="{{ $totalCols }}" style="font-weight: bold;">TEMUAN MONITORING:</td>
    </tr>
    @php 
        $temuan = $training->monitoringResults->where('answer', 'tidak'); 
    @endphp
    @forelse($temuan as $t)
        <tr>
            <td style="text-align: center;">-</td>
            <td colspan="{{ $totalCols - 1 }}">
                {{ $t->question->question_text ?? 'Indikator' }} : {{ $t->notes }} (Tindak Lanjut ke: {{ $t->follow_up_target }})
            </td>
        </tr>
    @empty
        <tr>
            <td style="text-align: center;">-</td>
            <td colspan="{{ $totalCols - 1 }}">Tidak ada temuan monitoring (Seluruh indikator terpenuhi).</td>
        </tr>
    @endforelse

    <tr><td colspan="{{ $totalCols }}"></td></tr>
    <tr>
        <td colspan="{{ $totalCols }}" style="font-weight: bold;">KESIMPULAN & REKOMENDASI KESELURUHAN:</td>
    </tr>
    @forelse($training->summaries->where('category', 'STAGE_FINAL_SUMMARY') as $sum)
        <tr>
            <td style="text-align: center;">•</td>
            <td colspan="{{ $totalCols - 1 }}">{{ $sum->conclusion }}</td>
        </tr>
    @empty
        <tr>
            <td style="text-align: center;">•</td>
            <td colspan="{{ $totalCols - 1 }}">-</td>
        </tr>
    @endforelse

    <tr><td colspan="{{ $totalCols }}"></td></tr>
    
    {{-- TANDA TANGAN --}}
    <tr>
        <td colspan="{{ $totalCols - 2 }}"></td>
        <td colspan="2" style="text-align: center;">
            Cimahi, {{ \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d F Y') }}<br>
            Tim Monitoring BPSDM Provinsi Jawa Barat<br><br><br><br>
            <strong>{{ Auth::user()->name }}</strong><br>
            NIP. {{ Auth::user()->whatsapp }}
        </td>
    </tr>
</table>