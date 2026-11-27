<div class="row g-3" data-quiz-question-fields>
    <div class="col-12">
        <label class="form-label" for="quizPrompt">Pertanyaan</label>
        <textarea name="prompt" id="quizPrompt" class="form-control" rows="3" maxlength="2000" required></textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="quizQuestionGroup">Rumpun</label>
        <input type="text" class="form-control" id="quizQuestionGroup" name="group" maxlength="100" list="quizQuestionGroups" aria-describedby="quizGroupHelp">
        <datalist id="quizQuestionGroups">
            @foreach(collect($questions)->pluck('group')->filter(fn ($group) => filled($group))->unique() as $group)
                <option value="{{ $group }}"></option>
            @endforeach
        </datalist>
        <div class="form-text" id="quizGroupHelp">Pilih rumpun yang ada atau ketik nama rumpun baru. Boleh dikosongkan.</div>
    </div>
    <fieldset class="col-12">
        <legend class="form-label">Jenis Soal</legend>
        <div class="d-flex flex-wrap gap-3">
            @foreach(['multiple' => 'Pilihan Ganda', 'boolean' => 'Benar / Salah'] as $type => $label)
                <div class="form-check">
                    <input type="radio" class="form-check-input" name="type" value="{{ $type }}" id="quizType-{{ $type }}" required>
                    <label class="form-check-label" for="quizType-{{ $type }}">{{ $label }}</label>
                </div>
            @endforeach
        </div>
    </fieldset>
    <div class="col-12" data-question-options>
        <div class="row g-2">
            @foreach(['A', 'B', 'C', 'D'] as $index => $letter)
                <div class="col-sm-6">
                    <label class="form-label" for="quizOption-{{ $index }}">Pilihan {{ $letter }}</label>
                    <input type="text" id="quizOption-{{ $index }}" name="options[]" class="form-control" maxlength="500" required>
                </div>
            @endforeach
        </div>
    </div>
    <fieldset class="col-12">
        <legend class="form-label">Kunci Jawaban</legend>
        <div class="d-flex flex-wrap gap-3" data-question-keys>
            @foreach(['A', 'B', 'C', 'D'] as $index => $letter)
                <div class="form-check" data-key-option>
                    <input type="radio" class="form-check-input" name="correct" value="{{ $index }}" id="quizKey-{{ $index }}" required>
                    <label class="form-check-label" for="quizKey-{{ $index }}">Pilihan {{ $letter }}</label>
                </div>
            @endforeach
        </div>
    </fieldset>
</div>
