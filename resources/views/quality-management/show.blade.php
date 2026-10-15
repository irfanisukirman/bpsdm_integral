@extends('layouts.master')
@section('title','Kelengkapan Mutu')
@section('content')
@php
    $percent = $total ? round($completed / $total * 100) : 100;
@endphp
<div class="container-xxl container-p-y"><a href="{{route('quality-management.index')}}" class="btn btn-sm btn-outline-secondary mb-3"><i class="bx bx-left-arrow-alt"></i> Kembali</a>
<div class="detail-hero mb-4"><div class="flex-grow-1"><span class="badge bg-white text-primary mb-3">{{$training->bidang ?: 'Bidang belum ditentukan'}}</span><h3 class="text-white fw-bold mb-1">{{$training->nama_pelatihan}}</h3><p class="text-white-50 mb-0">Dokumen sistem yang namanya sesuai ditautkan otomatis; berkas lain dapat diunggah pada setiap baris.</p></div><div class="progress-ring"><strong>{{$percent}}%</strong><small>lengkap</small></div></div>
@if(session('success'))<div class="alert alert-success"><i class="bx bx-check-circle me-1"></i>{{session('success')}}</div>@endif @if($errors->any())<div class="alert alert-danger">{{$errors->first()}}</div>@endif
<div class="card border-0 shadow-sm mb-4"><div class="card-body"><div class="d-flex justify-content-between mb-2"><div><h5 class="fw-bold mb-1">Kelengkapan Dokumen Wajib: {{$completed}} / {{$total}}</h5><small class="text-muted">{{$total-$completed}} wajib belum lengkap · {{$notRequired}} dari {{$catalogTotal}} dokumen ditetapkan Tidak Berlaku.</small></div><strong class="text-primary fs-4">{{$percent}}%</strong></div><div class="progress overall"><div class="progress-bar" style="width:{{$percent}}%"></div></div></div></div>
@foreach($catalog as $group)
    @php
        $groupCompleted = 0;
        foreach (array_keys($group['items']) as $groupItemKey) {
            if ($records->has($groupItemKey) && $records->get($groupItemKey)->is_required && $records->get($groupItemKey)->file_id) {
                $groupCompleted++;
            }
        }
    @endphp
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex align-items-center gap-3">
                <span class="group-icon"><i class="bx {{ $group['icon'] }}"></i></span>
                <div>
                    <h5 class="fw-bold mb-0">{{ $group['label'] }}</h5>
                    <small class="text-muted">{{ $groupCompleted }} dari {{ count($group['items']) }} tersedia</small>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead><tr><th>Kelengkapan</th><th>Nama Dokumen</th><th>Kebutuhan</th><th>Berkas Tersedia</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                @foreach($group['items'] as $key => $label)
                    @php
                        $record = $records->get($key);
                        $file = $record ? $record->file : null;
                        $isExisting = $record && $record->source === 'existing';
                        $isRequired = !$record || $record->is_required;
                    @endphp
                    <tr class="{{ $isRequired ? '' : 'not-applicable-row' }}">
                        <td class="text-center">
                            @if(!$isRequired)
                                <span class="status-empty" title="Tidak Berlaku"><i class="bx bx-minus"></i></span>
                            @elseif($file)
                                <span class="status-check"><i class="bx bx-check"></i></span>
                            @else
                                <span class="status-empty"><i class="bx bx-minus"></i></span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $label }}</strong>
                            @if($isExisting)
                                <small class="d-block text-success"><i class="bx bx-link"></i> Otomatis dari dokumen sistem</small>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('quality-management.requirement', [$training, $key]) }}" class="requirement-form">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm requirement-select {{ $isRequired ? 'is-required' : 'is-na' }}" aria-label="Status kebutuhan {{ $label }}">
                                    <option value="required" @selected($isRequired)>Wajib</option>
                                    <option value="not_applicable" @selected(!$isRequired)>Tidak Berlaku</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            @if($file)
                                <div class="file-name"><i class="bx bxs-file me-1"></i>{{ $file->display_name }}</div>
                                <small class="text-muted">{{ number_format(($file->file_size ?: 0) / 1024, 1) }} KB</small>
                            @else
                                <span class="badge bg-label-secondary">N/A</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                @if($file)
                                    <a href="{{ route('quality-management.view', [$training, $record]) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>
                                    <a href="{{ route('quality-management.download', [$training, $record]) }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-download"></i></a>
                                @endif
                                @if($isRequired)
                                    <button type="button" class="btn btn-sm {{ $file ? 'btn-outline-warning' : 'btn-primary' }} upload-btn" data-bs-toggle="modal" data-bs-target="#uploadModal" data-label="{{ $label }}" data-action="{{ route('quality-management.upload', [$training, $key]) }}"><i class="bx bx-upload"></i> {{ $file ? 'Ganti' : 'Upload file' }}</button>
                                @else
                                    <span class="badge bg-label-secondary">Dikecualikan</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
</div>
<div class="modal fade" id="uploadModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form id="uploadForm" method="POST" enctype="multipart/form-data" class="modal-content">@csrf<div class="modal-header"><div><h5 class="modal-title fw-bold">Upload Dokumen</h5><small id="uploadLabel" class="text-muted"></small></div><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="file" name="document" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip"><small class="text-muted d-block mt-2">PDF, Office, gambar, atau ZIP. Maksimal 20 MB.</small></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Dokumen</button></div></form></div></div>
@endsection
@push('css')<style>.detail-hero{display:flex;gap:2rem;align-items:center;background:linear-gradient(125deg,#263f82,#526fe0);border-radius:20px;padding:2rem}.progress-ring{flex:0 0 105px;height:105px;border:8px solid rgba(255,255,255,.22);border-top-color:#fff;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff}.progress-ring strong{font-size:1.35rem}.progress-ring small{font-size:.7rem}.overall{height:12px}.overall .progress-bar{background:linear-gradient(90deg,#696cff,#22c58b)}.group-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:12px;background:#eef0ff;color:#696cff;font-size:1.45rem}.status-check,.status-empty{width:28px;height:28px;display:inline-grid;place-items:center;border-radius:50%}.status-check{background:#dff8ec;color:#18a66a}.status-empty{background:#f1f2f4;color:#8d99a6}.file-name{max-width:280px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.table th{font-size:.72rem;text-transform:uppercase}.table td{padding-top:.9rem;padding-bottom:.9rem}.requirement-select{min-width:145px;font-weight:600}.requirement-select.is-required{color:#b54708;border-color:#f6c977;background:#fffaf0}.requirement-select.is-na{color:#697a8d;border-color:#d9dee3;background:#f5f5f7}.not-applicable-row>td{background:#fafafa;color:#8a9199}.not-applicable-row .file-name{opacity:.7}@media(max-width:600px){.progress-ring{display:none}}</style>@endpush
@push('js')
<script>
document.querySelectorAll('.upload-btn').forEach(btn => btn.addEventListener('click', () => {
    document.getElementById('uploadForm').action = btn.dataset.action;
    document.getElementById('uploadLabel').textContent = btn.dataset.label;
}));
document.querySelectorAll('.requirement-select').forEach(select => select.addEventListener('change', () => {
    select.style.pointerEvents = 'none';
    select.style.opacity = '.65';
    select.closest('form').submit();
}));
</script>
@endpush
