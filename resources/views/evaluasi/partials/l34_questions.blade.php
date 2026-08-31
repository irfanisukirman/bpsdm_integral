@foreach($items as $index => $q)
<div class="card mb-4 border shadow-none bg-light">
    <div class="card-body">
        <label class="form-label fw-bold mb-4 d-block">{{ $index + 1 }}. {{ strtoupper($q->question_text) }}</label>

        @if($q->type === 'slider')
            <div class="slider-wrapper px-2">
                <input type="range" name="scores[{{ $q->id }}]" class="form-range kirkpatrick-slider"
                       min="10" max="100" step="1" value="80" data-id="{{ $q->id }}">
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="badge bg-primary px-3 py-2 h5 mb-0" id="score-{{ $q->id }}">80</div>
                    <span class="fw-bold h5 mb-0" id="label-{{ $q->id }}" style="color:#71dd37">Cukup</span>
                </div>
            </div>
        @elseif($q->type === 'dropdown')
            <select name="scores[{{ $q->id }}]" class="form-select border-primary" required>
                <option value="">-- Pilih Jawaban --</option>
                @foreach(($q->options ?? []) as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        @elseif($q->type === 'checkbox')
            <div class="row g-2">
                @foreach(($q->options ?? []) as $optionIndex => $option)
                    <div class="col-md-6">
                        <label class="form-check border rounded p-3 w-100">
                            <input class="form-check-input" type="checkbox"
                                   name="scores[{{ $q->id }}][]"
                                   value="{{ $option }}"
                                   id="l34-check-{{ $q->id }}-{{ $optionIndex }}">
                            <span class="form-check-label ms-1">{{ $option }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        @else
            <textarea name="scores[{{ $q->id }}]" class="form-control" rows="3"
                      placeholder="Isi jawaban Anda..."></textarea>
        @endif
    </div>
</div>
@endforeach
