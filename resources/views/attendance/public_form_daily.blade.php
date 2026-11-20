@extends('layouts.auth')

@section('title', 'Presensi Harian')
@section('integral_theme', 'light')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/integral-brand.css') }}?v=20261119-white-canvas">
@endpush

@section('content')
@php
    $dateLabel = \Carbon\CarbonImmutable::canBeCreatedFromFormat($date, 'Y-m-d')
        ? \Carbon\CarbonImmutable::parse($date)->locale('id')->translatedFormat('l, d F Y')
        : $date;
@endphp
<main class="daily-attendance container-xxl py-3 py-md-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8"> {{-- Ukuran diperlebar agar progres muat samping/bawah --}}
            <div class="card shadow-lg border-0">
                <div class="card-header attendance-hero p-4 p-md-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="{{ asset('assets/img/favicon/inte.png') }}" alt="" width="36" height="36" class="attendance-logo">
                        <strong>INTEGRAL</strong>
                    </div>
                    <h1 class="h3 mb-2">Presensi Harian</h1>
                    <p class="mb-2">{{ $training->nama_pelatihan }}</p>
                    <span class="attendance-date"><i class="bx bx-calendar" aria-hidden="true"></i> {{ $dateLabel }}</span>
                </div>

                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger d-flex align-items-start" role="alert">
                            <i class="bx bx-error-circle fs-4 me-2"></i>
                            <div>
                                <strong>Presensi belum tersimpan.</strong>
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if($status == 'not_set')
                        <div class="attendance-state text-center mx-auto">
                            <i class="bx bx-error-circle text-warning mb-3" style="font-size: 5rem;"></i>
                            <h5>Akses Belum Tersedia</h5>
                            <p class="text-muted mb-0">Admin belum menetapkan waktu presensi untuk tanggal ini.</p>
                        </div>
                    @elseif($status == 'closed')
                        <div class="attendance-state text-center mx-auto">
                            <i class="bx bx-time-five text-danger mb-3" style="font-size: 5rem;"></i>
                            <h2 class="h5 fw-bold">Presensi di Luar Jam Pengisian</h2>
                            <p class="text-muted">Presensi dapat diisi pada jam berikut.</p>
                            <div class="h4 fw-bold text-primary bg-label-primary p-3 rounded d-inline-block">
                                {{ substr($open, 0, 5) }}–{{ substr($close, 0, 5) }} WIB
                            </div>
                        </div>
                    @else
                        <div class="row g-4">
                            <!-- BAGIAN KIRI: FORM INPUT (Hanya muncul jika masih ada yang belum absen) -->
                            <div class="{{ $notAttended->isEmpty() ? 'col-12' : 'col-md-6' }}">
                                @if(session('success'))
                                    <div class="attendance-state text-center" role="status">
                                        <i class="bx bx-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                                        <h5 class="text-success">Presensi Berhasil Dikirim</h5>
                                        <p class="small text-muted">Presensi Anda telah tercatat.</p>
                                        <button onclick="window.location.reload()" class="btn btn-sm btn-outline-primary">Tutup Pesan</button>
                                    </div>
                                @elseif($notAttended->isEmpty())
                                    <div class="text-center py-5">
                                        <i class="bx bx-party text-primary mb-3" style="font-size: 4rem;"></i>
                                        <h5>Luar Biasa!</h5>
                                        <p class="text-muted">Seluruh peserta ({{ $attended->count() }} orang) telah melakukan absensi hari ini.</p>
                                    </div>
                                @elseif(($isSelfService ?? false) && $formParticipants->isEmpty())
                                    <div class="text-center py-5">
                                        <i class="bx bxs-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                                        <h5 class="text-success">Presensi Sudah Tercatat</h5>
                                        <p class="text-muted mb-0">{{ $selfParticipant->name }}, Anda telah mengisi presensi hari ini.</p>
                                    </div>
                                @else
                                    <h6 class="fw-bold mb-3 text-primary"><i class="bx bx-edit me-2"></i>Isi Presensi</h6>
                                    <form action="{{ route('public.attendance.store_daily', [$training->id, $date]) }}" method="POST">
                                        @csrf
                                        <!-- Input Tersembunyi untuk Waktu dan Zona -->
                                        <input type="hidden" name="local_checkin_time" id="local_checkin_time">
                                        <input type="hidden" name="timezone_label" id="timezone_label">

                                        <!-- Jam Digital (Hanya untuk visual peserta) -->
                                        <div class="text-center mb-4 p-2 border rounded bg-light">
                                            <small class="text-muted d-block">Waktu Perangkat Anda:</small>
                                            <h3 id="clock" class="fw-bold mb-0 text-primary">00:00:00</h3>
                                            <span id="timezone_display" class="badge bg-label-primary">Menghitung...</span>
                                        </div>
                                        @if($isSelfService ?? false)
                                            <div class="mb-4 p-3 border border-primary rounded bg-label-primary">
                                                <small class="text-primary fw-bold d-block mb-2">PRESENSI ATAS NAMA</small>
                                                <div class="fw-bold text-dark">{{ $selfParticipant->name }}</div>
                                                <div class="small text-muted">NIP/NIK: {{ $selfParticipant->nip_nik ?: '-' }}</div>
                                                <input type="hidden" name="participant_id" value="{{ $selfParticipant->id }}">
                                            </div>
                                        @else
                                            <div class="mb-4">
                                                <label for="attendance-participant" class="form-label fw-bold">Pilih nama Anda</label>
                                                <select id="attendance-participant" name="participant_id" class="form-select form-select-lg" required>
                                                    <option value="">Pilih peserta</option>
                                                    @foreach($formParticipants as $p)
                                                        <option value="{{ $p->id }}" @selected((int) old('participant_id', $selectedParticipantId ?? 0) === (int) $p->id)>
                                                            {{ $p->name }} - {{ $p->nip_nik ?: 'NIP/NIK belum tersedia' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <fieldset class="mb-4">
                                            <legend class="form-label fw-bold">Status kehadiran</legend>
                                            <div class="row g-2">
                                                <div class="col-4">
                                                    <input type="radio" class="btn-check" name="status" id="stHadir" value="hadir" @checked(old('status','hadir')==='hadir')>
                                                    <label class="btn btn-outline-success w-100 py-2" for="stHadir">HADIR</label>
                                                </div>
                                                <div class="col-4">
                                                    <input type="radio" class="btn-check" name="status" id="stIzin" value="izin" @checked(old('status')==='izin')>
                                                    <label class="btn btn-outline-warning w-100 py-2" for="stIzin">IZIN</label>
                                                </div>
                                                <div class="col-4">
                                                    <input type="radio" class="btn-check" name="status" id="stSakit" value="sakit" @checked(old('status')==='sakit')>
                                                    <label class="btn btn-outline-danger w-100 py-2" for="stSakit">SAKIT</label>
                                                </div>
                                            </div>
                                        </fieldset>
                                        <div id="absence_reason_field" class="mb-4 d-none">
                                            <label for="absence_reason" class="form-label fw-bold small">KETERANGAN <span class="text-danger">*</span></label>
                                            <textarea name="keterangan" id="absence_reason" rows="3" minlength="10" maxlength="500" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Jelaskan alasan izin atau sakit secara lengkap, minimal 10 karakter.">{{ old('keterangan') }}</textarea>
                                            <div class="form-text">Wajib untuk status Izin atau Sakit. Contoh: Pemeriksaan dokter di rumah sakit.</div>
                                            @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bx bx-check me-1" aria-hidden="true"></i>Kirim Presensi</button>
                                    </form>
                                @endif
                            </div>

                            <!-- BAGIAN KANAN: ANTREAN PROGRES -->
                            <div class="{{ $notAttended->isEmpty() ? 'col-12' : 'col-md-6' }}">
                                <section class="attendance-progress">
                                <h6 class="fw-bold mb-3 d-flex justify-content-between align-items-center gap-2">
                                    <span><i class="bx bx-list-ol me-2"></i>Progres Kehadiran</span>
                                    <span class="badge bg-label-primary">{{ $attended->count() }} / {{ $attended->count() + $notAttended->count() }}</span>
                                </h6>
                                
                                <div class="progress mb-3" style="height: 10px;">
                                    @php 
                                        $total = $attended->count() + $notAttended->count();
                                        $perc = $total > 0 ? ($attended->count() / $total) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-primary" role="progressbar" aria-label="Progres kehadiran" aria-valuenow="{{ round($perc) }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $perc }}%"></div>
                                </div>

                                <div class="attendance-roster overflow-auto border rounded">
                                    <ul class="list-group list-group-flush">
                                        {{-- DAFTAR YANG SUDAH --}}
                                        @foreach($attended as $a)
                                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="bx bxs-check-circle text-success me-2"></i>
                                                    <div>
                                                        <small class="fw-bold text-dark d-block">{{ $a->name }}</small>
                                                        <small class="text-muted">NIP/NIK: {{ $a->nip_nik ?: '-' }}</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-label-primary">Tercatat</span>
                                            </li>
                                        @endforeach

                                        {{-- DAFTAR YANG BELUM --}}
                                        @foreach($notAttended as $n)
                                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-2 gap-2">
                                                <div class="d-flex align-items-center">
                                                    <i class="bx bx-loader-circle text-muted me-2"></i>
                                                    <div>
                                                        <small class="text-muted d-block">{{ $n->name }}</small>
                                                        <small class="text-muted">NIP/NIK: {{ $n->nip_nik ?: '-' }}</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-label-secondary">Belum</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                </section>
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
</main>
<script>
    (() => {
    const clock = document.getElementById('clock');
    if (!clock) return;

    function updateClock() {
        const now = new Date();
        
        // Format Jam
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        clock.textContent = `${hours}:${minutes}:${seconds}`;

        // Deteksi Zona Waktu berdasarkan Offset Menit
        // UTC+7 = -420, UTC+8 = -480, UTC+9 = -540 (dalam menit)
        const offset = now.getTimezoneOffset();
        let label = "WIB";
        if (offset === -480) label = "WITA";
        else if (offset === -540) label = "WIT";
        else if (offset < -540) label = "WIT"; // Antisipasi zona lebih timur
        else if (offset > -420) label = "WIB"; // Antisipasi zona lebih barat

        document.getElementById('timezone_display').innerText = label;
        document.getElementById('timezone_label').value = label;
        
        // Masukkan waktu real-time ke hidden input
        document.getElementById('local_checkin_time').value = `${now.getFullYear()}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

    setInterval(updateClock, 1000);
    updateClock();
    const reasonField = document.getElementById('absence_reason_field');
    const reasonInput = document.getElementById('absence_reason');
    function toggleReason() {
        const selected = document.querySelector('input[name="status"]:checked')?.value;
        const required = selected === 'izin' || selected === 'sakit';
        reasonField.classList.toggle('d-none', !required);
        reasonInput.required = required;
        if (!required) reasonInput.value = '';
    }
    document.querySelectorAll('input[name="status"]').forEach(input => input.addEventListener('change', toggleReason));
    toggleReason();
    })();
</script>

<style>
    .daily-attendance .attendance-hero {
        background: radial-gradient(ellipse at 100% 0%, rgba(112, 205, 148, .18), transparent 60%),
            linear-gradient(120deg, #005c36, #006d42);
        color: #eefaf3;
    }
    .daily-attendance .attendance-hero h1 { color: #fff; }
    .attendance-logo { padding: 4px; border-radius: 9px; object-fit: contain; background: #fff; }
    .attendance-date { display: inline-block; padding: .35rem .75rem; border-radius: 2rem; background: rgba(255, 255, 255, .12); }
    .attendance-state { max-width: 32rem; padding: 1.5rem; border-radius: 1rem; background: var(--integral-raised); }
    .attendance-state i { font-size: 2.75rem !important; }
    .attendance-progress { height: 100%; padding: 1rem; border: 1px solid var(--integral-border); border-radius: 1rem; background: var(--integral-raised); }
    .attendance-roster { max-height: 300px; background: var(--integral-surface); }
    .attendance-roster .list-group-item { border-color: var(--integral-border); }
    .daily-attendance .btn-check:checked + .btn-outline-success { background: #008444; border-color: #008444; color: #fff; }
    .daily-attendance .btn-check:checked + .btn-outline-warning { background: #ffb24f; border-color: #ffb24f; color: #371900; }
    .daily-attendance .btn-check:checked + .btn-outline-danger { background: #bd3b31; border-color: #bd3b31; color: #fff; }
</style>
@endsection
