@extends('layouts.auth')
@section('title', 'Verifikasi Tanda Tangan Elektronik')
@section('content')
@php
    $isCompleted = $document->status === 'completed';
    $orderedActions = $document->actions->sortByDesc(fn ($item) => $item->actor?->sequence ?? 0);
    $sourceLabel = ['training_certificates'=>'Sertifikat Pelatihan INTEGRAL','jct_certificates'=>'Jabar Corpu Talent','other_documents'=>'Dokumen Lainnya'][$document->request->source_type] ?? 'Dokumen Elektronik';
    $statusMeta = [
        'completed' => ['Sudah diverifikasi/TTE', 'success', 'check-circle'],
        'processing' => ['Sedang diproses', 'info', 'loader-alt'],
        'pending' => ['Menunggu tindakan', 'warning', 'time-five'],
        'waiting' => ['Menunggu giliran', 'secondary', 'hourglass'],
        'failed' => ['Perlu diproses ulang', 'danger', 'error-circle'],
        'rejected' => ['Ditolak', 'danger', 'x-circle'],
    ];
@endphp
<style>
    body{background:linear-gradient(145deg,#eef5ff 0%,#f8fbff 48%,#eefbf6 100%);min-height:100vh}
    .verify-shell{max-width:980px;margin:auto;padding:38px 12px 56px}
    .verify-brand{display:flex;align-items:center;justify-content:center;gap:10px;color:#566a7f;font-weight:700;letter-spacing:.04em}
    .verify-brand img{width:34px;height:34px;object-fit:contain}
    .verify-hero{position:relative;overflow:hidden;border:0;border-radius:24px;background:linear-gradient(135deg,#166534,#16a34a);color:#fff;box-shadow:0 18px 45px rgba(22,101,52,.18)}
    .verify-hero:after{content:"";position:absolute;width:260px;height:260px;border-radius:50%;right:-90px;top:-115px;background:rgba(255,255,255,.1)}
    .document-seal{position:relative;width:92px;height:92px;display:grid;place-items:center;border-radius:24px;background:rgba(255,255,255,.16);font-size:48px;flex:0 0 auto}
    .document-seal .seal-check{position:absolute;right:-7px;bottom:-7px;width:34px;height:34px;display:grid;place-items:center;border-radius:50%;background:#fff;color:#16a34a;font-size:24px;box-shadow:0 5px 15px rgba(0,0,0,.15)}
    .info-card,.actor-card{border:0;border-radius:18px;box-shadow:0 8px 28px rgba(34,48,62,.08)}
    .actor-card.signer{border-left:4px solid #16a34a}
    .actor-photo{width:70px;height:70px;flex:0 0 70px}
    .actor-photo img,.actor-photo span{width:100%;height:100%;object-fit:cover}
    .actor-photo span{display:grid;place-items:center;border-radius:50%;font-size:24px;font-weight:700}
    .verification-notice{border-radius:16px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
    .meta-item{padding:13px 15px;border-radius:12px;background:#f7f9fc;height:100%}
    .token-box{font-family:monospace;word-break:break-all;background:#f5f7fa;border-radius:10px;padding:9px 12px}
    @media(max-width:575.98px){.verify-shell{padding-top:24px}.verify-hero .card-body{padding:24px!important}.document-seal{width:76px;height:76px;font-size:40px}.actor-card .card-body{align-items:flex-start!important}.actor-status{width:100%;text-align:left!important;padding-left:86px}}
</style>

<div class="verify-shell">
    <div class="verify-brand mb-4">
        <img src="{{ asset('assets/img/favicon/inte.png') }}" alt="INTEGRAL">
        <span>INTEGRAL &middot; BPSDM PROVINSI JAWA BARAT</span>
    </div>

    <section class="card verify-hero mb-4">
        <div class="card-body p-4 p-md-5 position-relative" style="z-index:1">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                <div class="document-seal">
                    <i class="bx bx-file"></i>
                    <span class="seal-check"><i class="bx bx-check"></i></span>
                </div>
                <div class="flex-grow-1">
                    <span class="badge bg-white text-success mb-3 px-3 py-2"><i class="bx bx-shield-quarter me-1"></i>VERIFIKASI RESMI</span>
                    <h2 class="text-white fw-bold mb-2">Dokumen terdaftar pada sistem</h2>
                    <p class="mb-0 text-white" style="opacity:.9">Dokumen ini tercatat dan dapat diverifikasi melalui sistem INTEGRAL.</p>
                </div>
                <div class="text-md-end">
                    <span class="badge bg-{{ $isCompleted ? 'success' : 'warning' }} px-3 py-2">{{ $isCompleted ? 'PROSES TTE SELESAI' : 'MASIH DALAM PROSES' }}</span>
                </div>
            </div>
        </div>
    </section>

    <div class="verification-notice p-3 p-md-4 mb-4 d-flex gap-3 align-items-start">
        <i class="bx bx-info-circle fs-3 flex-shrink-0"></i>
        <div><strong class="d-block mb-1">Periksa keaslian dokumen</strong><span>Mohon cek dengan teliti kesesuaian antara dokumen yang terdaftar dalam sistem INTEGRAL dan dokumen fisik atau digital yang Anda terima.</span></div>
    </div>

    <section class="card info-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                <div><small class="text-uppercase text-muted fw-semibold">Informasi dokumen</small><h4 class="fw-bold mt-1 mb-1">{{ $document->participantCertificate?->participant?->name ?: $document->original_name }}</h4><span class="text-muted">{{ $document->original_name }}</span></div>
                <a href="{{ route('electronic-signatures.verify.download',$document->verification_token) }}" class="btn btn-primary align-self-md-center"><i class="bx bx-download me-2"></i>Unduh Dokumen</a>
            </div>
            <div class="row g-3">
                <div class="col-md-6"><div class="meta-item"><small class="text-muted d-block mb-1">Kegiatan/Pengajuan</small><strong>{{ $document->request->title }}</strong></div></div>
                <div class="col-md-3"><div class="meta-item"><small class="text-muted d-block mb-1">Jenis Dokumen</small><strong>{{ $sourceLabel }}</strong></div></div>
                <div class="col-md-3"><div class="meta-item"><small class="text-muted d-block mb-1">Status</small><span class="badge bg-label-{{ $isCompleted ? 'success' : 'warning' }}">{{ $isCompleted ? 'Valid &middot; Selesai' : 'Dalam Proses' }}</span></div></div>
            </div>
        </div>
    </section>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-2 mb-3">
        <div><h4 class="fw-bold mb-1">Dokumen ini telah diverifikasi dan/atau di-TTE oleh:</h4><p class="text-muted mb-0">Penandatangan akhir ditampilkan paling atas, dilanjutkan pemaraf sebelumnya.</p></div>
        <span class="badge bg-label-primary px-3 py-2">{{ $orderedActions->count() }} tahapan</span>
    </div>

    <div class="d-flex flex-column gap-3">
        @forelse($orderedActions as $action)
            @php
                $user = $action->actor?->user;
                $isSigner = $action->actor?->role === 'signer';
                $photo = $user?->profile_photo ? asset('storage/'.$user->profile_photo) : $user?->avatar;
                $meta = $statusMeta[$action->status] ?? ['Belum diproses','secondary','time-five'];
            @endphp
            <article class="card actor-card {{ $isSigner ? 'signer' : '' }}">
                <div class="card-body p-3 p-md-4 d-flex flex-wrap align-items-center gap-3">
                    <div class="actor-photo">@if($photo)<img src="{{ $photo }}" alt="Foto {{ $user?->name }}" class="rounded-circle">@else<span class="bg-label-{{ $isSigner ? 'success' : 'primary' }}">{{ mb_strtoupper(mb_substr($user?->name ?: '?',0,1)) }}</span>@endif</div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1"><strong class="fs-5">{{ $user?->name ?: 'Akun tidak tersedia' }}</strong><span class="badge bg-label-{{ $isSigner ? 'success' : 'primary' }}">{{ $isSigner ? 'Penandatangan' : 'Pemaraf tahap '.$action->actor?->sequence }}</span></div>
                        <span class="text-muted"><i class="bx bx-briefcase-alt-2 me-1"></i>{{ $user?->jabatan ?: 'Jabatan belum tersedia' }}</span>
                    </div>
                    <div class="actor-status text-end flex-shrink-0">
                        <span class="badge bg-label-{{ $meta[1] }} px-3 py-2"><i class="bx bx-{{ $meta[2] }} me-1"></i>{{ $meta[0] }}</span>
                        <small class="d-block text-muted mt-2">{{ $action->signed_at?->translatedFormat('d F Y, H:i').' WIB' ?: 'Belum ditindaklanjuti' }}</small>
                    </div>
                </div>
            </article>
        @empty
            <div class="card info-card"><div class="card-body text-center text-muted py-5"><i class="bx bx-user-check fs-1"></i><p class="mt-2 mb-0">Belum ada alur penandatangan pada dokumen ini.</p></div></div>
        @endforelse
    </div>

    <div class="text-center mt-4">
        <small class="text-muted d-block mb-2">ID Verifikasi Dokumen</small>
        <div class="token-box d-inline-block">{{ $document->verification_token }}</div>
        <p class="text-muted small mt-3 mb-0"><i class="bx bx-lock-alt me-1"></i>Halaman ini merupakan layanan verifikasi publik INTEGRAL.</p>
    </div>
</div>
@endsection
