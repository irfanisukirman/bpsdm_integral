@extends('layouts.auth')

@section('content')
@php
    $roleMeta = match ($role) {
        'atasan' => ['label' => 'Atasan Langsung', 'icon' => 'bx-briefcase', 'class' => 'info', 'description' => 'Nilai perubahan perilaku dan dampak kinerja alumni yang Anda amati.'],
        'rekan' => ['label' => 'Rekan Kerja', 'icon' => 'bx-group', 'class' => 'success', 'description' => 'Berikan penilaian berdasarkan pengalaman bekerja bersama alumni.'],
        default => ['label' => 'Peserta / Alumni', 'icon' => 'bx-user-pin', 'class' => 'primary', 'description' => 'Isi penilaian berdasarkan pengalaman dan perubahan yang Anda rasakan.'],
    };
    $roleLabel = $roleMeta['label'];
    $totalParticipants = $alreadyFilled->count() + $participants->count();
    $completionPercent = $totalParticipants > 0 ? round(($alreadyFilled->count() / $totalParticipants) * 100) : 100;
    $questionTotal = $questions->sum(fn ($items) => $items->count());
@endphp

<style>
    :root { --l34-primary:#635bff; --l34-dark:#182230; --l34-muted:#667085; --l34-line:#e7eaf0; --l34-page:#f5f7fb; }
    body { background: radial-gradient(circle at 10% 0, rgba(99,91,255,.09), transparent 28rem), var(--l34-page); }
    .container-xxl { max-width: 100% !important; padding: 0 !important; }
    .evaluation-shell { width:min(1240px, calc(100% - 32px)); margin:0 auto; padding:24px 0 48px; }
    .evaluation-topbar { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px; }
    .brand-wrap { display:flex; align-items:center; gap:10px; color:var(--l34-dark); text-decoration:none; }
    .brand-wrap img { width:40px; height:40px; object-fit:contain; padding:5px; border-radius:12px; background:#fff; box-shadow:0 5px 18px rgba(16,24,40,.08); }
    .brand-title { font-weight:800; letter-spacing:.04em; }
    .brand-subtitle { color:var(--l34-muted); font-size:.72rem; }
    .back-link { display:inline-flex; align-items:center; gap:7px; padding:9px 13px; color:#475467; background:#fff; border:1px solid var(--l34-line); border-radius:11px; text-decoration:none; font-size:.82rem; font-weight:600; }
    .back-link:hover { color:var(--l34-primary); border-color:#c9c5ff; }

    .page-hero { position:relative; overflow:hidden; display:flex; justify-content:space-between; gap:24px; padding:30px 34px; margin-bottom:20px; color:#fff; border-radius:24px; background:linear-gradient(135deg,#292561,#4339ad 58%,#635bff); box-shadow:0 18px 42px rgba(50,43,137,.18); }
    .page-hero::after { content:""; position:absolute; width:230px; height:230px; right:-90px; top:-125px; border-radius:50%; background:rgba(255,255,255,.08); }
    .hero-copy { position:relative; z-index:1; min-width:0; }
    .hero-kicker { display:flex; align-items:center; gap:7px; margin-bottom:8px; color:rgba(255,255,255,.7); font-size:.72rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
    .page-hero h1 { margin:0 0 8px; color:#fff; font-size:clamp(1.45rem,3vw,2.05rem); letter-spacing:-.025em; }
    .training-title { max-width:760px; margin:0; color:rgba(255,255,255,.74); line-height:1.55; }
    .role-badge { position:relative; z-index:1; align-self:center; display:flex; align-items:center; gap:11px; min-width:190px; padding:13px 15px; border:1px solid rgba(255,255,255,.17); border-radius:16px; background:rgba(255,255,255,.1); backdrop-filter:blur(8px); }
    .role-badge i { font-size:1.6rem; }
    .role-badge small { display:block; color:rgba(255,255,255,.62); }
    .role-badge strong { display:block; color:#fff; font-size:.9rem; }

    .content-grid { display:grid; grid-template-columns:minmax(0,1fr) 310px; gap:20px; align-items:start; }
    .form-card, .side-card { background:#fff; border:1px solid var(--l34-line); border-radius:22px; box-shadow:0 10px 30px rgba(16,24,40,.055); }
    .form-card { overflow:hidden; }
    .form-card-body { padding:28px; }

    .wizard-progress { --wizard-progress:0%; position:relative; display:grid; grid-template-columns:repeat(4,1fr); margin:0; padding:24px 28px 22px; border-bottom:1px solid var(--l34-line); background:#fbfcfe; }
    .wizard-progress::before, .wizard-progress::after { content:""; position:absolute; left:calc(12.5% + 8px); right:calc(12.5% + 8px); top:41px; height:3px; border-radius:5px; }
    .wizard-progress::before { background:#e7e9ef; }
    .wizard-progress::after { right:auto; width:var(--wizard-progress); background:#635bff; transition:width .25s ease; }
    .wizard-progress-item { position:relative; z-index:1; display:flex; flex-direction:column; align-items:center; gap:7px; color:#98a2b3; text-align:center; }
    .wizard-progress-item span { display:grid; place-items:center; width:36px; height:36px; border:3px solid #fbfcfe; border-radius:50%; background:#eaecf0; font-size:.8rem; font-weight:800; transition:.2s ease; }
    .wizard-progress-item small { font-size:.73rem; font-weight:700; }
    .wizard-progress-item.active { color:var(--l34-primary); }
    .wizard-progress-item.active span { color:#fff; background:var(--l34-primary); box-shadow:0 0 0 5px rgba(99,91,255,.12); }
    .wizard-progress-item.completed { color:#344054; }
    .wizard-progress-item.completed span { color:#fff; background:#12b76a; }

    .step-header { display:flex; gap:14px; align-items:flex-start; margin-bottom:22px; }
    .step-icon { flex:0 0 auto; display:grid; place-items:center; width:46px; height:46px; color:var(--l34-primary); border-radius:14px; background:#efeeff; font-size:1.35rem; }
    .step-header h2 { margin:0 0 5px; color:var(--l34-dark); font-size:1.25rem; }
    .step-header p { margin:0; color:var(--l34-muted); font-size:.82rem; line-height:1.5; }
    .identity-panel { padding:20px; margin-bottom:22px; border:1px solid #dedbff; border-radius:17px; background:linear-gradient(180deg,#f8f7ff,#fff); }
    .form-label { margin-bottom:7px; color:#344054; font-size:.76rem; font-weight:700; letter-spacing:.02em; }
    .form-control, .form-select { min-height:45px; border-color:#dfe3ea; border-radius:11px; }
    .form-control:focus, .form-select:focus { border-color:#8d87ff; box-shadow:0 0 0 .2rem rgba(99,91,255,.1); }
    .selected-status { display:inline-flex; margin-top:9px; padding:6px 9px; color:#5148cf; border-radius:8px; background:#eeecff; font-size:.72rem; font-weight:700; }

    .question-card { padding:20px; margin-bottom:14px; border:1px solid var(--l34-line); border-radius:17px; background:#fff; transition:border-color .2s, box-shadow .2s; }
    .question-card:focus-within { border-color:#bcb7ff; box-shadow:0 8px 22px rgba(99,91,255,.08); }
    .question-head { display:flex; gap:13px; align-items:flex-start; }
    .question-number { flex:0 0 auto; display:grid; place-items:center; width:30px; height:30px; color:#5148cf; border-radius:9px; background:#eeecff; font-size:.75rem; font-weight:800; }
    .question-copy { min-width:0; }
    .question-title { display:block; margin:2px 0 7px; color:#253044; font-size:.91rem; font-weight:700; line-height:1.55; }
    .answer-type { display:inline-flex; align-items:center; gap:5px; color:#7b8494; font-size:.7rem; }
    .question-answer { padding:17px 0 0 43px; }
    .slider-wrapper { padding:2px 4px; }
    .slider-value-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
    .score-label { color:var(--l34-muted); font-size:.75rem; }
    .score-output { min-width:45px; padding:7px 10px; color:#fff; border-radius:9px; background:#635bff; text-align:center; font-weight:800; }
    .kirkpatrick-slider { height:8px; }
    .kirkpatrick-slider::-webkit-slider-thumb { width:21px; height:21px; border:4px solid #fff; border-radius:50%; background:#635bff; box-shadow:0 2px 9px rgba(99,91,255,.4); }
    .slider-scale { display:flex; justify-content:space-between; gap:8px; margin-top:9px; color:#98a2b3; font-size:.65rem; }
    .slider-scale strong { color:#475467; font-size:.72rem; }
    .choice-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:9px; }
    .choice-option { display:flex; gap:9px; align-items:flex-start; min-height:48px; margin:0; padding:12px; border:1px solid #e2e5eb; border-radius:11px; cursor:pointer; transition:.18s ease; }
    .choice-option:hover, .choice-option:has(input:checked) { border-color:#aaa5ff; background:#f7f6ff; }
    .choice-option span { color:#475467; font-size:.79rem; line-height:1.4; }
    .answer-control { background:#fbfcfe; }
    .section-empty { padding:17px; color:#8a5a00; border:1px dashed #efca84; border-radius:13px; background:#fff9ec; font-size:.8rem; }
    .step-actions { display:flex; justify-content:space-between; gap:12px; padding-top:22px; margin-top:8px; border-top:1px solid #eef0f4; }
    .step-actions .btn { min-height:44px; padding-inline:18px; border-radius:11px; font-weight:700; }

    .sidebar { position:sticky; top:18px; display:grid; gap:14px; }
    .side-card { padding:20px; }
    .side-title { display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:13px; }
    .side-title h3 { margin:0; font-size:.92rem; color:#253044; }
    .progress-copy { display:flex; justify-content:space-between; color:var(--l34-muted); font-size:.72rem; }
    .progress { height:7px; margin:9px 0 5px; background:#eaecf0; }
    .progress-bar { background:linear-gradient(90deg,#635bff,#8c86ff); }
    .queue-list { max-height:330px; overflow:auto; margin:0; padding:0; list-style:none; }
    .queue-item { display:flex; align-items:center; gap:9px; padding:9px 0; border-bottom:1px solid #f0f1f4; }
    .queue-item:last-child { border-bottom:0; }
    .queue-icon { flex:0 0 auto; font-size:1.05rem; }
    .queue-name { overflow:hidden; color:#475467; font-size:.75rem; white-space:nowrap; text-overflow:ellipsis; }
    .queue-item.pending { opacity:.58; }
    .info-list { display:grid; gap:12px; }
    .info-row { display:flex; gap:9px; color:#667085; font-size:.76rem; line-height:1.45; }
    .info-row i { flex:0 0 auto; margin-top:1px; color:var(--l34-primary); font-size:1.05rem; }

    .success-state, .empty-queue { padding:65px 28px; text-align:center; }
    .state-icon { display:grid; place-items:center; width:78px; height:78px; margin:0 auto 18px; border-radius:50%; font-size:2.8rem; }
    .success-state .state-icon { color:#079455; background:#e8f8ef; }
    .empty-queue .state-icon { color:#635bff; background:#eeecff; }
    .success-state h2, .empty-queue h2 { margin-bottom:8px; color:var(--l34-dark); }
    .success-state p, .empty-queue p { max-width:520px; margin:0 auto 22px; color:var(--l34-muted); }
    .alert { border-radius:14px; }
    .public-footer { padding-top:18px; color:#8a93a2; text-align:center; font-size:.72rem; }

    @media(max-width:991px) { .content-grid{grid-template-columns:1fr}.sidebar{position:static;grid-template-columns:repeat(2,minmax(0,1fr))}.queue-card{grid-column:1/-1}.page-hero{padding:27px}.form-card-body{padding:24px} }
    @media(max-width:575px) { .evaluation-shell{width:calc(100% - 18px);padding:12px 0 30px}.back-link span{display:none}.page-hero{display:block;padding:24px 20px;border-radius:19px}.role-badge{margin-top:18px;min-width:0}.wizard-progress{padding:20px 8px 18px}.wizard-progress::before,.wizard-progress::after{top:37px}.wizard-progress-item small{font-size:.6rem}.form-card{border-radius:18px}.form-card-body{padding:20px 15px}.question-card{padding:16px}.question-answer{padding-left:0}.choice-grid{grid-template-columns:1fr}.slider-scale span{font-size:.57rem}.sidebar{grid-template-columns:1fr}.step-actions .btn{padding-inline:13px}.step-actions .btn-label{display:none} }
</style>

<div class="evaluation-shell">
    <header class="evaluation-topbar">
        <a href="{{ route('public.l34.gateway', $training->id) }}" class="brand-wrap">
            <img src="{{ asset('assets/img/favicon/inte.png') }}" alt="Logo INTEGRAL">
            <span><span class="brand-title d-block">INTEGRAL</span><span class="brand-subtitle d-block">Evaluasi Pasca Pelatihan</span></span>
        </a>
        <a href="{{ route('public.l34.gateway', $training->id) }}" class="back-link"><i class="bx bx-left-arrow-alt"></i><span>Ganti peran</span></a>
    </header>

    <section class="page-hero">
        <div class="hero-copy">
            <div class="hero-kicker"><i class="bx bx-line-chart"></i> Evaluasi Kirkpatrick Level 3 & 4</div>
            <h1>Formulir Penilaian 360°</h1>
            <p class="training-title">{{ $training->nama_pelatihan }}</p>
        </div>
        <div class="role-badge"><i class="bx {{ $roleMeta['icon'] }}"></i><span><small>Mengisi sebagai</small><strong>{{ $roleMeta['label'] }}</strong></span></div>
    </section>

    @if(session('success'))
        <div class="form-card success-state">
            <span class="state-icon"><i class="bx bx-check"></i></span>
            <h2>Penilaian berhasil dikirim</h2>
            <p>{{ session('success') }} Terima kasih telah membantu peningkatan mutu pelatihan.</p>
            <a href="{{ route('public.l34.gateway', $training->id) }}" class="btn btn-primary btn-lg"><i class="bx bx-grid-alt me-1"></i>Kembali ke pilihan peran</a>
        </div>
    @else
        @if($errors->any())
            <div class="alert alert-danger mb-3"><div class="fw-bold mb-1"><i class="bx bx-error-circle me-1"></i>Beberapa data belum lengkap</div><ul class="mb-0 ps-3 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mb-3"><i class="bx bx-error-circle me-1"></i>{{ session('error') }}</div>
        @endif

        <div class="content-grid">
            <div class="form-card">
                @if($formParticipants->isEmpty())
                    <div class="empty-queue">
                        <span class="state-icon"><i class="bx bx-check-double"></i></span>
                        <h2>Semua penilaian telah selesai</h2>
                        <p>{{ ($isSelfService ?? false) ? 'Evaluasi mandiri Anda sudah tercatat.' : 'Seluruh alumni sudah dinilai untuk peran '.$roleLabel.'.' }}</p>
                        <a href="{{ route('public.l34.gateway', $training->id) }}" class="btn btn-primary">Kembali ke pilihan peran</a>
                    </div>
                @else
                    <form action="{{ route('public.l34.store', [$training->id, $role]) }}" method="POST" id="l34WizardForm" novalidate>
                        @csrf
                        <div class="wizard-progress" id="wizardProgress">
                            @foreach([1=>'Data Diri',2=>'Penempatan',3=>'Perilaku',4=>'Dampak'] as $step=>$label)
                                <div class="wizard-progress-item {{ $step===1?'active':'' }}" data-indicator="{{ $step }}"><span>{{ $step }}</span><small>{{ $label }}</small></div>
                            @endforeach
                        </div>

                        <div class="form-card-body">
                            <section class="wizard-step" data-step="1">
                                <div class="step-header"><span class="step-icon"><i class="bx bx-id-card"></i></span><div><h2>Data diri dan profil</h2><p>Pilih alumni yang dinilai, kemudian lengkapi informasi identitas yang diperlukan.</p></div></div>
                                <div class="identity-panel">
                                    <div class="mb-3">
                                        @if($isSelfService ?? false)
                                            <label class="form-label">EVALUASI MANDIRI ATAS NAMA</label>
                                            <div class="p-3 border rounded bg-white">
                                                <div class="fw-bold text-dark">{{ $selfParticipant->name }}</div>
                                                <div class="small text-muted">NIP/NIK: {{ $selfParticipant->nip_nik ?: '-' }}</div>
                                            </div>
                                            <input type="hidden" name="participant_id" id="participantSelect" value="{{ $selfParticipant->id }}" data-status="{{ strtoupper($selfParticipant->status_kepegawaian ?: $selfParticipant->user?->status_kepegawaian ?: 'BELUM DIISI') }}">
                                        @else
                                        <label class="form-label" for="participantSelect">NAMA ALUMNI YANG DINILAI <span class="text-danger">*</span></label>
                                        <select name="participant_id" id="participantSelect" class="form-select form-select-lg" required>
                                            <option value="">Cari dan pilih nama / NIP alumni</option>
                                            @foreach($formParticipants as $p)
                                                <option value="{{ $p->id }}" data-status="{{ strtoupper($p->status_kepegawaian ?: $p->user?->status_kepegawaian ?: 'BELUM DIISI') }}" @selected(old('participant_id')==$p->id)>{{ $p->name }} — {{ $p->nip_nik ?: 'NIP/NIK belum tersedia' }}</option>
                                            @endforeach
                                        </select>
                                        @endif
                                        <span class="selected-status"><i class="bx bx-badge-check me-1"></i>Status: <span id="participantStatus" class="ms-1">-</span></span>
                                    </div>

                                    @if($role==='mandiri')
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label">PENDIDIKAN SAAT PELATIHAN</label><select name="edu_before" class="form-select" required>@foreach(['SD/SMP','SMA/SMK','D3','D4/S1','S2/S3'] as $v)<option value="{{ $v }}" @selected(old('edu_before')===$v)>{{ $v }}</option>@endforeach</select></div>
                                            <div class="col-md-6"><label class="form-label">PENDIDIKAN SAAT INI</label><select name="edu_after" class="form-select" required>@foreach(['SD/SMP','SMA/SMK','D3','D4/S1','S2/S3'] as $v)<option value="{{ $v }}" @selected(old('edu_after')===$v)>{{ $v }}</option>@endforeach</select></div>
                                            <div class="col-md-6"><label class="form-label">PANGKAT SAAT PELATIHAN</label><select name="rank_before" class="form-select" required>@foreach(['I/a','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c'] as $v)<option value="{{ $v }}" @selected(old('rank_before')===$v)>{{ $v }}</option>@endforeach</select></div>
                                            <div class="col-md-6"><label class="form-label">PANGKAT SAAT INI</label><select name="rank_after" class="form-select" required>@foreach(['I/a','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c'] as $v)<option value="{{ $v }}" @selected(old('rank_after')===$v)>{{ $v }}</option>@endforeach</select></div>
                                            @foreach(['pos_before'=>'JABATAN SAAT PELATIHAN','pos_after'=>'JABATAN SAAT INI','unit_before'=>'UNIT KERJA SAAT PELATIHAN','unit_after'=>'UNIT KERJA SAAT INI','dept_before'=>'PERANGKAT DAERAH SAAT PELATIHAN','dept_after'=>'PERANGKAT DAERAH SAAT INI'] as $name=>$label)
                                                <div class="col-md-6"><label class="form-label">{{ $label }}</label><input type="text" name="{{ $name }}" class="form-control" value="{{ old($name) }}" required></div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="row g-3">
                                            @foreach(['evaluator_name'=>'NAMA LENGKAP ANDA','evaluator_nip'=>'NIP / NIK ANDA','evaluator_pos'=>'JABATAN ANDA','evaluator_unit'=>'UNIT KERJA ANDA'] as $name=>$label)
                                                <div class="col-md-6"><label class="form-label">{{ $label }}</label><input type="text" name="{{ $name }}" class="form-control" value="{{ old($name) }}" required></div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @include('evaluasi.partials.l34_questions',['items'=>$questionSections['profile']])
                                <div class="step-actions justify-content-end"><button type="button" class="btn btn-primary wizard-next">Lanjut ke Penempatan <i class="bx bx-right-arrow-alt ms-1"></i></button></div>
                            </section>

                            @foreach([
                                2=>['key'=>'placement','title'=>'Penempatan Tugas dan Transfer Learning','icon'=>'bx-transfer-alt','description'=>'Nilai penerapan hasil pelatihan dalam penugasan dan lingkungan kerja.'],
                                3=>['key'=>'behavior','title'=>'Perubahan Perilaku','icon'=>'bx-trending-up','description'=>'Nilai perubahan perilaku kerja yang terlihat setelah mengikuti pelatihan.'],
                                4=>['key'=>'impact','title'=>'Dampak Pelatihan','icon'=>'bx-bar-chart-alt-2','description'=>'Nilai dampak pelatihan terhadap kinerja individu maupun organisasi.'],
                            ] as $step=>$section)
                                <section class="wizard-step d-none" data-step="{{ $step }}">
                                    <div class="step-header"><span class="step-icon"><i class="bx {{ $section['icon'] }}"></i></span><div><h2>{{ $section['title'] }}</h2><p>{{ $section['description'] }} Perspektif: <strong>{{ $roleLabel }}</strong>.</p></div></div>
                                    @include('evaluasi.partials.l34_questions',['items'=>$questionSections[$section['key']]])
                                    @if($questionSections[$section['key']]->isEmpty())<div class="section-empty"><i class="bx bx-info-circle me-1"></i>Belum ada pertanyaan pada bagian ini. Anda dapat melanjutkan ke bagian berikutnya.</div>@endif
                                    <div class="step-actions">
                                        <button type="button" class="btn btn-outline-secondary wizard-prev"><i class="bx bx-left-arrow-alt me-1"></i><span class="btn-label">Kembali</span></button>
                                        @if($step<4)
                                            <button type="button" class="btn btn-primary wizard-next">Lanjut <i class="bx bx-right-arrow-alt ms-1"></i></button>
                                        @else
                                            <button type="submit" class="btn btn-primary" id="submitEvaluation"><i class="bx bx-paper-plane me-1"></i>Kirim Penilaian</button>
                                        @endif
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </form>
                @endif
            </div>

            <aside class="sidebar">
                <div class="side-card">
                    <div class="side-title"><h3>Informasi pengisian</h3><span class="badge bg-label-primary">{{ $questionTotal }} soal</span></div>
                    <div class="info-list">
                        <div class="info-row"><i class="bx bx-user-check"></i><span>{{ $roleMeta['description'] }}</span></div>
                        <div class="info-row"><i class="bx bx-time-five"></i><span>Isi setiap bagian secara bertahap dan pastikan jawaban sesuai kondisi sebenarnya.</span></div>
                        <div class="info-row"><i class="bx bx-shield-quarter"></i><span>Jawaban digunakan untuk peningkatan mutu pelatihan.</span></div>
                    </div>
                </div>
                <div class="side-card queue-card">
                    <div class="side-title"><h3>Progres penilaian</h3><span class="badge bg-label-success">{{ $alreadyFilled->count() }}/{{ $totalParticipants }}</span></div>
                    <div class="progress-copy"><span>Sudah dinilai</span><strong>{{ $completionPercent }}%</strong></div>
                    <div class="progress"><div class="progress-bar" style="width:{{ $completionPercent }}%"></div></div>
                    <ul class="queue-list mt-3">
                        @foreach($alreadyFilled as $a)<li class="queue-item"><i class="bx bxs-check-circle text-success queue-icon"></i><span class="queue-name">{{ $a->name }} | {{ $a->nip_nik ?: '-' }}</span></li>@endforeach
                        @foreach($participants as $n)<li class="queue-item pending"><i class="bx bx-time-five queue-icon"></i><span class="queue-name">{{ $n->name }} | {{ $n->nip_nik ?: '-' }}</span></li>@endforeach
                    </ul>
                </div>
            </aside>
        </div>
    @endif

    <footer class="public-footer">INTEGRAL &copy; {{ date('Y') }} · Sistem Informasi Pengembangan Kompetensi Terintegrasi</footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('l34WizardForm');
    if (!form) return;
    let currentStep = 1;
    const steps = Array.from(form.querySelectorAll('.wizard-step'));
    const indicators = Array.from(form.querySelectorAll('.wizard-progress-item'));
    const progress = document.getElementById('wizardProgress');

    function showStep(step) {
        currentStep = Math.max(1, Math.min(4, step));
        steps.forEach(item => item.classList.toggle('d-none', Number(item.dataset.step) !== currentStep));
        indicators.forEach(item => {
            const number = Number(item.dataset.indicator);
            item.classList.toggle('active', number === currentStep);
            item.classList.toggle('completed', number < currentStep);
            const circle = item.querySelector('span');
            circle.textContent = number < currentStep ? '✓' : number;
        });
        progress.style.setProperty('--wizard-progress', (((currentStep - 1) / 3) * 75) + '%');
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function validateCurrentStep() {
        const section = form.querySelector(`.wizard-step[data-step="${currentStep}"]`);
        for (const field of section.querySelectorAll(':required')) {
            if (!field.checkValidity()) { field.reportValidity(); field.focus(); return false; }
        }
        for (const group of section.querySelectorAll('[data-checkbox-group]')) {
            const inputs = group.querySelectorAll('input[type="checkbox"]');
            const feedback = group.nextElementSibling;
            if (inputs.length && !Array.from(inputs).some(input => input.checked)) {
                feedback?.classList.remove('d-none');
                group.scrollIntoView({ behavior:'smooth', block:'center' });
                return false;
            }
            feedback?.classList.add('d-none');
        }
        return true;
    }

    form.querySelectorAll('.wizard-next').forEach(button => button.addEventListener('click', () => { if (validateCurrentStep()) showStep(currentStep + 1); }));
    form.querySelectorAll('.wizard-prev').forEach(button => button.addEventListener('click', () => showStep(currentStep - 1)));

    const participantSelect = document.getElementById('participantSelect');
    function updateStatus() { document.getElementById('participantStatus').textContent = participantSelect?.selectedOptions?.[0]?.dataset.status || participantSelect?.dataset.status || '-'; }
    participantSelect?.addEventListener('change', updateStatus); updateStatus();

    function getKirkLabel(value) {
        value = Number(value);
        if (value <= 60) return ['Sangat Kurang','#e63757'];
        if (value <= 70) return ['Kurang','#f59e0b'];
        if (value <= 80) return ['Cukup','#70a82b'];
        if (value <= 90) return ['Baik','#0789ad'];
        return ['Sangat Baik','#635bff'];
    }
    form.querySelectorAll('.kirkpatrick-slider').forEach(slider => {
        const update = () => {
            const [label,color] = getKirkLabel(slider.value);
            const output = document.getElementById(`score-${slider.dataset.id}`);
            const text = document.getElementById(`label-${slider.dataset.id}`);
            output.textContent = slider.value; output.style.backgroundColor = color;
            text.textContent = label; text.style.color = color;
        };
        slider.addEventListener('input', update); update();
    });
    form.querySelectorAll('[data-checkbox-group] input').forEach(input => input.addEventListener('change', () => input.closest('[data-checkbox-group]').nextElementSibling?.classList.add('d-none')));

    form.addEventListener('submit', function (event) {
        if (!validateCurrentStep()) { event.preventDefault(); return; }
        const button = document.getElementById('submitEvaluation');
        if (button) { button.disabled = true; button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...'; }
    });
});
</script>
@endsection
