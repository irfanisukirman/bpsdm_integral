@foreach($items as $index => $q)
    @php
        $savedValue = old('scores.'.$q->id);
        $answerType = match ($q->type) {
            'slider' => 'Skala 10–100',
            'dropdown' => 'Pilih satu',
            'checkbox' => 'Boleh lebih dari satu',
            default => 'Jawaban tertulis',
        };
    @endphp
    <article class="question-card">
        <div class="question-head">
            <span class="question-number">{{ $index + 1 }}</span>
            <div class="question-copy">
                <label class="question-title" for="question-{{ $q->id }}">{{ $q->question_text }}</label>
                <span class="answer-type"><i class="bx bx-edit-alt"></i>{{ $answerType }}</span>
            </div>
        </div>

        <div class="question-answer">
            @if($q->type === 'slider')
                @php $sliderValue = is_numeric($savedValue) ? (int) $savedValue : 80; @endphp
                <div class="slider-wrapper">
                    <div class="slider-value-row">
                        <span class="score-label">Nilai Anda</span>
                        <span class="score-output" id="score-{{ $q->id }}">{{ $sliderValue }}</span>
                    </div>
                    <input id="question-{{ $q->id }}" type="range" name="scores[{{ $q->id }}]"
                           class="form-range kirkpatrick-slider" min="10" max="100" step="1"
                           value="{{ $sliderValue }}" data-id="{{ $q->id }}">
                    <div class="slider-scale"><span>10 · Sangat Kurang</span><strong id="label-{{ $q->id }}">Cukup</strong><span>100 · Sangat Baik</span></div>
                </div>
            @elseif($q->type === 'dropdown')
                <select id="question-{{ $q->id }}" name="scores[{{ $q->id }}]" class="form-select answer-control" required>
                    <option value="">Pilih jawaban yang paling sesuai</option>
                    @foreach(($q->options ?? []) as $option)
                        <option value="{{ $option }}" @selected($savedValue === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            @elseif($q->type === 'checkbox')
                @php $checkedValues = is_array($savedValue) ? $savedValue : []; @endphp
                <div class="choice-grid" id="question-{{ $q->id }}" data-checkbox-group="{{ $q->id }}">
                    @foreach(($q->options ?? []) as $optionIndex => $option)
                        <label class="choice-option" for="l34-check-{{ $q->id }}-{{ $optionIndex }}">
                            <input class="form-check-input" type="checkbox" name="scores[{{ $q->id }}][]"
                                   value="{{ $option }}" id="l34-check-{{ $q->id }}-{{ $optionIndex }}"
                                   @checked(in_array($option, $checkedValues, true))>
                            <span>{{ $option }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="checkbox-feedback text-danger small mt-2 d-none">Pilih minimal satu jawaban.</div>
            @else
                <textarea id="question-{{ $q->id }}" name="scores[{{ $q->id }}]" class="form-control answer-control"
                          rows="4" placeholder="Tuliskan jawaban Anda secara jelas...">{{ $savedValue }}</textarea>
            @endif
        </div>
    </article>
@endforeach