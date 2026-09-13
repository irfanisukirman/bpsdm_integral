@extends('layouts.master')
@section('title', 'Pengaturan Bantuan Login')
@section('content')
<style>.setting-hero{border:0;border-radius:18px;background:linear-gradient(125deg,#293b77,#696cff);color:#fff}.setting-card{border:0;border-radius:18px;box-shadow:0 5px 24px rgba(34,48,62,.07)}.contact-row{border:1px solid #e7e9f1;border-radius:13px;padding:14px;background:#fafbff}</style>
<div class="card setting-hero mb-4"><div class="card-body p-4"><div class="d-flex align-items-center gap-3"><span class="avatar avatar-lg"><span class="avatar-initial rounded bg-white text-primary"><i class="bx bx-help-circle fs-2"></i></span></span><div><h4 class="text-white fw-bold mb-1">Bantuan Login</h4><p class="mb-0 opacity-75">Atur informasi dan kontak yang tampil saat pengguna memilih Lupa Password.</p></div></div></div></div>
@if(session('success'))<div class="alert alert-success"><i class="bx bx-check-circle me-1"></i>{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Periksa kembali isian:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('settings.login-help.update') }}">@csrf @method('PUT')
<div class="row g-4"><div class="col-lg-7"><div class="card setting-card"><div class="card-header border-bottom"><h5 class="fw-bold mb-1">Isi Bantuan</h5><small class="text-muted">Informasi ini dapat dilihat tanpa login.</small></div><div class="card-body">
<div class="mb-3"><label class="form-label fw-semibold">Judul Popup</label><input name="title" value="{{ old('title',$setting->title) }}" class="form-control" required></div>
<div class="mb-3"><label class="form-label fw-semibold">Penjelasan</label><textarea name="description" rows="3" class="form-control">{{ old('description',$setting->description) }}</textarea></div>
<div class="mb-3"><label class="form-label fw-semibold">Template Pesan WhatsApp</label><textarea name="message_template" rows="8" class="form-control" required>{{ old('message_template',$setting->message_template) }}</textarea><small class="text-muted">Gunakan kode <code>{ADMIN}</code> untuk nama admin yang dipilih.</small></div>
<div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="activeHelp" @checked(old('is_active',$setting->is_active))><label class="form-check-label fw-semibold" for="activeHelp">Tampilkan bantuan lupa password di halaman login</label></div>
</div></div></div>
<div class="col-lg-5"><div class="card setting-card"><div class="card-header border-bottom d-flex justify-content-between align-items-center"><div><h5 class="fw-bold mb-1">Kontak Admin</h5><small class="text-muted">Maksimal 10 kontak.</small></div><button type="button" class="btn btn-sm btn-primary" id="addContact"><i class="bx bx-plus me-1"></i>Tambah</button></div><div class="card-body"><div id="contacts" class="d-grid gap-3">
@forelse(old('contact_names',$setting->contacts ? collect($setting->contacts)->pluck('name')->all() : []) as $index=>$name)
<div class="contact-row"><div class="d-flex justify-content-between mb-2"><strong>Kontak <span class="contact-number">{{ $index+1 }}</span></strong><button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-contact"><i class="bx bx-trash"></i></button></div><input name="contact_names[]" value="{{ $name }}" class="form-control mb-2" placeholder="Nama admin" required><input name="contact_phones[]" value="{{ old('contact_phones.'.$index,data_get($setting->contacts,$index.'.phone')) }}" class="form-control" placeholder="628xxxxxxxxxx" required></div>
@empty
<div class="text-center text-muted py-4 empty-contact"><i class="bx bx-user-plus fs-1 d-block mb-2"></i>Belum ada kontak bantuan.</div>
@endforelse
</div></div></div></div></div>
<div class="d-flex justify-content-end mt-4"><button class="btn btn-primary px-4"><i class="bx bx-save me-1"></i>Simpan Pengaturan</button></div>
</form>
<template id="contactTemplate"><div class="contact-row"><div class="d-flex justify-content-between mb-2"><strong>Kontak <span class="contact-number"></span></strong><button type="button" class="btn btn-sm btn-icon btn-outline-danger remove-contact"><i class="bx bx-trash"></i></button></div><input name="contact_names[]" class="form-control mb-2" placeholder="Nama admin" required><input name="contact_phones[]" class="form-control" placeholder="628xxxxxxxxxx" required></div></template>
@endsection
@push('js')<script>const box=document.getElementById('contacts');function renumber(){box.querySelectorAll('.contact-row').forEach((row,i)=>row.querySelector('.contact-number').textContent=i+1)}document.getElementById('addContact').addEventListener('click',()=>{if(box.querySelectorAll('.contact-row').length>=10)return;box.querySelector('.empty-contact')?.remove();box.append(document.getElementById('contactTemplate').content.cloneNode(true));renumber()});box.addEventListener('click',e=>{const button=e.target.closest('.remove-contact');if(button){button.closest('.contact-row').remove();renumber()}});renumber();</script>@endpush
