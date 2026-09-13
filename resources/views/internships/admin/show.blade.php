@extends('layouts.master')
@section('title','Kelola Program Magang')
@section('content')
<style>.manage-hero{border:0;border-radius:18px;background:linear-gradient(125deg,#214e67,#4f9db9);color:#fff}.manage-card{border:0;border-radius:16px;box-shadow:0 5px 22px rgba(34,48,62,.07)}.intern-modal .modal-content{background:#fff!important;color:#344054!important;border:0!important;border-radius:20px!important;box-shadow:0 22px 70px rgba(25,35,50,.28)!important;overflow:hidden}.intern-modal .modal-header{background:linear-gradient(135deg,#f3fbf5,#fff)!important;border-bottom:1px solid #e8eee9!important;padding:22px 24px!important}.intern-modal .modal-body{background:#fff!important;padding:24px!important}.intern-modal .modal-footer{background:#fafbfc!important;border-top:1px solid #edf0f2!important;padding:16px 24px!important}.approval-person{background:#f7f9fb;border:1px solid #e7ebf0;border-radius:14px;padding:15px}.approval-icon{width:46px;height:46px;border-radius:13px;background:#e8f8e1;color:#56b925;display:flex;align-items:center;justify-content:center;font-size:25px}.approval-impact{border:1px solid #cce9d3;background:#f2fbf4;color:#285e34;border-radius:13px;padding:13px}.intern-modal .form-control{background:#fff!important;color:#344054!important}</style>
<a href="{{route('internships.index')}}" class="small text-muted"><i class="bx bx-left-arrow-alt"></i> Kembali</a>
<div class="card manage-hero my-3"><div class="card-body p-4"><div class="d-flex flex-column flex-lg-row justify-content-between gap-3"><div><span class="badge bg-white text-primary mb-2">{{ucfirst($program->status)}}</span><h3 class="text-white fw-bold">{{$program->title}}</h3><p class="mb-0 opacity-75">{{$program->bidang?:'Lintas bidang'}} &middot; {{$program->participants_count}} peserta</p></div><div class="align-self-lg-center"><button class="btn btn-light" id="copyInternLink" data-url="{{$program->public_url}}"><i class="bx bx-copy me-1"></i>Salin Link Pendaftaran</button></div></div></div></div>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{$e}}</li>@endforeach</ul></div>@endif
<div class="row g-4"><div class="col-xl-4"><div class="card manage-card"><div class="card-header border-bottom"><h5 class="fw-bold mb-1">Pengaturan Program</h5><small class="text-muted">Periode pendaftaran dan jam kerja.</small></div><div class="card-body"><form method="POST" action="{{route('internships.update',$program)}}" class="row g-3">@csrf @method('PUT')<div class="col-12"><label class="form-label">Nama program</label><input name="title" value="{{$program->title}}" class="form-control" required></div>@if(Auth::user()->role==='superadmin')<div class="col-12"><label class="form-label">Bidang</label><input name="bidang" value="{{$program->bidang}}" class="form-control"></div>@endif<div class="col-12"><label class="form-label">Deskripsi</label><textarea name="description" rows="3" class="form-control">{{$program->description}}</textarea></div><div class="col-6"><label class="form-label">Buka</label><input type="date" name="registration_opens_at" value="{{$program->registration_opens_at?->format('Y-m-d')}}" class="form-control"></div><div class="col-6"><label class="form-label">Tutup</label><input type="date" name="registration_closes_at" value="{{$program->registration_closes_at?->format('Y-m-d')}}" class="form-control"></div><div class="col-4"><label class="form-label">Masuk</label><input type="time" name="check_in_opens_at" value="{{substr($program->check_in_opens_at,0,5)}}" class="form-control" required></div><div class="col-4"><label class="form-label">Terlambat</label><input type="time" name="late_after" value="{{substr($program->late_after,0,5)}}" class="form-control" required></div><div class="col-4"><label class="form-label">Pulang</label><input type="time" name="check_out_opens_at" value="{{substr($program->check_out_opens_at,0,5)}}" class="form-control" required></div><div class="col-12"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(['draft'=>'Draft','open'=>'Buka pendaftaran','closed'=>'Tutup','archived'=>'Arsip'] as $v=>$l)<option value="{{$v}}" @selected($program->status===$v)>{{$l}}</option>@endforeach</select></div><div class="col-12 d-grid"><button class="btn btn-primary">Simpan Pengaturan</button></div></form></div></div></div>
<div class="col-xl-8"><div class="card manage-card"><div class="card-header border-bottom d-flex justify-content-between"><div><h5 class="fw-bold mb-1">Pendaftar Magang</h5><small class="text-muted">Periksa data sebelum mengaktifkan akun.</small></div><span class="badge bg-label-primary align-self-center">{{$participants->total()}}</span></div><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Peserta</th><th>Penempatan</th><th>Periode</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>@forelse($participants as $p)<tr><td><a href="{{route('internships.participants.attendance',$p)}}" class="fw-bold text-body">{{$p->name}}</a><small class="d-block text-muted">{{$p->student_number}} &middot; {{$p->institution}}</small><small>{{$p->email}}</small></td><td>{{$p->placement_unit}}<small class="d-block text-muted">{{$p->major}}</small></td><td><small>{{$p->start_date->format('d/m/Y')}}<br>s.d. {{$p->end_date->format('d/m/Y')}}</small></td><td><span class="badge bg-label-{{$p->status==='approved'?'success':($p->status==='rejected'?'danger':'warning')}}">{{ucfirst($p->status)}}</span>@if($p->status==='approved')<small class="d-block mt-1"><span class="text-muted">Predikat:</span> <strong>{{$p->final_grade?:app(\App\Services\InternshipCertificateService::class)->recommendedGrade($p)}}</strong>@if(!$p->final_grade)<span class="badge bg-label-info ms-1">Rekomendasi</span>@endif</small>@endif @if($p->review_note)<small class="d-block mt-1">{{$p->review_note}}</small>@endif</td><td class="text-end"><div class="d-flex justify-content-end flex-wrap gap-1"><a href="{{route('internships.participants.attendance',$p)}}" class="btn btn-sm btn-outline-info" title="Lihat rekap presensi"><i class="bx bx-calendar-check"></i></a><button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editParticipant{{$p->id}}" title="Edit data peserta"><i class="bx bx-edit-alt"></i></button><button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#resetPassword{{$p->id}}" title="Reset password"><i class="bx bx-key"></i></button><button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#gradeParticipant{{$p->id}}" title="Atur predikat"><i class="bx bx-award"></i></button>@if($p->status==='pending')<button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approve{{$p->id}}" title="Setujui pendaftaran"><i class="bx bx-check"></i></button><button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reject{{$p->id}}" title="Tolak pendaftaran"><i class="bx bx-x"></i></button>@endif</div></td></tr>
@empty<tr><td colspan="5" class="text-center py-5 text-muted">Belum ada pendaftar.</td></tr>@endforelse</tbody></table></div><div class="card-footer">{{$participants->links()}}</div></div></div></div>

<div class="card manage-card mt-4" id="sertifikat-magang">
 <div class="card-header border-bottom">
  <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
   <div><h5 class="fw-bold mb-1"><i class="bx bx-award text-warning me-1"></i>Alur Sertifikat Magang/PKL</h5><small class="text-muted">Selesaikan setiap tahap secara berurutan agar sertifikat yang diterima peserta sudah sah ditandatangani.</small></div>
   @if($latestTteRequest)<a href="{{route('electronic-signatures.show',$latestTteRequest)}}" class="btn btn-outline-primary align-self-lg-center"><i class="bx bx-file-find me-1"></i>Buka Bundel TTE Terakhir</a>@endif
  </div>
 </div>
 <div class="card-body border-bottom">
  <div class="row g-3">
   @php
    $generatedCount=$program->participants()->whereNotNull('certificate_generated_file_path')->count();
    $signedCount=$program->participants()->whereNotNull('certificate_file_path')->count();
    $sentCount=$program->participants()->whereNotNull('certificate_sent_at')->count();
    $readyToSend=max(0,$signedCount-$sentCount);
   @endphp
   @foreach([
    ['1','Template & Nomor',$program->certificate_template_path?'Siap':'Belum lengkap','bx-file','primary',$program->certificate_template_path],
    ['2','Predikat Akhir',$program->participants()->whereNotNull('final_grade')->count().' ditetapkan','bx-award','warning',$program->participants()->whereNotNull('final_grade')->exists()],
    ['3','Generate & TTE',$signedCount.' selesai dari '.$generatedCount,'bx-pen','info',$signedCount>0],
    ['4','Kirim Peserta',$sentCount.' sudah dikirim','bx-send','success',$sentCount>0]
   ] as $step)
   <div class="col-md-6 col-xl-3"><div class="border rounded-3 p-3 h-100 {{$step[5]?'bg-label-'.$step[4]:''}}"><div class="d-flex align-items-center gap-2"><span class="badge bg-{{$step[4]}} rounded-circle">{{$step[0]}}</span><i class="bx {{$step[3]}} fs-4 text-{{$step[4]}}"></i></div><strong class="d-block mt-3">{{$step[1]}}</strong><small class="text-muted">{{$step[2]}}</small></div></div>
   @endforeach
  </div>
 </div>
 <div class="card-body">
  <div class="row g-4">
   <div class="col-xl-7">
    <h6 class="fw-bold mb-3">1. Pengaturan Template dan Penandatangan</h6>
    <form method="POST" enctype="multipart/form-data" action="{{route('internships.certificates.settings',$program)}}" class="row g-3">@csrf @method('PUT')
     <div class="col-12"><label class="form-label fw-semibold">Template Sertifikat DOCX</label><input type="file" name="certificate_template" accept=".docx" class="form-control"><small class="text-muted">{{$program->certificate_template_path?'Template sudah tersimpan. Pilih file hanya jika ingin menggantinya.':'Unggah template DOCX maksimal 10 MB.'}}</small></div>
     <div class="col-md-7"><label class="form-label fw-semibold">Format nomor sertifikat *</label><input name="certificate_number_format" value="{{old('certificate_number_format',$program->certificate_number_format)}}" class="form-control" placeholder="001.{X}/MAGANG/BPSDM/2026" required><small class="text-muted">{X} otomatis menjadi nomor urut.</small></div>
     <div class="col-md-2"><label class="form-label fw-semibold">Mulai *</label><input type="number" min="1" name="certificate_start_sequence" value="{{old('certificate_start_sequence',$program->certificate_start_sequence?:1)}}" class="form-control" required></div>
     <div class="col-md-3"><label class="form-label fw-semibold">Tanggal terbit *</label><input type="date" name="certificate_issued_at" value="{{old('certificate_issued_at',$program->certificate_issued_at?->format('Y-m-d')?:today()->format('Y-m-d'))}}" class="form-control" required></div>
     <div class="col-md-6"><label class="form-label fw-semibold">Penandatangan akhir *</label><select name="certificate_signer_id" class="form-select" required><option value="">Pilih penandatangan</option>@foreach($tteUsers as $user)<option value="{{$user->id}}" @selected(old('certificate_signer_id',$program->certificate_signer_id)==$user->id)>{{$user->name}} &middot; {{$user->jabatan?:'Tanpa jabatan'}}</option>@endforeach</select>@if($tteUsers->isEmpty())<small class="text-danger">Belum ada akun penandatangan TTE.</small>@endif</div>
     <div class="col-md-6"><label class="form-label fw-semibold">Pemaraf sebelum penandatangan <span class="text-muted">(opsional)</span></label><select name="certificate_reviewer_ids[]" class="form-select" multiple size="3">@foreach($tteUsers as $user)<option value="{{$user->id}}" @selected(in_array($user->id,old('certificate_reviewer_ids',$program->certificate_reviewer_ids??[])))>{{$user->name}} &middot; {{$user->jabatan?:'Tanpa jabatan'}}</option>@endforeach</select><small class="text-muted">Gunakan Ctrl untuk memilih lebih dari satu.</small></div>
     <div class="col-12 d-flex flex-wrap gap-2"><button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan Pengaturan</button><a href="{{route('internships.certificates.template')}}" class="btn btn-outline-primary"><i class="bx bx-download me-1"></i>Unduh Template Contoh</a></div>
    </form>
   </div>
   <div class="col-xl-5">
    <div class="p-3 rounded border bg-light mb-3"><h6 class="fw-bold">Kode Template</h6><p class="small text-muted">Kode akan diganti otomatis tepat pada posisi yang Anda tentukan di Word.</p>@foreach(['nomor_sertifikat'=>'Nomor sertifikat','nama_lengkap'=>'Nama lengkap','kampus_sekolah'=>'Kampus/Sekolah','tanggal_mulai'=>'Tanggal mulai','tanggal_selesai'=>'Tanggal selesai','predikat'=>'Predikat akhir'] as $code=>$label)<div class="d-flex justify-content-between align-items-center border-top py-2"><span class="small">{{$label}}</span><code>$&#123;{{$code}}&#125;</code></div>@endforeach</div>
    <div class="alert alert-info mb-0"><strong><i class="bx bx-info-circle me-1"></i>Urutan proses</strong><ol class="small mb-0 mt-2 ps-3"><li>Pastikan predikat akhir peserta sudah benar.</li><li>Generate membuat PDF dan bundel TTE otomatis.</li><li>Penandatangan menyelesaikan TTE pada Pusat TTE.</li><li>Kembali ke sini lalu kirim hasil TTE ke peserta.</li></ol></div>
   </div>
  </div>
 </div>
 <div class="card-footer">
  <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
   <div><strong>2. Generate dan proses TTE</strong><small class="d-block text-muted">Hanya peserta yang masa magangnya sudah selesai yang diproses.</small></div>
   <div class="d-flex flex-wrap gap-2">
    <form method="POST" action="{{route('internships.certificates.generate',$program)}}">@csrf<button class="btn btn-info" @disabled(!$program->certificate_template_path||!$program->certificate_signer_id)><i class="bx bx-cog me-1"></i>Generate & Masukkan ke TTE</button></form>
    @if($latestTteRequest)<a href="{{route('electronic-signatures.show',$latestTteRequest)}}" class="btn btn-outline-info"><i class="bx bx-pen me-1"></i>Status TTE: {{ucfirst(str_replace('_',' ',$latestTteRequest->status))}}</a>@endif
    <form method="POST" action="{{route('internships.certificates.send-ready',$program)}}">@csrf<button class="btn btn-success" @disabled($readyToSend<1)><i class="bx bx-send me-1"></i>Kirim {{$readyToSend}} Sertifikat Siap</button></form>
   </div>
  </div>
 </div>
</div>

<div class="card manage-card mt-4" id="rekap-presensi">
 <div class="card-header border-bottom">
  <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
   <div><h5 class="fw-bold mb-1">Rekap Keseluruhan Presensi</h5><small class="text-muted">Perbandingan kehadiran seluruh peserta berdasarkan periode pilihan.</small></div>
   <a href="{{route('internships.recap.export',['program'=>$program,'from'=>$from,'to'=>$to])}}" class="btn btn-success align-self-lg-center"><i class="bx bx-spreadsheet me-1"></i>Unduh Excel</a>
  </div>
 </div>
 <div class="card-body border-bottom">
  <form method="GET" action="{{route('internships.show',$program)}}#rekap-presensi" class="row g-2 align-items-end">
   <div class="col-md-4"><label class="form-label fw-semibold">Dari tanggal</label><input type="date" name="from" value="{{$from}}" class="form-control" required></div>
   <div class="col-md-4"><label class="form-label fw-semibold">Sampai tanggal</label><input type="date" name="to" value="{{$to}}" class="form-control" required></div>
   <div class="col-md-4 d-flex flex-wrap gap-2"><button class="btn btn-primary flex-grow-1"><i class="bx bx-filter-alt me-1"></i>Tampilkan</button><a href="{{route('internships.show',['program'=>$program,'from'=>now()->startOfMonth()->toDateString(),'to'=>now()->endOfMonth()->toDateString()])}}#rekap-presensi" class="btn btn-outline-primary">Bulan Ini</a><a href="{{route('internships.show',['program'=>$program,'from'=>now()->startOfYear()->toDateString(),'to'=>now()->endOfYear()->toDateString()])}}#rekap-presensi" class="btn btn-outline-primary">Tahun Ini</a></div>
  </form>
  <div class="alert alert-info mt-3 mb-0"><i class="bx bx-calendar me-1"></i>Periode aktif: <strong>{{date('d/m/Y',strtotime($from))}}</strong> sampai <strong>{{date('d/m/Y',strtotime($to))}}</strong></div>
 </div>
 <div class="card-body border-bottom">
  <div class="row g-3">
   @foreach([['Peserta Aktif',$recapSummary['participants'],'bx-group','primary'],['Total Kehadiran',$recapSummary['present'],'bx-check-circle','success'],['Kejadian Terlambat',$recapSummary['late_days'],'bx-time','warning'],['Akumulasi Terlambat',$recapSummary['late_minutes'].' menit','bx-timer','danger'],['Izin / Sakit',$recapSummary['permission'].' / '.$recapSummary['sick'],'bx-file','info']] as $metric)
   <div class="col-6 col-xl"><div class="p-3 rounded border h-100"><i class="bx {{$metric[2]}} text-{{$metric[3]}} fs-3"></i><h5 class="fw-bold mt-2 mb-0">{{$metric[1]}}</h5><small class="text-muted">{{$metric[0]}}</small></div></div>
   @endforeach
  </div>
 </div>
 <div class="table-responsive">
  <table class="table align-middle mb-0">
   <thead><tr><th>Peserta</th><th class="text-center">Total Kehadiran</th><th class="text-center">Terlambat</th><th class="text-center">Total Menit</th><th class="text-center">Izin</th><th class="text-center">Sakit</th><th class="text-center">Belum Pulang</th><th></th></tr></thead>
   <tbody>
    @forelse($recapParticipants as $recap)
    <tr>
     <td><strong>{{$recap->name}}</strong><small class="d-block text-muted">{{$recap->student_number}} &middot; {{$recap->placement_unit}}</small></td>
     <td class="text-center"><span class="badge bg-label-success">{{$recap->recap_present_count}}</span></td>
     <td class="text-center"><span class="badge bg-label-{{$recap->recap_late_count?'warning':'secondary'}}">{{$recap->recap_late_count}}</span></td>
     <td class="text-center"><strong class="{{$recap->recap_late_minutes?'text-danger':''}}">{{(int)$recap->recap_late_minutes}} menit</strong></td>
     <td class="text-center">{{$recap->recap_permission_count}}</td><td class="text-center">{{$recap->recap_sick_count}}</td>
     <td class="text-center">@if($recap->recap_missing_checkout_count)<span class="badge bg-label-danger">{{$recap->recap_missing_checkout_count}}</span>@else<span class="text-muted">0</span>@endif</td>
     <td class="text-end"><a href="{{route('internships.participants.attendance',['participant'=>$recap,'month'=>substr($from,0,7)])}}" class="btn btn-sm btn-outline-primary" title="Buka detail"><i class="bx bx-show"></i></a></td>
    </tr>
    @empty<tr><td colspan="8" class="text-center text-muted py-5"><i class="bx bx-data fs-1 d-block mb-2"></i>Belum ada peserta aktif atau data presensi.</td></tr>@endforelse
   </tbody>
  </table>
 </div>
 <div class="card-footer">{{$recapParticipants->links()}}</div>
</div>

<div class="card manage-card mt-4">
 <div class="card-header border-bottom">
  <div class="d-flex justify-content-between align-items-center gap-3"><div><h5 class="fw-bold mb-1">Catatan Izin & Sakit</h5><small class="text-muted">Izin atau sakit langsung dihitung sebagai hadir administratif pada hari tersebut, tanpa proses persetujuan.</small></div><span class="badge bg-label-secondary">{{$absenceRequests->count()}} catatan</span></div>
 </div>
 <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Tanggal & Peserta</th><th>Jenis</th><th>Keterangan dan Bukti</th></tr></thead><tbody>
 @forelse($absenceRequests as $requestItem)
 <tr><td><strong>{{$requestItem->participant->name}}</strong><small class="d-block text-muted">{{$requestItem->attendance_date->translatedFormat('l, d F Y')}}</small></td><td><span class="badge bg-label-{{$requestItem->status==='sick'?'danger':'warning'}}"><i class="bx {{$requestItem->status==='sick'?'bx-plus-medical':'bx-time-five'}} me-1"></i>{{$requestItem->status==='sick'?'Sakit':'Izin'}}</span><small class="d-block text-muted mt-1">Hadir administratif</small></td><td><div style="white-space:pre-line">{{$requestItem->note}}</div>@if($requestItem->evidence_path)<a href="{{route('internships.absences.evidence',$requestItem)}}" class="btn btn-sm btn-outline-primary mt-2"><i class="bx bx-paperclip me-1"></i>Lihat Bukti</a>@else<small class="d-block text-muted mt-1">Tanpa lampiran</small>@endif</td></tr>
 @empty<tr><td colspan="3" class="text-center text-muted py-5"><i class="bx bx-note fs-1 d-block mb-2"></i>Belum ada catatan izin atau sakit.</td></tr>@endforelse
 </tbody></table></div>
</div>

@foreach($participants as $p)
<div class="modal fade intern-modal" id="editParticipant{{$p->id}}" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-lg">
  <form method="POST" action="{{route('internships.participants.update',$p)}}" class="modal-content">@csrf @method('PUT')
   <div class="modal-header"><div class="d-flex align-items-center gap-3"><span class="approval-icon bg-label-primary text-primary"><i class="bx bx-edit-alt"></i></span><div><h5 class="modal-title fw-bold mb-1">Edit Data Peserta</h5><small class="text-muted">Perbaiki identitas dan penempatan {{$p->name}}.</small></div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
   <div class="modal-body">
    <div class="alert alert-primary d-flex gap-2"><i class="bx bx-info-circle fs-5"></i><div>Jika NIS/NIM diubah, <strong>username login otomatis mengikuti NIS/NIM baru</strong>.</div></div>
    <div class="row g-3">
     <div class="col-md-6"><label class="form-label fw-semibold">Nama lengkap *</label><input name="name" value="{{$p->name}}" class="form-control" required></div>
     <div class="col-md-6"><label class="form-label fw-semibold">NIS/NIM (username) *</label><input name="student_number" value="{{$p->student_number}}" class="form-control" required></div>
     <div class="col-md-6"><label class="form-label fw-semibold">Sekolah/Kampus *</label><input name="institution" value="{{$p->institution}}" class="form-control" required></div>
     <div class="col-md-6"><label class="form-label fw-semibold">Jurusan *</label><input name="major" value="{{$p->major}}" class="form-control" required></div>
     <div class="col-md-6"><label class="form-label fw-semibold">Unit/Bidang penempatan *</label><input name="placement_unit" value="{{$p->placement_unit}}" class="form-control" required></div>
     <div class="col-md-6"><label class="form-label fw-semibold">Email *</label><input type="email" name="email" value="{{$p->email}}" class="form-control" required></div>
     <div class="col-md-6"><label class="form-label fw-semibold">Tanggal mulai *</label><input type="date" name="start_date" value="{{$p->start_date->format('Y-m-d')}}" class="form-control" required></div>
     <div class="col-md-6"><label class="form-label fw-semibold">Tanggal selesai *</label><input type="date" name="end_date" value="{{$p->end_date->format('Y-m-d')}}" class="form-control" required></div>
    </div>
   </div>
   <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan Perubahan</button></div>
  </form>
 </div>
</div>
<div class="modal fade intern-modal" id="gradeParticipant{{$p->id}}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><form method="POST" action="{{route('internships.participants.grade',$p)}}" class="modal-content">@csrf @method('PUT')
 <div class="modal-header"><div><h5 class="modal-title fw-bold">Tetapkan Predikat Akhir</h5><small class="text-muted">{{$p->name}} &middot; {{$p->student_number}}</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
 <div class="modal-body"><div class="alert alert-info"><small class="d-block text-muted">Rekomendasi sistem berdasarkan jumlah hari kerja, kehadiran, dan keterlambatan</small><strong>{{app(\App\Services\InternshipCertificateService::class)->recommendedGrade($p)}}</strong></div><label class="form-label fw-semibold">Keputusan predikat akhir *</label><select name="final_grade" class="form-select" required>@foreach(['Sangat Baik','Baik','Cukup','Kurang'] as $grade)<option value="{{$grade}}" @selected(($p->final_grade?:app(\App\Services\InternshipCertificateService::class)->recommendedGrade($p))===$grade)>{{$grade}}</option>@endforeach</select><small class="text-muted">Admin dapat menyesuaikan rekomendasi berdasarkan hasil penilaian pembimbing.</small></div>
 <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-warning"><i class="bx bx-save me-1"></i>Simpan Predikat</button></div>
</form></div></div>
<div class="modal fade intern-modal" id="resetPassword{{$p->id}}" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered">
  <form method="POST" action="{{route('internships.participants.password',$p)}}" class="modal-content">@csrf @method('PUT')
   <div class="modal-header"><div class="d-flex align-items-center gap-3"><span class="approval-icon bg-label-warning text-warning"><i class="bx bx-lock-open-alt"></i></span><div><h5 class="modal-title fw-bold mb-1">Reset Password</h5><small class="text-muted">{{$p->name}} &middot; username {{$p->student_number}}</small></div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
   <div class="modal-body">
    <div class="alert alert-warning mb-3"><i class="bx bx-error-circle me-1"></i>Password lama tidak dapat dilihat. Buat password baru minimal 8 karakter, lalu sampaikan secara aman kepada peserta.</div>
    <div class="mb-3"><label class="form-label fw-semibold">Password baru *</label><input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password" required></div>
    <div><label class="form-label fw-semibold">Ulangi password baru *</label><input type="password" name="password_confirmation" class="form-control" minlength="8" autocomplete="new-password" required></div>
   </div>
   <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-warning"><i class="bx bx-key me-1"></i>Reset Password</button></div>
  </form>
 </div>
</div>
@endforeach

@foreach($participants as $p)
@if($p->status==='pending')
<div class="modal fade intern-modal" id="approve{{$p->id}}" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered">
  <form method="POST" action="{{route('internships.participants.approve',$p)}}" class="modal-content">@csrf @method('PUT')
   <div class="modal-header"><div class="d-flex align-items-center gap-3"><span class="approval-icon"><i class="bx bx-user-check"></i></span><div><h5 class="modal-title fw-bold mb-1">Setujui Pendaftar</h5><small class="text-muted">Periksa kembali identitas sebelum mengaktifkan akun.</small></div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
   <div class="modal-body">
    <div class="approval-person mb-3"><div class="fw-bold fs-5">{{$p->name}}</div><div class="text-muted small">{{$p->student_number}} &middot; {{$p->institution}}</div><hr class="my-2"><div class="row g-2 small"><div class="col-6"><span class="text-muted d-block">Jurusan</span><strong>{{$p->major}}</strong></div><div class="col-6"><span class="text-muted d-block">Penempatan</span><strong>{{$p->placement_unit}}</strong></div><div class="col-12"><span class="text-muted d-block mt-1">Periode</span><strong>{{$p->start_date->format('d/m/Y')}} s.d. {{$p->end_date->format('d/m/Y')}}</strong></div></div></div>
    <div class="approval-impact mb-3"><i class="bx bx-info-circle me-1"></i><strong>Dampak persetujuan:</strong> akun dengan username <strong>{{$p->student_number}}</strong> langsung aktif dan peserta dapat masuk ke dashboard magang.</div>
    <label class="form-label fw-semibold">Catatan persetujuan <span class="text-muted fw-normal">(opsional)</span></label><textarea name="review_note" rows="3" class="form-control" placeholder="Tambahkan informasi untuk peserta jika diperlukan"></textarea>
   </div>
   <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Periksa Kembali</button><button class="btn btn-success"><i class="bx bx-check-circle me-1"></i>Setujui & Aktifkan Akun</button></div>
  </form>
 </div>
</div>
<div class="modal fade intern-modal" id="reject{{$p->id}}" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form method="POST" action="{{route('internships.participants.reject',$p)}}" class="modal-content">@csrf @method('PUT')<div class="modal-header"><div><h5 class="modal-title fw-bold">Tolak Pendaftaran</h5><small class="text-muted">{{$p->name}} &middot; {{$p->student_number}}</small></div><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label fw-semibold">Alasan penolakan *</label><textarea name="review_note" rows="4" class="form-control" placeholder="Jelaskan alasan agar dapat dipahami peserta" required></textarea></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger">Tolak Pendaftaran</button></div></form></div></div>
@endif
@endforeach
@endsection
@push('js')<script>document.getElementById('copyInternLink').addEventListener('click',async function(){await navigator.clipboard.writeText(this.dataset.url);this.innerHTML='<i class="bx bx-check me-1"></i>Link Tersalin';});</script>@endpush