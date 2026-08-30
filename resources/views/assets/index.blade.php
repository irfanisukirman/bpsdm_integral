@extends('layouts.master')
@section('title', 'Kelola Aset')
@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
 <div><h4 class="fw-bold mb-1">Kelola Aset</h4><p class="text-muted mb-0">Kelola ruangan, kendaraan, peralatan, dan fasilitas lainnya.</p></div>
 <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAsset"><i class="bx bx-plus me-1"></i>Tambah Aset</button>
</div>
@if(session('success'))<div class="alert alert-success alert-dismissible"><i class="bx bx-check-circle me-1"></i>{{session('success')}}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
<div class="text-muted small mb-3">Menampilkan {{ $assets->firstItem() ?? 0 }}-{{ $assets->lastItem() ?? 0 }} dari {{ $assets->total() }} aset</div>
<div class="row g-4">
@forelse($assets as $a)
 <div class="col-md-6 col-xl-4"><div class="card h-100 shadow-sm asset-card">
  @if($a->images->isNotEmpty())
   <div id="asset{{$a->id}}" class="carousel slide"><div class="carousel-inner">@foreach($a->images as $img)<div class="carousel-item {{$loop->first?'active':''}}"><img src="{{asset('storage/'.$img->path)}}" class="d-block w-100 asset-image" alt="Foto {{$a->name}}"></div>@endforeach</div>
   @if($a->images->count()>1)<button class="carousel-control-prev" type="button" data-bs-target="#asset{{$a->id}}" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button><button class="carousel-control-next" type="button" data-bs-target="#asset{{$a->id}}" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button><span class="badge bg-dark image-count"><i class="bx bx-images me-1"></i>{{$a->images->count()}}</span>@endif</div>
  @else <div class="asset-placeholder"><i class="bx bx-image-alt"></i><span>Belum ada foto</span></div> @endif
  <div class="card-body d-flex flex-column">
   <div class="d-flex justify-content-between"><span class="badge bg-label-primary text-capitalize">{{$a->type}}</span><span class="badge {{$a->is_public?'bg-label-success':'bg-label-secondary'}}">{{$a->is_public?'Publik':'Internal'}}</span></div>
   <h5 class="mt-3 mb-2">{{$a->name}}</h5>
   <div class="small text-muted mb-2"><i class="bx bx-map me-1"></i>{{$a->location}}</div>
   <div class="small text-muted mb-3"><i class="bx bx-group me-1"></i>Kapasitas {{$a->capacity??'-'}} orang</div>
   <p class="small mb-4 flex-grow-1">{{$a->facilities?:'Fasilitas belum dicantumkan.'}}</p>
   <div class="d-flex gap-2 pt-3 border-top"><button type="button" class="btn btn-sm btn-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#editAsset{{$a->id}}"><i class="bx bx-edit-alt me-1"></i>Edit</button>
    <form method="POST" action="{{route('assets.destroy',$a)}}" onsubmit="return confirm('Hapus aset ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Hapus aset"><i class="bx bx-trash"></i></button></form>
   </div>
  </div>
 </div></div>
 <div class="modal fade" id="editAsset{{$a->id}}" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><form class="modal-content" method="POST" enctype="multipart/form-data" action="{{route('assets.update',$a)}}">@csrf @method('PUT')<input type="hidden" name="page" value="{{$assets->currentPage()}}"><div class="modal-header"><div><h5 class="modal-title mb-1">Edit Aset</h5><small class="text-muted">Perbarui informasi {{$a->name}}</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">@include('assets.partials.form',['asset'=>$a,'editing'=>true])</div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan Perubahan</button></div></form></div></div>
@empty <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5"><i class="bx bx-cube fs-1 d-block mb-2"></i>Belum ada aset.</div></div></div>
@endforelse
</div>
@if($assets->hasPages())<div class="d-flex justify-content-center mt-4">{{$assets->onEachSide(1)->links()}}</div>@endif
<div class="modal fade" id="addAsset" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><form class="modal-content" method="POST" enctype="multipart/form-data" action="{{route('assets.store')}}">@csrf<div class="modal-header"><div><h5 class="modal-title mb-1">Tambah Aset</h5><small class="text-muted">Lengkapi informasi aset baru.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">@include('assets.partials.form',['asset'=>null,'editing'=>false])</div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan Aset</button></div></form></div></div>
@endsection
@push('css')
<style>
.asset-card{border:0;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease}.asset-card:hover{transform:translateY(-3px);box-shadow:0 .5rem 1.5rem rgba(67,89,113,.14)!important}.asset-image,.asset-placeholder{height:210px}.asset-image{object-fit:cover}.asset-placeholder{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;color:#a1acb8;background:#f5f5f9}.asset-placeholder i{font-size:2.5rem}.image-count{position:absolute;right:.75rem;bottom:.75rem;z-index:2;opacity:.85}
</style>
@endpush
