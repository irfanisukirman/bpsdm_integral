@extends('asset-rentals.public.layout')
@section('title','Reservasi Fasilitas')
@section('content')
<section class="public-hero py-5">
    <div class="container py-4 text-center">
        <span class="badge bg-white text-primary mb-3">TERBUKA UNTUK UMUM</span>
        <h1 class="text-white fw-bold mb-3">Temukan Fasilitas untuk Kegiatan Anda</h1>
        <p class="text-white-50 mx-auto mb-4" style="max-width:680px">Lihat fasilitas, harga, dan jadwalnya. Ajukan reservasi secara daring tanpa perlu membuat akun.</p>
        <a href="#lacak-reservasi" class="btn btn-light"><i class="bx bx-search-alt me-1"></i>Lacak Reservasi</a>
    </div>
</section>

<div class="container py-5">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section id="lacak-reservasi" class="card border-0 shadow-sm tracking-card mb-5">
        <div class="card-body p-4 p-lg-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5">
                    <span class="tracking-icon mb-3"><i class="bx bx-receipt"></i></span>
                    <h3 class="fw-bold mb-2">Lacak Status Reservasi</h3>
                    <p class="text-muted mb-3">Masukkan kode tiket reservasi. Jika lupa, masukkan email yang sama dengan saat melakukan booking.</p>
                    <div class="alert alert-warning mb-0 py-2"><i class="bx bx-info-circle me-1"></i><strong>Simpan kode tiket dan tautan status</strong> setelah mengirim reservasi.</div>
                </div>
                <div class="col-lg-7">
                    <form method="POST" action="{{ route('public.asset-rentals.lookup') }}">
                        @csrf
                        <label class="form-label fw-semibold">Kode tiket atau email terdaftar</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" name="lookup" value="{{ old('lookup', $lookupTerm) }}" class="form-control" placeholder="Contoh: RFP-202609-0001 atau nama@email.com" required>
                            <button class="btn btn-primary">Cari Reservasi</button>
                        </div>
                        <small class="text-muted d-block mt-2">Data hanya digunakan untuk menemukan reservasi yang pernah diajukan.</small>
                    </form>
                </div>
            </div>

            @if(!is_null($lookupResults))
                <div class="border-top mt-4 pt-4">
                    @if($lookupResults->isEmpty())
                        <div class="text-center py-3">
                            <i class="bx bx-search-alt fs-1 text-muted"></i>
                            <h5 class="fw-bold mt-2">Reservasi tidak ditemukan</h5>
                            <p class="text-muted mb-0">Periksa kembali kode tiket atau email Anda, atau hubungi pengelola.</p>
                        </div>
                    @else
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div><h5 class="fw-bold mb-1">Reservasi ditemukan</h5><small class="text-muted">{{ $lookupResults->count() }} tiket {{ $lookupType === 'email' ? 'terhubung dengan email tersebut' : 'ditemukan' }}.</small></div>
                        </div>
                        <div class="row g-3">
                            @foreach($lookupResults as $result)
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <strong class="text-primary">{{ $result->booking_code }}</strong>
                                            <span class="badge bg-label-{{ $result->status_color }}">{{ $result->status_label }}</span>
                                        </div>
                                        <h6 class="fw-bold mb-1">{{ $result->asset?->name }}</h6>
                                        <small class="text-muted d-block mb-3">{{ $result->rental_date->translatedFormat('d F Y') }}, {{ substr($result->start_time,0,5) }}–{{ substr($result->end_time,0,5) }}</small>
                                        <a href="{{ route('public.asset-rentals.status',$result->public_token) }}" class="btn btn-sm btn-outline-primary w-100"><i class="bx bx-show me-1"></i>Buka Status Tiket</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
        <div><h3 class="fw-bold mb-1">Fasilitas Tersedia</h3><p class="text-muted mb-0">{{ count($assets) }} fasilitas dapat diajukan untuk disewa.</p></div>
        @if($setting->manager_whatsapp)
            <a target="_blank" class="btn btn-success" href="https://wa.me/{{ $setting->manager_whatsapp_number }}?text={{ urlencode('Halo Admin, saya ingin menanyakan reservasi fasilitas BPSDM.') }}"><i class="bx bxl-whatsapp me-1"></i>Hubungi Admin Reservasi</a>
        @endif
    </div>

    <div class="row g-4">
        @forelse($assets as $asset)
            <div class="col-md-6 col-xl-4"><article class="card facility-card h-100">
                @if($asset->images->first())
                    <img class="facility-image" src="{{ Storage::url($asset->images->first()->path) }}" alt="{{ $asset->name }}">
                @else
                    <div class="placeholder-image"><i class="bx bx-building-house"></i></div>
                @endif
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between gap-2 mb-2"><span class="badge bg-label-primary">{{ ucfirst($asset->type) }}</span><span class="badge bg-label-success">Tersedia</span></div>
                    <h4 class="fw-bold mb-2">{{ $asset->name }}</h4>
                    <p class="text-muted small">{{ Str::limit($asset->description ?: 'Fasilitas publik yang dapat digunakan sesuai ketentuan pengelola.',110) }}</p>
                    <div class="d-flex align-items-end justify-content-between gap-2"><div><small class="text-muted d-block">Mulai dari</small><strong class="price fs-5">Rp {{ number_format($asset->hourly_rate,0,',','.') }}<small class="text-muted fw-normal">/jam</small></strong></div><a href="{{ route('public.asset-rentals.show',$asset) }}" class="btn btn-primary">Lihat & Booking</a></div>
                </div>
            </article></div>
        @empty
            <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body py-5 text-center"><i class="bx bx-calendar-x fs-1 text-muted"></i><h4 class="fw-bold mt-3">Belum ada fasilitas sewa</h4><p class="text-muted mb-0">Silakan kembali lagi setelah pengelola mengaktifkan fasilitas.</p></div></div></div>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
.tracking-card{border-radius:1.15rem!important;margin-top:-1.5rem;position:relative;z-index:2}.tracking-icon{display:grid;place-items:center;width:52px;height:52px;border-radius:15px;background:#eef0ff;color:#696cff;font-size:1.65rem}@media(max-width:767px){.tracking-card{margin-top:0}.tracking-card .input-group{display:flex}.tracking-card .input-group .btn{width:100%;margin-top:.65rem;border-radius:.5rem!important}.tracking-card .input-group .form-control{border-radius:0 .5rem .5rem 0!important}}
</style>
@endpush