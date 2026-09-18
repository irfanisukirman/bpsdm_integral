@extends('layouts.master')

@section('title', 'Routing Rules')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Manajemen Layanan /</span> Routing Rules</h4>
        <p class="text-muted small mb-0">Mapping Layanan + Kategori → Bidang → PIC.</p>
    </div>
    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bx bx-plus me-1"></i>Tambah Data</button>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('ticketing.master.routing.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Tambah Routing Rule</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Layanan</label>
                        <select name="service_id" class="form-select" required>
                            <option value="">-- Layanan --</option>
                            @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Kategori --</option>
                            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bidang Tujuan</label>
                        <select name="bidang_id" class="form-select" required>
                            <option value="">-- Bidang --</option>
                            @foreach($bidangs as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">PIC Default (Opsional)</label>
                        <select name="default_pic_user_id" class="form-select">
                            <option value="">-- PIC Default (Opsional) --</option>
                            @foreach($pics as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SLA Respons (jam, opsional)</label>
                        <input type="number" name="sla_respond_hours" step="0.1" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SLA Penyelesaian (jam, opsional)</label>
                        <input type="number" name="sla_resolve_hours" step="0.1" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary">Simpan Rule</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th class="ps-3">Layanan</th><th>Kategori</th><th>Bidang</th><th>PIC Default</th><th>SLA Respons</th><th>SLA Penyelesaian</th><th>Status</th><th class="pe-3">Aksi</th></tr></thead>
            <tbody>
            @foreach($rules as $r)
            <tr>
                <td class="ps-3">{{ $r->service?->name }}</td>
                <td>{{ $r->category?->name }}</td>
                <td>{{ $r->bidang?->name }}</td>
                <td class="small">{{ $r->defaultPic?->name ?? '-' }}</td>
                <td>{{ $r->sla_respond_hours ? $r->sla_respond_hours.' jam' : '-' }}</td>
                <td>{{ $r->sla_resolve_hours ? $r->sla_resolve_hours.' jam' : '-' }}</td>
                <td><span class="badge bg-{{ $r->is_active?'success':'secondary' }}">{{ $r->is_active?'Aktif':'Nonaktif' }}</span></td>
                <td class="pe-3">
                    <form action="{{ route('ticketing.master.routing.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return IntegralConfirm.ask('Hapus rule ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
            @if($rules->isEmpty())
            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada routing rule. Tambahkan rule agar tiket dapat diarahkan otomatis.</td></tr>
            @endif
            </tbody>
        </table>
    </div>
</div>
@endsection