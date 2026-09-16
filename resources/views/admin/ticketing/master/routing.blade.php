@extends('layouts.master')

@section('title', 'Routing Rules')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Manajemen Layanan /</span> Routing Rules</h4>
        <p class="text-muted small mb-0">Mapping Layanan + Kategori â†’ Bidang â†’ PIC.</p>
    </div>
    <a href="{{ route('ticketing.dashboard') }}" class="btn btn-outline-secondary btn-sm shadow-sm"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold"><i class="bx bx-plus-circle me-2 text-primary"></i>Tambahkan Routing Rule</h6></div>
    <div class="card-body">
        <form action="{{ route('ticketing.master.routing.store') }}" method="POST" class="row g-2">
            @csrf
            <div class="col-md-3">
                <select name="service_id" class="form-select" required>
                    <option value="">-- Layanan --</option>
                    @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select" required>
                    <option value="">-- Kategori --</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="bidang_id" class="form-select" required>
                    <option value="">-- Bidang --</option>
                    @foreach($bidangs as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="default_pic_user_id" class="form-select">
                    <option value="">-- PIC Default (Opsional) --</option>
                    @foreach($pics as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="sla_respond_hours" step="0.1" class="form-control" placeholder="SLA Respons (jam), opsional">
            </div>
            <div class="col-md-3">
                <input type="number" name="sla_resolve_hours" step="0.1" class="form-control" placeholder="SLA Penyelesaian (jam), opsional">
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan Rule</button>
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