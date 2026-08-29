@php
    $features = [
        [
            'title' => 'Kelola Pelatihan',
            'description' => 'Mengelola pelatihan, peserta, jadwal, metode pembelajaran, tahapan kegiatan, dan penanggung jawab bidang dalam satu alur.',
            'icon' => 'bx-book-open',
            'tag' => 'Pelatihan',
        ],
        [
            'title' => 'Presensi Digital',
            'description' => 'Presensi harian peserta berdasarkan jadwal, pemantauan status kehadiran, serta rekap daftar hadir yang dapat diunduh.',
            'icon' => 'bx-check-square',
            'tag' => 'Kehadiran',
        ],
        [
            'title' => 'Evaluasi Kirkpatrick',
            'description' => 'Evaluasi Level 1 sampai Level 4 untuk mengukur kepuasan, pembelajaran, perubahan perilaku, dan dampak pelatihan.',
            'icon' => 'bx-analyse',
            'tag' => 'Evaluasi L1–L4',
        ],
        [
            'title' => 'Monitoring & Tindak Lanjut',
            'description' => 'Instrumen monitoring, rekomendasi perbaikan kepada bidang terkait, status penyelesaian, dan laporan tindak lanjut.',
            'icon' => 'bx-shield-quarter',
            'tag' => 'Monitoring',
        ],
        [
            'title' => 'Manajemen Dokumen',
            'description' => 'Dokumen peserta, pengajar, dan keluaran pelatihan tersusun otomatis di dalam folder setiap kegiatan.',
            'icon' => 'bx-folder-open',
            'tag' => 'Dokumen',
        ],
        [
            'title' => 'Ruang Kerja Pengajar',
            'description' => 'Pengajar dapat melihat jadwal dan JP mengajar, melengkapi profil, serta mengunggah materi dan bukti mengajar per sesi.',
            'icon' => 'bx-chalkboard',
            'tag' => 'Pengajar',
        ],
        [
            'title' => 'Forum & Notifikasi',
            'description' => 'Forum percakapan setiap pelatihan serta notifikasi untuk pesan, presensi, evaluasi, dokumen, dan rekomendasi yang belum selesai.',
            'icon' => 'bx-message-rounded-dots',
            'tag' => 'Kolaborasi',
        ],
        [
            'title' => 'Data Alumni & Peta Sebaran',
            'description' => 'Riwayat alumni dan visualisasi sebaran peserta hingga wilayah desa atau kelurahan untuk mendukung analisis pemerataan.',
            'icon' => 'bx-map-alt',
            'tag' => 'Alumni',
        ],
        [
            'title' => 'Aset, Ruangan & Agenda',
            'description' => 'Kelola fasilitas dan foto aset, cek benturan pemakaian ruangan, susun agenda bidang atau pimpinan, dan tampilkan agenda publik.',
            'icon' => 'bx-building-house',
            'tag' => 'Fasilitas',
        ],
    ];
@endphp

<div class="row g-4 mt-2">
    @foreach($features as $index => $feature)
        <div class="col-md-6 col-lg-4 animate__animated animate__fadeInUp" style="animation-delay: {{ ($index % 3) * 0.08 }}s">
            <div class="feature-card h-100 position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div class="icon-box mb-0"><i class="bx {{ $feature['icon'] }}"></i></div>
                    <span class="badge bg-label-primary rounded-pill">{{ $feature['tag'] }}</span>
                </div>
                <h4 class="fw-bold text-dark">{{ $feature['title'] }}</h4>
                <p class="text-muted mb-0">{{ $feature['description'] }}</p>
            </div>
        </div>
    @endforeach
</div>
