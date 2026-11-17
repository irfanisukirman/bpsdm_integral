@extends('layouts.master')
@section('title','Pengaturan Laporan Monitoring')
@section('content')
<h4>Pengaturan Laporan Monitoring</h4><p>{{ $training->nama_pelatihan }}</p>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#monitoringReportSignatories">Atur Penandatangan &amp; Unduh</button>
@php $signatories = $training->monitoringReportSignatories(); @endphp
<div class="modal fade" id="monitoringReportSignatories" tabindex="-1" aria-labelledby="monitoringReportSignatoriesTitle" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
<form method="POST" action="{{ route('monitoring.report-settings.save',$training->id) }}">
@csrf
<input type="hidden" name="document" value="{{ $document }}">
<input type="hidden" name="stage_id" value="{{ old('stage_id',$stageId) }}">
<div class="modal-header"><h5 class="modal-title" id="monitoringReportSignatoriesTitle">Penandatangan Laporan Monitoring</h5><a href="{{ route('trainings.manage',$training->id) }}" class="btn-close" aria-label="Tutup"></a></div>
<div class="modal-body"><p class="text-muted">Periksa jabatan, nama, dan NIP sebelum mengunduh. Pengaturan ini digunakan bersama oleh Laporan Monitoring, Laporan Tindak Lanjut, dan Ceklis Monitoring. Satu isian Tim Monitoring mengisi seluruh bagian yang terkait.</p>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row g-4">
@foreach(['head'=>'Penandatangan pertama (Kepala Bidang)','monitor'=>'Penandatangan kedua (Tim Monitoring)'] as $prefix=>$heading)
<div class="col-md-6"><h6 class="fw-bold">{{ $heading }}</h6>
@foreach(['title'=>'Jabatan / sebutan','name'=>'Nama lengkap dan gelar','nip'=>'NIP'] as $suffix=>$label)
@php $field=$prefix.'_'.$suffix; @endphp
<div class="mb-3"><label for="sign-{{ $field }}" class="form-label">{{ $label }}</label><input id="sign-{{ $field }}" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field,$signatories[$field]) }}" required @if($suffix==='nip') inputmode="numeric" pattern="[0-9]{18}" minlength="18" maxlength="18" @else maxlength="{{ $suffix==='title'?250:200 }}" @endif>
@if($suffix==='nip')<small class="text-muted">18 angka tanpa awalan NIP.</small>@endif</div>
@endforeach
</div>
@endforeach
</div></div>
<div class="modal-footer"><a class="btn btn-outline-secondary" href="{{ route('trainings.manage',$training->id) }}">Kembali</a><button class="btn btn-outline-primary" name="download" value="0">Simpan Pengaturan</button><button class="btn btn-primary" name="download" value="1">Simpan &amp; Unduh {{ $document === 'ceklis' ? 'Excel' : 'Word' }}</button></div>
</form></div></div></div>
@push('js')<script>document.addEventListener('DOMContentLoaded',()=>bootstrap.Modal.getOrCreateInstance(document.getElementById('monitoringReportSignatories')).show());</script>@endpush

@endsection
