@extends('certifications.public_layout')
@section('title','Form Biodata Narasumber')
@section('public_subtitle','Biodata Narasumber Sertifikasi')
@section('content')
<div class="card border-0 shadow-lg">
 <div class="card-header bg-primary text-white p-4"><span class="badge bg-white text-primary mb-2">FORM PUBLIK NARASUMBER</span><h4 class="text-white fw-bold mb-1">Biodata Narasumber</h4><p class="mb-0 opacity-75">{{ $event->title }}</p></div>
 <div class="card-body p-4 p-md-5">
  @if(session('success'))<div class="alert alert-success"><i class="bx bx-check-circle me-1"></i>{{ session('success') }}@if(session('speaker_file_id'))<div class="mt-2"><a href="{{ route('documents.file.download',session('speaker_file_id')) }}" class="btn btn-sm btn-success"><i class="bx bx-download me-1"></i>Download Biodata DOCX</a></div>@endif</div>@endif
  @if($errors->any())<div class="alert alert-danger"><strong>Form belum dapat disimpan.</strong><div>{{ $errors->first() }}</div></div>@endif
  <div class="alert alert-info small"><i class="bx bx-info-circle me-1"></i>Isi data sesuai dokumen resmi. Nama kegiatan dan tanggal pembuatan akan diisi otomatis oleh sistem.</div>
  <form method="POST" action="{{ route('certifications.speakers.public.submit',$event->public_token) }}" id="speakerForm">@csrf
   <div class="row g-3">
    <div class="col-12"><label class="form-label required">Nama Lengkap dengan Gelar</label><input name="nama" class="form-control" value="{{ old('nama') }}" required></div>
    <div class="col-md-6"><label class="form-label">NIP</label><input name="nip" class="form-control" value="{{ old('nip') }}" placeholder="Isi NIP jika tersedia"></div>
    <div class="col-md-6"><label class="form-label">NIK</label><input name="nik" class="form-control" value="{{ old('nik') }}" placeholder="Wajib jika tidak memiliki NIP"><div class="form-text">Isi minimal salah satu: NIP atau NIK.</div></div>
    <div class="col-md-6"><label class="form-label required">Tempat/Tanggal Lahir</label><input name="tempat_tgllahir" class="form-control" value="{{ old('tempat_tgllahir') }}" placeholder="Bandung, 17 Agustus 1990" required></div>
    <div class="col-md-6"><label class="form-label">Pangkat/Golongan</label><input name="pangkat_golongan" class="form-control" value="{{ old('pangkat_golongan') }}"></div>
    <div class="col-md-6"><label class="form-label required">Jabatan</label><input name="jabatan" class="form-control" value="{{ old('jabatan') }}" required></div>
    <div class="col-md-6"><label class="form-label required">Instansi</label><input name="instansi" class="form-control" value="{{ old('instansi') }}" required></div>
    <div class="col-md-6"><label class="form-label required">Jenis Kelamin</label><select name="jenis_kelamin" class="form-select" required><option value="">Pilih jenis kelamin</option>@foreach(['Laki-laki','Perempuan'] as $gender)<option value="{{ $gender }}" @selected(old('jenis_kelamin')===$gender)>{{ $gender }}</option>@endforeach</select></div>
    <div class="col-12"><label class="form-label required">Alamat Rumah</label><textarea name="alamat_rumah" class="form-control" rows="3" required>{{ old('alamat_rumah') }}</textarea></div>
    <div class="col-12"><hr><h6 class="fw-bold"><i class="bx bx-credit-card me-1"></i>Informasi Rekening dan Pajak</h6><p class="text-muted small">Pastikan data rekening sesuai dengan identitas pemilik rekening.</p></div>
    <div class="col-md-6"><label class="form-label required">Nomor Rekening</label><input name="nomor_rekening" class="form-control" value="{{ old('nomor_rekening') }}" required></div>
    <div class="col-md-6"><label class="form-label required">Nama Bank</label><input name="nama_bank" class="form-control" value="{{ old('nama_bank') }}" required></div>
    <div class="col-md-8"><label class="form-label required">Nama Sesuai Rekening</label><input name="nama_sesuai_rekening" class="form-control" value="{{ old('nama_sesuai_rekening') }}" required></div>
    <div class="col-md-4"><label class="form-label">NPWP</label><input name="npwp" class="form-control" value="{{ old('npwp') }}"></div>
    <div class="col-12 mt-4"><div class="d-flex justify-content-between align-items-center mb-2"><label class="form-label required mb-0">Tanda Tangan</label><button type="button" class="btn btn-sm btn-outline-danger" id="clearSignature"><i class="bx bx-eraser me-1"></i>Hapus/Ulangi</button></div><div class="signature-wrap"><canvas id="signatureCanvas" class="signature-canvas"></canvas><div class="signature-line"></div><div class="signature-hint">Tanda tangan di dalam kotak menggunakan jari, stylus, atau mouse</div><div style="height:18px"></div></div><input type="hidden" name="signature_data" id="signatureData"><div id="signatureError" class="text-danger small mt-1 d-none">Silakan bubuhkan tanda tangan terlebih dahulu.</div></div>
   </div>
   <div class="alert alert-warning mt-4 small"><i class="bx bx-file me-1"></i>Dokumen DOCX akan dibuat dari template resmi dan otomatis diarsipkan pada folder kegiatan sertifikasi.</div>
   <button class="btn btn-primary btn-lg w-100" id="submitButton"><i class="bx bx-save me-1"></i>Simpan dan Buat Dokumen</button>
  </form>
 </div>
</div>
@endsection
@push('js')<script>
(()=>{const canvas=document.getElementById('signatureCanvas'),ctx=canvas.getContext('2d'),hidden=document.getElementById('signatureData'),form=document.getElementById('speakerForm'),error=document.getElementById('signatureError');let drawing=false,signed=false;
function setup(){const rect=canvas.getBoundingClientRect(),ratio=Math.max(window.devicePixelRatio||1,1);canvas.width=rect.width*ratio;canvas.height=210*ratio;ctx.setTransform(ratio,0,0,ratio,0,0);ctx.lineWidth=2.2;ctx.lineCap='round';ctx.lineJoin='round';ctx.strokeStyle='#1f2937'}
function point(e){const r=canvas.getBoundingClientRect();return{x:e.clientX-r.left,y:e.clientY-r.top}}canvas.addEventListener('pointerdown',e=>{e.preventDefault();drawing=true;canvas.setPointerCapture(e.pointerId);const p=point(e);ctx.beginPath();ctx.moveTo(p.x,p.y)});canvas.addEventListener('pointermove',e=>{if(!drawing)return;e.preventDefault();const p=point(e);ctx.lineTo(p.x,p.y);ctx.stroke();signed=true;error.classList.add('d-none')});['pointerup','pointercancel','pointerleave'].forEach(n=>canvas.addEventListener(n,()=>drawing=false));document.getElementById('clearSignature').onclick=()=>{ctx.clearRect(0,0,canvas.width,canvas.height);signed=false;hidden.value=''};
form.addEventListener('submit',e=>{if(!signed){e.preventDefault();error.classList.remove('d-none');canvas.scrollIntoView({behavior:'smooth',block:'center'});return}hidden.value=canvas.toDataURL('image/png');const btn=document.getElementById('submitButton');btn.disabled=true;btn.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Membuat dokumen...'});setup()})();
</script>@endpush
