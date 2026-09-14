@extends('layouts.master')
@section('title', 'Buku Tamu Digital')

@push('css')
<style>
    .guest-hero { border-radius: 22px; background: linear-gradient(135deg,#24366f 0%,#5268dc 55%,#19a8b5 100%); color:#fff; overflow:hidden; position:relative; }
    .guest-hero::after { content:""; position:absolute; width:260px; height:260px; right:-90px; top:-130px; border-radius:50%; background:rgba(255,255,255,.12); }
    .stat-card,.content-card { border:0; border-radius:18px; box-shadow:0 8px 28px rgba(35,48,90,.08); }
    .stat-icon { width:46px; height:46px; border-radius:14px; display:grid; place-items:center; font-size:1.45rem; }
    .active-panel { border-radius:20px; background:linear-gradient(180deg,#f6fffb,#fff); border:1px solid #d8f3e6; }
    .active-list { max-height:440px; overflow:auto; }
    .visitor-row { padding:16px; border:1px solid #e8edf5; border-radius:16px; background:#fff; transition:.18s ease; }
    .visitor-row:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(38,60,122,.09); }
    .visitor-avatar { width:46px; height:46px; border-radius:14px; display:grid; place-items:center; flex:none; background:#e8f8f0; color:#16835a; font-weight:700; }
    .live-dot { width:9px; height:9px; border-radius:50%; background:#27b879; display:inline-block; box-shadow:0 0 0 5px rgba(39,184,121,.13); }
    .location-card { border:1px solid #e6eaf2; border-radius:16px; transition:.18s ease; }
    .location-card:hover { border-color:#aebbf2; box-shadow:0 8px 22px rgba(49,67,135,.08); }
    .copy-feedback { min-width:104px; }
    .guest-table thead th { white-space:nowrap; font-size:.76rem; text-transform:uppercase; letter-spacing:.04em; color:#697386; background:#f7f8fc; }
    .guest-table td { vertical-align:middle; }
</style>
@endpush

@section('content')
<div class="guest-hero mb-4">
    <div class="card-body p-4 p-lg-5 position-relative" style="z-index:1">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2"><span class="live-dot"></span><small class="fw-semibold">MONITORING RESEPSIONIS</small></div>
                <h3 class="text-white fw-bold mb-1">Buku Tamu Digital</h3>
                <p class="mb-0 opacity-75">Pantau tamu yang berada di kantor, tentukan bidang tujuan, dan kelola akses publik.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if(auth()->user()->role === 'superadmin')
                    <a href="{{ route('guest-book.accounts') }}" class="btn btn-outline-light"><i class="bx bx-user-check me-1"></i>Pengelola Buku Tamu</a>
                    <button class="btn btn-light text-primary" data-bs-toggle="modal" data-bs-target="#locationModal"><i class="bx bx-plus me-1"></i>Tambah Lokasi</button>
                @endif
            </div>
        </div>
    </div>
</div>

@if(session('success'))<div class="alert alert-success border-0 shadow-sm"><i class="bx bx-check-circle me-1"></i>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger border-0 shadow-sm"><i class="bx bx-error-circle me-1"></i>{{ $errors->first() }}</div>@endif

<div class="row g-3 mb-4">
    @foreach([
        ['Tamu Hari Ini',$stats['today'],'bx-calendar-check','primary'],
        ['Masih di Kantor',$stats['inside'],'bx-user-check','success'],
        ['Sudah Keluar Hari Ini',$stats['out'],'bx-log-out','info'],
        ['Instansi Hari Ini',$stats['institutions'],'bx-building','warning']
    ] as [$label,$value,$icon,$color])
        <div class="col-6 col-xl-3"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <span class="stat-icon bg-label-{{ $color }} text-{{ $color }}"><i class="bx {{ $icon }}"></i></span>
            <div><div class="fs-3 fw-bold text-dark lh-1">{{ $value }}</div><small class="text-muted">{{ $label }}</small></div>
        </div></div></div>
    @endforeach
</div>

<div class="active-panel p-3 p-lg-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div><h5 class="fw-bold mb-1"><span class="live-dot me-2"></span>Sedang Berada di Kantor</h5><p class="text-muted mb-0">Daftar tamu yang belum melakukan check-out.</p></div>
        <span class="badge bg-success rounded-pill px-3 py-2">{{ $activeVisitors->count() }} tamu aktif</span>
    </div>
    <div class="active-list">
        <div class="row g-3">
            @forelse($activeVisitors as $visitor)
                <div class="col-xl-6">
                    <div class="visitor-row h-100">
                        <div class="d-flex gap-3">
                            <span class="visitor-avatar">{{ strtoupper(mb_substr($visitor->name,0,1)) }}</span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex flex-wrap justify-content-between gap-2"><div><strong class="text-dark">{{ $visitor->name }}</strong><small class="d-block text-muted">{{ $visitor->institution }}</small></div><span class="badge bg-label-success align-self-start">Sejak {{ $visitor->checked_in_at->format('H:i') }} WIB</span></div>
                                <div class="small mt-3"><div class="mb-1"><i class="bx bx-map-pin text-primary me-1"></i>{{ $visitor->target_bidang ?: 'Bidang tujuan belum ditentukan' }}</div><div class="text-muted"><i class="bx bx-message-square-detail me-1"></i>{{ $visitor->purpose }}</div></div>
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"><small class="text-muted"><i class="bx bx-time-five me-1"></i>{{ $visitor->checked_in_at->diffForHumans(null,true) }} di kantor</small><form method="POST" action="{{ route('guest-book.checkout',$visitor) }}">@csrf @method('PUT')<button class="btn btn-sm btn-success"><i class="bx bx-log-out me-1"></i>Tandai Keluar</button></form></div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="text-center py-5"><span class="stat-icon bg-label-success text-success mx-auto"><i class="bx bx-check"></i></span><h6 class="fw-bold mt-3 mb-1">Tidak ada tamu aktif</h6><p class="text-muted mb-0">Semua tamu sudah tercatat keluar dari kantor.</p></div></div>
            @endforelse
        </div>
    </div>
</div>

<div class="card content-card mb-4">
    <div class="card-header border-bottom bg-transparent p-4"><h5 class="fw-bold mb-1">Link Publik & QR Buku Tamu</h5><small class="text-muted">Bagikan tautan atau unduh QR untuk ditempatkan di meja resepsionis.</small></div>
    <div class="card-body p-4"><div class="row g-3">
        @forelse($locations as $location)
            <div class="col-lg-6"><div class="location-card p-3 h-100">
                <div class="d-flex justify-content-between gap-3"><div><strong>{{ $location->name }}</strong><small class="d-block text-muted">{{ $location->visits_count }} kunjungan &middot; {{ $location->active_visits_count }} masih di lokasi</small></div><span class="badge bg-label-{{ $location->is_active?'success':'secondary' }} align-self-start">{{ $location->is_active?'Aktif':'Nonaktif' }}</span></div>
                <div class="input-group my-3"><input id="link{{ $location->id }}" class="form-control bg-light" readonly value="{{ $location->public_url }}"><button type="button" class="btn btn-primary copy-public-link copy-feedback" data-target="link{{ $location->id }}"><i class="bx bx-copy me-1"></i>Salin</button></div>
                <div class="d-flex flex-wrap gap-2"><a href="{{ route('guest-book.qr',$location) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-qr me-1"></i>Unduh QR</a>@if(auth()->user()->role==='superadmin')<form method="POST" action="{{ route('guest-book.locations.toggle',$location) }}">@csrf @method('PUT')<button class="btn btn-sm btn-outline-secondary">{{ $location->is_active?'Nonaktifkan':'Aktifkan' }}</button></form>@endif</div>
            </div></div>
        @empty
            <div class="col-12 text-center text-muted py-4">Belum ada lokasi buku tamu.</div>
        @endforelse
    </div></div>
</div>

<div class="card content-card">
    <div class="card-header border-bottom bg-transparent p-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
            <div><h5 class="fw-bold mb-1">Riwayat Kunjungan</h5><small class="text-muted">Cari, lengkapi bidang tujuan, dan ekspor data kunjungan.</small></div>
            <div class="d-flex gap-2"><a href="{{ route('guest-book.export.excel',request()->query()) }}" class="btn btn-outline-success"><i class="bx bx-spreadsheet me-1"></i>Excel</a><a href="{{ route('guest-book.export.pdf',request()->query()) }}" class="btn btn-outline-danger"><i class="bx bxs-file-pdf me-1"></i>PDF</a></div>
        </div>
        <form class="row g-2 mt-2"><div class="col-lg-5"><div class="input-group"><span class="input-group-text"><i class="bx bx-search"></i></span><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama, instansi, tujuan, atau kode"></div></div><div class="col-md-3 col-lg-2"><input type="date" name="date" value="{{ request('date') }}" class="form-control"></div><div class="col-md-3 col-lg-2"><select name="status" class="form-select"><option value="">Semua status</option><option value="inside" @selected(request('status')==='inside')>Di kantor</option><option value="out" @selected(request('status')==='out')>Sudah keluar</option></select></div><div class="col-md-3 col-lg-2 d-grid"><button class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i>Terapkan</button></div>@if(request()->hasAny(['q','date','status']))<div class="col-md-3 col-lg-1 d-grid"><a href="{{ route('guest-book.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a></div>@endif</form>
    </div>
    <div class="table-responsive"><table class="table table-hover guest-table mb-0"><thead><tr><th>Tamu</th><th>Instansi/Jabatan</th><th>Keperluan</th><th>Bidang Tujuan</th><th>Waktu</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        @forelse($visits as $v)<tr><td><strong>{{ $v->name }}</strong><small class="d-block text-muted">{{ $v->visit_code }} &middot; {{ $v->whatsapp }}</small></td><td>{{ $v->institution }}<small class="d-block text-muted">{{ $v->position?:'-' }}</small></td><td style="min-width:230px">{{ $v->purpose }}<small class="d-block text-muted"><i class="bx bx-map me-1"></i>{{ $v->location?->name }}</small></td><td style="min-width:270px"><form method="POST" action="{{ route('guest-book.target-bidang',$v) }}">@csrf @method('PUT')<div class="input-group input-group-sm"><select name="target_bidang" class="form-select" required><option value="">Pilih bidang tujuan</option>@foreach($targetBidangOptions as $bidang)<option value="{{ $bidang }}" @selected($v->target_bidang===$bidang)>{{ $bidang }}</option>@endforeach</select><button class="btn btn-primary" title="Simpan"><i class="bx bx-save"></i></button></div></form>@if(!$v->target_bidang)<small class="text-warning d-block mt-1"><i class="bx bx-info-circle"></i> Belum ditentukan</small>@endif</td><td class="text-nowrap">{{ $v->checked_in_at->format('d-m-Y H:i') }}@if($v->checked_out_at)<small class="d-block text-muted">Keluar {{ $v->checked_out_at->format('H:i') }}</small>@endif</td><td><span class="badge bg-label-{{ $v->checked_out_at?'success':'warning' }}">{{ $v->checked_out_at?'Sudah keluar':'Di kantor' }}</span></td><td>@if(!$v->checked_out_at)<form method="POST" action="{{ route('guest-book.checkout',$v) }}">@csrf @method('PUT')<button class="btn btn-sm btn-success text-nowrap"><i class="bx bx-log-out me-1"></i>Keluar</button></form>@else<span class="text-muted"><i class="bx bx-check-circle"></i></span>@endif</td></tr>@empty<tr><td colspan="7" class="text-center py-5 text-muted"><i class="bx bx-search-alt fs-2 d-block mb-2"></i>Belum ada kunjungan yang sesuai.</td></tr>@endforelse
    </tbody></table></div>
    @if($visits->hasPages())<div class="card-footer bg-transparent p-3">{{ $visits->links() }}</div>@endif
</div>

@if(auth()->user()->role==='superadmin')
<div class="modal fade" id="locationModal"><div class="modal-dialog modal-dialog-centered"><form method="POST" action="{{ route('guest-book.locations.store') }}" class="modal-content border-0">@csrf<div class="modal-header"><h5 class="modal-title">Tambah Lokasi Buku Tamu</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Nama lokasi</label><input name="name" class="form-control" placeholder="Contoh: Resepsionis Utama" required><small class="text-muted">Setiap lokasi memperoleh link publik dan QR tersendiri.</small></div><div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Batal</button><button class="btn btn-primary">Simpan Lokasi</button></div></form></div></div>
@endif
@endsection

@push('js')
<script>
document.querySelectorAll('.copy-public-link').forEach(button => button.addEventListener('click', async function () {
    const input = document.getElementById(this.dataset.target);
    const original = this.innerHTML;
    try {
        if (navigator.clipboard && window.isSecureContext) await navigator.clipboard.writeText(input.value);
        else { input.select(); document.execCommand('copy'); window.getSelection()?.removeAllRanges(); }
        this.innerHTML = '<i class="bx bx-check me-1"></i>Tersalin';
        this.classList.remove('btn-primary'); this.classList.add('btn-success');
        if (window.Swal) Swal.fire({icon:'success',title:'Link berhasil disalin',text:'Tautan formulir Buku Tamu siap dibagikan.',toast:true,position:'top-end',showConfirmButton:false,timer:2200,timerProgressBar:true});
    } catch (error) {
        window.prompt('Salin tautan berikut:', input.value);
    }
    setTimeout(() => { this.innerHTML = original; this.classList.remove('btn-success'); this.classList.add('btn-primary'); }, 2200);
}));
</script>
@endpush