@php
    $features = [
        ['title' => 'Kelola Pelatihan', 'description' => 'Mengelola peserta, jadwal JP/OJ, pengajar, kelengkapan, forum, ID card, sertifikat, dan laporan kegiatan dalam satu pusat kendali.', 'icon' => 'bx-book-open', 'tag' => 'Pelatihan'],
        ['title' => 'Evaluasi & Analitik Dampak', 'description' => 'Evaluasi Level 1 sampai Level 4, dashboard grafik, laporan 360 derajat, dan rangkuman berbantuan AI untuk pengambilan keputusan.', 'icon' => 'bx-analyse', 'tag' => 'L1–L4'],
        ['title' => 'Presensi Terpadu', 'description' => 'Presensi peserta pelatihan dan formulir kegiatan publik dengan statistik respons, ekspor data, tanda tangan, dan lampiran digital.', 'icon' => 'bx-check-square', 'tag' => 'Presensi'],
        ['title' => 'Monitoring & Tindak Lanjut', 'description' => 'Instrumen monitoring, rekomendasi perbaikan, status penyelesaian, dashboard pengajar, serta jadwal harian lintas bidang.', 'icon' => 'bx-shield-quarter', 'tag' => 'Monitoring'],
        ['title' => 'Dokumen & Manajemen Mutu', 'description' => 'Arsip dokumen per tahun, kelengkapan penyelenggaraan, progres dokumen mutu, dan laporan kegiatan otomatis dari data pelatihan.', 'icon' => 'bx-folder-open', 'tag' => 'Mutu'],
        ['title' => 'Sertifikat & Tanda Tangan Elektronik', 'description' => 'Generate sertifikat dari template, alur TTE BSrE, QR verifikasi, bundel ZIP, dan distribusi sertifikat ke akun peserta.', 'icon' => 'bx-certification', 'tag' => 'TTE'],
        ['title' => 'Ruang Kerja Pengajar', 'description' => 'Pengajar melihat jadwal dan beban JP/OJ, melengkapi profil, mengunggah materi, serta memantau seluruh agenda mengajar.', 'icon' => 'bx-chalkboard', 'tag' => 'Pengajar'],
        ['title' => 'Alumni & Peta Sebaran', 'description' => 'Riwayat alumni, frekuensi mengikuti pelatihan, dan visualisasi sebaran peserta hingga wilayah desa atau kelurahan.', 'icon' => 'bx-map-alt', 'tag' => 'Alumni'],
        ['title' => 'Aset, Reservasi & Agenda', 'description' => 'Persetujuan pemakaian aset internal, reservasi fasilitas publik bertarif, monitoring jadwal, dan agenda organisasi.', 'icon' => 'bx-building-house', 'tag' => 'Fasilitas'],
        ['title' => 'Magang, PKL & Buku Tamu', 'description' => 'Pendaftaran dan presensi magang berbasis selfie, sertifikat peserta, serta buku tamu publik dengan QR dan monitoring kunjungan.', 'icon' => 'bx-id-card', 'tag' => 'Layanan Publik'],
        ['title' => 'Keuangan Bidang', 'description' => 'Pengelolaan struktur anggaran, kodering, GU, prognosis, realisasi, sisa anggaran, dan persentase penyerapan setiap bidang.', 'icon' => 'bx-wallet', 'tag' => 'Keuangan'],
        ['title' => 'Kerja Sama & Sertifikasi', 'description' => 'Rekap dokumen kerja sama, kegiatan sertifikasi, biodata narasumber, evaluasi peserta, dan pengumpulan sertifikat publik.', 'icon' => 'bx-handshake', 'tag' => 'Kelembagaan'],
        ['title' => 'Laporan Harian PPPK-PW', 'description' => 'Pencatatan kegiatan harian, monitoring Kasubag, dan rekap PDF bulanan dengan identitas serta tanda tangan pegawai dan atasan.', 'icon' => 'bx-notepad', 'tag' => 'Pelaporan'],
        ['title' => 'Asisten AI INTEGRAL', 'description' => 'Pencarian informasi dan penyusunan rangkuman evaluasi untuk membantu pengelola membaca data dengan lebih cepat.', 'icon' => 'bx-bot', 'tag' => 'AI'],
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
