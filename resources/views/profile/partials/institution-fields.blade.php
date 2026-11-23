@php
    $scope = old('institution_scope', $user->institution_scope ?? ($user->institution_id ? 'province' : ($user->instansi ? 'other' : '')));
@endphp
<div class="col-12 mb-3" data-profile-institution>
    <label for="institution_scope" class="form-label">Lingkup Instansi <span class="required-star">*</span></label>
    <select id="institution_scope" name="institution_scope" class="form-select" required>
        <option value="">-- Pilih Lingkup Instansi --</option>
        @foreach(\App\Support\ProfileInstitution::SCOPES as $value => $label)
            <option value="{{ $value }}" @selected($scope === $value)>{{ $label }}</option>
        @endforeach
    </select>

    <div class="mt-3" data-institution-scopes="province" @if($scope !== 'province') hidden @endif>
        <label for="institution_id" class="form-label">Instansi / Unit Kerja <span class="required-star">*</span></label>
        <select id="institution_id" name="institution_id" class="form-select" @if($scope === 'province') required @else disabled @endif>
            <option value="">-- Pilih Instansi --</option>
            @foreach($institutions as $institution)
                <option value="{{ $institution->id }}" @selected((string) old('institution_id', $user->institution_id) === (string) $institution->id)>{{ $institution->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mt-3" data-institution-scopes="municipality" @if($scope !== 'municipality') hidden @endif>
        <label for="institution_municipality_id" class="form-label">Kabupaten / Kota <span class="required-star">*</span></label>
        <select id="institution_municipality_id" name="institution_municipality_id" class="form-select" @if($scope === 'municipality') required @else disabled @endif>
            <option value="">-- Pilih Kabupaten / Kota --</option>
            @foreach($municipalities as $municipality)
                <option value="{{ $municipality->id }}" @selected((string) old('institution_municipality_id', $user->institution_municipality_id) === (string) $municipality->id)>{{ $municipality->name }}</option>
            @endforeach
        </select>
    </div>

    @php $manual = in_array($scope, ['municipality', 'other'], true); @endphp
    <div class="mt-3" data-institution-scopes="municipality other" @unless($manual) hidden @endunless>
        <label for="institution_name" class="form-label">Nama Instansi / Unit Kerja <span class="required-star">*</span></label>
        <input id="institution_name" name="institution_name" class="form-control" maxlength="255"
               value="{{ old('institution_name', $manual && !$user->institution_id ? $user->instansi : '') }}"
               @if($manual) required @else disabled @endif>
    </div>
</div>
<script>
    (() => {
        const group = document.currentScript.previousElementSibling;
        const scope = group.querySelector('[name="institution_scope"]');
        const fields = group.querySelectorAll('[data-institution-scopes]');
        const sync = () => fields.forEach(section => {
            const active = section.dataset.institutionScopes.split(' ').includes(scope.value);
            const field = section.querySelector('select, input');
            section.hidden = !active;
            field.disabled = !active;
            field.required = active;
        });
        scope.addEventListener('change', sync);
        window.addEventListener('pageshow', sync);
        sync();
    })();
</script>
