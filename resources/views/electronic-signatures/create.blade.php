@extends('layouts.master')
@section('title','Ajukan Tanda Tangan Elektronik')
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>.select2-container--bootstrap-5 .select2-selection{min-height:38px}</style>
@endpush
@section('content')
@php($pageMeta=[
 'training_certificates'=>['title'=>'Ajukan Sertifikat Pelatihan Integral','description'=>'Pilih pelatihan; seluruh sertifikat hasil generate akan masuk otomatis.','back'=>'electronic-signatures.integral','store'=>'electronic-signatures.integral.store'],
 'jct_certificates'=>['title'=>'Ajukan Sertifikat Jabar Corpu Talent','description'=>'Pilih kegiatan yang dikirim otomatis oleh aplikasi JCT.','back'=>'electronic-signatures.jct','store'=>'electronic-signatures.jct.store'],
 'other_documents'=>['title'=>'Ajukan TTE Dokumen Lain','description'=>'Tulis nama kegiatan lalu unggah beberapa PDF atau satu ZIP.','back'=>'electronic-signatures.documents','store'=>'electronic-signatures.documents.store'],
][$presetSource]??null)
<div class="container-xxl container-p-y">
 <div class="mb-4"><a href="{{route($pageMeta['back']??'electronic-signatures.index')}}" class="small"><i class="bx bx-arrow-back"></i> Kembali</a><h4 class="fw-bold mt-2 mb-1">{{$pageMeta['title']??'Ajukan Tanda Tangan Elektronik'}}</h4><p class="text-muted mb-0">{{$pageMeta['description']??'Pilih sumber dokumen yang akan diproses.'}}</p></div>
 @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
 <form method="POST" enctype="multipart/form-data" action="{{route($pageMeta['store'] ?? 'electronic-signatures.store')}}" class="card border-0 shadow-sm">@csrf
  <div class="card-body p-4">
   <h6 class="fw-bold mb-3">1. Data Dokumen</h6>
   @if($presetSource)<input class="source-type d-none" type="radio" name="source_type" value="{{$presetSource}}" checked><div class="alert alert-primary mb-4"><strong>{{['training_certificates'=>'TTE Sertifikat Pelatihan Integral','jct_certificates'=>'TTE Sertifikat dari JCT','other_documents'=>'TTE Dokumen Lain'][$presetSource]}}</strong><br><small>Sumber dokumen sudah ditentukan untuk halaman ini.</small></div>@else
   <div class="row g-3 mb-4">
    <div class="col-lg-4"><label class="border rounded p-3 d-flex gap-3 h-100"><input class="form-check-input source-type" type="radio" name="source_type" value="training_certificates" @checked(old('source_type','training_certificates')==='training_certificates')><span><strong>Sertifikat Pelatihan Integral</strong><small class="d-block text-muted">Dari data pelatihan dan hasil generate Integral.</small></span></label></div>
    <div class="col-lg-4"><label class="border rounded p-3 d-flex gap-3 h-100"><input class="form-check-input source-type" type="radio" name="source_type" value="jct_certificates" @checked(old('source_type')==='jct_certificates')><span><strong>Sertifikat dari JCT</strong><small class="d-block text-muted">Diambil dan dikirim kembali melalui API JCT.</small></span></label></div>
    <div class="col-lg-4"><label class="border rounded p-3 d-flex gap-3 h-100"><input class="form-check-input source-type" type="radio" name="source_type" value="other_documents" @checked(old('source_type')==='other_documents')><span><strong>Dokumen Lainnya</strong><small class="d-block text-muted">Unggah satu ZIP berisi kumpulan PDF.</small></span></label></div>
   </div>@endif
   @if(!$presetSource || $presetSource === 'training_certificates')
   <div id="trainingSource" class="rounded bg-light p-3 mb-4"><label class="form-label fw-semibold">Pilih Pelatihan *</label><select name="training_id" id="trainingId" class="form-select"><option value="">Pilih pelatihan yang sertifikatnya sudah di-generate</option>@foreach($trainings as $training)<option value="{{$training->id}}" @selected(old('training_id')==$training->id)>{{$training->nama_pelatihan}} &middot; {{$training->generated_certificates_count}} sertifikat &middot; {{$training->bidang}}</option>@endforeach</select>@if($trainings->isEmpty())<div class="alert alert-warning mt-3 mb-0">Belum ada pelatihan dengan PDF sertifikat hasil generate.</div>@else<small class="text-muted d-block mt-2">Nama pengajuan dan file peserta diisi otomatis berdasarkan pelatihan.</small>@endif</div>
   @endif
   @if(!$presetSource || $presetSource === 'jct_certificates')
   <div id="jctSource" class="rounded bg-light p-3 mb-4"><div class="d-flex justify-content-between gap-2 mb-3"><div><strong>Sertifikat Jabar Corpu Talent</strong><small class="d-block text-muted">Nama pelatihan dan daftar sertifikat diambil otomatis dari JCT.</small></div><span class="badge bg-label-{{$jctError?'danger':'info'}}">{{$jctError?'Koneksi bermasalah':'Terhubung ke JCT'}}</span></div>@if($jctError)<div class="alert alert-danger mb-3">{{$jctError}}</div>@endif<div class="row g-3"><div class="col-12"><label class="form-label">Pilih kegiatan dari JCT *</label><select id="jctOption" class="form-select jct-required"><option value="">Pilih data pelatihan Jabar Corpu Talent</option>@foreach($jctOptions as $option)<option value="{{$option['template_id']}}" data-activity="{{$option['activity_id']}}" data-name="{{$option['name']}}" data-users='@json($option['user_ids'])'>{{$option['name']}} &middot; {{count($option['user_ids'])}} sertifikat</option>@endforeach</select><small class="text-muted d-block mt-1">Ketik untuk mencari kegiatan berdasarkan nama pelatihan.</small>
    <div class="d-flex flex-wrap align-items-center gap-2 mt-3"><button type="button" id="jctCheckDownload" class="btn btn-outline-info btn-sm" disabled><i class="bx bx-download me-1"></i>Cek &amp; Download Sertifikat</button><span id="jctDownloadSummary" class="small text-muted">Pilih kegiatan terlebih dahulu.</span></div>
    </div><input type="hidden" name="jct_training_name" id="jctTrainingName"><input type="hidden" name="jct_activity_id" id="jctActivityId"><input type="hidden" name="jct_template_id" id="jctTemplateId"><textarea class="d-none" name="jct_user_ids" id="jctUserIds"></textarea>@if(!$jctError && $jctOptions->isEmpty())<div class="col-12"><div class="alert alert-warning mb-0">JCT belum mengirim daftar kegiatan peserta sertifikat.</div></div>@endif
    @if($jctDbConfigured)<small class="text-muted d-block mt-2">Daftar peserta diambil dari basis data JCT; status TTE akan disinkronkan kembali ke JCT.</small>
    @endif
    </div></div>
   @endif
   @if(!$presetSource || $presetSource === 'other_documents')
   <div id="uploadSource" class="rounded bg-light p-3 mb-4">
    <div class="row g-3">
     <div class="col-md-8"><label class="form-label">Nama kegiatan / dokumen *</label><input name="title" id="documentTitle" value="{{old('title')}}" class="form-control" placeholder="Contoh: Surat Keputusan Tim Penyelenggara"></div>
     @if(Auth::user()->role==='superadmin')<div class="col-md-4"><label class="form-label">Bidang</label><input name="bidang" value="{{old('bidang')}}" class="form-control"></div>@endif
     <div class="col-12">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2"><div><label class="form-label fw-semibold mb-0">File dokumen PDF</label><small class="text-muted d-block">Tambahkan seluruh dokumen yang akan diproses dalam satu pengajuan.</small></div><button type="button" id="addDocument" class="btn btn-sm btn-outline-primary"><i class="bx bx-plus me-1"></i>Tambah Dokumen</button></div>
      <div id="documentInputs"><div class="input-group document-input mb-2"><span class="input-group-text"><i class="bx bxs-file-pdf text-danger"></i></span><input type="file" name="documents[]" accept="application/pdf,.pdf" class="form-control"><button type="button" class="btn btn-outline-danger remove-document" title="Hapus"><i class="bx bx-trash"></i></button></div></div>
      <small class="text-muted">Maksimal 100 file, masing-masing maksimal 20 MB.</small>
     </div>
     <div class="col-12 text-center text-muted small">— ATAU —</div>
     <div class="col-12"><label class="form-label">Upload ZIP kumpulan PDF</label><input type="file" name="document_zip" id="documentZip" accept="application/zip,.zip" class="form-control"><small class="text-muted">Maksimal 500 PDF dan total hasil ekstraksi 500 MB. Folder di dalam ZIP diperbolehkan.</small></div>
    </div>
   </div>
   @endif
   <div class="alert alert-info py-2 mb-4"><i class="bx bx-scan me-1"></i>Ukuran dan orientasi setiap PDF dideteksi otomatis. Sistem mempertahankan halaman asli lalu menyesuaikan posisi QR dan catatan legal.</div>
   <div class="mb-4"><label class="form-label">Keterangan tambahan</label><textarea name="description" class="form-control" rows="2">{{old('description')}}</textarea></div>
   <hr><h6 class="fw-bold mb-3">2. Urutan Persetujuan</h6><div id="reviewers"></div><button type="button" id="addReviewer" class="btn btn-sm btn-outline-secondary mb-4"><i class="bx bx-plus"></i> Tambah Pemaraf</button>
   <div><label class="form-label fw-semibold">Penandatangan Akhir *</label><select name="signer_id" class="form-select" required><option value="">Pilih akun penandatangan</option>@foreach($users as $user)<option value="{{$user->id}}" @selected(old('signer_id')==$user->id)>{{$user->name}} &middot; {{$user->nip_nik}} &middot; {{$user->jabatan?:'-'}}</option>@endforeach</select>@if($users->isEmpty())<small class="text-danger">Belum ada akun penandatangan. Superadmin perlu membuatnya terlebih dahulu.</small>@endif</div>
   <div class="alert alert-info mt-4 mb-0"><i class="bx bx-shield me-1"></i>Setelah tanda tangan terakhir selesai, sertifikat final otomatis masuk ke akun peserta dan notifikasi sertifikat terbit akan muncul.</div>
  </div><div class="card-footer text-end"><button class="btn btn-primary" @disabled($users->isEmpty())><i class="bx bx-send me-1"></i>Buat Antrean Tanda Tangan</button></div>
</form>
</div>
<div class="modal fade" id="modalJctDownload" tabindex="-1" aria-labelledby="modalJctDownloadLabel" aria-hidden="true">
 <div class="modal-dialog modal-lg">
  <div class="modal-content">
   <div class="modal-header bg-primary text-white">
    <h5 class="modal-title" id="modalJctDownloadLabel">Download Sertifikat JCT</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
   </div>
   <div class="modal-body">
    <div id="jctDownloadAlert"></div>
    <div class="row">
     <div class="col-md-4"><div class="text-center p-4 rounded bg-light"><i class="bx bxs-file-pdf text-danger" style="font-size:4rem"></i><small class="d-block text-muted mt-2">Sertifikat JCT</small></div></div>
     <div class="col-md-8">
      <div class="mb-2"><label class="fw-semibold mb-0">Jumlah Sertifikat : <span class="jctTotalSertif text-primary">0</span></label></div>
      <div class="mb-2"><label class="fw-semibold mb-0">Sudah didownload : <span class="jctHaveDownload text-success">0</span></label></div>
      <div class="mb-2"><label class="fw-semibold mb-0">Belum didownload : <span class="jctNotHaveDownload text-danger">0</span></label></div>
      <div id="jctPerVersion" class="mb-3"></div>
      <label class="fw-semibold">Log :</label>
      <span id="jctLoadingDownload" class="text-danger" hidden>( <i class="bx bx-hourglass bx-spin me-1"></i> Loading )</span>
      <select id="jctLogDownload" size="6" class="form-select" multiple style="height:180px;width:100%"></select>
      <small class="text-muted d-block mt-2">Integral akan mendownload otomatis sertifikat dari JCT lalu menyimpannya untuk diproses TTE.</small>
     </div>
    </div>
    <input type="hidden" id="jctModalTemplateId">
    <input type="hidden" id="jctModalActivityId">
    <input type="hidden" id="jctModalName">
    <input type="hidden" id="jctModalPemaraf">
   </div>
   <div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="closeJctDownload">Batal</button>
    <button type="button" id="jctBtnDownload" class="btn btn-primary">Download</button>
   </div>
  </div>
 </div>
</div>
@endsection
@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
var jctSelect=document.getElementById('jctOption');
if(jctSelect&&window.jQuery){jQuery(jctSelect).select2({theme:'bootstrap-5',width:'100%',placeholder:'Pilih data pelatihan Jabar Corpu Talent',allowClear:true,dropdownParent:jctSelect.closest('#jctSource')});}
function applyJctSelection(){const el=document.getElementById('jctOption');const option=el?.selectedOptions[0];document.getElementById('jctActivityId').value=option?.dataset.activity||'';document.getElementById('jctTemplateId').value=option?.value||'';document.getElementById('jctTrainingName').value=option?.dataset.name||'';document.getElementById('jctUserIds').value=JSON.parse(option?.dataset.users||'[]').join('\n');forceJctDownloadButtonState();}
    jQuery('#jctOption').on('change',applyJctSelection);
    const jctModalEl=document.getElementById('modalJctDownload');
    let jctSertifData=[];
    function jctModal(){return window.bootstrap?bootstrap.Modal.getOrCreateInstance(jctModalEl):null;}
    function forceJctDownloadButtonState(){const el=document.getElementById('jctOption');const button=document.getElementById('jctCheckDownload');if(!button)return;const selected=el?.value&&el?.selectedOptions[0]?.dataset?.activity;button.disabled=!selected;const summary=document.getElementById('jctDownloadSummary');if(!selected){summary.textContent='Pilih kegiatan terlebih dahulu.';}else if(summary.textContent==='Pilih kegiatan terlebih dahulu.'){summary.textContent=el.selectedOptions[0].dataset.name+'\u00a0·\u00a0cek status & download sertifikat sebelum mengajukan.';}}
    document.getElementById('jctCheckDownload')?.addEventListener('click',function(){
     const option=document.getElementById('jctOption')?.selectedOptions[0];
     if(!option?.value||!option?.dataset?.activity){forceJctDownloadButtonState();return;}
     document.getElementById('jctModalTemplateId').value=option.value;
     document.getElementById('jctModalActivityId').value=option.dataset.activity;
     document.getElementById('jctModalName').value=option.dataset.name||'';
     document.getElementById('jctModalPemaraf').value='';
     document.getElementById('jctLogDownload').innerHTML='';
     document.getElementById('jctDownloadAlert').innerHTML='';
     $('.jctTotalSertif').text('0');$('.jctHaveDownload').text('0');$('.jctNotHaveDownload').text('0');
     document.getElementById('jctPerVersion').innerHTML='';
     document.getElementById('jctLoadingDownload').hidden=false;
     jctModal()?.show();
     fetch('{{route('electronic-signatures.jct.download-info')}}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content},body:JSON.stringify({id:option.value,activity:option.dataset.activity})})
      .then(r=>r.json().then(data=>({ok:r.ok,data}))).then(({ok,data})=>{
       if(!ok)throw new Error(data.message||'Gagal mengambil info download.');
       jctSertifData=Array.isArray(data.sertif)?data.sertif:[];
       document.getElementById('jctModalPemaraf').value=data.pemaraf||'';
       $('.jctTotalSertif').text(data.total_sertif);$('.jctHaveDownload').text(data.haveDownload);$('.jctNotHaveDownload').text(data.notHaveDownload);
       const per=document.getElementById('jctPerVersion');per.innerHTML='';
       (data.perVersion||[]).forEach(v=>{const b=document.createElement('span');b.className='badge bg-label-info me-2 mb-1';b.textContent='v'+v.versi+': '+v.haveDownload+'/'+v.total+' didownload';per.appendChild(b);});
      }).catch(err=>{document.getElementById('jctDownloadAlert').innerHTML='<div class="alert alert-danger mb-2">'+err.message+'</div>';})
      .finally(()=>{document.getElementById('jctLoadingDownload').hidden=true;});}
    );
    document.getElementById('jctBtnDownload')?.addEventListener('click',function(){
     const templateId=document.getElementById('jctModalTemplateId').value;
     const activityId=document.getElementById('jctModalActivityId').value;
     const pemaraf=document.getElementById('jctModalPemaraf').value;
     const button=this;
     if(!templateId||!activityId)return;
     button.disabled=true;document.getElementById('closeJctDownload').disabled=true;document.getElementById('jctLoadingDownload').hidden=false;
     const log=document.getElementById('jctLogDownload');log.innerHTML='';
     document.getElementById('jctDownloadAlert').innerHTML='';
     let berhasil=0,gagal=0,completed=0;
     const total=jctSertifData.length;
     function finish(){document.getElementById('jctLoadingDownload').hidden=true;document.getElementById('closeJctDownload').disabled=false;button.disabled=false;
      document.getElementById('jctDownloadAlert').innerHTML='<div class="alert alert-success mb-0">Download sertifikat selesai. '+berhasil+' berhasil, '+gagal+' gagal.</div>';
      $('.jctHaveDownload').text(berhasil);$('.jctNotHaveDownload').text(gagal);
     }
     if(total===0){finish();return;}
     jctSertifData.forEach((item,index)=>{
      const userId=item.user_id||'';
      fetch('{{route('electronic-signatures.jct.download')}}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content},body:JSON.stringify({id_template:templateId,id_activity:activityId,user_id:userId,row:index+1,pemaraf:pemaraf})})
       .then(r=>r.json().then(data=>({ok:r.ok,data}))).then(({data})=>{
        const opt=document.createElement('option');opt.textContent=data.msg||((index+1)+'. Hasil USER: '+userId);log.appendChild(opt);log.scrollTop=log.scrollHeight;
        data.download_status?berhasil++:gagal++;
       })
       .catch(err=>{const opt=document.createElement('option');opt.textContent=(index+1)+'. Gagal (Request Error). [USER: '+userId+']';log.appendChild(opt);gagal++;})
       .finally(()=>{completed++;if(completed===total)finish();});
     });
    });
</script>
<script>
const users=@json($users->map(fn($user)=>['id'=>$user->id,'label'=>$user->name.' &middot; '.$user->nip_nik]));const box=document.getElementById('reviewers');document.getElementById('addReviewer').onclick=()=>{const row=document.createElement('div');row.className='input-group mb-2';row.innerHTML='<span class="input-group-text">Pemaraf</span><select name="reviewer_ids[]" class="form-select"><option value="">Pilih pemaraf</option>'+users.map(user=>`<option value="${user.id}">${user.label}</option>`).join('')+'</select><button type="button" class="btn btn-outline-danger"><i class="bx bx-trash"></i></button>';row.querySelector('button').onclick=()=>row.remove();box.appendChild(row)};
const documentInputs=document.getElementById('documentInputs');document.getElementById('addDocument')?.addEventListener('click',()=>{const row=document.createElement('div');row.className='input-group document-input mb-2';row.innerHTML='<span class="input-group-text"><i class="bx bxs-file-pdf text-danger"></i></span><input type="file" name="documents[]" accept="application/pdf,.pdf" class="form-control" required><button type="button" class="btn btn-outline-danger remove-document" title="Hapus"><i class="bx bx-trash"></i></button>';documentInputs.appendChild(row);});documentInputs?.addEventListener('click',event=>{const button=event.target.closest('.remove-document');if(!button)return;const rows=documentInputs.querySelectorAll('.document-input');if(rows.length===1){rows[0].querySelector('input').value='';return;}button.closest('.document-input').remove();});
function toggleSource(){const selected=document.querySelector('[name="source_type"]:checked');if(!selected)return;const source=selected.value;const training=source==='training_certificates',jct=source==='jct_certificates',other=source==='other_documents';document.getElementById('trainingSource')?.classList.toggle('d-none',!training);document.getElementById('jctSource')?.classList.toggle('d-none',!jct);document.getElementById('uploadSource')?.classList.toggle('d-none',!other);const trainingId=document.getElementById('trainingId');if(trainingId)trainingId.required=training;const documentTitle=document.getElementById('documentTitle');if(documentTitle)documentTitle.required=other;document.querySelectorAll('.jct-required').forEach(item=>item.required=jct);}document.querySelectorAll('.source-type').forEach(item=>item.addEventListener('change',toggleSource));toggleSource();
</script>
@endpush


