<section class="card mb-4" aria-labelledby="annualPostEvaluationTitle">
    <div class="card-body">
        <h5 class="fw-bold mb-1" id="annualPostEvaluationTitle">Ringkasan Evaluasi Pasca Tahunan</h5>
        <p class="text-muted mb-3">Lihat hasil survei alumni dan nilai wawancara yang sudah terisi untuk tiap pelatihan.</p>
        <form method="GET" action="{{ route('post-evaluation.index') }}" class="row g-2 align-items-end">
            <input type="hidden" name="tab" value="ringkasan-tahunan">
            <div class="col-12 col-sm-4 col-lg-3">
                <label class="form-label" for="postEvaluationYear">Tahun Mulai Pelatihan</label>
                <select class="form-select" id="postEvaluationYear" name="year" @disabled($years->isEmpty())>
                    @forelse($years as $option)
                        <option value="{{ $option }}" @selected($year === $option)>{{ $option }}</option>
                    @empty
                        <option>Belum ada tahun</option>
                    @endforelse
                </select>
            </div>
            <div class="col-12 col-sm">
                <label class="form-label" for="annualPostEvaluationSearch">Cari Pelatihan</label>
                <input type="search" name="q" id="annualPostEvaluationSearch" class="form-control" value="{{ $search }}" maxlength="100">
            </div>
            <div class="col-12 col-sm-auto d-flex flex-wrap gap-2">
                <button class="btn btn-primary" type="submit"><i class="bx bx-filter-alt me-1" aria-hidden="true"></i>Tampilkan</button>
                @if($trainings->total())
                    <a class="btn btn-outline-primary" href="{{ route('post-evaluation.index', [
                        'tab' => 'ringkasan-tahunan', 'year' => $year, 'q' => $search !== '' ? $search : null, 'format' => 'xlsx',
                    ]) }}"><i class="bx bx-download me-1" aria-hidden="true"></i>Ekspor Excel</a>
                @endif
            </div>
        </form>
    </div>
</section>

<div class="d-grid gap-3">
    @forelse($trainings as $row)
        @php $training = $row['training']; @endphp
        <article class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <span class="badge bg-label-primary mb-2">{{ $training->program_evaluasi }}</span>
                        <h5 class="mb-1">{{ $training->nama_pelatihan }}</h5>
                        <div class="text-muted small">{{ $training->bidang }} · Angkatan {{ $training->angkatan }}</div>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('post-evaluation.show', [$training, 'tab' => 'ringkasan']) }}">Lihat Hasil <i class="bx bx-right-arrow-alt" aria-hidden="true"></i></a>
                </div>
                <div class="row g-3">
                    <div class="col-sm">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="small fw-semibold mb-1">Rerata Survei Alumni</div>
                            @if($row['survey']['average'] !== null)
                                <strong class="fs-4">{{ number_format($row['survey']['average'], 2, ',', '.') }}%</strong>
                                <div class="small text-muted">{{ $row['survey']['valid'] }} dari {{ $row['total'] }} alumni memiliki nilai survei</div>
                            @else
                                <span class="text-muted">Belum ada nilai survei</span>
                            @endif
                        </div>
                    </div>
                    @if($row['interview']['average'] !== null)
                        <div class="col-sm">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small fw-semibold mb-1">Rerata Wawancara</div>
                                <strong class="fs-4">{{ number_format($row['interview']['average'], 2, ',', '.') }}%</strong>
                                <div class="small text-muted">{{ $row['interview']['valid'] }} dari {{ $row['total'] }} alumni memiliki nilai wawancara · {{ $row['completed'] }} wawancara selesai</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="card card-body text-center text-muted py-4">Belum ada pelatihan yang sesuai dengan filter ini.</div>
    @endforelse
</div>
@if($trainings->hasPages())
    <div class="mt-4">{{ $trainings->links() }}</div>
@endif
