# Dashboard Evaluasi Level 3 dan 4

Halaman `/trainings/{id}/evaluasi-l34/dashboard` tersedia untuk Superadmin dan Admin Bidang yang bidangnya sesuai. Hak akses tambahan mengikuti peran aktif akun. Perhitungan memakai peserta berstatus approved dan pertanyaan aktif yang berlaku untuk program/peran pelatihan. Tidak ada perubahan jawaban atau data peserta saat membuka dashboard.

Dashboard memuat sasaran alumni, kelengkapan seluruh peran, indeks Level 3/4, progres tiap sumber penilai, perbandingan hasil, sebaran dampak per alumni, rincian indikator dengan n jawaban tiap peran, rekomendasi berbasis data, serta daftar alumni yang bisa dicari/difilter untuk tindak lanjut. Daftar alumni ditampilkan 10 baris per halaman. Indikator terbawah di grafik dibatasi enam butir agar terbaca; rincian seluruh indikator tetap tersedia.

Satu respons pada soal profil tidak berarti selesai. Status mulai mengisi berarti ada respons pada instrumen aktif; lengkap berarti seluruh pertanyaan skala aktif per peran mempunyai nilai valid. Isian teks opsional tidak menjadi syarat kelengkapan skala. Instrumen peran yang belum tersedia ditandai, bukan dihilangkan dari target pasangan alumni-penilai. Peserta pending/rejected tidak menjadi denominator.

Skala Tidak Sesuai/Kurang Sesuai/Sesuai/Sangat Sesuai dikonversi 25/50/75/100. Pilihan angka 1-4 lama juga dikonversi. Slider memakai angka asli 0-100 termasuk nol. Teks, profil, dropdown Ya/Tidak dan nilai di luar rentang tidak membentuk indeks. Pertanyaan/jawaban versi ganda dengan judul, bagian dan nomor kemunculan yang sama dihitung sekali, menggunakan respons terakhir. Hasil dengan evaluator_role tidak sesuai kategori pertanyaan diabaikan.

Rerata indikator adalah rata-rata jawaban valid yang tersedia; indeks tiap level adalah rata-rata indikator terisi pada bagian Perubahan Sikap Perilaku/Perubahan Perilaku atau Dampak Pelatihan. Bagian dukungan, hambatan dan Policy Brief disajikan terpisah. Rerata antar-penilai dihitung per bagian dari rerata indikator masing-masing peran. Distribusi dampak menghitung setiap alumni satu kali berdasarkan rerata jawaban Level 4 yang tersedia. Alumni tanpa nilai Level 4 ditampilkan sebagai Belum dinilai. Skor tinggi untuk hambatan berarti hambatan lebih kuat.

Kategori selaras dengan hasil pasca: Kurang <=60, Cukup >60 sampai79, Baik >79 sampai89, Sangat Baik >89 sampai100. Acuan 80 hanya referensi pemantauan, bukan aturan kelulusan atau pernyataan dampak sebab-akibat.

Tombol analisis AI tetap dijalankan secara eksplisit oleh pengelola. Payload menggunakan agregat dari layanan perhitungan dashboard yang sama; identitas, NIP, dan catatan alumni tidak dikirim. Analisis lama yang source_hash-nya tidak sama dengan agregat sekarang disembunyikan dan diberi penanda perlu diperbarui. Dashboard tidak memanggil AI secara otomatis. Grafik dan gaya dimuat melalui stack css/js layout utama. Menu Cetak menampilkan rincian tabel yang sebelumnya terlipat.
