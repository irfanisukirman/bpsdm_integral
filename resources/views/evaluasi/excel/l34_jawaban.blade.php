<table>
    <thead>
        <tr>
            <th rowspan="2">NO</th>
            <th rowspan="2">NAMA ALUMNI</th>
            <th rowspan="2">STATUS</th>
            <th colspan="2">PENDIDIKAN</th>
            <th colspan="2">PANGKAT</th>
            @foreach($questions as $q)
                <th colspan="3">{{ $q->sub_category }} - {{ $q->question_text }}</th>
            @endforeach
        </tr>
        <tr>
            <th>DULU</th><th>SKRG</th>
            <th>DULU</th><th>SKRG</th>
            @foreach($questions as $q)
                <th>Mandiri</th><th>Atasan</th><th>Rekan</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($participants as $index => $p)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $p->name }}</td>
            <td>{{ $p->status_kepegawaian ?: $p->user?->status_kepegawaian ?: '-' }}</td>
            <td>{{ $p->alumniProfile->edu_during_training ?? '-' }}</td>
            <td>{{ $p->alumniProfile->edu_current ?? '-' }}</td>
            <td>{{ $p->alumniProfile->rank_during_training ?? '-' }}</td>
            <td>{{ $p->alumniProfile->rank_current ?? '-' }}</td>
            @foreach($questions as $q)
                @foreach(['mandiri', 'atasan', 'rekan'] as $role)
                    @php
                        $result = $p->evaluationResultsL34->first(fn($item) =>
                            $item->evaluator_role === $role &&
                            $item->question?->question_text === $q->question_text &&
                            $item->question?->sub_category === $q->sub_category
                        );
                        $answer = '-';
                        if ($result) {
                            if ($result->score !== null) {
                                $answer = $result->score;
                            } elseif ($result->note) {
                                $decoded = json_decode($result->note, true);
                                $answer = is_array($decoded) ? implode(', ', $decoded) : $result->note;
                            }
                        }
                    @endphp
                    <td>{{ $answer }}</td>
                @endforeach
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
