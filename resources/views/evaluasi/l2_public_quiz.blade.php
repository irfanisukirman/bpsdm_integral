@extends('layouts.auth')
@section('title', $participant || $attempt ? $quiz->title : 'Evaluasi Level 2')
@section('integral_theme', 'light')
@section('body_class', 'public-quiz-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/integral-brand.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/public-final-quiz.css') }}?v=2">
@endpush

@section('content')
<main class="public-quiz {{ $participant && ($publicAccess['confirmed'] ?? false) && !$scheduleMessage ? 'public-quiz-wide' : '' }}">
    <header class="public-quiz-header">
        <div class="public-quiz-heading">
            @if($participant || $attempt)
                <div class="fw-semibold small mb-2">{{ $quiz->isPretest() ? 'Pretest' : 'Posttest' }}</div>
                <h1>{{ $quiz->title }}</h1>
            @else
                <h1>Evaluasi Level 2</h1>
            @endif
            <p class="mb-0">{{ $training->nama_pelatihan }}</p>
        </div>
        <svg class="public-quiz-art" viewBox="0 0 440 290" fill="none" aria-hidden="true" focusable="false">
            <circle cx="215" cy="145" r="114" fill="#CBF6D5" fill-opacity=".13"/>
            <circle cx="215" cy="145" r="137" stroke="#CBF6D5" stroke-opacity=".22" stroke-dasharray="4 8"/>
            <rect x="169" y="49" width="170" height="194" rx="20" transform="rotate(12 169 49)" fill="#7CD3A3"/>
            <rect x="105" y="55" width="177" height="196" rx="20" transform="rotate(-8 105 55)" fill="#E8F8ED"/>
            <g transform="rotate(-8 105 55)">
                <rect x="134" y="83" width="73" height="10" rx="5" fill="#185F40"/>
                <rect x="134" y="111" width="23" height="23" rx="6" fill="#198754"/>
                <path d="m140 123 4 4 7-9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="169" y="117" width="83" height="10" rx="5" fill="#9DD9B5"/>
                <rect x="134" y="150" width="23" height="23" rx="6" stroke="#58A780" stroke-width="2"/>
                <rect x="169" y="156" width="67" height="10" rx="5" fill="#9DD9B5"/>
                <rect x="134" y="189" width="23" height="23" rx="6" stroke="#58A780" stroke-width="2"/>
                <rect x="169" y="195" width="78" height="10" rx="5" fill="#9DD9B5"/>
            </g>
            <circle cx="323" cy="212" r="38" fill="#D5FB91"/>
            <path d="m307 212 11 11 20-23" stroke="#145B3C" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M75 107v26m-13-13h26M358 55v24m-12-12h24" stroke="#CFF7D9" stroke-opacity=".75" stroke-width="3" stroke-linecap="round"/>
            <circle cx="73" cy="209" r="4" fill="#D5FB91"/>
            <circle cx="374" cy="152" r="4" fill="#D5FB91"/>
        </svg>
    </header>

    <div class="public-quiz-content">
        @if($scheduleMessage)
            @include('evaluasi.l2_quiz_body')
        @elseif($attempt)
            <section class="public-quiz-panel text-center" data-quiz-receipt-url="{{ route('public.final-quiz.show', $training->level2PublicToken()) }}">
                <h2 class="h5">Jawaban berhasil dikirim</h2>
                <p class="text-muted mb-1">Nilai Kuis</p>
                <p class="display-4 fw-bold mb-2">{{ number_format((float) $attempt->score, 2, ',', '.') }} <span class="fs-5 text-muted">/ 100</span></p>
                @unless($quiz->isPretest())
                    <span class="badge bg-label-{{ $attempt->passed ? 'success' : 'warning' }}">{{ $attempt->passed ? 'Lulus' : 'Belum Lulus' }}</span>
                @endunless
                <p class="small text-muted mt-4 mb-2">Kembali ke formulir identitas dalam <strong data-quiz-seconds>10</strong> detik.</p>
                <progress class="public-quiz-progress" max="10" value="10" aria-label="Waktu untuk melihat nilai"></progress>
            </section>
            <script src="{{ asset('assets/js/public-quiz-receipt.js') }}" defer></script>
        @elseif($participant && !$publicAccess['confirmed'])
            <div class="public-quiz-panel">
                @if(session('quiz_notice'))
                    <p class="alert alert-info" role="status">{{ session('quiz_notice') }}</p>
                @endif
                <h2 class="h5">Konfirmasi Identitas</h2>
                <p class="text-muted">Pastikan nama dan instansi di bawah ini sudah sesuai.</p>
                <dl class="public-quiz-identity mb-4">
                    <dt>Nama</dt>
                    <dd>{{ $participant->name }}</dd>
                    <dt>Instansi / Perangkat Daerah</dt>
                    <dd>{{ $participant->instansi ?: $participant->user?->instansi ?: 'Belum tercatat' }}</dd>
                </dl>
                <form method="POST" action="{{ route('public.final-quiz.confirm', $training->level2PublicToken()) }}">
                    @csrf
                    <input type="hidden" name="quiz_access" value="{{ $publicAccess['nonce'] }}">
                    <button class="btn btn-primary" type="submit">Data Sesuai, Mulai Ujian <i class="bx bx-right-arrow-alt ms-1" aria-hidden="true"></i></button>
                </form>
            </div>
        @elseif($participant)
            @include('evaluasi.l2_quiz_body')
        @else
            <div class="public-quiz-panel">
                <h2 class="h5">Verifikasi Peserta</h2>
                <p class="text-muted">Masukkan NIP/NIK yang terdaftar dan token pribadi dari penyelenggara.</p>
                @if(session('quiz_completed'))
                    <div class="alert alert-warning" role="status">{{ session('quiz_completed') }}</div>
                @endif
                <form method="POST" action="{{ route('public.final-quiz.verify', $training->level2PublicToken()) }}">
                    @csrf
                    <label class="form-label" for="participantNip">NIP/NIK</label>
                    <input class="form-control @error('nip') is-invalid @enderror" id="participantNip" name="nip"
                           inputmode="numeric" maxlength="18" pattern="[0-9]{16}|[0-9]{18}" autocomplete="off"
                           aria-describedby="nipHelp" required>
                    <div id="nipHelp" class="form-text">18 digit NIP atau 16 digit NIK.</div>
                    <label class="form-label mt-3" for="participantToken">Token Pribadi</label>
                    <input class="form-control @error('token') is-invalid @enderror" id="participantToken" name="token"
                           type="password" maxlength="16" autocomplete="off" spellcheck="false" required>
                    @if($errors->any())<p class="text-danger small mt-2" role="alert">{{ $errors->first() }}</p>@endif
                    <button class="btn btn-primary mt-4" type="submit">Verifikasi dan Lanjutkan <i class="bx bx-right-arrow-alt ms-1" aria-hidden="true"></i></button>
                </form>
            </div>
        @endif
    </div>
</main>
@endsection
