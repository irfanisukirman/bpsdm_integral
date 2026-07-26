<table>
    <thead>
        <tr>
            <th rowspan="2">NO</th>
            <th rowspan="2">NAMA ALUMNI</th>
            <th colspan="2">PENDIDIKAN</th>
            <th colspan="2">PANGKAT</th>
            @foreach($questions as $q)
                <th>{{ $q->question_text }}</th>
            @endforeach
        </tr>
        <tr>
            <th>DULU</th><th>SKRG</th>
            <th>DULU</th><th>SKRG</th>
            @foreach($questions as $q)
                <th>Skor</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($participants as $index => $p)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $p->name }}</td>
            <td>{{ $p->alumniProfile->edu_during_training ?? '-' }}</td>
            <td>{{ $p->alumniProfile->edu_current ?? '-' }}</td>
            <td>{{ $p->alumniProfile->rank_during_training ?? '-' }}</td>
            <td>{{ $p->alumniProfile->rank_current ?? '-' }}</td>
            @foreach($questions as $q)
                @php $res = $p->evaluationResultsL34->where('question_id', $q->id)->avg('score'); @endphp
                <td>{{ $res ? round($res, 1) : '-' }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>