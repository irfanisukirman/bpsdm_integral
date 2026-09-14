@extends('layouts.auth')
@section('title','Presensi Berhasil')
@section('content')
<div class="container py-5" style="max-width:650px"><div class="card border-0 shadow-sm text-center"><div class="card-body p-5"><div class="d-inline-grid place-items-center rounded-circle bg-success text-white mb-3" style="width:84px;height:84px"><i class="bx bx-check" style="font-size:48px"></i></div><h2 class="fw-bold">Presensi berhasil dikirim</h2><p class="text-muted">{{$form->confirmation_message?:'Terima kasih. Kehadiran Anda telah tercatat pada sistem INTEGRAL.'}}</p><div class="alert alert-light border mt-4"><small class="text-muted d-block">Kode Bukti Respons</small><strong class="font-monospace text-break">{{$response->response_token}}</strong><small class="d-block text-muted mt-2">Simpan kode ini sebagai bukti pengisian.</small></div><p class="small text-muted mt-4 mb-0">{{$form->title}}<br>{{$response->submitted_at->translatedFormat('d F Y, H:i')}} WIB</p></div></div></div>
@endsection
