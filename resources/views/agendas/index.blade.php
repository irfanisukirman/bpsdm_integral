@extends('layouts.master')
@section('title','Kelola Agenda')
@section('content')
@php
    $eventGroups = $events->getCollection()->groupBy(fn($event) => $event['starts_at']->format('Y-m-d'));
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Agenda Kegiatan</h4>
        <p class="text-muted mb-0">Agenda umum dan jadwal pelatihan dalam satu daftar terintegrasi.</p>
    </div>
    <a href="{{ route('agendas.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>Buat Agenda</a>
</div>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
<div class="d-flex flex-wrap gap-2 mb-3">
    <span class="badge bg-label-primary"><i class="bx bx-calendar me-1"></i>Agenda Umum</span>
    <span class="badge bg-label-success"><i class="bx bx-book-open me-1"></i>Jadwal Pelatihan</span>
    <small class="text-muted ms-sm-auto">Menampilkan {{$events->firstItem()??0}}-{{$events->lastItem()??0}} dari {{$events->total()}} kegiatan</small>
</div>
@forelse($eventGroups as $date=>$items)
 <div class="d-flex align-items-center gap-3 mt-4 mb-2">
  <div class="badge bg-primary rounded-pill px-3 py-2">{{\Carbon\Carbon::parse($date)->translatedFormat('l, d F Y')}}</div>
  <div class="border-top flex-grow-1"></div><small class="text-muted">{{$items->count()}} kegiatan</small>
 </div>
 <div class="card mb-3"><div class="list-group list-group-flush">
 @foreach($items as $event)
  @php
   $isPast=$event['ends_at']->isPast();$isOngoing=$event['starts_at']->isPast()&&$event['ends_at']->isFuture();
   $isTraining=$event['type']==='training';
  @endphp
  <div class="list-group-item p-3 p-lg-4">
   <div class="row align-items-center g-3">
    <div class="col-md-2 col-xl-1 text-md-center"><div class="fw-bold fs-5 text-primary">{{$event['starts_at']->format('H:i')}}</div><small class="text-muted">s.d. {{$event['ends_at']->format('H:i')}}</small></div>
    <div class="col-md-7 col-xl-8 border-start-md">
     <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
      <h5 class="mb-0 fw-bold">{{$event['title']}}</h5>
      <span class="badge {{$isTraining?'bg-label-success':'bg-label-primary'}}">{{$isTraining?'Pelatihan':$event['subtitle']}}</span>
      <span class="badge bg-label-{{$isOngoing?'success':($isPast?'secondary':'info')}}">{{$isOngoing?'Sedang berlangsung':($isPast?'Selesai':'Akan datang')}}</span>
     </div>
     @if($isTraining)<div class="fw-semibold text-dark small mb-1"><i class="bx bx-list-ul me-1"></i>{{$event['subtitle']}}</div>@endif
     <div class="text-muted small d-flex flex-wrap gap-3">
      <span><i class="bx bx-map me-1"></i>{{$event['location']?:'Lokasi belum ditentukan'}}</span>
      <span><i class="bx bx-user me-1"></i>{{$event['executor']?:'Pelaksana belum dicantumkan'}}</span>
      <span><i class="bx bx-buildings me-1"></i>{{$event['bidang']}}</span>
     </div>
     @if($event['description'])<p class="mb-0 mt-2 text-muted">{{\Illuminate\Support\Str::limit($event['description'],180)}}</p>@endif
    </div>
    <div class="col-md-3 col-xl-3">
     <div class="d-flex justify-content-md-end gap-2">
      @if($isTraining)
       <a href="{{$event['manage_url']}}" class="btn btn-sm btn-outline-success"><i class="bx bx-calendar-edit me-1"></i>Kelola Jadwal</a>
      @else
       <a href="{{$event['edit_url']}}" class="btn btn-sm btn-outline-primary"><i class="bx bx-edit me-1"></i>Edit</a>
       <form method="POST" action="{{$event['delete_url']}}" onsubmit="return confirm('Hapus agenda ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button></form>
      @endif
     </div>
     <div class="text-md-end mt-2"><small class="text-muted">Dibuat oleh {{$event['creator']??'-'}}</small></div>
    </div>
   </div>
  </div>
 @endforeach
 </div></div>
@empty
 <div class="card"><div class="card-body text-center py-5"><i class="bx bx-calendar-x display-4 text-muted"></i><h5 class="mt-3">Belum ada kegiatan</h5><p class="text-muted">Agenda dan jadwal pelatihan akan tampil di sini.</p></div></div>
@endforelse
@if($events->hasPages())<div class="mt-4">{{$events->onEachSide(1)->links()}}</div>@endif
@endsection
