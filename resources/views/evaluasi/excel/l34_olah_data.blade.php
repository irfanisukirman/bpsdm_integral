@php
    $roles = ['mandiri' => 'Mandiri', 'atasan' => 'Atasan', 'rekan' => 'Rekan Kerja'];
    $labelScore = ['Sangat Kurang' => 20, 'Kurang' => 40, 'Cukup' => 60, 'Baik' => 80, 'Sangat Baik' => 100];
    $sections = [
        'PENEMPATAN TUGAS DAN TRANSFER LEARNING' => $questionsPlacement,
        'PERUBAHAN PERILAKU (L3)' => $questionsL3,
        'DAMPAK PELATIHAN (L4)' => $questionsL4,
    ];
@endphp
<table>
    <tr><th colspan="8" style="font-weight:bold;background-color:#2F5597;color:#FFFFFF;">OLAH DATA EVALUASI LEVEL 3 &amp; 4</th></tr>
    <tr><td>Pelatihan</td><td colspan="7">{{ $training->nama_pelatihan }}</td></tr>
    <tr><td>Bidang</td><td colspan="7">{{ $training->bidang }}</td></tr>
    <tr><td>Responden</td><td>Mandiri: {{ $totalResponden['mandiri'] }}</td><td>Atasan: {{ $totalResponden['atasan'] }}</td><td>Rekan: {{ $totalResponden['rekan'] }}</td></tr>
    <tr></tr>

    <tr><th colspan="8" style="font-weight:bold;background-color:#F4B183;">RINGKASAN PERUBAHAN DATA DIRI</th></tr>
    <tr><td>Profil Mandiri Tersimpan</td><td>{{ $profiles->count() }}</td></tr>
    <tr><td>Jabatan Berubah</td><td>{{ $profiles->filter(fn($profile) => $profile->pos_during_training !== $profile->pos_current)->count() }}</td></tr>
    <tr><td>Unit Kerja Berubah</td><td>{{ $profiles->filter(fn($profile) => $profile->unit_during_training !== $profile->unit_current)->count() }}</td></tr>
    <tr><td>Perangkat Daerah Berubah</td><td>{{ $profiles->filter(fn($profile) => $profile->dept_during_training !== $profile->dept_current)->count() }}</td></tr>
    <tr></tr>

    @foreach($sections as $sectionTitle => $sectionQuestions)
        <tr><th colspan="8" style="font-weight:bold;background-color:#F4B183;">{{ $sectionTitle }}</th></tr>
        @forelse($sectionQuestions as $questionNumber => $question)
            @php
                $roleResults = collect();
                foreach ($roles as $roleKey => $roleName) {
                    $roleQuestion = $allQuestions->first(fn($candidate) =>
                        $candidate->category === 'l34_' . $roleKey &&
                        $candidate->sub_category === $question->sub_category &&
                        $candidate->question_text === $question->question_text
                    );
                    $roleResults->put($roleKey, $roleQuestion
                        ? $results->where('evaluator_role', $roleKey)->where('question_id', $roleQuestion->id)
                        : collect());
                }

                $options = $question->options ?? [];
                if ($question->type === 'slider') {
                    $options = array_keys($labelScore);
                } elseif ($question->type === 'text') {
                    $options = ['Jawaban terisi'];
                }
            @endphp
            <tr><td colspan="8" style="font-weight:bold;">{{ $questionNumber + 1 }}. {{ $question->question_text }}</td></tr>
            <tr style="font-weight:bold;background-color:#D9EAF7;">
                <td>Pilihan / Skala</td>
                @foreach($roles as $roleName)
                    <td>{{ $roleName }} (Jumlah)</td><td>{{ $roleName }} (%)</td>
                @endforeach
                <td>Catatan</td>
            </tr>
            @foreach($options as $option)
                <tr>
                    <td>{{ $option }}</td>
                    @foreach($roles as $roleKey => $roleName)
                        @php
                            $roleItems = $roleResults->get($roleKey, collect());
                            $count = $roleItems->filter(function ($result) use ($question, $option, $labelScore) {
                                if ($question->type === 'checkbox') {
                                    $selected = json_decode((string) $result->note, true);
                                    return is_array($selected) && in_array($option, $selected, true);
                                }
                                if ($question->type === 'text') {
                                    return filled($result->note);
                                }
                                if ($result->score !== null) {
                                    $numeric = (float) $result->score;
                                    $target = $labelScore[$option] ?? null;
                                    return $target !== null && $numeric === (float) $target;
                                }
                                return trim((string) $result->note) === (string) $option;
                            })->count();
                            $denominator = max(1, $roleItems->unique('participant_id')->count());
                        @endphp
                        <td>{{ $count }}</td>
                        <td>{{ round(($count / $denominator) * 100, 1) }}</td>
                    @endforeach
                    <td>{{ $question->type === 'checkbox' ? 'Boleh lebih dari satu jawaban' : '' }}</td>
                </tr>
            @endforeach
            <tr></tr>
        @empty
            <tr><td colspan="8">Belum ada pertanyaan pada bagian ini.</td></tr>
        @endforelse
    @endforeach

    <tr><th colspan="8" style="font-weight:bold;background-color:#70AD47;color:#FFFFFF;">RATA-RATA SKOR DAMPAK (L4)</th></tr>
    @foreach($roles as $roleKey => $roleName)
        @php
            $roleImpactResults = $results->where('evaluator_role', $roleKey)
                ->filter(fn($result) => $result->question?->sub_category === 'Dampak Pelatihan');
            $values = $roleImpactResults->map(fn($result) =>
                $result->score !== null
                    ? (float) $result->score
                    : ($labelScore[trim((string) $result->note)] ?? null)
            )->filter(fn($value) => $value !== null);
        @endphp
        <tr><td>{{ $roleName }}</td><td>{{ $values->isNotEmpty() ? round($values->avg(), 1) : 0 }}</td><td>{{ $values->count() }} jawaban</td></tr>
    @endforeach
</table>
