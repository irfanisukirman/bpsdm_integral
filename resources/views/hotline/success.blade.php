@extends('layouts.form')

@section('form_title', 'Tiket Terkirim')
@section('module_name', 'Hotline')
@section('page_title', 'Tiket Berhasil Dibuat')
@section('page_description', 'Terima kasih, aduan Anda telah kami terima.')
@section('back_url', 'javascript:history.back()')
@section('form_footer') @overwrite

@section('form_content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body text-center py-5">
        <div class="mb-3">
            <span style="width:72px;height:72px;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;background:#e8f5e9;">
                <i class="bx bx-check-circle text-success" style="font-size:42px;"></i>
            </span>
        </div>
        <h4 class="fw-bold">Aduan Anda Telah Diterima</h4>
        <p class="text-muted mb-4">Simpan nomor tiket berikut untuk melacak status aduan Anda.</p>

        <div class="d-inline-block px-4 py-3 rounded-3 border" style="background:#f8f9ff;">
            <div class="text-muted small mb-1">Nomor Tiket</div>
            <div class="display-6 fw-bold text-primary letter-spacing">{{ $ticket->ticket_number }}</div>
        </div>

        <div class="row justify-content-center mt-4 text-start">
            <div class="col-md-8">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width:40%;">Tanggal Dibuat</td>
                            <td class="fw-semibold">{{ $ticket->created_at->translatedFormat('d F Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Layanan</td>
                            <td class="fw-semibold">{{ ucwords(str_replace('-', ' ', $ticket->service)) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kategori</td>
                            <td class="fw-semibold">{{ ucwords(str_replace('-', ' ', $ticket->category)) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td><span class="badge bg-primary">{{ $ticket->status_label }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="alert alert-info text-start mt-4 mb-4">
            <i class="bx bx-envelope me-2"></i>
            <strong>Tautan tracking juga telah dikirim ke email Anda.</strong>
            Cek kotak masuk (atau spam) pada <strong>{{ $ticket->email }}</strong> untuk tautan pelacakan.
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="{{ route('hotline.tracking', $ticket->tracking_token) }}" class="btn btn-primary">
                <i class="bx bx-search-alt me-1"></i> Buka Halaman Tracking
            </a>
            <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                <i class="bx bx-home me-1"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection