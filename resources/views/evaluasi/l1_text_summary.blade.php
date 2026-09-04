@extends('layouts.master')

@section('title', 'Rangkuman Saran & Masukan Penyelenggara')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <a href="{{ route('evall1.index', $training->id) }}" class="small text-muted text-decoration-none"><i class="bx bx-arrow-back me-1"></i>Kembali ke Evaluasi Level 1</a>
            <h4 class="fw-bold mt-2 mb-1">Rangkuman Saran &amp; Masukan</h4>
            <p class="text-muted mb-0">Jawaban teks evaluasi penyelenggara untuk <strong>{{ $training->nama_pelatihan }}</strong>.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="#adminSummaryCard" class="btn btn-warning"><i class="bx bx-edit me-1"></i>{{ $adminSummary ? 'Perbarui Kesimpulan' : 'Isi Kesimpulan' }}</a>
            <a href="{{ route('evall12.export_word', $training->id) }}" class="btn btn-primary"><i class="bx bxs-file-doc me-1"></i>Download Laporan L1 &amp; L2</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible border-0 shadow-sm"><i class="bx bx-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm"><i class="bx bx-error-circle me-2"></i>{{ $errors->first() }}</div>
    @endif

    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="card-body bg-warning p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <small class="text-dark text-uppercase fw-bold opacity-75">Evaluasi Penyelenggara</small>
                    <h5 class="text-dark fw-bold mb-1 mt-1">{{ $training->nama_pelatihan }}</h5>
                    <p class="text-dark opacity-75 mb-0">Angkatan {{ $training->angkatan }} &middot; {{ $training->bidang }}</p>
                </div>
                <span class="badge bg-white text-warning px-3 py-2">Masukan peserta tersimpan dalam laporan</span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['icon' => 'bx-message-square-detail', 'color' => 'primary', 'label' => 'Total Masukan', 'value' => $responses->count()],
            ['icon' => 'bx-group', 'color' => 'success', 'label' => 'Peserta Memberi Masukan', 'value' => $respondentCount],
            ['icon' => 'bx-question-mark', 'color' => 'warning', 'label' => 'Pertanyaan Teks', 'value' => $textQuestions->count()],
            ['icon' => 'bx-pie-chart-alt', 'color' => 'info', 'label' => 'Cakupan Responden', 'value' => number_format($responseRate, 1, ',', '.').' %'],
        ] as $stat)
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body p-3 p-md-4">
                    <span class="avatar-initial rounded bg-label-{{ $stat['color'] }} p-2 d-inline-flex mb-3"><i class="bx {{ $stat['icon'] }} fs-4"></i></span>
                    <small class="text-muted d-block">{{ $stat['label'] }}</small>
                    <h4 class="fw-bold mb-0">{{ $stat['value'] }}</h4>
                </div></div>
            </div>
        @endforeach
    </div>

    @if($responses->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4"><div class="card-body py-3">
            <div class="input-group">
                <span class="input-group-text border-0 bg-transparent"><i class="bx bx-search"></i></span>
                <input type="search" id="feedbackSearch" class="form-control border-0 shadow-none" placeholder="Cari isi masukan, nama peserta, atau pertanyaan...">
                <span class="input-group-text border-0 bg-transparent text-muted small" id="feedbackSearchInfo">{{ $responses->count() }} masukan</span>
            </div>
        </div></div>
    @endif

    @forelse($responseGroups as $group)
        <section class="card border-0 shadow-sm mb-4 feedback-question">
            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-start gap-3">
                <div>
                    <small class="text-warning text-uppercase fw-bold">Pertanyaan</small>
                    <h6 class="fw-bold mb-0 mt-1">{{ $group['question']->question_text }}</h6>
                </div>
                <span class="badge bg-label-primary flex-shrink-0">{{ $group['responses']->count() }} jawaban</span>
            </div>
            <div class="card-body p-3 p-md-4">
                @forelse($group['responses'] as $index => $response)
                    <article class="feedback-item border rounded p-3 mb-3" data-search="{{ strtolower($group['question']->question_text.' '.($response->participant?->name ?? '').' '.($response->participant?->nip_nik ?? '').' '.$response->note) }}">
                        <div class="d-flex align-items-start gap-3">
                            <span class="avatar avatar-sm flex-shrink-0"><span class="avatar-initial rounded-circle bg-label-primary">{{ $index + 1 }}</span></span>
                            <div class="flex-grow-1 min-w-0">
                                <p class="mb-2 text-dark feedback-text">{{ $response->note }}</p>
                                <div class="d-flex flex-wrap gap-2 align-items-center small text-muted">
                                    <span><i class="bx bx-user me-1"></i>{{ $response->participant?->name ?? 'Peserta tidak ditemukan' }}</span>
                                    @if($response->participant?->nip_nik)<span>&middot; {{ $response->participant->nip_nik }}</span>@endif
                                    <span>&middot; {{ $response->created_at->translatedFormat('d M Y, H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="text-center py-4 text-muted"><i class="bx bx-message-square-x fs-2 d-block mb-2"></i>Belum ada jawaban pada pertanyaan ini.</div>
                @endforelse
            </div>
        </section>
    @empty
        <div class="card border-0 shadow-sm"><div class="card-body text-center py-5">
            <span class="avatar-initial rounded-circle bg-label-warning d-inline-flex p-3 mb-3"><i class="bx bx-message-square-x fs-2"></i></span>
            <h5 class="fw-bold">Belum ada pertanyaan teks</h5>
            <p class="text-muted mb-0">Tambahkan pertanyaan bertipe teks pada instrumen Evaluasi L1 Penyelenggara.</p>
        </div></div>
    @endforelse

    <section class="card border-0 shadow-sm mt-4" id="adminSummaryCard">
        <div class="card-header border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-3">
            <div>
                <small class="text-warning text-uppercase fw-bold">Telaah Admin</small>
                <h5 class="fw-bold mb-1 mt-1">Kesimpulan Umum dan Tindak Lanjut</h5>
                <p class="text-muted small mb-0">Baca seluruh masukan di atas, kemudian susun satu kesimpulan resmi untuk laporan.</p>
            </div>
            @if($adminSummary)
                <span class="badge bg-label-success"><i class="bx bx-check me-1"></i>Sudah disimpulkan</span>
            @else
                <span class="badge bg-label-warning"><i class="bx bx-time me-1"></i>Belum disimpulkan</span>
            @endif
        </div>
        <div class="card-body p-3 p-md-4">
            <div class="alert alert-info border-0 small">
                <i class="bx bx-info-circle me-1"></i>Jawaban asli dan identitas peserta hanya menjadi bahan telaah internal. Laporan Word hanya mencantumkan kesimpulan dan tindak lanjut yang ditulis pada form ini.
            </div>
            <form method="POST" action="{{ route('evall1.organizer-summary.store', $training->id) }}">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label class="form-label fw-bold">Kesimpulan Umum Saran dan Masukan <span class="text-danger">*</span></label>
                    <textarea name="conclusion" class="form-control @error('conclusion') is-invalid @enderror" rows="6" maxlength="10000" placeholder="Contoh: Secara umum peserta menilai materi dan pelayanan penyelenggara telah berjalan dengan baik..." required>{{ old('conclusion', $adminSummary?->conclusion) }}</textarea>
                    @error('conclusion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Rangkum pola utama dari keseluruhan jawaban peserta dengan bahasa laporan yang objektif.</div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Rencana Tindak Lanjut <span class="text-danger">*</span></label>
                    <textarea name="follow_up" class="form-control @error('follow_up') is-invalid @enderror" rows="5" maxlength="10000" placeholder="Contoh: Penyelenggara akan menambahkan studi kasus, menyempurnakan rundown, dan memperkuat koordinasi panitia..." required>{{ old('follow_up', $adminSummary?->follow_up) }}</textarea>
                    @error('follow_up')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Tuliskan tindakan konkret yang akan dilakukan berdasarkan kesimpulan tersebut.</div>
                </div>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 border-top pt-3">
                    <div class="small text-muted">
                        @if($adminSummary?->reviewed_at)
                            Terakhir diperbarui {{ $adminSummary->reviewed_at->translatedFormat('d F Y, H:i') }} oleh {{ $adminSummary->reviewer?->name ?? 'Admin' }}.
                        @else
                            Kesimpulan belum pernah disimpan.
                        @endif
                    </div>
                    <button type="submit" class="btn btn-warning"><i class="bx bx-save me-1"></i>Simpan untuk Laporan</button>
                </div>
            </form>
        </div>
    </section>

    <div id="feedbackEmptySearch" class="card border-0 shadow-sm d-none"><div class="card-body text-center py-5 text-muted">
        <i class="bx bx-search-alt fs-1 d-block mb-2"></i>Tidak ada masukan yang sesuai dengan pencarian.
    </div></div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($errors->any())
    document.getElementById('adminSummaryCard')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    @endif
    const input = document.getElementById('feedbackSearch');
    if (!input) return;
    const items = Array.from(document.querySelectorAll('.feedback-item'));
    const sections = Array.from(document.querySelectorAll('.feedback-question'));
    const info = document.getElementById('feedbackSearchInfo');
    const empty = document.getElementById('feedbackEmptySearch');
    input.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();
        let visible = 0;
        items.forEach(function (item) {
            const matches = !keyword || item.dataset.search.includes(keyword);
            item.classList.toggle('d-none', !matches);
            if (matches) visible++;
        });
        sections.forEach(function (section) {
            section.classList.toggle('d-none', !section.querySelector('.feedback-item:not(.d-none)'));
        });
        info.textContent = visible + ' masukan ditemukan';
        empty.classList.toggle('d-none', visible > 0);
    });
});
</script>
@endpush

<style>
    .feedback-item { background: #fcfcfd; transition: border-color .2s ease, transform .2s ease; }
    .feedback-item:hover { border-color: #696cff !important; transform: translateY(-1px); }
    .feedback-text { white-space: pre-line; line-height: 1.65; }
    .min-w-0 { min-width: 0; }
</style>
@endsection
