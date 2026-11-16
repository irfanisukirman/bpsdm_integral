<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="referrer" content="no-referrer"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Pengisian Inovasi / Proper - INTEGRAL</title>
<style>
*{box-sizing:border-box}body{font:15px/1.65 system-ui,-apple-system,sans-serif;color:#213d34;background:#f0f6f3;margin:0;padding:28px 16px}main{max-width:900px;margin:auto}.hero{padding:32px;background:linear-gradient(120deg,#145842,#24816a);color:white;border-radius:20px;margin-bottom:22px}.hero small{text-transform:uppercase;letter-spacing:.12em;color:#cee8dc}.hero h1{font-size:28px;line-height:1.3;margin:12px 0}.hero p{margin:6px 0}.card{background:white;border:1px solid #d6e6dd;border-radius:18px;padding:28px;box-shadow:0 8px 32px #163e3010}.steps{display:flex;flex-wrap:wrap;gap:12px;margin:20px 0}.steps span{padding:8px 14px;background:#edf8f2;border-radius:10px;color:#28654e;font-weight:600}label{display:block;font-weight:650;margin:16px 0 8px}input,textarea,select{font:inherit;width:100%;padding:12px;border:1px solid #bdcec4;border-radius:9px;background:white;color:#213d34}textarea{resize:vertical;min-height:110px}input:focus,textarea:focus{outline:3px solid #15966b24;border-color:#15966b}.group{padding:22px;border:1px solid #d7e8e0;border-left:5px solid #15966b;border-radius:12px;margin:22px 0;background:#fbfdfb}.group.long{border-left-color:#ba8735;background:#fffdf8}.group.current{border-left-color:#4f7f9f;background:#f8fbfd}h2{font-size:20px;margin:0 0 8px}h3{font-size:17px;margin:0}.help{font-size:13px;color:#667f71;font-weight:400}.radios{display:flex;flex-wrap:wrap;gap:22px}.radios label{display:flex;gap:8px;align-items:center;font-weight:500}.radios input{width:auto}.evidence{padding:16px;background:#eef7f1;border-radius:10px}.notice{padding:16px;border-radius:10px;background:#e3f5e9;margin-bottom:18px}.error{background:#fff0ee;color:#a22f25}.status{margin:16px 0;padding:16px;border:1px solid #c9e2d3;border-radius:10px;background:#f1faf5}.actions{display:flex;flex-wrap:wrap;gap:20px;align-items:center;margin-top:24px}button{border:0;border-radius:10px;padding:14px 25px;font:inherit;font-weight:700;color:white;background:#167650;cursor:pointer}button:disabled{opacity:.5;cursor:not-allowed}[hidden]{display:none!important}@media(max-width:600px){body{padding:12px}.hero,.card{padding:20px}.hero h1{font-size:23px}.group{padding:16px}.actions button{width:100%}}
</style></head><body><main>
<header class="hero"><small>INTEGRAL - Evaluasi Pasca Pelatihan</small><h1>Pengisian Inovasi / Proper</h1><p>{{ $training->nama_pelatihan }}</p><p>{{ $training->program_evaluasi }} - Angkatan {{ $training->angkatan }}</p></header>
<div class="card">
@if(session('success'))<div class="notice" role="status">{{ session('success') }}</div>
@endif
@if($errors->any())<div class="notice error" role="alert"><strong>Periksa kembali isian Anda.</strong><ul>
@foreach($errors->all() as $error)<li>{{ $error }}</li>
@endforeach</ul></div>
@endif
<p>Lengkapi inovasi/Proper, rencana capaian dan kondisi terkini. Data serta dokumen akan digunakan pengelola dan tim penilai pelatihan ini.</p><div class="steps"><span>1. Identitas alumni</span><span>2. Rencana &amp; evidence</span><span>3. Dokumen Proper</span></div>
<form id="properForm" action="{{ route('post-evaluation.proper.store',$training->proper_uid) }}" method="POST" enctype="multipart/form-data">
@csrf
<label>Nama pelatihan<input type="text" readonly value="{{ $training->nama_pelatihan }}"></label>
<label for="participant_identity">NIP/NIK peserta</label><input id="participant_identity" type="text" name="participant_identity" maxlength="80" value="{{ old('participant_identity') }}" autocomplete="off" required placeholder="Masukkan NIP/NIK yang terdaftar pada pelatihan ini"><small class="help">Nama dan status dokumen muncul setelah identitas terverifikasi.</small><div id="participantStatus" class="status" role="status" aria-live="polite" hidden></div>

<label for="proper_title">Judul Inovasi / Proper</label><input id="proper_title" name="proper_title" maxlength="2000" value="{{ old('proper_title') }}" required placeholder="Tuliskan judul inovasi atau Proper">
@foreach(['medium'=>['1. Rencana Jangka Menengah','Milestone / Rencana Jangka Menengah'],'long'=>['2. Rencana Jangka Panjang','Milestone / Rencana Jangka Panjang']] as $period=>$labels)
<section class="group {{ $period }}"><h2>{{ $labels[0] }}</h2><label for="{{ $period }}_milestone">{{ $labels[1] }}</label><textarea id="{{ $period }}_milestone" name="{{ $period }}_milestone" required maxlength="10000" placeholder="Jelaskan rencana dan target capaian">{{ old($period.'_milestone') }}</textarea><h3 style="margin-top:20px">Evidence {{ $period === 'medium' ? 'Jangka Menengah' : 'Jangka Panjang' }}</h3><p class="help">Foto kegiatan wajib diunggah jika sudah dilaksanakan.</p><div class="radios"><label><input type="radio" name="{{ $period }}_evidence_status" value="sudah" required @checked(old($period.'_evidence_status')==='sudah')>Sudah Dilaksanakan</label><label><input type="radio" name="{{ $period }}_evidence_status" value="belum" required @checked(old($period.'_evidence_status','belum')==='belum')>Belum Dilaksanakan</label></div><div class="evidence" data-evidence="{{ $period }}"><label for="{{ $period }}_evidence">Foto bukti kegiatan</label><input id="{{ $period }}_evidence" type="file" name="{{ $period }}_evidence" accept=".jpg,.jpeg,.png,.webp"><small class="help">JPG, PNG atau WebP, maksimal 10 MB.</small></div></section>
@endforeach
<section class="group current"><h2>3. Kondisi Proper Saat Ini</h2><p class="help">Gambarkan perkembangan dan keadaan terbaru Proper.</p><label for="current_condition">Penjelasan kondisi saat ini</label><textarea id="current_condition" name="current_condition" required maxlength="10000">{{ old('current_condition') }}</textarea></section>
<label for="document">Dokumen Inovasi / Proper</label><input id="document" type="file" name="document" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar" required><small class="help">PDF, Word, PowerPoint, Excel, ZIP atau RAR. Maksimal 100 MB, mengikuti batas unggahan server.</small><p class="help">Pengiriman ulang mengganti data dan dokumen Proper sebelumnya, tanpa mengubah nilai wawancara tim. Status belum dilaksanakan akan menghapus evidence periode tersebut.</p><div class="actions"><button id="submitProper" type="submit">Kirim Data &amp; Dokumen Proper</button><small class="help">Data dan dokumen tersimpan privat.</small></div>
</form></div><p class="help" style="text-align:center">INTEGRAL - Hasil Evaluasi Pasca Level 3 &amp; 4</p></main>
<script>
(()=>{
 const form=document.getElementById('properForm'),button=document.getElementById('submitProper');
 document.querySelectorAll('[data-evidence]').forEach(box=>{
  const period=box.dataset.evidence,input=box.querySelector('input[type=file]');
  const update=()=>{const selected=form.querySelector(`input[name="${period}_evidence_status"]:checked`);const active=selected?.value==='sudah';box.hidden=!active;input.required=active;input.disabled=!active;};
  form.querySelectorAll(`input[name="${period}_evidence_status"]`).forEach(radio=>radio.addEventListener('change',update));update();
 });
 const identity=document.getElementById('participant_identity');if(!identity)return;
 const status=document.getElementById('participantStatus');let timer,sequence=0;button.disabled=true;
 const verify=()=>{clearTimeout(timer);const current=++sequence;button.disabled=true;status.hidden=!identity.value.trim();if(status.hidden)return;status.textContent='Memeriksa peserta...';timer=setTimeout(async()=>{
  try{const response=await fetch(@json(route('post-evaluation.proper.verify',$training->proper_uid)),{method:'POST',headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({participant_identity:identity.value.trim()})});const data=await response.json();if(current!==sequence)return;
   if(!response.ok || !data.found)throw new Error(data.errors ? Object.values(data.errors).flat().join(' ') : 'NIP/NIK belum ditemukan.');
   status.textContent=data.name+' - '+(data.has_document ? 'Dokumen sudah tersedia: '+data.document.name+'. Pengiriman baru akan menggantikannya.' : 'Belum ada dokumen Proper.');button.disabled=false;
  }catch(error){if(current===sequence)status.textContent=error.message || 'Pemeriksaan belum berhasil. Coba kembali.';}
 },450);};identity.addEventListener('input',verify);verify();
})();
</script></body></html>
