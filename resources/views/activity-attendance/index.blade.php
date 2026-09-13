@extends('layouts.master')
@section('title','Presensi Kegiatan')
@section('content')
@php($statusMeta=['draft'=>['Draft','secondary'],'open'=>['Dibuka','success'],'closed'=>['Ditutup','warning'],'archived'=>['Diarsipkan','dark']])
<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
 <div><h4 class="fw-bold mb-1">Presensi Kegiatan</h4><p class="text-muted mb-0">Buat dan bagikan formulir presensi untuk rapat maupun kegiatan umum.</p></div>
 <a href="{{route('activity-attendance.create')}}" class="btn btn-primary align-self-lg-center"><i class="bx bx-plus me-1"></i>Buat Form</a>
</div>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
<div class="card border-0 shadow-sm mb-4"><div class="card-body"><form class="row g-2">
 <div class="col-md-7"><div class="input-group"><span class="input-group-text"><i class="bx bx-search"></i></span><input name="q" value="{{request('q')}}" class="form-control" placeholder="Cari judul kegiatan"></div></div>
 <div class="col-md-3"><select name="status" class="form-select"><option value="">Semua status</option>@foreach($statusMeta as $key=>$meta)<option value="{{$key}}" @selected(request('status')===$key)>{{$meta[0]}}</option>@endforeach</select></div>
 <div class="col-md-2"><button class="btn btn-outline-primary w-100">Terapkan</button></div>
</form></div></div>
<div class="row g-4">
@forelse($forms as $form)@php($meta=$statusMeta[$form->status]??['Draft','secondary'])
<div class="col-md-6 col-xl-4"><article class="card border-0 shadow-sm h-100"><div class="card-body">
 <div class="d-flex justify-content-between gap-2 mb-3"><span class="avatar-initial rounded bg-label-primary p-2"><i class="bx bx-clipboard fs-4"></i></span><span class="badge bg-label-{{$meta[1]}} align-self-start">{{$meta[0]}}</span></div>
 <h5 class="fw-bold mb-1">{{$form->title}}</h5><p class="small text-muted text-truncate mb-3">{{$form->subtitle?:'Tanpa deskripsi'}}</p>
 <div class="d-flex flex-wrap gap-2 mb-3"><span class="badge bg-label-info">{{$form->questions_count}} pertanyaan</span><span class="badge bg-label-success">{{$form->responses_count}} respons</span>@if(Auth::user()->role==='superadmin')<span class="badge bg-label-secondary">{{$form->bidang?:'Tanpa bidang'}}</span>@endif</div>
 <small class="text-muted d-block"><i class="bx bx-calendar me-1"></i>{{$form->opens_at?->translatedFormat('d M Y H:i')?:'Waktu fleksibel'}} @if($form->closes_at) - {{$form->closes_at->translatedFormat('d M Y H:i')}}@endif</small>
</div><div class="card-footer bg-transparent border-top d-flex flex-wrap gap-2">
 <a href="{{route('activity-attendance.edit',$form)}}" class="btn btn-sm btn-primary"><i class="bx bx-edit me-1"></i>Kelola</a>
 <a href="{{route('activity-attendance.responses',$form)}}" class="btn btn-sm btn-outline-success"><i class="bx bx-list-check me-1"></i>Respons</a>
 <a href="{{$form->public_url}}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bx bx-link-external"></i></a>
 <button type="button" class="btn btn-sm btn-outline-secondary copy-public-link" data-url="{{$form->public_url}}" title="Salin link"><i class="bx bx-copy"></i></button>
</div></article></div>
@empty<div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center py-5 text-muted"><i class="bx bx-clipboard fs-1"></i><h5 class="mt-2">Belum ada form presensi</h5><p>Buat form pertama untuk mulai menerima presensi publik.</p></div></div></div>@endforelse
</div>
@if($forms->hasPages())<div class="mt-4">{{$forms->links()}}</div>@endif
@endsection
@push('js')<script>document.querySelectorAll('.copy-public-link').forEach(b=>b.addEventListener('click',async()=>{await navigator.clipboard.writeText(b.dataset.url);const old=b.innerHTML;b.innerHTML='<i class="bx bx-check"></i>';b.classList.add('btn-success');setTimeout(()=>{b.innerHTML=old;b.classList.remove('btn-success')},1500)}));</script>@endpush
