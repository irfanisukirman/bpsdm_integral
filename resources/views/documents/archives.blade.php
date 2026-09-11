@extends('layouts.master')
@section('title','Arsip Dokumen')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-bold text-primary mb-2"><i class="bx bx-archive-in me-1"></i>Ruang Dokumen INTEGRAL</div>
            <h3 class="fw-bold mb-1">Arsip Dokumen</h3>
            <p class="text-muted mb-0">Folder induk tahun sebelumnya tersimpan otomatis di sini. Seluruh subfolder, file, versi, dan relasinya tetap utuh.</p>
        </div>
        <a href="{{ route('documents.index') }}" class="btn btn-primary"><i class="bx bx-folder-open me-1"></i>Dokumen Aktif</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(($errors ?? null)?->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label fw-semibold">Cari folder</label><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nama pelatihan atau kegiatan"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Tahun arsip</label><select name="year" class="form-select"><option value="">Semua tahun</option>@foreach($years as $year)<option value="{{ $year }}" @selected((string)request('year')===(string)$year)>{{ $year }}</option>@endforeach</select></div>
            @if(Auth::user()->role === 'superadmin')
                <div class="col-md-3"><label class="form-label fw-semibold">Bidang</label><select name="bidang" class="form-select"><option value="">Semua bidang</option>@foreach($bidangOptions as $bidang)<option value="{{ $bidang }}" @selected(request('bidang')===$bidang)>{{ $bidang }}</option>@endforeach</select></div>
            @endif
            <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i>Filter</button><a href="{{ route('documents.archives') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a></div>
        </form>
    </div></div>

    <div class="d-flex justify-content-between align-items-center mb-3"><h5 class="fw-bold mb-0">Folder Terarsip</h5><span class="badge bg-label-secondary">{{ $folders->total() }} folder</span></div>
    <div class="row g-4">
        @forelse($folders as $folder)
            @php($canManage=app(\App\Services\DocumentAccessService::class)->canManage(Auth::user(),$folder))
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card border-0 shadow-sm h-100 archive-folder-card"><div class="card-body p-4 position-relative">
                    <div class="d-flex justify-content-between align-items-start mb-3"><span class="archive-icon"><i class="bx bx-archive"></i></span><span class="badge bg-label-primary">{{ $folder->document_year }}</span></div>
                    <h6 class="fw-bold mb-1 text-break">{{ $folder->name }}</h6>
                    <small class="text-muted d-block mb-1"><i class="bx bx-buildings me-1"></i>{{ $folder->bidang }}</small>
                    <small class="text-muted d-block mb-3">{{ $folder->children_count }} subfolder · {{ $folder->files_count }} file langsung</small>
                    <a href="{{ route('documents.index',['folder'=>$folder->id,'bidang'=>$folder->bidang]) }}" class="btn btn-outline-primary btn-sm w-100"><i class="bx bx-show me-1"></i>Lihat Isi Arsip</a>
                    @if($canManage && $folder->document_year >= now()->year && $folder->is_archived)
                        <form method="POST" action="{{ route('documents.folder.restore',$folder) }}" class="mt-2" onsubmit="return confirm('Kembalikan folder ini ke dokumen aktif?')">@csrf @method('PUT')<button class="btn btn-outline-success btn-sm w-100"><i class="bx bx-undo me-1"></i>Kembalikan ke Aktif</button></form>
                    @endif
                </div></div>
            </div>
        @empty
            <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><span class="archive-empty"><i class="bx bx-archive"></i></span><h5 class="fw-bold mt-3">Arsip tidak ditemukan</h5><p class="text-muted mb-0">Belum ada folder arsip atau tidak ada data yang sesuai dengan filter.</p></div></div></div>
        @endforelse
    </div>
    @if($folders->hasPages())<div class="mt-4">{{ $folders->links() }}</div>@endif
</div>
@endsection
@push('styles')
<style>.archive-folder-card{border-radius:1rem;transition:.2s}.archive-folder-card:hover{transform:translateY(-3px);box-shadow:0 .6rem 1.4rem rgba(67,89,113,.14)!important}.archive-icon,.archive-empty{display:inline-grid;place-items:center;width:48px;height:48px;border-radius:14px;background:#eef0f4;color:#697a8d;font-size:1.5rem}.archive-empty{width:76px;height:76px;font-size:2.25rem}</style>
@endpush