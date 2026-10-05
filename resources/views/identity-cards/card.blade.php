<div class="training-id-card" id="trainingIdCard" style="--card-primary:{{ $setting->primary_color }};--card-accent:{{ $setting->accent_color }};--card-text:{{ $setting->text_color }};--card-opacity:{{ $setting->background_opacity / 100 }}">
    <div class="training-id-card__background" @if($backgroundUrl) style="background-image:url('{{ $backgroundUrl }}')" @endif></div>
    <div class="training-id-card__shape shape-one"></div><div class="training-id-card__shape shape-two"></div>
    <div class="training-id-card__content">
        <header>
            <div class="training-id-card__logo">@if($logoUrl)<img src="{{ $logoUrl }}" alt="Logo">@else<i class="bx bx-buildings"></i>@endif</div>
            <div><span>KARTU PESERTA</span><strong>{{ $training->nama_pelatihan }}</strong></div>
        </header>
        <main>
            <div class="training-id-card__photo">@if($photoUrl)<img src="{{ $photoUrl }}" alt="Foto {{ $participant->name }}">@else<div class="photo-empty"><i class="bx bx-user"></i><small>Foto peserta</small></div>@endif</div>
            <div class="training-id-card__identity"><small>PESERTA</small><h2>{{ $participant->name }}</h2><div class="identity-line"></div><p>{{ $participant->jabatan ?: 'Peserta Pelatihan' }}</p><p>{{ $participant->instansi ?: 'BPSDM Provinsi Jawa Barat' }}</p></div>
        </main>
        <footer><span><i class="bx bx-calendar"></i>{{ \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d M') }} – {{ \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d M Y') }}</span><span><i class="bx bx-map"></i>{{ $training->lokasi ?: 'BPSDM Provinsi Jawa Barat' }}</span></footer>
    </div>
</div>