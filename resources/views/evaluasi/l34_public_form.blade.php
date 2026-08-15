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
                                    <form action="{{ route('public.l34.store', [$training->id, $role]) }}" method="POST">
                                        @csrf
                                        
                                        {{-- 1. DATA PROFIL --}}
                                        <div class="card bg-label-primary border-0 mb-5 shadow-none">
                                            <div class="card-body">
                                                <h5 class="fw-bold mb-4 text-primary text-uppercase">
                                                    <i class="bx bx-user-circle me-2"></i>Data Profil {{ $role == 'mandiri' ? 'Alumni' : 'Penilai' }}
                                                </h5>
                                                
                                                <div class="mb-4">
                                                    <label class="form-label fw-bold">PILIH NAMA ALUMNI</label>
                                                    <select name="participant_id" class="form-select form-select-lg border-primary" required>
                                                        <option value="">-- Cari Nama / NIP --</option>
                                                        @foreach($participants as $p)
                                                            <option value="{{ $p->id }}">{{ $p->name }} - (NIP: {{ $p->nip_nik }})</option>
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
                                                        <div class="col-md-6"><label class="form-label small fw-bold">JABATAN (DULU)</label><input type="text" name="pos_before" class="form-control"></div>
                                                        <div class="col-md-6"><label class="form-label small fw-bold">JABATAN (SKRG)</label><input type="text" name="pos_after" class="form-control"></div>
                                                        <div class="col-md-6"><label class="form-label small fw-bold">UNIT KERJA (DULU)</label><input type="text" name="unit_before" class="form-control"></div>
                                                        <div class="col-md-6"><label class="form-label small fw-bold">UNIT KERJA (SKRG)</label><input type="text" name="unit_after" class="form-control"></div>
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

                                        {{-- 2. PENEMPATAN TUGAS --}}
                                        <div class="mb-5 p-2">
                                            <h5 class="fw-bold border-bottom pb-2 text-primary text-uppercase"><i class="bx bx-transfer-alt me-2"></i>Penempatan Tugas & Transfer Ilmu</h5>
                                            @php $tasks = [
                                                '1. Apakah saat ini Ybs sedang bertugas yang berkaitan dengan pelatihan?',
                                                '2. Bila jawaban nomor 1 adalah Iya, apakah pengetahuan yang diperoleh membantu Ybs dalam menjalankan tugas?',
                                                '3. Bila jawaban nomor 1 adalah Tidak, apakah pengetahuan yang diperoleh membantu Ybs dalam menjalankan tugas?',
                                                '4. Bila jawaban nomor 1 adalah Tidak, apakah pelatihan memiliki keterkaitan dengan bidang tugas Ybs?',
                                                '5. Apakah Ybs sudah melakukan transfer learning hasil pelatihan pada rekan kerja?'
                                            ]; @endphp
                                            @foreach($tasks as $i => $task)
                                            <div class="mb-4">
                                                <label class="form-label d-block small fw-bold">{{ $task }}</label>
                                                <div class="d-flex gap-4">
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

                                        {{-- 3. INSTRUMEN --}}
                                        @foreach($questions as $subCat => $items)
                                        <div class="mb-5">
                                            <h5 class="fw-bold border-bottom pb-2 text-primary text-uppercase">{{ $subCat }}</h5>
                                            @foreach($items as $index => $q)
                                            <div class="card mb-4 border shadow-none bg-light">
                                                <div class="card-body">
                                                    <label class="form-label fw-bold mb-4 d-block">{{ $index+1 }}. {{ strtoupper($q->question_text) }}</label>
                                                    
                                                    @if($q->type == 'slider')
                                                        <div class="slider-wrapper px-2">
                                                            <input type="range" name="scores[{{ $q->id }}]" class="form-range kirkpatrick-slider" min="10" max="100" step="1" value="80" data-id="{{ $q->id }}">
                                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                                <div class="badge bg-primary px-3 py-2 h5 mb-0" id="score-{{ $q->id }}">80</div>
                                                                <span class="fw-bold h5 mb-0" id="label-{{ $q->id }}" style="color: #71dd37;">Cukup</span>
                                                            </div>
                                                        </div>
                                                    @elseif($q->type == 'dropdown')
                                                        <select name="scores[{{ $q->id }}]" class="form-select border-primary" required>
                                                            <option value="">-- Pilih Jawaban --</option>
                                                            @if(is_array($q->options))
                                                                @foreach($q->options as $opt) <option value="{{ $opt }}">{{ $opt }}</option> @endforeach
                                                            @endif
                                                        </select>
                                                    @else
                                                        <textarea name="scores[{{ $q->id }}]" class="form-control" rows="3" placeholder="Isi jawaban Anda..."></textarea>
                                                    @endif
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
                    <small class="text-muted">SIM-PEL © {{ date('Y') }}</small>
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
</style>
@endsection