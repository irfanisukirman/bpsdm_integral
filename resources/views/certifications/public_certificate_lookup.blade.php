@extends('certifications.public_layout')
@section('title','Akses Evaluasi dan Sertifikat')
@section('public_subtitle','Evaluasi dan Pengumpulan Sertifikat')
@section('content')
<div class="card border-0 shadow-lg"><div class="card-body p-4 p-md-5"><div class="text-center mb-4"><span class="avatar-initial rounded-circle bg-label-success p-3 d-inline-flex"><i class="bx bx-award fs-2"></i></span><h3 class="fw-bold mt-3 mb-1">Evaluasi & Sertifikat Peserta</h3><p class="text-muted mb-0">{{ $event->title }} · {{ $event->type->name }}</p></div>
@if($errors->any())<div class="alert alert-danger"><i class="bx bx-error-circle me-1"></i>{{ $errors->first() }}</div>@endif
<div class="alert alert-info"><i class="bx bx-info-circle me-1"></i>Masukkan NIP/NIK yang terdaftar. Nama peserta akan ditampilkan otomatis dan formulir hanya dapat dibuka oleh peserta yang telah dinyatakan lulus.</div>
<form method="POST" action="{{ route('certifications.certificates.public.verify',$event->public_token) }}">@csrf<label class="form-label required">NIP/NIK Peserta</label><div class="input-group input-group-lg"><span class="input-group-text"><i class="bx bx-id-card"></i></span><input name="nip_nik" value="{{ old('nip_nik') }}" class="form-control" autocomplete="off" placeholder="Masukkan NIP atau NIK" required></div><button class="btn btn-success btn-lg w-100 mt-4"><i class="bx bx-log-in-circle me-1"></i>Buka Formulir</button></form>
</div></div>
@endsection
