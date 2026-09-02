@extends('layouts.master')

@section('title', 'Portal Mitra')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-5 text-center">
            <span class="avatar-initial rounded-circle bg-label-warning d-inline-flex p-3 mb-3"><i class="bx bx-handshake fs-1"></i></span>
            <h4 class="fw-bold">Portal Mitra</h4>
            <p class="text-muted mb-0">Selamat datang, {{ $user->name }}. Akun Anda sudah terdaftar sebagai Mitra. Fitur dan layanan Mitra akan ditambahkan pada pengembangan berikutnya.</p>
        </div>
    </div>
</div>
@endsection