@extends($widget ? 'layouts.complaint-widget' : ($guest ? 'layouts.auth' : 'layouts.master'))
@section('integral_theme', 'light')
@section('title', 'Buat Pengaduan')
@if($guest && !$widget)
    @include('complaints.guest-styles')
@endif

@section('content')
<main class="container py-4" style="max-width:760px">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">{{ $mode === 'chat' ? 'Chat dengan Admin' : 'Ajukan Pengaduan' }}</h1>
        @if($guest)
            <a href="{{ route('complaints.guest.track', ['widget' => $widget ? 1 : null]) }}" class="btn btn-outline-primary">Lacak Tiket</a>
        @else
            <a href="{{ route('complaints.index', ['widget' => $widget ? 1 : null]) }}" class="btn btn-outline-secondary">Riwayat Pengaduan</a>
        @endif
    </div>
    <div class="card"><div class="card-body">
        @include('complaints.feedback')
        <form method="POST" enctype="multipart/form-data" action="{{ route($guest ? 'complaints.guest.store' : 'complaints.store', ['widget' => $widget ? 1 : null, 'mode' => $mode]) }}"
            @if($mode !== 'report')
                data-complaint-availability="{{ route($guest ? 'complaints.guest.availability' : 'complaints.availability') }}"
            @endif>
            @csrf
            @if($mode === 'chat')
                <div class="complaint-chat mb-3">
                    <div class="complaint-bubble">
                        <strong>Bantuan Integral</strong>
                        <p class="mb-0 mt-1">Selamat datang, Kaka! Ada yang bisa admin bantu? Silakan isi nama dan instansi Kaka, lalu sampaikan keluhannya. Admin akan melanjutkan percakapan di sini.</p>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="complaint-chat-name" class="form-label">Nama Kaka</label>
                    <input id="complaint-chat-name" name="chat_name" class="form-control" maxlength="100" autocomplete="name"
                        value="{{ old('chat_name', auth()->user()?->name) }}" required>
                </div>
                <div class="mb-3">
                    <label for="complaint-chat-institution" class="form-label">Instansi Kaka</label>
                    <input id="complaint-chat-institution" name="chat_institution" class="form-control" maxlength="150" autocomplete="organization"
                        value="{{ old('chat_institution') }}" required>
                </div>
            @else
                <div class="mb-3">
                    <label for="complaint-category" class="form-label">Kategori</label>
                    <select id="complaint-category" name="category" class="form-select" required>
                        <option value="">Pilih kategori</option>
                        @foreach(\App\Models\ComplaintTicket::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" @selected(old('category', $category) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @if($guest)
                    <div class="mb-3">
                        <label for="complaint-name" class="form-label">Nama (opsional)</label>
                        <input id="complaint-name" name="guest_name" class="form-control" maxlength="100" autocomplete="name" value="{{ old('guest_name') }}">
                    </div>
                @elseif($trainings->isNotEmpty())
                    <div class="mb-3" id="complaint-training-field">
                        <label for="complaint-training" class="form-label">Pelatihan terkait (opsional)</label>
                        <select id="complaint-training" name="training_id" class="form-select">
                            <option value="">Pilih pelatihan</option>
                            @foreach($trainings as $training)
                                <option value="{{ $training->id }}" @selected((int) old('training_id', $trainingId) === $training->id)>
                                    {{ $training->nama_pelatihan }}{{ filled($training->angkatan) ? ' · Angkatan '.$training->angkatan : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
            @endif
            @include('complaints.message-fields', ['messageLabel' => $mode === 'chat' ? 'Keluhan Kaka' : 'Pesan'])
            @if($mode !== 'report')
                <p data-complaint-presence role="status">Memeriksa ketersediaan admin…</p>
            @endif
            @if($guest && $mode !== 'chat')
                <p class="text-muted">Setelah laporan dikirim, simpan tautan atau kode pelacakan untuk melihat balasan tanpa login.</p>
            @endif
            <button type="submit" class="btn btn-primary" data-complaint-compose>Kirim Pengaduan</button>
        </form>
    </div></div>
</main>
@if(!$guest && $trainings->isNotEmpty())
    <script>
        {
            const category = document.getElementById('complaint-category');
            const field = document.getElementById('complaint-training-field');
            const trainingCategories = @json(\App\Models\ComplaintTicket::TRAINING_CATEGORIES);
            const updateTrainingField = () => {
                field.hidden = !trainingCategories.includes(category.value);
                field.querySelector('select').disabled = field.hidden;
            };
            category.addEventListener('change', updateTrainingField);
            updateTrainingField();
        }
    </script>
@endif
@endsection
