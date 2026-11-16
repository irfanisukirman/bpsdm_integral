
@extends('layouts.master')

@section('title','Wawancara Evaluasi Pasca')

@section('content')
<a href="{{ route('post-evaluation.show',[$training,'tab'=>'wawancara']) }}">&larr; Kembali ke wawancara</a><h4 class="fw-bold mt-3">{{ $participant->name }}</h4><p class="text-muted">{{ $training->nama_pelatihan }} - {{ $training->program_evaluasi }} - {{ $participant->instansi }}</p>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())<div class="alert alert-danger"><ul>
@foreach($errors->all() as $error)<li>{{ $error }}</li>
@endforeach</ul></div>
@endif
<form action="{{ route('post-evaluation.interview.save',[$training,$participant]) }}" method="POST" id="interviewForm">
@csrf 
@method('PUT')<input type="hidden" name="revision" value="{{ old('revision',$interview->revision) }}">
<div class="card card-body mb-4"><label class="form-label fw-bold">Judul inovasi / aksi / proyek / hasil pelatihan</label><input class="form-control mb-3" name="project" maxlength="2000" value="{{ old('project',$interview->project) }}">

@foreach(['medium_milestone'=>'Target jangka menengah','long_milestone'=>'Target jangka panjang','current_condition'=>'Kondisi saat ini dan keberlanjutan hasil'] as $key=>$label)<label class="form-label fw-bold">{{ $label }}</label><textarea class="form-control mb-3" rows="3" name="{{ $key }}" maxlength="10000">{{ old($key,$interview->$key) }}</textarea>
@endforeach</div>

@foreach($rubric as $i=>$criterion)<section class="card card-body mb-3"><div class="row g-4"><div class="col-lg-7"><h5>{{ $i+1 }}. {{ $criterion['label'] }} <span class="badge bg-label-primary">{{ $criterion['weight'] }}%</span></h5><label class="form-label" for="score-{{ $i }}">Nilai 0-100</label><input class="form-control mb-3" id="score-{{ $i }}" name="scores[{{ $i }}]" type="number" min="0" max="100" step="0.0001" value="{{ old('scores.'.$i,$interview->scores[$i] ?? '') }}"><label class="form-label" for="note-{{ $i }}">Catatan / dasar penilaian</label><textarea id="note-{{ $i }}" class="form-control" name="notes[{{ $i }}]" rows="3" maxlength="10000">{{ old('notes.'.$i,$interview->notes[$i] ?? '') }}</textarea></div><div class="col-lg-5"><h6>Pedoman penilaian</h6><dl class="small mb-0">
@foreach(\App\Services\PostEvaluationService::SCORE_GUIDES[$i] as $guide)<dt>{{ $guide['range'] }}</dt><dd>{{ $criterion['guides'][$loop->index] ?? $guide['description'] }}</dd>
@endforeach</dl></div></div></section>
@endforeach
<div class="card card-body mb-4"><label class="form-label fw-bold">Status wawancara</label><select class="form-select mb-3" name="status"><option value="draft" 
@selected(old('status',$interview->status)==='draft')>Draf</option><option value="completed" 
@selected(old('status',$interview->status)==='completed')>Selesai - kelima nilai wajib terisi</option></select><p class="small text-muted">Rerata pembanding survei memakai kriteria 2-4. Nilai berbobot memakai kelima kriteria. Menyimpan wawancara tidak mengubah survei.</p><button class="btn btn-primary" type="submit">Simpan Wawancara</button><small id="saveStatus" class="mt-2" role="status"></small></div></form>
<section class="card card-body"><h5>Dokumen inovasi dan bukti capaian</h5><p class="small text-muted">Status pelaksanaan dari pengisian alumni: jangka menengah <strong>{{ $interview->medium_evidence_status ?? 'belum diisi' }}</strong>; jangka panjang <strong>{{ $interview->long_evidence_status ?? 'belum diisi' }}</strong>.</p><ul>
@foreach($evidence as $file)<li><a href="{{ route('post-evaluation.evidence',[$training,$file->id]) }}">{{ ['innovation'=>'Dokumen inovasi','medium'=>'Bukti jangka menengah','long'=>'Bukti jangka panjang'][$file->kind] }}: {{ $file->original_name }}</a></li>
@endforeach</ul><form action="{{ route('post-evaluation.evidence.upload',[$training,$participant]) }}" method="POST" enctype="multipart/form-data">
@csrf<div class="row g-2"><div class="col-md-4"><label class="form-label">Jenis dokumen</label><select name="kind" class="form-select"><option value="innovation">Dokumen inovasi</option><option value="medium">Bukti jangka menengah</option><option value="long">Bukti jangka panjang</option></select></div><div class="col-md-5"><label class="form-label">PDF, maksimal 10 MB</label><input class="form-control" type="file" name="file" accept="application/pdf" required></div><div class="col-md-3 align-self-end"><button class="btn btn-outline-primary">Unggah Bukti</button></div></div><small class="text-muted">Unggahan baru mengganti jenis dokumen yang sama.</small></form></section>

@endsection

@push('js')<script>
(()=>{
 const form=document.getElementById('interviewForm'),status=document.getElementById('saveStatus');
 let dirty=false,busy=false,version=0,timer,conflict=false;
 const save=async()=>{
  clearTimeout(timer);if(busy || conflict || !dirty)return;
  busy=true;const sentVersion=version;status.textContent='Menyimpan...';
  try {
   const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json'},credentials:'same-origin'});
   if(response.status===409){conflict=true;throw new Error('Data berubah di perangkat lain. Salin catatan Anda dan muat ulang halaman.');}
   const data=await response.json();
   if(!response.ok)throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Penyimpanan gagal.'));
   form.elements.revision.value=data.revision;dirty=version!==sentVersion;
   status.textContent=dirty?'Ada perubahan baru, menunggu disimpan.':'Tersimpan otomatis.';
   if(dirty)timer=setTimeout(save,500);
  } catch(error){status.textContent=error.message || 'Belum tersimpan. Periksa koneksi dan klik Simpan Wawancara.';status.classList.add('text-danger');}
  finally{busy=false;}
 };
 form.addEventListener('input',()=>{dirty=true;version++;status.classList.remove('text-danger');status.textContent='Ada perubahan yang belum disimpan.';clearTimeout(timer);timer=setTimeout(save,1300);});
 form.addEventListener('submit',event=>{event.preventDefault();dirty=true;save();});
 window.addEventListener('beforeunload',event=>{if(dirty){event.preventDefault();event.returnValue='';}});
})();
</script>
@endpush
