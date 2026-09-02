@extends('layouts.master')

@section('title', 'Lengkapi Administrasi Narasumber')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="d-flex align-items-start gap-3 mb-4">
                <span class="avatar-initial rounded bg-label-info p-3"><i class="bx bx-chalkboard fs-3"></i></span>
                <div>
                    <h4 class="fw-bold mb-1">Lengkapi Administrasi Narasumber</h4>
                    <p class="text-muted mb-0">Lengkapi data administrasi pembayaran sebelum menggunakan portal narasumber.</p>
                </div>
            </div>

            @if(session('warning'))
                <div class="alert alert-warning"><i class="bx bx-info-circle me-1"></i>{{ session('warning') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success"><i class="bx bx-check-circle me-1"></i>{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Data belum dapat disimpan.</strong>
                    <ul class="mb-0 mt-2 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('pengajar.setup.store') }}" method="POST">
                @csrf

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom"><h5 class="mb-0"><i class="bx bx-credit-card me-1"></i>Informasi Rekening</h5></div>
                    <div class="card-body pt-4">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Nomor NPWP <span class="text-danger">*</span></label><input name="npwp" class="form-control" value="{{ old('npwp') }}" placeholder="Masukkan nomor NPWP" required autofocus></div>
                            <div class="col-md-6"><label class="form-label">Nama Bank <span class="text-danger">*</span></label><input name="nama_bank" class="form-control" value="{{ old('nama_bank') }}" placeholder="Contoh: Bank BJB" required></div>
                            <div class="col-md-6"><label class="form-label">Nomor Rekening <span class="text-danger">*</span></label><input name="nomor_rekening" class="form-control" value="{{ old('nomor_rekening') }}" inputmode="numeric" pattern="[0-9]+" required></div>
                            <div class="col-md-6"><label class="form-label">Rekening Atas Nama <span class="text-danger">*</span></label><input name="nama_rekening" class="form-control" value="{{ old('nama_rekening', auth()->user()->name) }}" required></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pb-4">
                    <button type="submit" class="btn btn-primary px-4"><i class="bx bx-save me-1"></i>Simpan dan Masuk Portal Narasumber</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
