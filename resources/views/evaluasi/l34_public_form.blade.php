@extends('layouts.auth')

@section('content')
<div class="container-xxl py-5">
    <div class="row justify-content-center">
        <!-- Main Wrapper -->
        <div class="col-lg-11">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-dark text-white text-center py-4">
                    <h4 class="text-white mb-1">EVALUASI PASCA PELATIHAN (360°)</h4>
                    <p class="mb-0 opacity-75">{{ $training->nama_pelatihan }}</p>
                </div>

                <div class="card-body p-4">
                    <!-- Bagian Pesan Notifikasi -->
                    @if(session('success'))
                        <div class="text-center py-5 animate__animated animate__fadeIn">
                            <i class="bx bx-check-circle text-success mb-4" style="font-size: 6rem;"></i>
                            <h3 class="fw-bold">Berhasil Terkirim!</h3>
                            <p class="text-muted mb-4 px-lg-5">{{ session('success') }}</p>
                            <a href="{{ route('public.l34.gateway', $training->id) }}" class="btn btn-primary btn-lg shadow">
                                <i class="bx bx-grid-alt me-1"></i> Pilih Peran Lain / Lanjutkan
                            </a>
                        </div>
                    @else
                        <!-- Tampilkan Error Validasi -->
                        @if ($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm mb-4">
                                <div class="fw-bold mb-2"><i class="bx bx-error-circle me-1"></i> Mohon perbaiki:</div>
                                <ul class="mb-0 small">
                                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger border-0 shadow-sm mb-4">
                                <i class="bx bx-error me-1"></i> {{ session('error') }}
                            </div>
                        @endif

                        <div class="row">
                            <!-- KOLOM KIRI: FORMULIR -->
                            <div class="col-md-7 col-lg-8 border-end">
                                @if($participants->isEmpty())
                                    <div class="text-center py-5">
                                        <i class="bx bx-party text-primary mb-3" style="font-size: 5rem;"></i>
                                        <h5>Antrean Selesai!</h5>
                                        <p class="text-muted">Semua alumni telah dinilai untuk peran <strong>{{ strtoupper($role) }}</strong>.</p>
                                    </div>
                                @else
                                    @php
                                        $roleLabel = match($role) {
                                            'rekan' => 'Rekan Kerja',
                                            'atasan' => 'Atasan Langsung',
                                            default => 'Mandiri',
                                        };
                                    @endphp
                                    <form action="{{ route('public.l34.store', [$training->id, $role]) }}" method="POST" id="l34WizardForm">
                                        @csrf

                                        <div class="wizard-progress mb-4">
                                            @foreach([1 => 'Data Diri', 2 => 'Penempatan', 3 => 'Perilaku', 4 => 'Dampak'] as $step => $label)
                                                <div class="wizard-progress-item {{ $step === 1 ? 'active' : '' }}" data-indicator="{{ $step }}">
                                                    <span>{{ $step }}</span><small>{{ $label }}</small>
                                                </div>
                                            @endforeach
                                        </div>

                                        <section class="wizard-step" data-step="1">
                                            <h5 class="section-heading">
                                                1. Data Diri Alumni (Status <span id="participantStatus">-</span>)
                                            </h5>
                                        
                                        {{-- 1. DATA PROFIL --}}
                                        <div class="card bg-label-primary border-0 mb-5 shadow-none">
                                            <div class="card-body">
                                                <h5 class="fw-bold mb-4 text-primary text-uppercase">
                                                    <i class="bx bx-user-circle me-2"></i>Data Profil {{ $role == 'mandiri' ? 'Alumni' : 'Penilai' }}
                                                </h5>
                                                
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">PILIH NAMA ALUMNI</label>
                                                    <select name="participant_id" id="participantSelect" class="form-select form-select-lg border-primary" required>
                                                        <option value="">-- Cari Nama / NIP --</option>
                                                        @foreach($participants as $p)
                                                            <option value="{{ $p->id }}" data-status="{{ strtoupper($p->status_kepegawaian ?: $p->user?->status_kepegawaian ?: 'BELUM DIISI') }}">{{ $p->name }} - (NIP: {{ $p->nip_nik }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                @if($role == 'mandiri')
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold">PENDIDIKAN SAAT PELATIHAN</label>
                                                            <select name="edu_before" class="form-select">
                                                                @foreach(['SD/SMP','SMA/SMK','D3','D4/S1','S2/S3'] as $edu) <option value="{{ $edu }}">{{ $edu }}</option> @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold">PENDIDIKAN SAAT INI</label>
                                                            <select name="edu_after" class="form-select">
                                                                @foreach(['SD/SMP','SMA/SMK','D3','D4/S1','S2/S3'] as $edu) <option value="{{ $edu }}">{{ $edu }}</option> @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold">PANGKAT SAAT PELATIHAN</label>
                                                            <select name="rank_before" class="form-select">
                                                                @foreach(['I/a','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c'] as $gol) <option value="{{ $gol }}">{{ $gol }}</option> @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold">PANGKAT SAAT INI</label>
                                                            <select name="rank_after" class="form-select">
                                                                @foreach(['I/a','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c'] as $gol) <option value="{{ $gol }}">{{ $gol }}</option> @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6"><label class="form-label small fw-bold">JABATAN (DULU)</label><input type="text" name="pos_before" class="form-control" value="{{ old('pos_before') }}" required></div>
                                                        <div class="col-md-6"><label class="form-label small fw-bold">JABATAN (SKRG)</label><input type="text" name="pos_after" class="form-control" value="{{ old('pos_after') }}" required></div>
                                                        <div class="col-md-6"><label class="form-label small fw-bold">UNIT KERJA (DULU)</label><input type="text" name="unit_before" class="form-control" value="{{ old('unit_before') }}" required></div>
                                                        <div class="col-md-6"><label class="form-label small fw-bold">UNIT KERJA (SKRG)</label><input type="text" name="unit_after" class="form-control" value="{{ old('unit_after') }}" required></div>
                                                        <div class="col-md-6"><label class="form-label small fw-bold">PERANGKAT DAERAH (SAAT PELATIHAN)</label><input type="text" name="dept_before" class="form-control" value="{{ old('dept_before') }}" required></div>
                                                        <div class="col-md-6"><label class="form-label small fw-bold">PERANGKAT DAERAH (SAAT INI)</label><input type="text" name="dept_after" class="form-control" value="{{ old('dept_after') }}" required></div>
                                                    </div>
                                                @else
                                                    <div class="row g-3">
                                                        <div class="col-md-6"><label class="form-label fw-bold small">NAMA ANDA</label><input type="text" name="evaluator_name" class="form-control" required></div>
                                                        <div class="col-md-6"><label class="form-label fw-bold small">NIP / NIK ANDA</label><input type="text" name="evaluator_nip" class="form-control" required></div>
                                                        <div class="col-md-6"><label class="form-label fw-bold small">JABATAN ANDA</label><input type="text" name="evaluator_pos" class="form-control" required></div>
                                                        <div class="col-md-6"><label class="form-label fw-bold small">UNIT KERJA ANDA</label><input type="text" name="evaluator_unit" class="form-control" required></div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        @include('evaluasi.partials.l34_questions', ['items' => $questionSections['profile']])
                                        <div class="d-flex justify-content-end mb-4">
                                            <button type="button" class="btn btn-primary wizard-next">Lanjut <i class="bx bx-right-arrow-alt ms-1"></i></button>
                                        </div>
                                        </section>

                                        {{-- 2. PENEMPATAN TUGAS --}}
                                        <section class="wizard-step d-none" data-step="2">
                                        <div class="mb-5 p-2">
                                            <h5 class="section-heading">2. Penempatan Tugas dan Transfer Learning ({{ $roleLabel }})</h5>
                                            @include('evaluasi.partials.l34_questions', ['items' => $questionSections['placement']])
                                            @if($questionSections['placement']->isEmpty())
                                                <div class="alert alert-warning">Pertanyaan Penempatan Tugas belum tersedia untuk bidang dan peran ini.</div>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between mb-4">
                                            <button type="button" class="btn btn-outline-secondary wizard-prev"><i class="bx bx-left-arrow-alt me-1"></i>Kembali</button>
                                            <button type="button" class="btn btn-primary wizard-next">Lanjut <i class="bx bx-right-arrow-alt ms-1"></i></button>
                                        </div>
                                        </section>

                                        {{-- 3. INSTRUMEN --}}
                                        <section class="wizard-step d-none" data-step="3">
                                            <h5 class="section-heading">3. Perubahan Perilaku ({{ $roleLabel }})</h5>
                                            @include('evaluasi.partials.l34_questions', ['items' => $questionSections['behavior']])
                                            @if($questionSections['behavior']->isEmpty())
                                                <div class="alert alert-warning">Pertanyaan Perubahan Perilaku belum tersedia untuk bidang dan peran ini.</div>
                                            @endif
                                            <div class="d-flex justify-content-between mb-4">
                                                <button type="button" class="btn btn-outline-secondary wizard-prev"><i class="bx bx-left-arrow-alt me-1"></i>Kembali</button>
                                                <button type="button" class="btn btn-primary wizard-next">Lanjut <i class="bx bx-right-arrow-alt ms-1"></i></button>
                                            </div>
                                        </section>

                                        <section class="wizard-step d-none" data-step="4">
                                            <h5 class="section-heading">4. Dampak Pelatihan ({{ $roleLabel }})</h5>
                                            @include('evaluasi.partials.l34_questions', ['items' => $questionSections['impact']])
                                            @if($questionSections['impact']->isEmpty())
                                                <div class="alert alert-warning">Pertanyaan Dampak Pelatihan belum tersedia untuk bidang dan peran ini.</div>
                                            @endif
                                            <div class="d-flex justify-content-between mb-5">
                                                <button type="button" class="btn btn-outline-secondary wizard-prev"><i class="bx bx-left-arrow-alt me-1"></i>Kembali</button>
                                                <button type="submit" class="btn btn-primary btn-lg shadow fw-bold">
                                                    <i class="bx bx-paper-plane me-2"></i>Kirim Penilaian
                                                </button>
                                            </div>
                                        </section>
                                    </form>
                                @endif
                            </div>

                            <!-- KOLOM KANAN: PROGRES -->
                            <div class="col-md-5 col-lg-4 ps-md-4 mt-4 mt-md-0">
                                <div class="sticky-top" style="top: 20px;">
                                    <h6 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                                        <span><i class="bx bx-list-ol me-2"></i>ANTREAN PROGRES</span>
                                        <span class="badge bg-label-secondary">{{ $alreadyFilled->count() }} / {{ $alreadyFilled->count() + $participants->count() }}</span>
                                    </h6>
                                    
                                    <div class="overflow-auto scroll-area border rounded p-2 bg-white" style="max-height: 500px;">
                                        <ul class="list-group list-group-flush">
                                            @foreach($alreadyFilled as $a)
                                                <li class="list-group-item d-flex align-items-center bg-transparent py-2 border-bottom">
                                                    <i class="bx bxs-check-circle text-success me-2 h5 mb-0"></i>
                                                    <span class="text-dark small fw-bold">{{ $a->name }}</span>
                                                </li>
                                            @endforeach
                                            @foreach($participants as $n)
                                                <li class="list-group-item d-flex align-items-center bg-transparent py-2 border-bottom opacity-50">
                                                    <i class="bx bx-loader-circle text-muted me-2 h5 mb-0"></i>
                                                    <span class="text-muted small">{{ $n->name }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-light text-center py-3">
                    <small class="text-muted">Powered by <strong>INTEGRAL</strong> &copy; {{ date('Y') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let currentStep = 1;

    function showStep(step) {
        currentStep = step;
        $('.wizard-step').addClass('d-none');
        $('.wizard-step[data-step="' + step + '"]').removeClass('d-none');
        $('.wizard-progress-item').each(function() {
            const indicator = Number($(this).data('indicator'));
            $(this).toggleClass('active', indicator === step);
            $(this).toggleClass('completed', indicator < step);
        });
        window.scrollTo({ top: $('#l34WizardForm').offset().top - 20, behavior: 'smooth' });
    }

    function currentStepIsValid() {
        const fields = $('.wizard-step[data-step="' + currentStep + '"]').find(':input[required]').toArray();
        for (const field of fields) {
            if (!field.checkValidity()) {
                field.reportValidity();
                return false;
            }
        }
        return true;
    }

    $('.wizard-next').on('click', function() {
        if (currentStepIsValid()) showStep(Math.min(4, currentStep + 1));
    });

    $('.wizard-prev').on('click', function() {
        showStep(Math.max(1, currentStep - 1));
    });

    $('#participantSelect').on('change', function() {
        const status = $(this).find(':selected').data('status') || '-';
        $('#participantStatus').text(status);
    }).trigger('change');

    function getKirkLabel(val) {
        val = parseInt(val);
        if (val <= 60) return { t: "Sangat Kurang", c: "#ff3e1d" };
        if (val <= 70) return { t: "Kurang", c: "#fdac41" };
        if (val <= 80) return { t: "Cukup", c: "#71dd37" };
        if (val <= 90) return { t: "Baik", c: "#03c3ec" };
        return { t: "Sangat Baik", c: "#696cff" };
    }

    $('.kirkpatrick-slider').on('input', function() {
        let val = $(this).val();
        let id = $(this).data('id');
        let res = getKirkLabel(val);
        $(`#score-${id}`).text(val).css('background-color', res.c);
        $(`#label-${id}`).text(res.t).css('color', res.c);
    });

    $('.kirkpatrick-slider').each(function() {
        $(this).trigger('input');
    });
});
</script>

<style>
    body { background-color: #f5f5f9; }
    .scroll-area::-webkit-scrollbar { width: 3px; }
    .scroll-area::-webkit-scrollbar-thumb { background: #d9dee3; border-radius: 10px; }
    .kirkpatrick-slider { height: 10px; -webkit-appearance: none; background: #ebedef; border-radius: 5px; outline: none; }
    .kirkpatrick-slider::-webkit-slider-thumb { 
        width: 22px; height: 22px; background: #696cff; border: 4px solid #fff; 
        border-radius: 50%; cursor: pointer; box-shadow: 0 0 10px rgba(105,108,255,0.4); 
        -webkit-appearance: none;
    }
    .custom-option-basic { width: 100%; cursor: pointer; }
    .section-heading {
        color: #696cff;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 2px solid #e7e7ff;
        padding-bottom: .75rem;
        margin-bottom: 1.5rem;
    }
    .wizard-progress { display: flex; justify-content: space-between; position: relative; gap: .5rem; }
    .wizard-progress::before { content: ''; position: absolute; top: 17px; left: 8%; right: 8%; height: 2px; background: #e7e7ff; z-index: 0; }
    .wizard-progress-item { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; flex: 1; color: #a1acb8; text-align: center; }
    .wizard-progress-item span { width: 36px; height: 36px; display: grid; place-items: center; border-radius: 50%; background: #f0f2f4; font-weight: 700; margin-bottom: .4rem; }
    .wizard-progress-item small { font-weight: 600; }
    .wizard-progress-item.active span { background: #696cff; color: #fff; box-shadow: 0 0 0 5px rgba(105,108,255,.14); }
    .wizard-progress-item.active { color: #696cff; }
    .wizard-progress-item.completed span { background: #71dd37; color: #fff; }
    @media (max-width: 575.98px) {
        .wizard-progress-item small { font-size: 10px; }
        .wizard-progress::before { left: 10%; right: 10%; }
    }
</style>
@endsection
