@extends('layouts.master')
@section('title','Kelola Bidang')
@section('content')
<div class="container-xxl container-p-y">
<h4 class="fw-bold mb-2">Kelola Bidang</h4>
<p class="text-muted">Perubahan nama diterapkan pada akun dan data terkait. Bidang yang masih digunakan tidak dapat dihapus.</p>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Nama Bidang</th><th>Penggunaan</th><th class="text-end">Tindakan</th></tr></thead><tbody>
@foreach($bidangs as $bidang)
<tr><td class="text-wrap">{{ $bidang->name }}</td><td>{{ $bidang->usage_count }} referensi data</td><td class="text-end text-nowrap"><button type="button" class="btn btn-sm btn-outline-primary edit-bidang" data-bs-toggle="modal" data-bs-target="#editBidang" data-action="{{ route('settings.bidang.update',$bidang) }}" data-name="{{ $bidang->name }}">Ganti Nama</button> <form class="d-inline" method="POST" action="{{ route('settings.bidang.destroy',$bidang) }}" onsubmit="return confirm('Hapus nama bidang ini dari daftar pilihan?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" @disabled($bidang->usage_count>0 || $bidangs->count()<=1) title="{{ $bidang->usage_count>0?'Masih digunakan oleh akun atau data kegiatan':'Hapus nama bidang' }}">Hapus</button></form></td></tr>
@endforeach
</tbody></table></div></div>
<div class="modal fade" id="editBidang" tabindex="-1" aria-labelledby="editBidangTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><form id="editBidangForm" method="POST" class="modal-content">@csrf @method('PUT')<div class="modal-header"><h5 class="modal-title" id="editBidangTitle">Ganti Nama Bidang</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><label for="bidangName" class="form-label">Nama bidang</label><input id="bidangName" name="name" class="form-control" maxlength="255" required><small class="text-muted">Akun, pelatihan, dokumen dan data bidang terkait akan mengikuti nama baru.</small></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan Perubahan</button></div></form></div></div>
</div>
@endsection
@push('js')<script>document.querySelectorAll('.edit-bidang').forEach(button=>button.addEventListener('click',()=>{document.getElementById('editBidangForm').action=button.dataset.action;document.getElementById('bidangName').value=button.dataset.name;}));</script>@endpush
