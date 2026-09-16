@extends('layouts.master')

@section('title', 'Master Bidang')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Manajemen Layanan /</span> Master Bidang</h4>
        <p class="text-muted small mb-0">Kelola bidang penanggung jawab tiket.</p>
    </div>
    <a href="{{ route('ticketing.dashboard') }}" class="btn btn-outline-secondary btn-sm shadow-sm"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bx bx-plus-circle me-2 text-primary"></i>Tambah Bidang</h6></div>
    <div class="card-body">
        <form action="{{ route('ticketing.master.bidang.store') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-5">
                <input type="text" name="name" class="form-control" placeholder="Nama bidang" required>
            </div>
            <div class="col-md-5">
                <select name="default_handler_user_id" class="form-select">
                    <option value="">-- Default Handler (Opsional) --</option>
                    @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th class="ps-3">Nama</th><th>Default Handler</th><th>Status</th><th class="pe-3">Aksi</th></tr></thead>
            <tbody>
            @foreach($items as $item)
            <tr>
                <td class="ps-3 fw-semibold">{{ $item->name }}</td>
                <td class="small">{{ $item->defaultHandler?->name ?? '-' }}</td>
                <td><span class="badge bg-{{ $item->is_active?'success':'secondary' }}">{{ $item->is_active?'Aktif':'Nonaktif' }}</span></td>
                <td class="pe-3">
                    <a href="javascript:void(0)" onclick="editRow('{{ $item->id }}','{{ addslashes($item->name) }}',{{ $item->default_handler_user_id ?: 'null' }})" class="btn btn-sm btn-outline-warning"><i class="bx bx-edit"></i></a>
                    <form action="{{ route('ticketing.master.bidang.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return IntegralConfirm.ask('Hapus bidang ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editForm" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">Edit Bidang</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Bidang</label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Default Handler</label>
                    <select name="default_handler_user_id" id="editHandler" class="form-select">
                        <option value="">-- Default Handler (Opsional) --</option>
                        @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>@endforeach
                    </select>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editActive" checked>
                    <label class="form-check-label" for="editActive">Aktif</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script>
function editRow(id, name, handler) {
    const form = document.getElementById('editForm');
    form.action = '/ticketing/master/bidang/' + id;
    document.getElementById('editName').value = name;
    const sel = document.getElementById('editHandler');
    sel.value = handler || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endpush
@endsection