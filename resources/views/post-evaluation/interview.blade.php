@extends('layouts.master')
@section('title','Wawancara Evaluasi Pasca')
@section('content')
<style>
.proper-reference{margin-top:18px;padding:18px;border:1px solid #cfe3d8;border-radius:12px;background:#f5faf7}.proper-reference h6{color:#176344;font-weight:700}.proper-reference dl{margin:0}.proper-reference dl>div{display:grid;grid-template-columns:180px minmax(0,1fr);gap:12px;padding:10px 0;border-top:1px solid #deebe4}.proper-reference dt{font-size:.8rem;color:#64786e}.proper-reference dd{margin:0;white-space:pre-wrap;overflow-wrap:anywhere}.interview-survey td{white-space:normal;overflow-wrap:anywhere;vertical-align:top}.interview-criterion{scroll-margin-top:20px}.score-guide dt{margin-top:12px}.proper-documents{display:flex;flex-wrap:wrap;gap:8px;align-items:center}.proper-documents small{overflow-wrap:anywhere}.criterion-reference .proper-reference{margin:0 0 20px}.interview-score-comment{display:grid;grid-template-columns:124px minmax(0,1fr);gap:20px;align-items:start}.interview-score-box{width:124px;min-width:0}.interview-score-box input{height:70px;text-align:center;font-size:1.6rem;font-weight:700;font-variant-numeric:tabular-nums;padding:.5rem}.interview-comment-box{min-width:0}.interview-score-box .form-label{font-size:.85rem}.interview-title{white-space:pre-wrap;overflow-wrap:anywhere}@media(max-width:600px){.interview-score-comment{grid-template-columns:94px minmax(0,1fr);gap:12px}.interview-score-box{width:94px}.interview-score-box input{font-size:1.3rem;height:62px}.proper-reference dl>div{grid-template-columns:1fr;gap:4px}}
</style>
<a href="{{ route('post-evaluation.show',[$training,'tab'=>'wawancara']) }}">&larr; Kembali ke wawancara</a>
<h4 class="fw-bold mt-3">{{ $participant->name }}</h4><p class="text-muted">{{ $training->nama_pelatihan }} - {{ $training->program_evaluasi }} - {{ $participant->instansi }}</p>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>

@endif


@if($errors->any())
<div class="alert alert-danger"><ul>
@foreach($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul></div>

@endif

<form action="{{ route('post-evaluation.interview.save',[$training,$participant]) }}" method="POST" id="interviewForm">
@csrf
@method('PUT')
<input type="hidden" name="revision" value="{{ old('revision',$interview->revision) }}">
<section class="card card-body mb-4">
<h5>Informasi wawancara</h5><p class="text-muted">Penilai: <strong>{{ auth()->user()->name }}</strong>. Isian tersimpan otomatis saat perangkat terhubung.</p>
<label class="form-label fw-bold">Nama aksi perubahan</label><div class="border rounded p-3 interview-title">{{ $interview->project ?: 'Belum diisi peserta.' }}</div>
<div class="proper-reference"><h6>Informasi Proper dari peserta</h6><dl><div><dt>Judul Inovasi/Proper</dt><dd>{{ $interview->project ?: 'Belum diisi peserta.' }}</dd></div><div><dt>Kondisi Proper saat ini</dt><dd>{{ $interview->current_condition ?: 'Belum diisi peserta.' }}</dd></div></dl></div>
@php $properDocument=$evidence->firstWhere('kind','innovation'); @endphp
<div class="proper-documents mt-3">

@if($properDocument)
<strong>Dokumen Proper:</strong><small>{{ $properDocument->original_name }}</small>
@include('post-evaluation.document-actions',['document'=>$properDocument])
@else
<small class="text-muted">Dokumen Proper belum diunggah peserta.</small>

@endif

</div><small class="text-muted mt-3">Informasi Proper diisi melalui link publik pelatihan. Evaluator membaca data ini sebagai dasar penilaian.</small>
</section>
<section class="card card-body mb-4"><h5>Jawaban survei</h5><p class="text-muted">Jawaban evaluasi langsung dari Integral. Khusus PKP, PKA, PKN dan LATSAR: Tidak Sesuai=1, Kurang Sesuai=2, Sesuai=3, Sangat Sesuai=4. Konversi ke skala 100 adalah nilai tersebut dikali 25. Teknis menggunakan skala sumbernya sendiri. Hanya tiga indikator hasil berikut yang ditampilkan. Sumber penilai ditampilkan terpisah.</p>
<div class="table-responsive"><table class="table interview-survey"><thead><tr><th>Pertanyaan</th><th>Penilai</th><th>Jawaban asli</th><th>Skala 1-4</th><th>Konversi 100</th></tr></thead><tbody>
@foreach(app(\App\Services\PostEvaluationService::class)->surveyQuestionLabels($training) as $indicator=>$label)
@php $indicatorAnswers=collect($surveyAnswers)->where('indicator',$indicator); @endphp
<tr data-survey-indicator="{{ $indicator }}"><td style="min-width:220px">{{ $label }}</td>
@foreach(['role','answer','four','converted'] as $column)
<td>
@forelse($indicatorAnswers as $answer)
<div class="mb-2">
@if($column==='role')
{{ $training->evaluationRoleLabels()[$answer['role']] }}
@elseif($column==='answer')
{{ $answer['answer'] ?? '-' }}
@elseif($column==='four')
<strong>{{ $answer['four'] === null ? '-' : number_format($answer['four'],0) }}</strong>
@if($answer['scale']==='hundred')<small class="d-block text-muted">Sumber slider 0-100</small>@endif
@else
{{ $answer['converted'] === null ? '-' : number_format($answer['converted'],0) }}
@endif
</div>
@empty
<span class="text-muted">-</span>
@endforelse
</td>
@endforeach
</tr>
@endforeach
</tbody></table></div>
</section>
@php
    $scoreService = app(\App\Services\PostEvaluationService::class);
    $currentScores = collect(range(0,4))->map(fn ($i) => old('scores.'.$i, $interview->scores[$i] ?? null))->map(fn ($v) => $v === '' || $v === null ? null : (float) $v)->all();
    $filledScores = count(array_filter($currentScores, fn ($v) => $v !== null));
    $weightedScore = $filledScores === 5 ? array_sum(array_map(fn ($v,$r) => $v * $r['weight'] / 100, $currentScores, $rubric)) : null;
    $interviewAverage = $scoreService->average(array_slice($currentScores,1,3));
    $surveyIndicators = collect(range(0,2))->map(fn ($i) => $scoreService->average(collect($surveyAnswers)->where('role','mandiri')->where('indicator',$i)->pluck('converted')->all()))->all();
    $surveyAverage = $scoreService->average($surveyIndicators);
@endphp
<section class="card card-body mb-4 border border-primary" aria-label="Ringkasan penilaian langsung">
<div class="row g-4 align-items-center">
<div class="col-md-4"><small class="text-primary fw-bold">PENGISIAN LANGSUNG</small><h5 class="mt-2 mb-1">Nilai akhir berbobot</h5><div class="display-5 fw-bold text-primary" id="liveWeightedScore" aria-live="polite">{{ $weightedScore === null ? '-' : number_format($weightedScore,2,',','.') }}</div><span class="badge bg-label-primary mt-2" id="liveFilledScores">{{ $filledScores }}/5 nilai terisi</span></div>
<div class="col-md-8"><div class="row g-3">
<div class="col-sm-4"><small class="text-muted d-block">Wawancara ekspose</small><strong class="fs-4" id="liveInterviewAverage">{{ $interviewAverage === null ? '-' : number_format($interviewAverage,2,',','.') }}</strong></div>
<div class="col-sm-4"><small class="text-muted d-block">Survei</small><strong class="fs-4">{{ $surveyAverage === null ? '-' : number_format($surveyAverage,2,',','.') }}</strong></div>
<div class="col-sm-4"><small class="text-muted d-block">Status wawancara</small><strong id="liveInterviewStatus">{{ old('status',$interview->status) === 'completed' ? 'Selesai' : 'Draf' }}</strong></div>
</div><p class="mt-3 mb-1">Isi nilai dan catatan saat wawancara. Setelah seluruh konfirmasi selesai, tandai wawancara selesai.</p><small class="text-muted">Nilai berbobot memakai lima kriteria; wawancara ekspose memakai rerata kriteria 2?4. Nilai kosong tidak dihitung sebagai nol.</small></div>
</div>
</section>
@foreach($rubric as $i=>$criterion)
<section class="card card-body mb-4 interview-criterion" id="criterion-{{ $i }}"><div class="d-flex flex-wrap gap-2 justify-content-between align-items-start mb-3"><h5>{{ $i+1 }}. {{ $criterion['label'] }}</h5><span class="badge bg-label-primary">Bobot {{ $criterion['weight'] }}%</span></div>
<div class="row g-4"><div class="col-lg-7"><div class="interview-score-comment"><div class="interview-score-box"><label class="form-label fw-bold" for="score-{{ $i }}">Nilai wawancara</label><input class="form-control mb-2 interview-score-input" inputmode="decimal" id="score-{{ $i }}" name="scores[{{ $i }}]" type="number" min="0" max="100" step="0.0001" value="{{ old('scores.'.$i,$interview->scores[$i] ?? '') }}"><p class="small text-muted mb-0">Maksimal 100.<br>Kosongkan bila belum dinilai.</p></div><div class="interview-comment-box">
<label class="form-label fw-bold" for="note-{{ $i }}">Hasil wawancara &amp; bukti</label><textarea id="note-{{ $i }}" class="form-control" name="notes[{{ $i }}]" rows="4" maxlength="10000" placeholder="Tuliskan capaian, alasan pemberian nilai, kendala, dan bukti yang dikonfirmasi.">{{ old('notes.'.$i,$interview->notes[$i] ?? '') }}</textarea></div></div></div>
<div class="col-lg-5 criterion-reference">

@if(in_array($i,[0,1],true))
@php $period=$i===0 ? 'medium' : 'long'; $periodFile=$evidence->firstWhere('kind',$period); $statusValue=$interview->{$period.'_evidence_status'}; @endphp
<div class="proper-reference"><h6>Data {{ $i===0 ? 'Jangka Menengah' : 'Jangka Panjang' }} dari peserta</h6><dl><div><dt>Milestone / rencana</dt><dd>{{ $interview->{$period.'_milestone'} ?: 'Belum diisi peserta.' }}</dd></div><div><dt>Status evidence</dt><dd><span class="badge {{ $statusValue==='sudah' ? 'bg-label-success' : 'bg-label-warning' }}">{{ $statusValue==='sudah' ? 'Sudah Dilaksanakan' : ($statusValue==='belum' ? 'Belum Dilaksanakan' : 'Belum diisi') }}</span></dd></div></dl>

@if($periodFile)
<div class="proper-documents mt-3"><small>{{ $periodFile->original_name }}</small>
@include('post-evaluation.document-actions',['document'=>$periodFile])
</div>

@endif

</div>

@endif

<h6>Keterangan rentang nilai</h6><dl class="small mb-0 score-guide">
@foreach(\App\Services\PostEvaluationService::SCORE_GUIDES[$i] as $guide)
<dt>{{ $guide['range'] }}</dt><dd>{{ $criterion['guides'][$loop->index] ?? $guide['description'] }}</dd>
@endforeach
</dl></div></div></section>
@endforeach
<div class="card card-body mb-4"><label class="form-label fw-bold">Status wawancara</label><select class="form-select mb-3" name="status"><option value="draft" @selected(old('status',$interview->status)==='draft')>Draf</option><option value="completed" @selected(old('status',$interview->status)==='completed')>Selesai - kelima nilai wajib terisi</option></select><p class="small text-muted">Rerata pembanding survei memakai kriteria 2-4. Nilai berbobot memakai kelima kriteria. Menyimpan wawancara tidak mengubah survei atau informasi Proper peserta.</p><button class="btn btn-primary" type="submit">Simpan Wawancara</button><small id="saveStatus" class="mt-2" role="status"></small></div>
</form>
@endsection

@push('js')<script>
(()=>{
 const form=document.getElementById('interviewForm'),status=document.getElementById('saveStatus');
 form.querySelectorAll('.interview-score-input').forEach(input=>{
  input.addEventListener('input',()=>{
   if(input.value==='')return;
   const value=Number(input.value);
   if(Number.isFinite(value)) {
    if(value>100)input.value='100';
    else if(value<0)input.value='0';
   }
  });
 });
 const weights=@json(array_column($rubric,'weight'));
 const formatScore=value=>value===null?'-':value.toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2});
 const updateScoreSummary=()=>{
  const values=[...form.querySelectorAll('.interview-score-input')].map(input=>input.value===''?null:Number(input.value)).map(value=>value!==null&&Number.isFinite(value)&&value>=0&&value<=100?value:null);
  const filled=values.filter(value=>value!==null).length;
  const comparison=values.slice(1,4).filter(value=>value!==null);
  document.getElementById('liveWeightedScore').textContent=formatScore(filled===5?values.reduce((sum,value,i)=>sum+value*weights[i]/100,0):null);
  document.getElementById('liveFilledScores').textContent=filled+'/5 nilai terisi';
  document.getElementById('liveInterviewAverage').textContent=formatScore(comparison.length?comparison.reduce((sum,value)=>sum+value,0)/comparison.length:null);
  document.getElementById('liveInterviewStatus').textContent=form.elements.status.value==='completed'?'Selesai':'Draf';
 };
 form.addEventListener('input',updateScoreSummary);
 form.addEventListener('change',updateScoreSummary);
 updateScoreSummary();
 let dirty=false,busy=false,version=0,timer,conflict=false;
 const save=async()=>{
  clearTimeout(timer);if(busy || conflict || !dirty)return;
  if(!form.checkValidity()){status.textContent='Periksa nilai: gunakan angka 0-100, maksimal empat angka desimal.';return;}
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
