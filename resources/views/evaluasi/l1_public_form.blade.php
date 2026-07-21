@extends('layouts.auth')

@section('content')
<div class="container-xxl py-5">
    <div class="row justify-content-center">
        <div class="col-md-11 col-lg-10">
            <div class="card shadow-lg border-0">
                <div class="alert alert-info border-0 mb-4 shadow-sm py-3">
                    <div class="d-flex align-items-center">
                        @if($type == 'penyelenggara')
                            <i class="bx bx-building me-3 h2 mb-0"></i>
                            <div>
                                <small class="d-block text-uppercase fw-bold opacity-75">Menilai Objek:</small> 
                                <span class="h5 fw-bold mb-0">Instansi Penyelenggara Diklat</span>
                            </div>
                        @else
                            <i class="bx bx-user-voice me-3 h2 mb-0"></i>
                            <div>
                                <small class="d-block text-uppercase fw-bold opacity-75">Menilai Narasumber:</small> 
                                <span class="h5 fw-bold mb-0">{{ $schedule->pic }}</span>
                                
                                <div class="mt-1">
                                    <span class="badge bg-white text-info shadow-sm me-1">
                                        Materi: {{ $schedule->activity }}
                                    </span>
                                    {{-- TAMBAHKAN TANGGAL DI SINI --}}
                                    <span class="badge bg-white text-dark shadow-sm">
                                        <i class="bx bx-calendar me-1"></i> {{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('d F Y') }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row">
                        <!-- KOLOM KIRI -->
                        <div class="col-md-7 col-lg-8 border-end">
                            @if(session('success'))
                                <div class="text-center py-5">
                                    <i class="bx bx-check-circle text-success mb-3" style="font-size: 5rem;"></i>
                                    <h4 class="text-success fw-bold">Berhasil!</h4>
                                    <p>{{ session('success') }}</p>
                                    <button onclick="window.location.reload()" class="btn btn-primary">Kembali ke Form</button>
                                </div>
                            @else
                                <form action="{{ route('public.evall1.store', $training->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="schedule_id" value="{{ $sid }}">

                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Pilih Nama Anda</label>
                                        <select name="participant_id" class="form-select form-select-lg border-primary" required>
                                            <option value="">-- Cari Nama --</option>
                                            @foreach($participants as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @foreach($questions as $index => $q)
                                    <div class="question-card mb-5 p-3 border rounded">
                                        <label class="form-label fw-bold d-block mb-4">{{ $index + 1 }}. {{ strtoupper($q->question_text) }}</label>

                                        @if($q->type == 'slider')
                                            <div class="px-2">
                                                <input type="range" name="answers[{{ $q->id }}]" 
                                                       class="form-range kirk-slider" 
                                                       min="10" max="100" step="1" value="80"
                                                       data-qid="{{ $q->id }}">
                                                
                                                <div class="d-flex justify-content-between align-items-center mt-3">
                                                    <div class="p-2 bg-primary text-white rounded fw-bold h5 mb-0" id="val-{{ $q->id }}" style="min-width: 50px; text-align: center;">80</div>
                                                    <span class="fw-bold h5 mb-0" id="lab-{{ $q->id }}" style="color: #71dd37;">Cukup</span>
                                                </div>
                                            </div>
                                        @elseif($q->type == 'dropdown')
                                            <select name="answers[{{ $q->id }}]" class="form-select border-primary" required>
                                                <option value="">-- Pilih Jawaban --</option>
                                                @foreach($q->options as $opt)
                                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <textarea name="answers[{{ $q->id }}]" class="form-control" rows="3" placeholder="Isi masukan anda..."></textarea>
                                        @endif
                                    </div>
                                    @endforeach

                                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow mt-3 py-3 fw-bold">
                                        <i class="bx bx-paper-plane me-2"></i> KIRIM SEKARANG
                                    </button>
                                </form>
                            @endif
                        </div>

                        <!-- KOLOM KANAN: PROGRES -->
                        <div class="col-md-5 col-lg-4 ps-md-4 mt-4 mt-md-0">
                            <h6 class="fw-bold mb-3"><i class="bx bx-list-check me-2"></i>Antrean Progres</h6>
                            <div class="overflow-auto" style="max-height: 500px;">
                                <ul class="list-group list-group-flush">
                                    @foreach($alreadyFilled as $a)
                                        <li class="list-group-item d-flex align-items-center bg-transparent px-0 py-2">
                                            <i class="bx bxs-check-circle text-success me-2"></i>
                                            <span class="text-dark small fw-bold">{{ $a->name }}</span>
                                        </li>
                                    @endforeach
                                    @foreach($participants as $n)
                                        <li class="list-group-item d-flex align-items-center bg-transparent px-0 py-2 opacity-50">
                                            <i class="bx bx-minus-circle text-muted me-2"></i>
                                            <span class="text-muted small">{{ $n->name }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT JQUERY LANGSUNG DI BLADE AGAR PASTI JALAN --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function getLabel(val) {
        val = parseInt(val);
        if (val <= 60) return { l: "Sangat Kurang", c: "#ff3e1d" };
        if (val <= 70) return { l: "Kurang", c: "#fdac41" };
        if (val <= 80) return { l: "Cukup", c: "#71dd37" };
        if (val <= 90) return { l: "Baik", c: "#03c3ec" };
        return { l: "Sangat Baik", c: "#696cff" };
    }

    $('.kirk-slider').on('input', function() {
        const val = $(this).val();
        const qid = $(this).data('qid');
        const info = getLabel(val);

        $(`#val-${qid}`).text(val).css('background-color', info.c);
        $(`#lab-${qid}`).text(info.l).css('color', info.c);
    });
});
</script>

<style>
    body { background-color: #f5f5f9; }
    .question-card { background: #fff; transition: 0.3s; }
    .question-card:hover { border-color: #696cff !important; }
    .kirk-slider { height: 10px; cursor: pointer; }
    <style>
    .alert-info {
        background: linear-gradient(45deg, #03c3ec, #009ef7) !important;
        color: white !important;
    }
    .alert-info .badge {
        font-size: 0.85rem;
        padding: 5px 12px;
        color: #009ef7 !important;
    }
</style>
@endsection