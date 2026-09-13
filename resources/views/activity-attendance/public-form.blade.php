@extends('layouts.auth')
@section('title',$form->title)
@section('content')
<style>
:root{--attendance-primary:#696cff;--attendance-dark:#293b77;--attendance-soft:#f0f1ff}
body{background:linear-gradient(145deg,#f5f7ff 0%,#eef2fb 52%,#f8f9ff 100%);min-height:100vh}
body:before,body:after{content:"";position:fixed;border-radius:50%;filter:blur(2px);z-index:-1}
body:before{width:330px;height:330px;background:rgba(105,108,255,.08);top:-160px;right:-80px}
body:after{width:260px;height:260px;background:rgba(3,195,236,.07);bottom:-120px;left:-80px}
.public-form{max-width:820px;margin:auto;padding:28px 12px 70px}
.brand-mark{display:inline-flex;align-items:center;gap:10px;padding:8px 14px;border-radius:30px;background:rgba(255,255,255,.8);box-shadow:0 4px 18px rgba(34,48,62,.06);backdrop-filter:blur(8px)}
.brand-mark img{width:34px;height:34px;object-fit:contain}.brand-mark strong{letter-spacing:.08em;color:#344054}
.form-hero{border:0;border-radius:22px;background:linear-gradient(125deg,var(--attendance-dark),#5059bd 58%,var(--attendance-primary));color:#fff;overflow:hidden;position:relative;box-shadow:0 15px 38px rgba(54,67,139,.20)}
.form-hero:before{content:"";position:absolute;width:260px;height:260px;border-radius:50%;background:rgba(255,255,255,.09);right:-65px;top:-150px}
.form-hero:after{content:"";position:absolute;width:120px;height:120px;border:20px solid rgba(255,255,255,.05);border-radius:50%;right:80px;bottom:-75px}
.form-hero .card-body{position:relative;z-index:1}.form-hero h2{color:#fff;line-height:1.25}.form-hero .hero-description{color:rgba(255,255,255,.78)}
.hero-chip{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.18);color:#fff;border-radius:30px;padding:7px 12px;font-size:.82rem}
.progress-shell{position:sticky;top:10px;z-index:50;background:rgba(255,255,255,.92);border:1px solid rgba(220,224,238,.8);border-radius:15px;padding:12px 15px;box-shadow:0 7px 25px rgba(34,48,62,.08);backdrop-filter:blur(10px)}
.progress{height:7px;border-radius:8px;background:#e9ebf5}.progress-bar{background:linear-gradient(90deg,#696cff,#8f92ff);transition:width .3s ease}
.question-card{border:1px solid transparent;border-radius:17px;box-shadow:0 5px 22px rgba(34,48,62,.065);transition:.22s ease;overflow:hidden}
.question-card:hover,.question-card.is-active{border-color:#c6c9fa;box-shadow:0 10px 30px rgba(105,108,255,.10);transform:translateY(-1px)}
.question-card.is-complete{border-left:4px solid #71dd37}
.question-index{width:32px;height:32px;border-radius:10px;background:var(--attendance-soft);color:#595ccd;display:inline-flex;align-items:center;justify-content:center;font-weight:700;flex:0 0 auto}
.form-control,.form-select{border-radius:11px;min-height:45px;border-color:#dfe3eb}.form-control:focus,.form-select:focus{border-color:#8f92ef;box-shadow:0 0 0 .2rem rgba(105,108,255,.12)}
.choice-label{border:1px solid #e2e5ed!important;border-radius:12px!important;transition:.18s ease;cursor:pointer;margin:0}
.choice-label:hover{border-color:#afb3f3!important;background:#f8f8ff}.choice-label:has(input:checked){border-color:#797ced!important;background:#f0f1ff;color:#4e51bd}
.signature-pad{width:100%;height:190px;border:2px dashed #b7bfd0;border-radius:14px;background:linear-gradient(#fff,#fcfcff);touch-action:none;cursor:crosshair}
.submit-card{background:linear-gradient(135deg,#fff,#f8f8ff)}.submit-btn{border:0;border-radius:12px;background:linear-gradient(90deg,#5f62e7,#777af4);box-shadow:0 8px 20px rgba(105,108,255,.24);transition:.2s ease}.submit-btn:hover{transform:translateY(-1px);box-shadow:0 11px 25px rgba(105,108,255,.3)}
.required-star{color:#ff3e1d}.closed-state{border:0;border-radius:18px;box-shadow:0 8px 28px rgba(34,48,62,.08)}
@media(max-width:575.98px){.public-form{padding:18px 4px 45px}.form-hero{border-radius:17px}.form-hero .card-body,.question-card .card-body{padding:20px!important}.progress-shell{top:6px}.hero-chip{font-size:.76rem}}
</style>
<div class="public-form">
 <div class="text-center mb-4">
  <div class="brand-mark"><img src="{{ asset('assets/img/favicon/inte.png') }}" alt="Integral"><strong>INTEGRAL</strong></div>
 </div>
 <div class="card form-hero mb-4">
  <div class="card-body p-4 p-md-5">
   <div class="d-flex align-items-center gap-2 mb-3"><span class="hero-chip"><i class="bx bx-calendar-check"></i>Presensi Kegiatan</span></div>
   <h2 class="fw-bold mb-2">{{ $form->title }}</h2>
   @if($form->subtitle)
    <p class="hero-description mb-4" style="white-space:pre-line">{{ $form->subtitle }}</p>
   @endif
   <div class="d-flex flex-wrap gap-2">
    @if($form->location)
     <span class="hero-chip"><i class="bx bx-map"></i>{{ $form->location }}</span>
    @endif
    @if($form->opens_at)
     <span class="hero-chip"><i class="bx bx-calendar"></i>{{ $form->opens_at->translatedFormat('d M Y') }}</span>
    @endif
    @if($form->closes_at)
     <span class="hero-chip"><i class="bx bx-time"></i>Batas {{ $form->closes_at->translatedFormat('d M Y, H:i') }} WIB</span>
    @endif
   </div>
  </div>
 </div>
 @if(!$form->isOpen())<div class="alert alert-warning closed-state text-center p-4 p-md-5"><i class="bx bx-lock-alt fs-1"></i><h4 class="mt-2">Presensi tidak tersedia</h4><p class="mb-0">@if($form->status==='draft')Form masih dalam tahap persiapan.@elseif($form->status==='closed'||$form->status==='archived')Presensi sudah ditutup.@elseif($form->opens_at&&now()->lt($form->opens_at))Presensi dibuka {{$form->opens_at->translatedFormat('d F Y H:i')}} WIB.@else Presensi belum tersedia.@endif</p></div>
 @else
 @if($errors->any())<div class="alert alert-danger"><strong>Mohon periksa isian berikut:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $e)<li>{{$e}}</li>@endforeach</ul></div>@endif
 <div class="progress-shell mb-3"><div class="d-flex justify-content-between align-items-center mb-2"><span class="small fw-semibold"><i class="bx bx-list-check me-1 text-primary"></i>Progres pengisian</span><span class="small text-primary fw-bold" id="progressText">0%</span></div><div class="progress"><div class="progress-bar" id="formProgress" style="width:0%"></div></div></div>
 <form method="POST" action="{{route('activity-attendance.public.store',$form->public_token)}}" enctype="multipart/form-data" id="attendanceForm">@csrf<input name="website" class="d-none" tabindex="-1" autocomplete="off">
 @foreach($form->questions as $qIndex => $q)<div class="card question-card mb-3" data-question-type="{{ $q->type }}"><div class="card-body p-4">
  @if($q->type==='info')<div class="d-flex gap-2"><i class="bx bx-info-circle text-primary fs-4"></i><div style="white-space:pre-line">{{$q->label}}</div></div>
  @else<div class="d-flex align-items-start gap-3 mb-3"><span class="question-index">{{ $qIndex + 1 }}</span><label class="form-label fw-semibold fs-6 mb-0 pt-1" for="answer{{$q->id}}">{{$q->label}} @if($q->is_required)<span class="required-star">*</span>@endif</label></div>@if($q->help_text)<small class="d-block text-muted mb-2">{{$q->help_text}}</small>@endif
   @if($q->type==='short_text')<input id="answer{{$q->id}}" name="answers[{{$q->id}}]" value="{{old('answers.'.$q->id)}}" class="form-control" @required($q->is_required)>
   @elseif($q->type==='long_text')<textarea id="answer{{$q->id}}" name="answers[{{$q->id}}]" class="form-control" rows="5" @required($q->is_required)>{{old('answers.'.$q->id)}}</textarea>
   @elseif($q->type==='dropdown')<select id="answer{{$q->id}}" name="answers[{{$q->id}}]" class="form-select" @required($q->is_required)><option value="">Pilih jawaban</option>@foreach($q->options?:[] as $option)<option value="{{$option}}" @selected(old('answers.'.$q->id)===$option)>{{$option}}</option>@endforeach</select>
   @elseif($q->type==='radio')<div class="d-flex flex-column gap-2">@foreach($q->options?:[] as $option)<label class="form-check choice-label p-3"><input type="radio" name="answers[{{$q->id}}]" value="{{$option}}" class="form-check-input" @checked(old('answers.'.$q->id)===$option) @required($q->is_required)><span class="form-check-label">{{$option}}</span></label>@endforeach</div>
   @elseif($q->type==='checkbox')<div class="d-flex flex-column gap-2">@foreach($q->options?:[] as $option)<label class="form-check choice-label p-3"><input type="checkbox" name="answers[{{$q->id}}][]" value="{{$option}}" class="form-check-input" @checked(in_array($option,old('answers.'.$q->id,[])))><span class="form-check-label">{{$option}}</span></label>@endforeach</div>
   @elseif(in_array($q->type, ['file', 'photo']))
    @if($q->type === 'photo')
     <input type="file" id="answer{{ $q->id }}" name="answers[{{ $q->id }}]" class="form-control attendance-file" accept="image/jpeg,image/png,image/webp" capture="environment" data-max-kb="{{ $q->max_file_size_kb ?: 5120 }}" data-label="{{ $q->label }}" @required($q->is_required)>
    @else
     <input type="file" id="answer{{ $q->id }}" name="answers[{{ $q->id }}]" class="form-control attendance-file" data-max-kb="{{ $q->max_file_size_kb ?: 5120 }}" data-label="{{ $q->label }}" @required($q->is_required)>
    @endif
    <small class="text-muted file-limit-note"><i class="bx bx-info-circle me-1"></i>Maksimal {{ number_format(($q->max_file_size_kb ?: 5120) / 1024, 1) }} MB</small>
   @elseif($q->type==='signature')<canvas class="signature-pad" data-input="signature{{$q->id}}"></canvas><input type="hidden" id="signature{{$q->id}}" name="answers[{{$q->id}}]" value="{{old('answers.'.$q->id)}}"><div class="d-flex justify-content-between mt-2"><small class="text-muted">Gunakan jari atau mouse.</small><button type="button" class="btn btn-sm btn-outline-secondary clear-signature">Hapus Tanda Tangan</button></div>
   @endif
  @endif
 </div></div>@endforeach
 <div class="card question-card submit-card"><div class="card-body p-4"><div class="d-flex align-items-start gap-2 mb-3"><i class="bx bx-shield-quarter text-success fs-4"></i><p class="small text-muted mb-0">Data dikirim secara aman dan hanya digunakan sebagai bukti kehadiran kegiatan.</p></div><button class="btn btn-primary btn-lg w-100 submit-btn" id="submitAttendance"><i class="bx bx-send me-2"></i><span>Kirim Presensi</span></button></div></div>
 </form>
 @endif
</div>
@endsection
@if($form->isOpen())<script>
document.addEventListener('DOMContentLoaded',()=>document.querySelectorAll('.signature-pad').forEach(canvas=>{const input=document.getElementById(canvas.dataset.input),ctx=canvas.getContext('2d');let drawing=false,dirty=false;function resize(){const saved=input.value,w=canvas.clientWidth,h=190,ratio=window.devicePixelRatio||1;canvas.width=w*ratio;canvas.height=h*ratio;ctx.setTransform(ratio,0,0,ratio,0,0);ctx.lineWidth=2;ctx.lineCap='round';ctx.strokeStyle='#1f2937';if(saved){const img=new Image;img.onload=()=>ctx.drawImage(img,0,0,w,h);img.src=saved}}function point(e){const r=canvas.getBoundingClientRect();return{x:e.clientX-r.left,y:e.clientY-r.top}}canvas.addEventListener('pointerdown',e=>{drawing=true;dirty=true;canvas.setPointerCapture(e.pointerId);const p=point(e);ctx.beginPath();ctx.moveTo(p.x,p.y)});canvas.addEventListener('pointermove',e=>{if(!drawing)return;const p=point(e);ctx.lineTo(p.x,p.y);ctx.stroke()});canvas.addEventListener('pointerup',()=>{drawing=false;if(dirty)input.value=canvas.toDataURL('image/png')});canvas.parentElement.querySelector('.clear-signature').addEventListener('click',()=>{ctx.clearRect(0,0,canvas.width,canvas.height);input.value='';dirty=false});resize()}));

document.addEventListener('DOMContentLoaded',function(){
 const form=document.getElementById('attendanceForm');if(!form)return;
 const cards=[...form.querySelectorAll('.question-card[data-question-type]')].filter(card=>card.dataset.questionType!=='info');
 const bar=document.getElementById('formProgress'),text=document.getElementById('progressText');
 function answered(card){
  const controls=[...card.querySelectorAll('input,textarea,select')];
  const checks=controls.filter(el=>['radio','checkbox'].includes(el.type));
  if(checks.length)return checks.some(el=>el.checked);
  return controls.some(el=>el.type==='file'?el.files.length>0:String(el.value||'').trim()!=='');
 }
 function updateProgress(){
  const done=cards.filter(answered).length,percent=cards.length?Math.round(done/cards.length*100):100;
  if(bar)bar.style.width=percent+'%';if(text)text.textContent=percent+'%';
  cards.forEach(card=>card.classList.toggle('is-complete',answered(card)));
 }
 cards.forEach(card=>{card.addEventListener('focusin',()=>card.classList.add('is-active'));card.addEventListener('focusout',()=>card.classList.remove('is-active'));card.addEventListener('change',updateProgress);card.addEventListener('input',updateProgress);card.addEventListener('pointerup',()=>setTimeout(updateProgress,50));});
 form.addEventListener('submit',()=>{const button=document.getElementById('submitAttendance');if(button){button.disabled=true;button.querySelector('i').className='bx bx-loader-alt bx-spin me-2';button.querySelector('span').textContent='Mengirim presensi...';}});
 form.querySelectorAll('.attendance-file').forEach(input=>{
  input.addEventListener('change',function(){
   const file=this.files[0],maxKb=Number(this.dataset.maxKb||5120),note=this.parentElement.querySelector('.file-limit-note');
   this.setCustomValidity('');this.classList.remove('is-invalid');
   if(file&&file.size>maxKb*1024){
    const maxLabel=maxKb>=1024?(maxKb/1024).toFixed(1).replace('.0','')+' MB':maxKb+' KB';
    const actualLabel=(file.size/1024/1024).toFixed(2)+' MB';
    const message='Ukuran file '+actualLabel+' melebihi batas maksimal '+maxLabel+'. Silakan pilih file yang lebih kecil.';
    this.setCustomValidity(message);this.classList.add('is-invalid');
    if(note){note.classList.remove('text-muted');note.classList.add('text-danger','fw-semibold');note.innerHTML='<i class="bx bx-error-circle me-1"></i>'+message;}
    this.reportValidity();
   }else if(note){
    note.classList.remove('text-danger','fw-semibold');note.classList.add('text-muted');
    const maxLabel=maxKb>=1024?(maxKb/1024).toFixed(1).replace('.0','')+' MB':maxKb+' KB';
    note.innerHTML='<i class="bx bx-check-circle text-success me-1"></i>File siap diunggah. Maksimal '+maxLabel+'.';
   }
   updateProgress();
  });
 });
 updateProgress();
});
</script>@endif
