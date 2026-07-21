@extends('layouts.master')
@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Evaluasi /</span> Level 2: Learning (Pre/Post Test)</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Nilai Peserta</h5>
        <button class="btn btn-success btn-sm"><i class="bx bx-upload"></i> Import Nilai Excel</button>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nama Peserta</th>
                    <th width="150">Pre-Test</th>
                    <th width="150">Post-Test</th>
                    <th>N-Gain</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($participants as $p)
                <tr>
                    <td><strong>{{ $p->name }}</strong><br><small>{{ $p->nip_nik }}</small></td>
                    <td><input type="number" class="form-control form-control-sm" value="{{ $p->evaluationL2->pretest ?? 0 }}"></td>
                    <td><input type="number" class="form-control form-control-sm" value="{{ $p->evaluationL2->postest ?? 0 }}"></td>
                    <td><span class="badge bg-label-primary">{{ ($p->evaluationL2->postest ?? 0) - ($p->evaluationL2->pretest ?? 0) }}</span></td>
                    <td><button class="btn btn-primary btn-sm"><i class="bx bx-save"></i></button></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection