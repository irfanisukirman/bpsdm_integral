
@extends('layouts.master')

@section('title','Hasil Evaluasi Pasca Level 3 & 4')

@section('content')
<h4 class="fw-bold mb-2">Hasil Evaluasi Pasca Level 3 &amp; 4</h4>
<p class="text-muted">Hasil survei, wawancara dan ekspose dari data evaluasi pelatihan, tanpa import ulang.</p>
<form method="GET" class="input-group mb-4"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari pelatihan" maxlength="100"><button class="btn btn-primary">Cari</button></form>
<div class="row g-3">
@forelse($trainings as $training)
<div class="col-md-6 col-xl-4"><div class="card h-100 shadow-sm"><div class="card-body"><span class="badge bg-label-primary mb-3">{{ $training->program_evaluasi }}</span><h5>{{ $training->nama_pelatihan }}</h5><p class="small text-muted">{{ $training->bidang }} - Angkatan {{ $training->angkatan }}</p><a class="btn btn-primary" href="{{ route('post-evaluation.show',$training) }}">Buka Hasil <i class="bx bx-right-arrow-alt"></i></a></div></div></div>

@empty<div class="col"><div class="card card-body">Belum ada pelatihan yang dapat Anda akses.</div></div>
@endforelse</div>
<div class="mt-4">{{ $trainings->links() }}</div>

@endsection
