@php $signatories = $training->l34ReportSignatories(); @endphp
<div class="modal fade" id="l34ReportSignatories" tabindex="-1" aria-labelledby="l34ReportSignatoriesTitle" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
<form method="POST" action="{{ route('evall34.report-signatories',$training->id) }}">
@csrf
<div class="modal-header"><h5 class="modal-title" id="l34ReportSignatoriesTitle">Penandatangan Laporan Word</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
<div class="modal-body"><p class="text-muted">Periksa jabatan, nama, dan NIP sebelum mengunduh. Pengaturan disimpan untuk pelatihan ini dan otomatis digunakan pada bagian penandatangan laporan.</p>
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
<div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-outline-primary" name="download" value="0">Simpan Pengaturan</button><button class="btn btn-primary" name="download" value="1">Simpan &amp; Unduh Word</button></div>
</form></div></div></div>
@if($errors->any())
@push('js')<script>document.addEventListener('DOMContentLoaded',()=>bootstrap.Modal.getOrCreateInstance(document.getElementById('l34ReportSignatories')).show());</script>@endpush
@endif
