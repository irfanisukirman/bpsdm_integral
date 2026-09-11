@extends('layouts.master')
@section('title','Persetujuan Peminjaman Aset')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
 <div class="d-flex flex-wrap justify-content-between gap-3 mb-4"><div><h4 class="fw-bold mb-1">Persetujuan Peminjaman Aset</h4><p class="text-muted mb-0">Verifikasi surat, jadwal, bidang pemohon, dan ketersediaan aset.</p></div><a href="{{route('assets.dashboard')}}" class="btn btn-outline-primary">Dashboard Aset</a></div>
 <div class="row g-3 mb-4">@foreach(['pending'=>['Menunggu','warning'],'revision'=>['Perlu Perbaikan','info'],'approved'=>['Disetujui','success'],'rejected'=>['Ditolak','danger']] as $key=>$meta)<div class="col-6 col-xl-3"><a href="{{route('asset-loans.index',['status'=>$key])}}" class="card border-0 shadow-sm h-100 text-decoration-none"><div class="card-body"><small class="text-muted">{{$meta[0]}}</small><h3 class="fw-bold text-{{$meta[1]}} mb-0">{{$counts[$key]??0}}</h3></div></a></div>@endforeach</div>
 <div class="card border-0 shadow-sm"><div class="card-header border-bottom d-flex flex-wrap justify-content-between gap-2"><h5 class="fw-bold mb-0">Daftar Pengajuan</h5><div class="btn-group btn-group-sm">@foreach(['pending'=>'Menunggu','revision'=>'Perbaikan','approved'=>'Disetujui','rejected'=>'Ditolak','all'=>'Semua'] as $key=>$label)<a href="{{route('asset-loans.index',['status'=>$key])}}" class="btn btn-{{$status===$key?'primary':'outline-secondary'}}">{{$label}}</a>@endforeach</div></div>
 <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Kegiatan</th><th>Bidang / Pemohon</th><th>Waktu</th><th>Aset</th><th>Surat</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
 @forelse($requests as $loan)@php $source=$loan->requestable;$training=$source instanceof \App\Models\Schedule;$parent=$training?$source?->training:$source?->agenda;$start=$training?\Carbon\Carbon::parse($source->date.' '.$source->start_time):$source?->starts_at;$end=$training?\Carbon\Carbon::parse($source->date.' '.$source->end_time):$source?->ends_at;$assetNames=collect($loan->asset_ids)->map(fn($id)=>$assets[$id]->name??'Aset #'.$id);$color=['pending'=>'warning','approved'=>'success','revision'=>'info','rejected'=>'danger'][$loan->status]??'secondary';@endphp
 <tr id="loan-{{$loan->id}}"><td><span class="badge bg-label-{{$training?'primary':'success'}}">{{$training?'Pelatihan':'Nonpelatihan'}}</span><strong class="d-block mt-1">{{$training?($source?->activity):($source?->title??$parent?->name)}}</strong><small class="text-muted">{{$parent?->nama_pelatihan??$parent?->name??'-'}}</small></td>
 <td><strong>{{$parent?->bidang??$loan->submitter?->bidang??'-'}}</strong><small class="d-block text-muted">{{$loan->submitter?->name??'-'}} / {{$loan->contact_person??'-'}}</small></td>
 <td class="text-nowrap"><strong>{{$start?->translatedFormat('d M Y')}}</strong><small class="d-block text-muted">{{$start?->format('H:i')}}-{{$end?->format('H:i')}}</small></td>
 <td>@foreach($assetNames as $name)<span class="badge bg-label-warning d-block mb-1 text-start">{{$name}}</span>@endforeach</td>
 <td><a href="{{route('asset-loans.document',$loan)}}" target="_blank" class="btn btn-sm btn-outline-danger"><i class="bx bxs-file-pdf me-1"></i>Lihat PDF</a></td>
 <td><span class="badge bg-label-{{$color}}">{{['pending'=>'Menunggu','approved'=>'Disetujui','revision'=>'Perlu Perbaikan','rejected'=>'Ditolak'][$loan->status]??$loan->status}}</span>@if($loan->review_note)<small class="d-block text-muted mt-1">{{$loan->review_note}}</small>@endif</td>
 <td><button type="button" class="btn btn-sm btn-primary review-button" data-bs-toggle="modal" data-bs-target="#reviewLoanModal"
   data-action="{{route('asset-loans.review',$loan)}}" data-document="{{route('asset-loans.document',$loan)}}"
   data-kind="{{$training?'Pelatihan':'Nonpelatihan'}}" data-activity="{{e($training?($source?->activity):($source?->title??$parent?->name))}}"
   data-parent="{{e($parent?->nama_pelatihan??$parent?->name??'-')}}" data-field="{{e($parent?->bidang??$loan->submitter?->bidang??'-')}}"
   data-applicant="{{e($loan->submitter?->name??'-')}}" data-contact="{{e($loan->contact_person??'-')}}"
   data-time="{{$start?->translatedFormat('d M Y, H:i')}} - {{$end?->format('H:i')}}"
   data-assets="{{e($assetNames->join(', '))}}" data-purpose="{{e($loan->purpose??'-')}}" data-attendees="{{$loan->attendee_count??'-'}}"
   data-note="{{e($loan->review_note??'')}}">Periksa</button></td></tr>
 @empty<tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada pengajuan pada status ini.</td></tr>@endforelse
 </tbody></table></div>@if($requests->hasPages())<div class="card-footer">{{$requests->links()}}</div>@endif</div>
</div>

<div class="modal fade" id="reviewLoanModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-lg modal-dialog-centered">
  <form method="POST" action="" id="reviewLoanForm" class="modal-content review-modal">
   @csrf @method('PUT')
   <div class="modal-header border-bottom"><div><span id="reviewKind" class="badge bg-label-primary mb-2"></span><h5 class="modal-title fw-bold">Pemeriksaan Peminjaman Aset</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
   <div class="modal-body p-4">
    <div class="alert alert-info border-0"><i class="bx bx-info-circle me-2"></i>Pastikan surat, aset, dan waktu pemakaian sudah sesuai sebelum memberikan keputusan.</div>
    <div class="card bg-light border mb-4"><div class="card-body"><h5 id="reviewActivity" class="fw-bold mb-1"></h5><p id="reviewParent" class="text-muted mb-3"></p>
     <div class="row g-3">
      <div class="col-md-6"><small class="text-muted d-block">Bidang Pemohon</small><strong id="reviewField"></strong></div>
      <div class="col-md-6"><small class="text-muted d-block">Pemohon / Kontak</small><strong id="reviewApplicant"></strong><span id="reviewContact"></span></div>
      <div class="col-md-6"><small class="text-muted d-block">Waktu Pemakaian</small><strong id="reviewTime"></strong></div>
      <div class="col-md-6"><small class="text-muted d-block">Jumlah Pengguna</small><strong id="reviewAttendees"></strong></div>
      <div class="col-12"><small class="text-muted d-block">Aset yang Diajukan</small><strong id="reviewAssets"></strong></div>
      <div class="col-12"><small class="text-muted d-block">Keperluan Peminjaman</small><div id="reviewPurpose"></div></div>
     </div>
    </div></div>
    <a id="reviewDocument" href="#" target="_blank" class="btn btn-outline-danger mb-4"><i class="bx bxs-file-pdf me-1"></i>Buka Surat Peminjaman PDF</a>
    <div class="row g-3"><div class="col-md-5"><label class="form-label fw-bold">Keputusan</label><select name="decision" id="reviewDecision" class="form-select" required><option value="approved">Setujui Peminjaman</option><option value="revision">Minta Perbaikan</option><option value="rejected">Tolak Pengajuan</option></select></div>
    <div class="col-md-7"><label class="form-label fw-bold">Catatan Pengelola <span id="noteRequired" class="text-danger d-none">*</span></label><textarea name="review_note" id="reviewNote" rows="3" class="form-control" placeholder="Berikan catatan yang jelas untuk pemohon"></textarea><small class="text-muted">Wajib jika meminta perbaikan atau menolak.</small></div></div>
   </div>
   <div class="modal-footer border-top"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary"><i class="bx bx-check-shield me-1"></i>Simpan Keputusan</button></div>
  </form>
 </div>
</div>
@endsection

@push('css')
<style>.review-modal{background:#fff!important;opacity:1!important}.modal-backdrop.show{opacity:.55}.review-modal .modal-body{background:#fff}</style>
@endpush
@push('js')
<script>
document.addEventListener('DOMContentLoaded',function(){
 const modal=document.getElementById('reviewLoanModal'),form=document.getElementById('reviewLoanForm'),decision=document.getElementById('reviewDecision'),note=document.getElementById('reviewNote');
 modal.addEventListener('show.bs.modal',function(event){const d=event.relatedTarget.dataset;form.action=d.action;
  document.getElementById('reviewKind').textContent=d.kind;document.getElementById('reviewActivity').textContent=d.activity;
  document.getElementById('reviewParent').textContent=d.parent;document.getElementById('reviewField').textContent=d.field;
  document.getElementById('reviewApplicant').textContent=d.applicant;document.getElementById('reviewContact').textContent=' / '+d.contact;
  document.getElementById('reviewTime').textContent=d.time;document.getElementById('reviewAssets').textContent=d.assets;
  document.getElementById('reviewPurpose').textContent=d.purpose;document.getElementById('reviewAttendees').textContent=d.attendees;
  document.getElementById('reviewDocument').href=d.document;decision.value='approved';note.value=d.note||'';toggleRequired();
 });
 function toggleRequired(){const required=decision.value!=='approved';note.required=required;document.getElementById('noteRequired').classList.toggle('d-none',!required);}
 decision.addEventListener('change',toggleRequired);
});
</script>
@endpush
