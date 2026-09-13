@extends('layouts.master')
@section('title','Detail Presensi Magang')
@section('content')
<style>
.intern-detail-hero{border:0;border-radius:20px;background:linear-gradient(125deg,#173f55,#2f7895 62%,#59a6bf);color:#fff;overflow:hidden}
.intern-detail-card{border:0;border-radius:17px;box-shadow:0 5px 22px rgba(34,48,62,.07)}
.intern-avatar{width:58px;height:58px;border-radius:16px;background:rgba(255,255,255,.16);display:flex;align-items:center;justify-content:center;font-size:28px}
.metric-icon{width:43px;height:43px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:23px}
.status-dot{width:9px;height:9px;border-radius:50%;display:inline-block}
.filter-panel{background:#f7f9fb;border:1px solid #e9edf2;border-radius:14px;padding:15px}
</style>
<a href="{{route('internships.show',$participant->program)}}" class="small text-muted"><i class="bx bx-left-arrow-alt"></i> Kembali ke program</a>

<div class="card intern-detail-hero my-3">
 <div class="card-body p-4">
  <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
   <div class="d-flex align-items-center gap-3">
    <span class="intern-avatar"><i class="bx bx-user"></i></span>
    <div><span class="badge bg-white text-primary mb-2">{{ucfirst($participant->status)}}</span><h3 class="text-white fw-bold mb-1">{{$participant->name}}</h3><div class="opacity-75">{{$participant->student_number}} &middot; {{$participant->institution}} &middot; {{$participant->placement_unit}}</div></div>
   </div>
   <div class="d-flex flex-wrap gap-2">
    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#adminAttendanceModal"><i class="bx bx-user-check me-1"></i>Absenkan Peserta</button>
    <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#editParticipant{{$participant->id}}"><i class="bx bx-edit-alt me-1"></i>Edit Data</button>
    <a class="btn btn-outline-light" href="{{route('internships.participants.attendance.export',array_filter(['participant'=>$participant,'month'=>$month,'status'=>$status]))}}"><i class="bx bx-download me-1"></i>Unduh Excel</a>
   </div>
  </div>
 </div>
</div>

@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif

<div class="row g-3 mb-4">
 @foreach([
 ['Total Kehadiran',$stats['present'],'bx-check-circle','success'],
 ['Hari Terlambat',$stats['late_days'],'bx-time-five','warning'],
 ['Total Keterlambatan',$stats['late_minutes'].' menit','bx-timer','danger'],
 ['Izin / Sakit',$stats['permission'].' / '.$stats['sick'],'bx-file','info'],
 ['Belum Presensi Pulang',$stats['missing_checkout'],'bx-log-out-circle','secondary']
 ] as $item)
 <div class="col-6 col-xl"><div class="card intern-detail-card h-100"><div class="card-body d-flex gap-3 align-items-center"><span class="metric-icon bg-label-{{$item[3]}} text-{{$item[3]}}"><i class="bx {{$item[2]}}"></i></span><div><h5 class="fw-bold mb-0">{{$item[1]}}</h5><small class="text-muted">{{$item[0]}}</small></div></div></div></div>
 @endforeach
</div>

<div class="card intern-detail-card">
 <div class="card-header border-bottom"><div class="d-flex flex-column flex-lg-row justify-content-between gap-3"><div><h5 class="fw-bold mb-1">Riwayat Presensi</h5><small class="text-muted">Data kehadiran dan keterlambatan {{$participant->name}}.</small></div><span class="badge bg-label-primary align-self-lg-center">{{$attendances->total()}} catatan</span></div></div>
 <div class="card-body border-bottom">
  <form method="GET" class="filter-panel row g-2 align-items-end">
   <div class="col-md-5"><label class="form-label small fw-semibold">Bulan</label><input type="month" name="month" value="{{$month}}" class="form-control"></div>
   <div class="col-md-4"><label class="form-label small fw-semibold">Status</label><select name="status" class="form-select"><option value="">Semua status</option>@foreach(['present'=>'Hadir','permission'=>'Izin','sick'=>'Sakit','absent'=>'Tidak hadir'] as $value=>$label)<option value="{{$value}}" @selected($status===$value)>{{$label}}</option>@endforeach</select></div>
   <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i>Terapkan</button><a href="{{route('internships.participants.attendance',$participant)}}" class="btn btn-outline-secondary"><i class="bx bx-reset"></i></a></div>
  </form>
 </div>
 <div class="table-responsive">
  <table class="table align-middle mb-0">
   <thead><tr><th>Tanggal</th><th>Status</th><th>Jam Masuk</th><th>Jam Pulang</th><th>Keterlambatan</th><th>Verifikasi</th><th>Keterangan</th></tr></thead>
   <tbody>
    @forelse($attendances as $attendance)
    @php
     $statusMap=['present'=>['Hadir','success'],'permission'=>['Izin','warning'],'sick'=>['Sakit','danger'],'absent'=>['Tidak hadir','secondary']];
     $current=$statusMap[$attendance->status]??[ucfirst($attendance->status),'secondary'];
    @endphp
    <tr>
     <td><strong>{{$attendance->attendance_date->translatedFormat('d M Y')}}</strong><small class="d-block text-muted">{{$attendance->attendance_date->translatedFormat('l')}}</small></td>
     <td><span class="badge bg-label-{{$current[1]}}"><span class="status-dot bg-{{$current[1]}} me-1"></span>{{$current[0]}}</span></td>
     <td>{{$attendance->check_in_at?->format('H:i')?:'-'}}</td>
     <td>@if($attendance->status==='present'&&!$attendance->check_out_at)<span class="badge bg-label-secondary">Belum tercatat</span>@else{{$attendance->check_out_at?->format('H:i')?:'-'}}@endif</td>
     <td>@if($attendance->late_minutes>0)<span class="text-danger fw-semibold">{{$attendance->late_minutes}} menit</span>@else<span class="text-muted">-</span>@endif</td>
     <td>@if($attendance->review_status==='not_required')<span class="text-muted">-</span>@else<span class="badge bg-label-{{$attendance->review_status==='approved'?'success':($attendance->review_status==='rejected'?'danger':'warning')}}">{{ucfirst($attendance->review_status)}}</span>@endif</td>
     <td><span title="{{$attendance->note}}">{{str($attendance->note?:'-')->limit(45)}}</span>@if($attendance->evidence_path)<a href="{{route('internships.absences.evidence',$attendance)}}" class="d-block small text-primary"><i class="bx bx-paperclip"></i> Bukti</a>@endif</td>
    </tr>
    @empty<tr><td colspan="7" class="text-center text-muted py-5"><i class="bx bx-calendar-x fs-1 d-block mb-2"></i>Belum ada presensi pada filter ini.</td></tr>@endforelse
   </tbody>
  </table>
 </div>
 <div class="card-footer">{{$attendances->links()}}</div>
</div>

<div class="modal fade intern-modal" id="adminAttendanceModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form method="POST" action="{{route('internships.participants.mark-present',$participant)}}" class="modal-content">@csrf
 <div class="modal-header"><div><h5 class="modal-title fw-bold"><i class="bx bx-user-check text-success me-1"></i>Absenkan Peserta</h5><small class="text-muted">{{$participant->name}}</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
 <div class="modal-body">
  <div class="alert alert-success d-flex gap-2"><i class="bx bx-info-circle fs-4"></i><small>Kehadiran yang dicatat admin selalu bernilai <strong>tepat waktu</strong> dengan keterlambatan 0 menit.</small></div>
  <div class="row g-3">
   <div class="col-sm-7"><label class="form-label fw-semibold">Tanggal kehadiran *</label><input type="date" name="attendance_date" value="{{old('attendance_date',today('Asia/Jakarta')->toDateString())}}" min="{{$participant->start_date->toDateString()}}" max="{{$participant->end_date->toDateString()}}" class="form-control" required></div>
   <div class="col-sm-5"><label class="form-label fw-semibold">Jam masuk *</label><input type="time" name="check_in_time" value="{{old('check_in_time',now('Asia/Jakarta')->format('H:i'))}}" class="form-control" required></div>
   <div class="col-12"><label class="form-label fw-semibold">Catatan admin</label><textarea name="note" rows="3" class="form-control" placeholder="Contoh: Presensi dibantu karena kendala perangkat">{{old('note')}}</textarea></div>
  </div>
 </div>
 <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-success"><i class="bx bx-check me-1"></i>Catat Kehadiran</button></div>
</form></div></div>

<div class="modal fade intern-modal" id="editParticipant{{$participant->id}}" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><form method="POST" action="{{route('internships.participants.update',$participant)}}" class="modal-content">@csrf @method('PUT')
 <div class="modal-header"><div><h5 class="modal-title fw-bold">Edit Data Peserta</h5><small class="text-muted">NIS/NIM baru otomatis menjadi username login.</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
 <div class="modal-body"><div class="row g-3">
  <div class="col-md-6"><label class="form-label">Nama lengkap *</label><input name="name" value="{{$participant->name}}" class="form-control" required></div><div class="col-md-6"><label class="form-label">NIS/NIM *</label><input name="student_number" value="{{$participant->student_number}}" class="form-control" required></div>
  <div class="col-md-6"><label class="form-label">Sekolah/Kampus *</label><input name="institution" value="{{$participant->institution}}" class="form-control" required></div><div class="col-md-6"><label class="form-label">Jurusan *</label><input name="major" value="{{$participant->major}}" class="form-control" required></div>
  <div class="col-md-6"><label class="form-label">Penempatan *</label><input name="placement_unit" value="{{$participant->placement_unit}}" class="form-control" required></div><div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" value="{{$participant->email}}" class="form-control" required></div>
  <div class="col-md-6"><label class="form-label">Tanggal mulai *</label><input type="date" name="start_date" value="{{$participant->start_date->format('Y-m-d')}}" class="form-control" required></div><div class="col-md-6"><label class="form-label">Tanggal selesai *</label><input type="date" name="end_date" value="{{$participant->end_date->format('Y-m-d')}}" class="form-control" required></div>
 </div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Perubahan</button></div>
</form></div></div>
@endsection
