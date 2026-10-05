@extends('layouts.master')
@section('title','Kelola ID Card Pelatihan')
@section('content')
@php
$logoUrl=$setting->logo_path?Storage::url($setting->logo_path):null;
$backgroundUrl=$setting->background_path?Storage::url($setting->background_path):null;
$photoUrl=$sample->pasFotoFile?->file_path?Storage::url($sample->pasFotoFile->file_path):($sample->user?->profile_photo?Storage::url($sample->user->profile_photo):null);
$participant=$sample;
@endphp
<div class="container-xxl container-p-y identity-settings-page">
 <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4"><div><a href="{{route('trainings.manage',$training)}}" class="small text-muted"><i class="bx bx-arrow-back"></i> Kembali ke Pusat Kendali</a><h4 class="fw-bold mt-2 mb-1">Kelola ID Card Peserta</h4><p class="text-muted mb-0">Atur identitas visual satu kali untuk seluruh peserta {{ $training->nama_pelatihan }}.</p></div><span class="badge bg-label-{{$setting->enabled?'success':'warning'}} align-self-start p-2">{{$setting->enabled?'Aktif untuk peserta':'Belum diaktifkan'}}</span></div>
 @if(session('success'))<div class="alert alert-success"><i class="bx bx-check-circle me-1"></i>{{session('success')}}</div>@endif
 @if($errors->any())<div class="alert alert-danger">{{$errors->first()}}</div>@endif
 <div class="row g-4"><div class="col-xl-5"><form method="POST" enctype="multipart/form-data" action="{{route('training-id-cards.settings.update',$training)}}" class="card border-0 shadow-sm">@csrf @method('PUT')
  <div class="card-header border-bottom"><h5 class="mb-1 fw-bold">Pengaturan Desain</h5><small class="text-muted">Ukuran hasil selalu dikunci 9,2 × 12,4 cm portrait.</small></div>
  <div class="card-body"><div class="form-check form-switch p-3 rounded bg-label-primary mb-4"><input type="checkbox" class="form-check-input ms-0 me-3" name="enabled" value="1" id="enabled" @checked($setting->enabled)><label class="form-check-label fw-semibold" for="enabled">Aktifkan pembuatan ID card untuk peserta</label></div>
   <div class="mb-3"><label class="form-label fw-semibold">Logo ID Card</label><input type="file" name="logo" id="logoInput" accept="image/png,image/jpeg,image/webp" class="form-control"><small class="text-muted">Disarankan PNG transparan, maksimal 5 MB.</small>@if($setting->logo_path)<div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="removeLogo"><label for="removeLogo" class="form-check-label text-danger">Hapus logo saat ini</label></div>@endif</div>
   <div class="mb-3"><label class="form-label fw-semibold">Gambar Latar</label><input type="file" name="background" id="backgroundInput" accept="image/png,image/jpeg,image/webp" class="form-control"><small class="text-muted">Opsional, maksimal 10 MB.</small>@if($setting->background_path)<div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_background" value="1" id="removeBackground"><label for="removeBackground" class="form-check-label text-danger">Hapus gambar latar saat ini</label></div>@endif</div>
   <div class="row g-3"><div class="col-4"><label class="form-label small">Warna utama</label><input type="color" name="primary_color" id="primaryColor" value="{{$setting->primary_color}}" class="form-control form-control-color w-100"></div><div class="col-4"><label class="form-label small">Aksen</label><input type="color" name="accent_color" id="accentColor" value="{{$setting->accent_color}}" class="form-control form-control-color w-100"></div><div class="col-4"><label class="form-label small">Teks</label><input type="color" name="text_color" id="textColor" value="{{$setting->text_color}}" class="form-control form-control-color w-100"></div></div>
   <div class="mt-3"><div class="d-flex justify-content-between"><label class="form-label fw-semibold">Transparansi latar</label><strong id="opacityValue">{{$setting->background_opacity}}%</strong></div><input type="range" name="background_opacity" id="backgroundOpacity" min="0" max="100" value="{{$setting->background_opacity}}" class="form-range"><small class="text-muted">0% transparan penuh, 100% gambar terlihat utuh.</small></div>
  </div><div class="card-footer border-top d-grid"><button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan dan Terapkan</button></div></form></div>
  <div class="col-xl-7"><div class="card border-0 shadow-sm sticky-xl-top" style="top:90px"><div class="card-header border-bottom d-flex justify-content-between"><div><h5 class="fw-bold mb-1">Preview Langsung</h5><small class="text-muted">Contoh menggunakan data peserta yang tersedia.</small></div><span class="badge bg-label-info">9,2 × 12,4 cm</span></div><div class="card-body preview-shell">@include('identity-cards.card')</div><div class="card-footer border-top small text-muted"><i class="bx bx-info-circle me-1"></i>Posisi elemen dikunci agar hasil seluruh peserta konsisten dan siap cetak.</div></div></div></div>
</div>
@include('identity-cards.styles')
@endsection
@push('js')<script>
const card=document.getElementById('trainingIdCard');
[['primaryColor','--card-primary'],['accentColor','--card-accent'],['textColor','--card-text']].forEach(([id,key])=>document.getElementById(id)?.addEventListener('input',e=>card.style.setProperty(key,e.target.value)));
document.getElementById('backgroundOpacity')?.addEventListener('input',e=>{card.style.setProperty('--card-opacity',e.target.value/100);document.getElementById('opacityValue').textContent=e.target.value+'%'});
function previewFile(input,selector){input?.addEventListener('change',e=>{const file=e.target.files[0];if(!file)return;const url=URL.createObjectURL(file);const box=card.querySelector(selector);if(selector.includes('background'))box.style.backgroundImage=`url('${url}')`;else box.innerHTML=`<img src="${url}" alt="Preview">`;});}
previewFile(document.getElementById('logoInput'),'.training-id-card__logo');previewFile(document.getElementById('backgroundInput'),'.training-id-card__background');
</script>@endpush