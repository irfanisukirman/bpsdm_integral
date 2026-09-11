@extends('layouts.master')

@section('title', 'Catatan Pelaksanaan')

@section('content')
<div class="execution-notes-page">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('trainings.index') }}">Pelatihan</a></li>
            <li class="breadcrumb-item"><a href="{{ route('trainings.manage', $training) }}">Kelola</a></li>
            <li class="breadcrumb-item active">Catatan Pelaksanaan</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible border-0 shadow-sm"><i class="bx bx-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <section class="notes-hero mb-4">
        <div class="d-flex align-items-start gap-3 min-w-0">
            <span class="notes-hero__icon"><i class="bx bx-note"></i></span>
            <div class="min-w-0"><span class="notes-eyebrow">PENGAMATAN KELAS</span><h3 class="fw-bold text-white mt-1 mb-2">Catatan Pelaksanaan</h3><p class="mb-0">{{ $training->nama_pelatihan }}</p></div>
        </div>
        <div class="notes-total"><strong>{{ $notes->total() }}</strong><span>Total catatan</span></div>
    </section>

    <div class="row g-4 align-items-start">
        <div class="col-xl-8 order-2 order-xl-1">
            <div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="fw-bold mb-1">Riwayat Catatan</h5><p class="text-muted small mb-0">Catatan terbaru ditampilkan paling atas.</p></div><span class="badge bg-label-primary">{{ $notes->total() }} catatan</span></div>

            <div class="notes-list">
                @forelse($notes as $executionNote)
                    <article class="note-card">
                        <div class="note-card__top">
                            <span class="note-card__icon"><i class="bx bx-message-square-detail"></i></span>
                            <div class="flex-grow-1 min-w-0"><h5 class="fw-bold mb-1 text-break">{{ $executionNote->title }}</h5><div class="note-meta"><span><i class="bx bx-user"></i>{{ $executionNote->author?->name ?: 'Pengguna tidak tersedia' }}</span><span><i class="bx bx-calendar"></i>{{ $executionNote->created_at->translatedFormat('d F Y, H:i') }} WIB</span></div></div>
                        </div>
                        <div class="note-content">{!! nl2br(e($executionNote->note)) !!}</div>
                    </article>
                @empty
                    <div class="card border-0 shadow-sm"><div class="card-body empty-notes"><span><i class="bx bx-notepad"></i></span><h5 class="fw-bold">Belum ada catatan</h5><p>Gunakan formulir untuk menyimpan pengamatan pertama pada pelatihan ini.</p></div></div>
                @endforelse
            </div>

            @if($notes->hasPages())
                <div class="mt-4">{{ $notes->links() }}</div>
            @endif
        </div>

        <div class="col-xl-4 order-1 order-xl-2">
            <div class="card border-0 shadow-sm sticky-note-form">
                <div class="card-header bg-transparent border-bottom p-4"><h5 class="fw-bold mb-1"><i class="bx bx-edit text-primary me-2"></i>Catatan Baru</h5><p class="text-muted small mb-0">Tuliskan hasil pengamatan kelas secara ringkas dan jelas.</p></div>
                <form action="{{ route('trainings.execution-notes.store', $training) }}" method="POST">
                    @csrf
                    <div class="card-body p-4">
                        <div class="mb-3"><label class="form-label fw-semibold" for="note_title">Judul catatan <span class="text-danger">*</span></label><input id="note_title" name="note_title" value="{{ old('note_title') }}" maxlength="200" class="form-control @error('note_title') is-invalid @enderror" placeholder="Contoh: Pengamatan kelas hari pertama" required>@error('note_title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div><label class="form-label fw-semibold" for="note_content">Isi catatan <span class="text-danger">*</span></label><textarea id="note_content" name="note_content" rows="9" maxlength="10000" class="form-control @error('note_content') is-invalid @enderror" placeholder="Tuliskan kondisi kelas, kejadian penting, atau hal yang perlu diingat..." required>{{ old('note_content') }}</textarea>@error('note_content')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">Hanya menjadi arsip internal pelatihan dan tidak memengaruhi evaluasi.</div></div>
                    </div>
                    <div class="card-footer bg-transparent border-top p-4 d-grid gap-2"><button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan Catatan</button><a href="{{ route('trainings.manage', $training) }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali ke Pusat Kendali</a></div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.execution-notes-page{width:100%;min-width:0}.min-w-0{min-width:0}.notes-hero{display:flex;align-items:center;justify-content:space-between;gap:2rem;padding:1.5rem 1.75rem;border-radius:1rem;background:linear-gradient(135deg,#696cff,#4f56ca);box-shadow:0 .5rem 1.4rem rgba(105,108,255,.18);color:rgba(255,255,255,.82)}.notes-hero__icon{display:grid;place-items:center;flex:0 0 58px;width:58px;height:58px;border-radius:16px;background:rgba(255,255,255,.16);color:#fff;font-size:1.8rem}.notes-eyebrow{font-size:.7rem;font-weight:700;letter-spacing:.12em;color:#ffd27d}.notes-total{display:flex;flex-direction:column;align-items:center;min-width:115px;padding:.75rem 1rem;border-radius:.8rem;background:rgba(255,255,255,.14);color:#fff}.notes-total strong{font-size:1.6rem}.notes-total span{font-size:.72rem}.notes-list{display:grid;gap:1rem}.note-card{padding:1.25rem;border:1px solid #e7e8ee;border-radius:.9rem;background:#fff;box-shadow:0 .2rem .7rem rgba(67,89,113,.05)}.note-card__top{display:flex;align-items:flex-start;gap:.8rem}.note-card__icon{display:grid;place-items:center;flex:0 0 42px;width:42px;height:42px;border-radius:11px;background:#eef0ff;color:#696cff;font-size:1.25rem}.note-meta{display:flex;flex-wrap:wrap;gap:.35rem 1rem;color:#8592a3;font-size:.75rem}.note-meta span{display:flex;align-items:center;gap:.3rem}.note-content{margin-top:1rem;padding:1rem;border-radius:.7rem;background:#f8f9fb;color:#4b5563;line-height:1.7;overflow-wrap:anywhere}.sticky-note-form{position:sticky;top:1rem}.empty-notes{text-align:center;padding:3rem 1.5rem}.empty-notes>span{display:grid;place-items:center;width:64px;height:64px;margin:0 auto 1rem;border-radius:50%;background:#eef0ff;color:#696cff;font-size:2rem}.empty-notes p{margin:0;color:#8592a3}
@media(max-width:1199.98px){.sticky-note-form{position:static}}
@media(max-width:575.98px){.notes-hero{align-items:flex-start;flex-direction:column;padding:1.25rem}.notes-total{align-items:flex-start;width:100%}.note-card{padding:1rem}}
</style>
@endsection