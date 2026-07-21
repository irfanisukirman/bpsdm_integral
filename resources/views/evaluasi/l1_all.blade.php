@extends('layouts.master')
@section('content')
<h4 class="fw-bold py-3 mb-4">Daftar Pelatihan: Evaluasi Level 1</h4>
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Pelatihan</th>
                    <th>Bidang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trainings as $t)
                <tr>
                    <td><strong>{{ $t->nama_pelatihan }}</strong></td>
                    <td>{{ $t->bidang }}</td>
                    <td>
                        <a href="{{ route('evall1.index', $t->id) }}" class="btn btn-sm btn-primary">Lihat Progres L1</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection