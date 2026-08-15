@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card bg-label-primary border-0">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Selamat Datang, {{ Auth::user()->name }}! 👋</h5>
                            <p class="mb-4">
                                Senang melihat Anda kembali. Anda telah mengikuti <span class="fw-bold">{{ $totalFollowed }}</span> pelatihan di INTEGRAL. 
                                Terus kembangkan kompetensi Anda!
                            </p>
                            <a href="{{ route('participant.trainings') }}" class="btn btn-sm btn-primary">Lihat Pelatihan Baru</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ Auth::user()->avatar ?? asset('assets/img/illustrations/man-with-laptop-light.png') }}" height="140" alt="Avatar" class="rounded-circle mb-3 shadow" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Statistik Kecil -->
        <div class="col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <span class="d-block mb-1 text-muted">Pelatihan Tersedia</span>
                    <h3 class="card-title mb-2 text-info">{{ \App\Models\Training::where('tgl_selesai', '>=', now())->count() }}</h3>
                    <a href="{{ route('participant.trainings') }}" class="small">Lihat Daftar <i class="bx bx-right-arrow-alt"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <span class="d-block mb-1 text-muted">Riwayat Pelatihan</span>
                    <h3 class="card-title mb-2 text-success">{{ $totalFollowed }}</h3>
                    <a href="{{ route('participant.history') }}" class="small">Lihat Riwayat <i class="bx bx-right-arrow-alt"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection