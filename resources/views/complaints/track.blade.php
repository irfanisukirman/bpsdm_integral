@extends($widget ? 'layouts.complaint-widget' : 'layouts.auth')
@section('integral_theme', 'light')
@section('title', 'Lacak Pengaduan')
@unless($widget)
    @include('complaints.guest-styles')
@endunless

@section('content')
<main class="container py-5" style="max-width:660px">
    <div class="card"><div class="card-body">
        <h1 class="h4">Lacak Pengaduan</h1>
        <p class="text-muted">Masukkan kode pelacakan yang Anda simpan setelah mengirim pengaduan.</p>
        @include('complaints.feedback')
        <form method="POST" action="{{ route('complaints.guest.lookup', ['widget' => $widget ? 1 : null]) }}">
            @csrf
            <label for="complaint-code" class="form-label">Kode pelacakan</label>
            <input id="complaint-code" name="code" class="form-control mb-3" maxlength="48" autocomplete="off" spellcheck="false" required>
            <button type="submit" class="btn btn-primary">Lacak Tiket</button>
            <a href="{{ route('complaints.guest.create', ['widget' => $widget ? 1 : null]) }}" class="btn btn-outline-primary">Buat Pengaduan</a>
        </form>
    </div></div>
</main>
@endsection
