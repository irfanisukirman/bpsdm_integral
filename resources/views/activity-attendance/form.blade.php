@extends('layouts.master')
@section('title','Buat Presensi Kegiatan')
@section('content')
<div class="mb-4"><a href="{{route('activity-attendance.index')}}" class="small"><i class="bx bx-left-arrow-alt"></i> Kembali</a><h4 class="fw-bold mt-2 mb-1">Buat Form Presensi</h4><p class="text-muted">Isi informasi kegiatan. Pertanyaan ditambahkan setelah form disimpan.</p></div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{$e}}</li>@endforeach</ul></div>@endif
<div class="card border-0 shadow-sm"><div class="card-body p-4"><form method="POST" action="{{route('activity-attendance.store')}}" class="row g-3">@csrf
 <div class="col-12"><label class="form-label">Judul Kegiatan <span class="text-danger">*</span></label><input name="title" value="{{old('title')}}" class="form-control form-control-lg" required placeholder="Contoh: Rapat Koordinasi Bulanan"></div>
 <div class="col-12"><label class="form-label">Subjudul/Deskripsi</label><textarea name="subtitle" class="form-control" rows="4" placeholder="Jelaskan tujuan atau petunjuk pengisian">{{old('subtitle')}}</textarea></div>
 @if(Auth::user()->role==='superadmin')<div class="col-md-6"><label class="form-label">Bidang Pengelola</label><input name="bidang" value="{{old('bidang',Auth::user()->bidang)}}" class="form-control" placeholder="Nama bidang"></div>@else<input type="hidden" name="bidang" value="{{Auth::user()->bidang}}">@endif
 <div class="col-md-6"><label class="form-label">Lokasi/Media</label><input name="location" value="{{old('location')}}" class="form-control" placeholder="Ruang rapat / Zoom"></div>
 <div class="col-md-6"><label class="form-label">Dibuka Pada</label><input type="datetime-local" name="opens_at" value="{{old('opens_at')}}" class="form-control"></div>
 <div class="col-md-6"><label class="form-label">Ditutup Pada</label><input type="datetime-local" name="closes_at" value="{{old('closes_at')}}" class="form-control"></div>
 <div class="col-md-4"><label class="form-label">Status Awal</label><select name="status" class="form-select"><option value="draft">Draft</option><option value="open">Langsung Dibuka</option></select></div>
 <div class="col-md-8"><label class="form-label">Pesan Setelah Mengisi</label><input name="confirmation_message" value="{{old('confirmation_message')}}" class="form-control" placeholder="Terima kasih, kehadiran Anda telah tercatat."></div>
 <div class="col-12"><button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan dan Susun Pertanyaan</button></div>
</form></div></div>
@endsection
