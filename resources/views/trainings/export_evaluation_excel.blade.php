<table>
    <thead>
        <tr>
            <th colspan="6" style="font-weight: bold; text-align: center;">HASIL EVALUASI LEVEL 1 & LEVEL 2</th>
        </tr>
        <tr>
            <th colspan="6" style="font-weight: bold; text-align: center;">{{ strtoupper($training->nama_pelatihan) }}</th>
        </tr>
        <tr></tr>
        <tr>
            <th style="background-color: #2F5597; color: #FFFFFF; border: 1px solid #000; font-weight: bold; text-align: center;">NO</th>
            <th style="background-color: #2F5597; color: #FFFFFF; border: 1px solid #000; font-weight: bold; text-align: center;">NIP / NIK</th>
            <th style="background-color: #2F5597; color: #FFFFFF; border: 1px solid #000; font-weight: bold; text-align: center;">NAMA LENGKAP</th>
            <th style="background-color: #C00000; color: #FFFFFF; border: 1px solid #000; font-weight: bold; text-align: center;">SKOR L1 (REAKSI)</th>
            <th style="background-color: #C00000; color: #FFFFFF; border: 1px solid #000; font-weight: bold; text-align: center;">PREDIKAT L1</th>
            <th style="background-color: #0070C0; color: #FFFFFF; border: 1px solid #000; font-weight: bold; text-align: center;">SKOR L2 (LEARNING)</th>
            <th style="background-color: #0070C0; color: #FFFFFF; border: 1px solid #000; font-weight: bold; text-align: center;">PREDIKAT L2</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $row)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $row['nip'] }}</td>
            <td style="border: 1px solid #000;">{{ $row['nama'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $row['l1_score'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $row['l1_predicate'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $row['l2_score'] }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $row['l2_predicate'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>