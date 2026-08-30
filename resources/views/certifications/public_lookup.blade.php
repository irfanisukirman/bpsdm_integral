@extends('certifications.public_layout')
@section('title','Akses Biodata Sertifikasi')
@section('content')
<div class="card border-0 shadow-lg"><div class="card-body p-4 p-md-5"><div class="text-center mb-4"><span class="avatar-initial rounded-circle bg-label-primary p-3 d-inline-flex"><i class="bx bx-certification fs-2"></i></span><h3 class="fw-bold mt-3 mb-1">{{$event->title}}</h3><p class="text-muted mb-0">{{$event->type->name}} · {{$event->start_date->translatedFormat('d M Y')}} - {{$event->end_date->translatedFormat('d M Y')}}</p></div>
@if($errors->any())<div class="alert alert-danger"><i class="bx bx-error-circle me-1"></i>{{$errors->first()}}</div>@endif
<div class="alert alert-info"><i class="bx bx-info-circle me-1"></i>Masukkan NIP/NIK yang telah didaftarkan oleh panitia untuk membuka formulir biodata.</div>
<form method="POST" action="{{route('certifications.public.verify',$event->public_token)}}">@csrf<label class="form-label required">NIP/NIK</label><div class="input-group input-group-lg"><span class="input-group-text"><i class="bx bx-id-card"></i></span><input name="nip_nik" value="{{old('nip_nik')}}" class="form-control" autocomplete="off" placeholder="Masukkan NIP atau NIK" required></div><button class="btn btn-primary btn-lg w-100 mt-4"><i class="bx bx-search me-1"></i>Cari Data Peserta</button></form>
</div></div>
@endsection
