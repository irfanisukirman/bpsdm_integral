@extends('layouts.auth')

@section('content')
<div class="container-xxl py-5">
    <div class="row justify-content-center">
        <!-- Wrapper Utama -->
        <div class="col-lg-11">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-dark text-white text-center py-4">
                    <h4 class="text-white mb-1">EVALUASI PASCA PELATIHAN (360°)</h4>
                    <p class="mb-0 opacity-75">{{ $training->nama_pelatihan }}</p>
                </div>

                <div class="card-body p-4">
                    <!-- Menampilkan Error Validasi (Jika Ada) -->
                    @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <div class="fw-bold mb-2"><i class="bx bx-error-circle me-1"></i> Ada kesalahan input:</div>
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <i class="bx bx-error me-1"></i> {{ session('error') }}
                        </div>
                    @endif

                    <div class="row">
                        <!-- KOLOM KIRI: FORMULIR ATAU PESAN SUKSES -->
                        <div class="col-md-7 col-lg-8 border-end">
                            
                            @if(session('success'))
                                {{-- TAMPILAN SAAT SELESAI MENGISI --}}
                                <div class="text-center py-5 animate__animated animate__fadeIn">
                                    <div class="mb-4">
                                        <i class="bx bx-check-circle text-success" style="font-size: 6rem;"></i>
                                    </div>
                                    <h3 class="fw-bold text-dark">Terima Kasih!</h3>
                                    <p class="text-muted mb-4 px-lg-5">
                                        {{ session('success') }} <br>
                                        Data Anda telah berhasil direkam ke dalam database sistem.
                                    </p>
                                    <div class="d-flex justify-content-center gap-3">
                                        {{-- Tombol diarahkan ke Gateway --}}
                                        <a href="{{ route('public.l34.gateway', $training->id) }}" class="btn btn-primary btn-lg px-5 shadow">
                                            <i class="bx bx-grid-alt me-1"></i> Pilih Peran Lain
                                        </a>
                                    </div>
                                </div>
                            @elseif($participants->isEmpty())
                                {{-- TAMPILAN JIKA ANTREAN HABIS --}}
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="bx bx-party text-primary" style="font-size: 5rem;"></i>
                                    </div>
                                    <h4 class="fw-bold">Luar Biasa!</h4>
                                    <p class="text-muted">Seluruh peserta/alumni telah selesai dinilai untuk peran <strong>{{ strtoupper($role) }}</strong>.</p>
                                    <hr class="my-4 mx-5">
                                    <small class="text-muted">Tidak ada lagi nama yang tersisa di daftar antrean.</small>
                                </div>
                            @else
                                {{-- TAMPILAN FORMULIR AKTIF --}}
                                <form action="{{ route('public.l34.store', [$training->id, $role]) }}" method="POST">
                                    @csrf
                                    
                                    {{-- 1. DATA PROFIL --}}
                                    <div class="card bg-label-primary border-0 mb-5 shadow-none">
                                        <div class="card-body">
                                            <h5 class="fw-bold mb-4 text-primary text-uppercase">
                                                <i class="bx bx-user-pin me-2"></i>Data Profil {{ $role == 'mandiri' ? 'Alumni' : 'Penilai' }}
                                            </h5>
                                            
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">PILIH NAMA / NIP ALUMNI</label>
                                                <select name="participant_id" class="form-select form-select-lg border-primary" required>
                                                    <option value="">-- Cari Nama / NIP --</option>
                                                    @foreach($participants as $p)
                                                        <option value="{{ $p->id }}" {{ old('participant_id') == $p->id ? 'selected' : '' }}>
                                                            {{ $p->name }} - (NIP: {{ $p->nip_nik }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="form-text mt-2"><i class="bx bx-info-circle me-1"></i>Pilih nama alumni yang akan Anda nilai hari ini.</div>
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
                                                        <label class="form-label small fw-bold">PANGKAT/GOL SAAT PELATIHAN</label>
                                                        <select name="rank_before" class="form-select">
                                                            @foreach(['I/a','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c'] as $gol) <option value="{{ $gol }}">{{ $gol }}</option> @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold">PANGKAT/GOL SAAT INI</label>
                                                        <select name="rank_after" class="form-select">
                                                            @foreach(['I/a','II/a','II/b','II/c','II/d','III/a','III/b','III/c','III/d','IV/a','IV/b','IV/c'] as $gol) <option value="{{ $gol }}">{{ $gol }}</option> @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6"><label class="form-label small fw-bold">JABATAN (DULU)</label><input type="text" name="pos_before" class="form-control" placeholder="..." value="{{ old('pos_before') }}"></div>
                                                    <div class="col-md-6"><label class="form-label small fw-bold">JABATAN (SEKARANG)</label><input type="text" name="pos_after" class="form-control" placeholder="..." value="{{ old('pos_after') }}"></div>
                                                    <div class="col-md-6"><label class="form-label small fw-bold">UNIT KERJA (DULU)</label><input type="text" name="unit_before" class="form-control" placeholder="..." value="{{ old('unit_before') }}"></div>
                                                    <div class="col-md-6"><label class="form-label small fw-bold">UNIT KERJA (SEKARANG)</label><input type="text" name="unit_after" class="form-control" placeholder="..." value="{{ old('unit_after') }}"></div>
                                                </div>
                                            @else
                                                <div class="row g-3">
                                                    <div class="col-md-6"><label class="form-label fw-bold small">NAMA ANDA (PENILAI)</label><input type="text" name="evaluator_name" class="form-control" placeholder="..." required></div>
                                                    <div class="col-md-6"><label class="form-label fw-bold small">NIP / NIK ANDA</label><input type="text" name="evaluator_nip" class="form-control" placeholder="..." required></div>
                                                    <div class="col-md-6"><label class="form-label fw-bold small">JABATAN ANDA</label><input type="text" name="evaluator_pos" class="form-control" placeholder="..." required></div>
                                                    <div class="col-md-6"><label class="form-label fw-bold small">UNIT KERJA ANDA</label><input type="text" name="evaluator_unit" class="form-control" placeholder="..." required></div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- 2. PENEMPATAN TUGAS --}}
                                    <div class="mb-5 p-2">
                                        <h5 class="fw-bold border-bottom pb-2"><i class="bx bx-transfer-alt me-2 text-primary"></i>PENEMPATAN TUGAS & TRANSFER LEARNING</h5>
                                        @php $tasks = [
                                            '1. Apakah saat ini Ybs sedang bertugas yang berkaitan dengan pelatihan?',
                                            '2. Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Ybs dalam menjalankan tugas?',
                                            '3. Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Ybs dalam menjalankan tugas?',
                                            '4. Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Ybs?',
                                            '5. Apakah Ybs sudah melakukan transfer learning hasil pelatihan pada rekan kerja?'
                                        ]; @endphp
                                        @foreach($tasks as $i => $task)
                                        <div class="mb-4">
                                            <label class="form-label d-block small fw-bold text-dark">{{ $task }}</label>
                                            <div class="d-flex gap-4 mt-2">
                                                <div class="form-check custom-option custom-option-basic">
                                                    <label class="form-check-label custom-option-content" for="t-y-{{$i}}">
                                                        <input class="form-check-input" type="radio" name="task[{{$i}}]" value="Ya" id="t-y-{{$i}}" checked>
                                                        <span class="custom-option-header"><span class="h6 mb-0">Ya / Sudah</span></span>
                                                    </label>
                                                </div>
                                                <div class="form-check custom-option custom-option-basic">
                                                    <label class="form-check-label custom-option-content" for="t-t-{{$i}}">
                                                        <input class="form-check-input" type="radio" name="task[{{$i}}]" value="Tidak" id="t-t-{{$i}}">
                                                        <span class="custom-option-header"><span class="h6 mb-0 text-danger">Tidak / Belum</span></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                    {{-- 3. PERUBAHAN PERILAKU & DAMPAK --}}
                                    @foreach($questions as $subCat => $items)
                                    <div class="mb-5">
                                        <h5 class="fw-bold border-bottom pb-2 text-primary text-uppercase">{{ $subCat }}</h5>
                                        @foreach($items as $index => $q)
                                        <div class="card mb-4 border shadow-none bg-light">
                                            <div class="card-body">
                                                <label class="form-label small fw-bold mb-4 d-block">{{ $index+1 }}. {{ strtoupper($q->question_text) }}</label>
                                                
                                                <div class="slider-wrapper px-2">
                                                    <input type="range" name="scores[{{ $q->id }}]" class="form-range custom-slider" min="10" max="100" step="1" value="80" data-id="{{ $q->id }}">
                                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                                        <div class="badge bg-primary px-3 py-2 h5 mb-0 shadow-sm" id="score-{{ $q->id }}">80</div>
                                                        <div class="text-end">
                                                            <span class="fw-bold h5 mb-0" id="label-{{ $q->id }}" style="color: #71dd37;">Cukup</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endforeach

                                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow py-3 fw-bold mb-5">
                                        <i class="bx bx-paper-plane me-2"></i> KIRIM PENILAIAN SEKARANG
                                    </button>
                                </form>
                            @endif
                        </div>

                        <!-- KOLOM KANAN: PROGRES ANTREAN -->
                        <div class="col-md-5 col-lg-4 ps-md-4 mt-4 mt-md-0">
                            <div class="sticky-top" style="top: 20px;">
                                <h6 class="fw-bold mb-3 d-flex justify-content-between align-items-center">
                                    <span><i class="bx bx-list-ol me-2"></i>ANTREAN PROGRES</span>
                                    <span class="badge bg-label-secondary text-dark">{{ $alreadyFilled->count() }} / {{ $alreadyFilled->count() + $participants->count() }}</span>
                                </h6>
                                
                                @php 
                                    $totalCount = $alreadyFilled->count() + $participants->count();
                                    $perc = $totalCount > 0 ? ($alreadyFilled->count() / $totalCount) * 100 : 0;
                                @endphp
                                <div class="progress mb-4" style="height: 10px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: {{ $perc }}%"></div>
                                </div>

                                <div class="overflow-auto scroll-area border rounded p-2 bg-white" style="max-height: 600px;">
                                    <ul class="list-group list-group-flush">
                                        {{-- DAFTAR YANG SUDAH --}}
                                        @foreach($alreadyFilled as $a)
                                            <li class="list-group-item d-flex align-items-center bg-transparent py-2 border-bottom">
                                                <i class="bx bxs-check-circle text-success me-2 h5 mb-0"></i>
                                                <div class="d-flex flex-column">
                                                    <span class="text-dark small fw-bold">{{ $a->name }}</span>
                                                    <small class="text-muted" style="font-size: 10px;">Selesai Dinilai</small>
                                                </div>
                                            </li>
                                        @endforeach

                                        {{-- DAFTAR YANG BELUM --}}
                                        @foreach($participants as $n)
                                            <li class="list-group-item d-flex align-items-center bg-transparent py-2 border-bottom opacity-50">
                                                <i class="bx bx-loader-circle text-muted me-2 h5 mb-0"></i>
                                                <div class="d-flex flex-column">
                                                    <span class="text-muted small">{{ $n->name }}</span>
                                                    <small class="text-muted" style="font-size: 10px;">Belum</small>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <p class="small text-muted mt-3 italic"><i class="bx bx-info-circle me-1"></i>Daftar di atas adalah alumni pelatihan ini yang akan dinilai dampaknya.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light text-center py-3">
                    <small class="text-muted">SIM-PEL &copy; {{ date('Y') }} | Monitoring & Evaluasi Pelatihan</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function getKirkLabel(val) {
        val = parseInt(val);
        if (val <= 60) return { t: "Sangat Kurang", c: "#ff3e1d" };
        if (val <= 70) return { t: "Kurang", c: "#fdac41" };
        if (val <= 80) return { t: "Cukup", c: "#71dd37" };
        if (val <= 90) return { t: "Baik", c: "#03c3ec" };
        return { t: "Sangat Baik", c: "#696cff" };
    }

    $('.custom-slider').on('input', function() {
        let val = $(this).val();
        let id = $(this).data('id');
        let res = getKirkLabel(val);
        $(`#score-${id}`).text(val).css('background-color', res.c);
        $(`#label-${id}`).text(res.t).css('color', res.c);
    });

    $('.custom-slider').each(function() {
        $(this).trigger('input');
    });
});
</script>

<style>
    body { background-color: #f5f5f9; }
    .scroll-area::-webkit-scrollbar { width: 3px; }
    .scroll-area::-webkit-scrollbar-thumb { background: #d9dee3; border-radius: 10px; }
    
    .custom-slider { height: 10px; -webkit-appearance: none; background: #ebedef; border-radius: 5px; outline: none; }
    .custom-slider::-webkit-slider-thumb { 
        width: 22px; height: 22px; background: #696cff; border: 4px solid #fff; 
        border-radius: 50%; cursor: pointer; box-shadow: 0 0 10px rgba(105,108,255,0.4); 
        -webkit-appearance: none;
    }
    .custom-option-basic { width: 100%; cursor: pointer; }
    .card-header { border-bottom: 1px solid rgba(255,255,255,0.1); }
</style>
@endsection