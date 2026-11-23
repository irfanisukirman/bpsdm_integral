@php
    $openingDate = $level === '1' ? $training->evaluation_l1_opens_on : $training->evaluation_l34_opens_on;
    $isOpen = $openingDate && $openingDate->toDateString() <= now('Asia/Jakarta')->toDateString();
@endphp
<section class="card mb-4" id="jadwal-evaluasi" aria-labelledby="evaluationOpeningTitle">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <h5 class="mb-0" id="evaluationOpeningTitle">Jadwal Pengisian Evaluasi</h5>
            <span class="badge bg-label-{{ $isOpen ? 'success' : 'warning' }}">
                {{ $isOpen ? 'Dibuka' : ($openingDate ? 'Terjadwal' : 'Belum dijadwalkan') }}
            </span>
        </div>
        <p class="text-muted mb-3">
            @if($openingDate)
                Tanggal buka {{ $openingDate->translatedFormat('d F Y') }}, pukul 00.00 WIB.
            @else
                Formulir akan tersedia setelah pengelola menyimpan tanggal buka.
            @endif
        </p>
        @if(session('evaluation_schedule_saved'))
            <div class="alert alert-success" role="status">{{ session('evaluation_schedule_saved') }}</div>
        @endif
        @if(auth()->user()->role === 'superadmin' || (auth()->user()->role === 'admin_bidang' && auth()->user()->bidang === $training->bidang))
            <form method="POST" action="{{ route('trainings.evaluation-opening-date', [$training, $level]) }}" class="row g-2 align-items-end">
                @csrf
                @method('PUT')
                <div class="col-12 col-sm-auto">
                    <label class="form-label" for="evaluationOpeningDate">Tanggal mulai pengisian</label>
                    <input type="date" name="opens_on" id="evaluationOpeningDate" value="{{ old('opens_on', $openingDate?->toDateString()) }}"
                           class="form-control @error('opens_on') is-invalid @enderror" required>
                    @error('opens_on')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-sm-auto">
                    <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
                </div>
            </form>
            <small class="text-muted d-block mt-2">Pengisian tetap terbuka setelah tanggal mulai.</small>
        @endif
    </div>
</section>
