@extends('layouts.master')

@section('title', 'Daftar Tiket')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Manajemen Layanan /</span> Daftar Tiket</h4>
        <p class="text-muted small mb-0">Semua tiket {{ Auth::user()->role==='admin_bidang' ? 'bidang '.Auth::user()->bidang : '' }}.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('ticketing.dashboard') }}" class="btn btn-outline-secondary btn-sm shadow-sm"><i class="bx bx-grid-alt me-1"></i>Dashboard</a>
        <a href="{{ route('ticketing.export.excel', request()->query()) }}" class="btn btn-outline-success btn-sm shadow-sm"><i class="bx bx-file me-1"></i>Excel</a>
        <a href="{{ route('ticketing.export.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm shadow-sm"><i class="bx bxs-file-pdf me-1"></i>PDF</a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('ticketing.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="No tiket / nama / email / NIP">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($statuses as $st)<option value="{{ $st }}" {{ request('status')===$st?'selected':'' }}>{{ ucwords(str_replace('_',' ',$st)) }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Layanan</label>
                <select name="service" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($services as $s)<option value="{{ $s->slug }}" {{ request('service')===$s->slug?'selected':'' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Kategori</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($categories as $c)<option value="{{ $c->slug }}" {{ request('category')===$c->slug?'selected':'' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            @if(Auth::user()->role === 'superadmin')
            <div class="col-md-2">
                <label class="form-label">Bidang</label>
                <select name="bidang" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($bidangs as $b)<option value="{{ $b->name }}" {{ request('bidang')===$b->name?'selected':'' }}>{{ $b->name }}</option>@endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <label class="form-label">PIC</label>
                <select name="assigned_to" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach($pics as $p)<option value="{{ $p->id }}" {{ request('assigned_to')==(string)$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('ticketing.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="ps-3">No. Tiket</th>
                    <th>Tanggal</th>
                    <th>Pengaju</th>
                    <th>Layanan</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    @if(Auth::user()->role==='superadmin')<th>Bidang</th>@endif
                    <th>PIC</th>
                    <th>SLA</th>
                    <th class="pe-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $t)
                <tr>
                    <td class="ps-3 fw-semibold text-primary">{{ $t->ticket_number }}</td>
                    <td class="small">{{ $t->created_at->format('d/m/Y') }}<br><span class="text-muted">{{ $t->created_at->format('H:i') }}</span></td>
                    <td class="small">{{ $t->submitter_name }}<br><span class="text-muted">{{ $t->email }}</span></td>
                    <td class="small">{{ ucwords(str_replace('-',' ',$t->service)) }}</td>
                    <td class="small">{{ ucwords(str_replace('-',' ',$t->category)) }}</td>
                    <td><span class="badge bg-{{ $t->status_color }}">{{ $t->status_label }}</span></td>
                    @if(Auth::user()->role==='superadmin')<td class="small">{{ $t->bidang }}</td>@endif
                    <td class="small">{{ $t->assignee?->name ?? '-' }}</td>
                    <td>
                        @php $ind = \App\Services\SlaService::calculateIndicator($t); @endphp
                        <span class="badge bg-label-{{ $ind==='aman'?'success':($ind==='mendekati'?'warning':($ind==='terlewati'?'danger':($ind==='selesai'?'secondary':'secondary'))) }}">
                            {{ $ind==='aman'?'Aman':($ind==='mendekati'?'Mendekati':($ind==='terlewati'?'Terlewati':($ind==='selesai'?'Selesai':'â€”'))) }}
                        </span>
                    </td>
                    <td class="pe-3">
                        <a href="{{ route('ticketing.show', $t) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada tiket ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tickets->hasPages())
    <div class="card-footer bg-white py-3">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection