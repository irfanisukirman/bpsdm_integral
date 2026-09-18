@extends('layouts.master')

@section('title', 'Konfigurasi SLA')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Manajemen Layanan /</span> Konfigurasi SLA</h4>
        <p class="text-muted small mb-0">Atur target waktu respons dan penyelesaian tiket.</p>
    </div>
    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bx bx-plus me-1"></i>Tambah Data</button>
</div>

<div class="alert alert-info shadow-sm border-0">
    <i class="bx bx-info-circle me-2"></i>
    Jika SLA dikosongkan (semua "Semua"), nilai berlaku untuk seluruh tiket (fallback). Prioritas: lebih spesifik diutamakan (misal kombinasi Layanan + Kategori + Bidang â†’ lebih diprioritaskan daripada global).
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('ticketing.master.sla.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Tambahkan Konfigurasi SLA</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Layanan</label>
                        <select name="service_id" class="form-select">
                            <option value="">Semua Layanan</option>
                            @foreach($services as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bidang</label>
                        <select name="bidang_id" class="form-select">
                            <option value="">Semua Bidang</option>
                            @foreach($bidangs as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Target Respons (jam)</label>
                        <input type="number" name="respond_hours" step="0.1" class="form-control" placeholder="Respons (jam)" required min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Target Penyelesaian (jam)</label>
                        <input type="number" name="resolve_hours" step="0.1" class="form-control" placeholder="Selesai (jam)" required min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jam Kerja Mulai</label>
                        <input type="time" name="working_hours_start" class="form-control" value="08:00" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jam Kerja Selesai</label>
                        <input type="time" name="working_hours_end" class="form-control" value="17:00" required>
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">Format: Respons (jam) Â· Selesai (jam) Â· Jam Kerja Mulai Â· Jam Kerja Selesai</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary">Simpan SLA</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th class="ps-3">Layanan</th><th>Kategori</th><th>Bidang</th><th>Respons Target</th><th>Penyelesaian Target</th><th>Jam Kerja</th><th>Status</th><th class="pe-3">Aksi</th></tr></thead>
            <tbody>
            @foreach($slas as $sla)
            <tr>
                <td class="ps-3">{{ $sla->service?->name ?? 'Semua' }}</td>
                <td>{{ $sla->category?->name ?? 'Semua' }}</td>
                <td>{{ $sla->bidang?->name ?? 'Semua' }}</td>
                <td>{{ $sla->respond_hours }} jam</td>
                <td>{{ $sla->resolve_hours }} jam</td>
                <td class="small">{{ substr($sla->working_hours_start,0,5) }} - {{ substr($sla->working_hours_end,0,5) }}</td>
                <td><span class="badge bg-{{ $sla->is_active?'success':'secondary' }}">{{ $sla->is_active?'Aktif':'Nonaktif' }}</span></td>
                <td class="pe-3">
                    <form action="{{ route('ticketing.master.sla.destroy', $sla) }}" method="POST" class="d-inline" onsubmit="return IntegralConfirm.ask('Hapus konfigurasi SLA ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bx bx-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
            @if($slas->isEmpty())
            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada konfigurasi SLA.</td></tr>
            @endif
            </tbody>
        </table>
    </div>
</div>
@endsection