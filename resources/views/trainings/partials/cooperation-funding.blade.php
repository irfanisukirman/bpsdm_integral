<div class="row">
    @foreach([
        'kerjasama' => ['Kerjasama', \App\Models\Training::COOPERATION_OPTIONS],
        'sumber_anggaran' => ['Sumber Anggaran', \App\Models\Training::FUNDING_SOURCE_OPTIONS],
    ] as $field => [$label, $options])
        <div class="col-md-6 mb-3">
            <label for="{{ $field }}" class="form-label">{{ $label }}</label>
            <select name="{{ $field }}" id="{{ $field }}" class="form-select @error($field) is-invalid @enderror">
                <option value="">Pilih {{ $label }}</option>
                @foreach($options as $option)
                    <option value="{{ $option }}" @selected(old($field, $training?->{$field} ?? null) === $option)>{{ $option }}</option>
                @endforeach
            </select>
            @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endforeach
</div>
