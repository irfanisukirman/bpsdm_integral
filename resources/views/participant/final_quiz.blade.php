@extends('layouts.master')
@section('title', $quiz->title)

@section('content')
<div class="container-xxl container-p-y">
    <a href="{{ route('participant.training.show', [$training, 'tab' => 'evaluasi']) }}" class="small"><i class="bx bx-arrow-back me-1"></i>Kembali ke Evaluasi Pelatihan</a>
    <h4 class="mt-2 mb-1">{{ $quiz->title }}</h4>
    <p class="text-muted mb-4">{{ $training->nama_pelatihan }}</p>

    @if($accountToken)
        <div class="mb-3" style="max-width: 18rem">
            <label for="accountQuizToken" class="form-label">Token pribadi</label>
            <input id="accountQuizToken" class="form-control" value="{{ $accountToken }}" readonly autocomplete="off">
            <div class="form-text">Token otomatis terisi dari akun Anda.</div>
        </div>
    @endif
    @include('evaluasi.l2_quiz_body')
</div>
@endsection
