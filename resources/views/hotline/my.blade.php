@extends('layouts.master')

@section('title', 'Aduan Saya')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Hotline Integral /</span> Aduan Saya</h4>
        <p class="text-muted small mb-0">Daftar aduan yang pernah Anda kirim beserta status penanganannya. Untuk aduan baru, gunakan tombol Hotline di layar.</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="ps-3">No. Tiket</th>
                    <th>Tanggal</th>
                    <th>Layanan</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th class="pe-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $t)
                <tr>
                    <td class="ps-3 fw-semibold text-primary">{{ $t->ticket_number }}</td>
                    <td class="small">{{ $t->created_at->format('d/m/Y') }}<br><span class="text-muted">{{ $t->created_at->format('H:i') }}</span></td>
                    <td class="small">{{ ucwords(str_replace('-',' ',$t->service)) }}</td>
                    <td class="small">{{ ucwords(str_replace('-',' ',$t->category)) }}</td>
                    <td><span class="badge bg-{{ $t->status_color }}">{{ $t->status_label }}</span></td>
                    <td class="pe-3"><a href="{{ route('hotline.tracking', $t->tracking_token) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-search me-1"></i>Lacak</a></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="bx bx-support fs-1 text-muted"></i>
                        <p class="fw-semibold mt-2 mb-1">Belum ada aduan</p>
                        <small class="text-muted">Anda belum pernah mengirim aduan melalui Hotline Integral. Gunakan tombol <strong>Hotline</strong> di pojok kanan bawah untuk membuat aduan.</small>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tickets->hasPages())
    <div class="card-footer bg-white py-3">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection