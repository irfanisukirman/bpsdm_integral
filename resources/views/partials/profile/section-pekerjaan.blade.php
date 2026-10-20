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
                @php
                    $golonganPns = \App\Http\Requests\ProfileDataRequest::GOLONGAN_PNS;
                    $golonganPppk = \App\Http\Requests\ProfileDataRequest::GOLONGAN_PPPK;
                    $statusKepegawaian = old('status_kepegawaian', $profile->status_kepegawaian);
                    $golonganTerpilih = old('golongan', $profile->golongan);
                    // Golongan PPPK/PPPK-PW, selain itu (termasuk belum dipilih) memakai daftar PNS.
                    $golonganAktif = in_array($statusKepegawaian, ['PPPK', 'PPPK-PW'], true) ? $golonganPppk : $golonganPns;
                @endphp
                <label class="form-label">Golongan <span class="text-muted small">(opsional)</span></label>
                <select name="golongan"
                        id="registerGolongan"
                        class="form-select"
                        data-pns='@json($golonganPns)'
                        data-pppk='@json($golonganPppk)'>
                    <option value="">-- Tidak memiliki golongan --</option>
                    @foreach($golonganAktif as $item)
                        <option value="{{ $item }}" @selected($golonganTerpilih === $item)>{{ $item }}</option>
                    @endforeach
                </select>
                <div class="form-text">Pilih golongan sesuai status kepegawaian.</div>
            </div>
        </div>

        <script>
        (function () {
            const status = document.querySelector('select[name="status_kepegawaian"]');
            const golongan = document.getElementById('registerGolongan');
            if (!status || !golongan) return;

            let groups;
            try {
                groups = {
                    PNS: JSON.parse(golongan.dataset.pns || '[]'),
                    PPPK: JSON.parse(golongan.dataset.pppk || '[]'),
                };
            } catch (e) {
                return;
            }

            function render(keepValue) {
                const current = keepValue ? golongan.value : '';
                const list = (status.value === 'PPPK' || status.value === 'PPPK-PW') ? groups.PPPK : groups.PNS;
                golongan.innerHTML = '<option value="">-- Tidak memiliki golongan --</option>';
                list.forEach(function (item) {
                    const option = document.createElement('option');
                    option.value = item;
                    option.textContent = item;
                    if (item === current) option.selected = true;
                    golongan.appendChild(option);
                });
            }

            status.addEventListener('change', function () { render(false); });
            render(true);
        })();
        </script>
    </div>
</div>