@extends('layouts.master')
@section('title','Kelola Sertifikasi')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
 <div><h4 class="fw-bold mb-1">Kelola Sertifikasi</h4><p class="text-muted mb-0">Pelaksanaan, peserta, dan kelulusan sertifikasi Bidang SKPK.</p></div>
 <div class="d-flex flex-wrap gap-2"><button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#exportModal" @disabled($exportYears->isEmpty())><i class="bx bx-spreadsheet me-1"></i>Export Excel</button><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#typeModal"><i class="bx bx-category me-1"></i>Jenis Sertifikasi</button><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal"><i class="bx bx-plus me-1"></i>Buat Pelaksanaan</button></div>
</div>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{session('error')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{$errors->first()}}</div>@endif
<div class="row g-3 mb-4">
 @foreach([['bx-category','Jenis Sertifikasi',$stats['types'],'primary'],['bx-calendar-event','Pelaksanaan',$stats['events'],'info'],['bx-group','Total Peserta',$stats['participants'],'warning'],['bx-certification','Total Lulusan',$stats['graduates'],'success']] as [$icon,$label,$value,$color])
 <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3"><span class="avatar-initial rounded bg-label-{{$color}} p-3"><i class="bx {{$icon}} fs-4"></i></span><div><small class="text-muted">{{$label}}</small><h4 class="mb-0 fw-bold">{{$value}}</h4></div></div></div></div>
 @endforeach
</div>
<div class="row g-4">
@forelse($events as $event)
 <div class="col-md-6 col-xl-4"><div class="card h-100 border-0 shadow-sm"><div class="card-body d-flex flex-column">
  <div class="d-flex justify-content-between align-items-start gap-2 mb-3"><span class="badge bg-label-primary">{{$event->type->name}}</span><div class="d-flex align-items-center gap-2"><small class="text-muted">{{$event->start_date->format('Y')}}</small><button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editEvent{{$event->id}}" title="Edit pelaksanaan"><i class="bx bx-edit"></i></button><form method="POST" action="{{route('certifications.destroy',$event)}}" onsubmit="return confirm('Hapus pelaksanaan ini secara permanen? Seluruh peserta, status kelulusan, tanda tangan, biodata PDF, berita acara, file import, dan folder kegiatan akan ikut dihapus. Jenis sertifikasi tidak akan dihapus.')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus seluruh data pelaksanaan"><i class="bx bx-trash"></i></button></form></div></div>
  <h5 class="fw-bold mb-2">{{$event->title}}</h5>
  <div class="small text-muted mb-2"><i class="bx bx-calendar me-1"></i>{{$event->start_date->translatedFormat('d M Y')}} - {{$event->end_date->translatedFormat('d M Y')}}</div>
  <div class="small text-muted mb-3"><i class="bx bx-map me-1"></i>{{$event->location}}</div>
  <div class="row g-2 mt-auto mb-3"><div class="col-6"><div class="rounded bg-label-info p-3 text-center"><h4 class="mb-0">{{$event->participants_count}}</h4><small>Peserta</small></div></div><div class="col-6"><div class="rounded bg-label-success p-3 text-center"><h4 class="mb-0">{{$event->graduates_count}}</h4><small>Lulusan</small></div></div></div>
  <a href="{{route('certifications.show',$event)}}" class="btn btn-primary"><i class="bx bx-cog me-1"></i>Kelola</a>
 </div></div></div>
@empty <div class="col-12"><div class="card"><div class="card-body py-5 text-center text-muted"><i class="bx bx-certification display-4"></i><h5 class="mt-3">Belum ada pelaksanaan sertifikasi</h5></div></div></div>
@endforelse
</div>
@if($events->hasPages())<div class="mt-4">{{$events->links()}}</div>@endif

<div class="modal fade" id="exportModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form method="GET" action="{{route('certifications.export')}}" class="modal-content"><div class="modal-header"><div><h5 class="modal-title">Export Data Sertifikasi</h5><small class="text-muted">Pilih tahun pelaksanaan yang akan direkap.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">
 <label class="form-label fw-semibold" for="export-year">Tahun Pelaksanaan</label>
 <select class="form-select" id="export-year" name="year" required><option value="">Pilih tahun</option>@foreach($exportYears as $year)<option value="{{$year}}">{{$year}}</option>@endforeach</select>
 <div class="alert alert-info d-flex gap-2 mt-3 mb-0"><i class="bx bx-info-circle fs-5"></i><div class="small">File berisi sheet <strong>Ringkasan</strong> jumlah pelaksanaan, peserta, dan lulusan, serta sheet terpisah untuk setiap jenis sertifikasi lengkap dengan data pesertanya.</div></div>
 </div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success"><i class="bx bx-download me-1"></i>Download Excel</button></div></form></div></div>
@foreach($events as $event)
<div class="modal fade" id="editEvent{{$event->id}}" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="POST" action="{{route('certifications.update',$event)}}" class="modal-content">@csrf @method('PUT')
 <div class="modal-header"><div><h5 class="modal-title">Edit Pelaksanaan Sertifikasi</h5><small class="text-muted">Perbarui informasi {{$event->title}}</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
 <div class="modal-body"><div class="row g-3">
  <div class="col-md-5"><label class="form-label">Jenis Sertifikasi</label><select name="certification_type_id" class="form-select" required>@foreach($types as $type)<option value="{{$type->id}}" @selected($event->certification_type_id===$type->id)>{{$type->name}}</option>@endforeach</select></div>
  <div class="col-md-7"><label class="form-label">Nama Sertifikasi/Judul</label><input name="title" class="form-control" value="{{$event->title}}" required></div>
  <div class="col-md-4"><label class="form-label">Tanggal Mulai</label><input type="date" name="start_date" class="form-control" value="{{$event->start_date->format('Y-m-d')}}" required></div>
  <div class="col-md-4"><label class="form-label">Tanggal Selesai</label><input type="date" name="end_date" class="form-control" value="{{$event->end_date->format('Y-m-d')}}" required></div>
  <div class="col-md-4"><label class="form-label">Jumlah Peserta</label><input type="number" min="0" name="participant_quota" class="form-control" value="{{$event->participant_quota}}" required></div>
  <div class="col-12"><label class="form-label">Lokasi</label><input name="location" class="form-control" value="{{$event->location}}" required></div>
  <div class="col-md-4"><label class="form-label">Nama Pengawas</label><input name="supervisor_name" class="form-control" value="{{$event->supervisor_name}}" required></div>
  <div class="col-md-4"><label class="form-label">Nomor HP Pengawas</label><input name="supervisor_phone" class="form-control" value="{{$event->supervisor_phone}}"></div>
  <div class="col-md-4"><label class="form-label">Instansi/Lembaga Pengawas</label><input name="supervisor_institution" class="form-control" value="{{$event->supervisor_institution}}"></div>
 </div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan Perubahan</button></div>
</form></div></div>
@endforeach
<div class="modal fade" id="typeModal"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Master Jenis Sertifikasi</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">
 <form method="POST" action="{{route('certifications.types.store')}}" class="d-flex gap-2 mb-4">@csrf<input name="name" class="form-control" placeholder="Contoh: PBJP Level 1" required><button class="btn btn-primary text-nowrap">Tambah</button></form>
 <div class="list-group">@forelse($types as $type)<div class="list-group-item d-flex justify-content-between align-items-center"><div><strong>{{$type->name}}</strong><small class="text-muted ms-2">{{$type->events_count}} pelaksanaan</small></div><form method="POST" action="{{route('certifications.types.destroy',$type)}}" onsubmit="return confirm('Hapus jenis sertifikasi ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-icon btn-outline-danger" {{$type->events_count?'disabled':''}}><i class="bx bx-trash"></i></button></form></div>@empty<div class="text-center text-muted py-3">Belum ada jenis sertifikasi.</div>@endforelse</div>
</div></div></div></div>

<div class="modal fade" id="eventModal"><div class="modal-dialog modal-lg modal-dialog-centered"><form method="POST" action="{{route('certifications.store')}}" class="modal-content">@csrf<div class="modal-header"><h5 class="modal-title">Buat Pelaksanaan Sertifikasi</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3">
 <div class="col-md-5"><label class="form-label">Jenis Sertifikasi</label><select name="certification_type_id" class="form-select" required><option value="">Pilih jenis</option>@foreach($types as $type)<option value="{{$type->id}}">{{$type->name}}</option>@endforeach</select></div>
 <div class="col-md-7"><label class="form-label">Nama Sertifikasi/Judul</label><input name="title" class="form-control" required></div>
 <div class="col-md-4"><label class="form-label">Tanggal Mulai</label><input type="date" name="start_date" class="form-control" required></div><div class="col-md-4"><label class="form-label">Tanggal Selesai</label><input type="date" name="end_date" class="form-control" required></div><div class="col-md-4"><label class="form-label">Jumlah Peserta</label><input type="number" min="0" name="participant_quota" class="form-control" value="0" required></div>
 <div class="col-12"><label class="form-label">Lokasi</label><input name="location" class="form-control" required></div>
 <div class="col-md-4"><label class="form-label">Nama Pengawas</label><input name="supervisor_name" class="form-control" required></div><div class="col-md-4"><label class="form-label">Nomor HP Pengawas</label><input name="supervisor_phone" class="form-control"></div><div class="col-md-4"><label class="form-label">Instansi/Lembaga Pengawas</label><input name="supervisor_institution" class="form-control"></div>
 </div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan & Kelola</button></div></form></div></div>
@endsection
