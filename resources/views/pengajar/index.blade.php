@extends('layouts.master')
@section('title', 'Pengajar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h4 class="fw-bold mb-1">Kelola Pengajaran</h4><p class="text-muted mb-0">{{ $training->nama_pelatihan }} · Angkatan {{ $training->angkatan }}</p></div>
    <a href="{{ route('pengajar.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card h-100"><div class="card-header"><h5 class="mb-0">Data Administrasi</h5></div>
            <div class="card-body"><form method="POST" action="{{ route('pengajar.profile.update') }}">@csrf @method('PUT')
                @foreach(['npwp'=>'Nomor NPWP','nomor_rekening'=>'Nomor Rekening','nama_bank'=>'Nama Bank','nama_rekening'=>'Rekening Atas Nama'] as $field=>$label)
                <div class="mb-3"><label class="form-label">{{ $label }}</label><input class="form-control" name="{{ $field }}" value="{{ old($field, optional($user->pengajar)->{$field}) }}" required></div>
                @endforeach
                <button class="btn btn-primary">Simpan Data</button>
            </form></div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100"><div class="card-header"><h5 class="mb-0">Kelengkapan Dokumen</h5><small class="text-muted">Cukup diunggah satu kali.</small></div>
            <div class="card-body"><form method="POST" enctype="multipart/form-data" action="{{ route('pengajar.requirements.upload', $training) }}">@csrf
                @foreach(['file_cv'=>['CV Pengajar','cv_path'],'file_sertifikat'=>['Sertifikat TOT Pengajar','sertifikat_path'],'file_surat_tugas'=>['Surat Tugas Pengajar','surat_tugas_path']] as $field=>$meta)
                <div class="mb-3"><label class="form-label">{{ $meta[0] }}</label><input type="file" class="form-control" name="{{ $field }}" accept="application/pdf">
                    @include('pengajar.partials.file_status', ['path' => optional($user->pengajar)->{$meta[1]}, 'label' => $meta[0]])
                </div>
                @endforeach
                <button class="btn btn-primary">Unggah Kelengkapan</button>
            </form></div>
        </div>
    </div>
</div>

<div class="card"><div class="card-header"><h5 class="mb-0">Administrasi Tiap Sesi</h5></div><div class="table-responsive">
<table class="table align-middle"><thead><tr><th>Materi / Kegiatan</th><th>JP</th><th>Metode</th><th>Tanggal & Jam</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($schedules as $schedule)
@php
 $profilePaths = $user->pengajar ? [$user->pengajar->cv_path, $user->pengajar->sertifikat_path, $user->pengajar->surat_tugas_path] : [];
 $profileFilesExist = count($profilePaths) === 3 && collect($profilePaths)->every(fn ($path) => $path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path));
 $profileComplete = $user->pengajar && $user->pengajar->npwp && $user->pengajar->nomor_rekening && $user->pengajar->nama_bank && $user->pengajar->nama_rekening && $profileFilesExist;
 $sessionComplete = $schedule->pengajarDocuments && $schedule->pengajarDocuments->isComplete();
@endphp
<tr><td><strong>{{ $schedule->activity }}</strong><br><small>{{ $schedule->training->nama_pelatihan }}</small></td><td>{{ $schedule->jp ?? '-' }}</td><td>{{ ucfirst($schedule->training->metode ?? $schedule->training->model ?? '-') }}</td>
<td>{{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('d M Y') }}<br>{{ substr($schedule->start_time,0,5) }} - {{ substr($schedule->end_time,0,5) }}</td>
<td><span class="badge {{ $profileComplete && $sessionComplete ? 'bg-success' : 'bg-warning' }}">{{ $profileComplete && $sessionComplete ? 'Selesai' : 'Belum Selesai' }}</span></td>
<td><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#session{{ $schedule->id }}">Buka</button></td></tr>
@empty<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada jadwal mengajar.</td></tr>@endforelse
</tbody></table></div></div>

{{-- Modal berada di luar table-responsive agar tidak terpotong oleh overflow container. --}}
@foreach($schedules as $schedule)
<div class="modal fade" id="session{{ $schedule->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content" method="POST" enctype="multipart/form-data" action="{{ route('pengajar.session.upload', $schedule) }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">{{ $schedule->activity }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-4">{{ \Carbon\Carbon::parse($schedule->date)->translatedFormat('d M Y') }}, {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}</p>
                @foreach(['bahan_ajar'=>['Upload Bahan Ajar','bahan_ajar_path','.pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx'],'rbpmp_rp'=>['Upload RBPMP/RP/KURIKULUM','rbpmp_rp_path','.pdf,.doc,.docx'],'bukti_mengajar'=>['Upload Bukti Mengajar (Foto)','bukti_mengajar_path','.jpg,.jpeg,.png,.pdf']] as $field=>$meta)
                    <div class="mb-3">
                        <label class="form-label">{{ $meta[0] }}</label>
                        <input type="file" name="{{ $field }}" class="form-control" accept="{{ $meta[2] }}">
                        @include('pengajar.partials.file_status', ['path' => optional($schedule->pengajarDocuments)->{$meta[1]}, 'label' => $meta[0]])
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-primary">Simpan Dokumen</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
