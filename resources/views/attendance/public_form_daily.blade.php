@extends('layouts.auth')

@section('content')
<div class="container-xxl py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8"> {{-- Ukuran diperlebar agar progres muat samping/bawah --}}
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="text-white mb-1">PRESENSI HARIAN</h4>
                    <p class="mb-0 opacity-75">{{ $training->nama_pelatihan }}</p>
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
                        <div class="text-center py-4">
                            <i class="bx bx-error-circle text-warning mb-3" style="font-size: 5rem;"></i>
                            <h5>Akses Belum Tersedia</h5>
                            <p class="text-muted">Admin belum mengatur jendela waktu absensi untuk hari ini.</p>
                        </div>
                    @elseif($status == 'closed')
                        <div class="text-center py-4">
                            <i class="bx bx-time-five text-danger mb-3" style="font-size: 5rem;"></i>
                            <h5 class="text-danger fw-bold">Waktu Absensi Ditutup</h5>
                            <div class="h4 fw-bold text-primary bg-label-primary p-3 rounded d-inline-block">
                                {{ substr($open, 0, 5) }} s/d {{ substr($close, 0, 5) }}
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <!-- BAGIAN KIRI: FORM INPUT (Hanya muncul jika masih ada yang belum absen) -->
                            <div class="{{ $notAttended->isEmpty() ? 'col-12' : 'col-md-6' }} border-end">
                                @if(session('success'))
                                    <div class="text-center py-4 animate__animated animate__fadeIn">
                                        <i class="bx bx-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                                        <h5 class="text-success">Terima Kasih!</h5>
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
                                                <label class="form-label fw-bold small">PILIH NAMA ANDA</label>
                                                <select name="participant_id" class="form-select form-select-lg border-primary" required>
                                                    <option value="">-- Cari Nama --</option>
                                                    @foreach($formParticipants as $p)
                                                        <option value="{{ $p->id }}" @selected((int) old('participant_id', $selectedParticipantId ?? 0) === (int) $p->id)>
                                                            {{ $p->name }} - {{ $p->nip_nik ?: 'NIP/NIK belum tersedia' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <div class="mb-4">
                                            <label class="form-label fw-bold small">STATUS</label>
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
                                        </div>
                                        <div id="absence_reason_field" class="mb-4 d-none">
                                            <label for="absence_reason" class="form-label fw-bold small">KETERANGAN <span class="text-danger">*</span></label>
                                            <textarea name="keterangan" id="absence_reason" rows="3" minlength="10" maxlength="500" class="form-control @error('keterangan') is-invalid @enderror" placeholder="Jelaskan alasan izin atau sakit secara lengkap, minimal 10 karakter.">{{ old('keterangan') }}</textarea>
                                            <div class="form-text">Wajib untuk status Izin atau Sakit. Contoh: Pemeriksaan dokter di rumah sakit.</div>
                                            @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">KIRIM SEKARANG</button>
                                    </form>
                                @endif
                            </div>

                            <!-- BAGIAN KANAN: ANTREAN PROGRES -->
                            <div class="{{ $notAttended->isEmpty() ? 'col-12' : 'col-md-6' }} ps-md-4 mt-4 mt-md-0">
                                <h6 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                                    <span><i class="bx bx-list-ol me-2"></i>Progres Kehadiran</span>
                                    <span class="badge bg-label-primary">{{ $attended->count() }} / {{ $attended->count() + $notAttended->count() }}</span>
                                </h6>
                                
                                <div class="progress mb-3" style="height: 10px;">
                                    @php 
                                        $total = $attended->count() + $notAttended->count();
                                        $perc = $total > 0 ? ($attended->count() / $total) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $perc }}%"></div>
                                </div>

                                <div class="overflow-auto shadow-sm border rounded p-2" style="max-height: 300px; background: #fcfcfd;">
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
                                                <span class="badge badge-dot bg-success"></span>
                                            </li>
                                        @endforeach

                                        {{-- DAFTAR YANG BELUM --}}
                                        @foreach($notAttended as $n)
                                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent py-2 opacity-50">
                                                <div class="d-flex align-items-center">
                                                    <i class="bx bx-loader-circle text-muted me-2"></i>
                                                    <div>
                                                        <small class="text-muted d-block">{{ $n->name }}</small>
                                                        <small class="text-muted">NIP/NIK: {{ $n->nip_nik ?: '-' }}</small>
                                                    </div>
                                                </div>
                                                <span class="badge badge-dot bg-secondary"></span>
                                            </li>
                                        @endforeach
                                    </ul>
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
<script>
    function updateClock() {
        const now = new Date();
        
        // Format Jam
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        document.getElementById('clock').innerText = `${hours}:${minutes}:${seconds}`;

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
</script>

<style>
    body { background-color: #f5f5f9; }
    .list-group-item { border: none; border-bottom: 1px solid #eee; }
    .list-group-item:last-child { border: none; }
    .btn-check:checked + .btn-outline-success { background-color: #71dd37 !important; color: #fff; }
    .btn-check:checked + .btn-outline-warning { background-color: #ffab00 !important; color: #fff; }
    .btn-check:checked + .btn-outline-danger { background-color: #ff3e1d !important; color: #fff; }
</style>
@endsection
