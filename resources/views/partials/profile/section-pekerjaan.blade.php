{{--
    Section 4 - Pekerjaan (ASN).
    Parameter: $profile App\Models\User
--}}
<div class="card shadow-sm border-0{{ ($hidden ?? false) ? ' d-none' : '' }} mb-4" data-section="pekerjaan">
    <div class="card-body p-4">
        <div class="form-section-title">
            <i class="bx bx-briefcase-alt-2 fs-4 me-2"></i> Pekerjaan (ASN)
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Jabatan Saat Ini <span class="required-star">*</span></label>
                <input type="text"
                       name="jabatan"
                       class="form-control"
                       placeholder="Contoh: Analis SDM Aparatur"
                       value="{{ old('jabatan', $profile->jabatan) }}"
                       autocomplete="off"
                       required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Status Kepegawaian <span class="required-star">*</span></label>
                <select name="status_kepegawaian" class="form-select" required>
                    <option value="">-- Pilih Status --</option>
                    @foreach(['PNS', 'PPPK', 'PPPK-PW'] as $status)
                        <option value="{{ $status }}" @selected(old('status_kepegawaian', $profile->status_kepegawaian) === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Golongan <span class="text-muted small">(opsional)</span></label>
                <select name="golongan" class="form-select">
                    <option value="">-- Tidak memiliki golongan --</option>
                    @foreach($golonganOptions ?? \App\Http\Requests\ProfileDataRequest::GOLONGAN as $item)
                        <option value="{{ $item }}" @selected(old('golongan', $profile->golongan) === $item)>{{ $item }}</option>
                    @endforeach
                </select>
                <div class="form-text">Pilih golongan saat ini sesuai data kepegawaian.</div>
            </div>
        </div>
    </div>
</div>