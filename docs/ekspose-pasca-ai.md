# Ekspose pasca dan narasi

Ekspose Integral berisi sembilan slide 16:9. Grafik dan rubrik mengikuti Pasca terbaru: 0-25%, >25-50%, >50-75%, >75-100%. Data kosong bukan nilai nol. Rata-rata ekspose memakai tiga indikator terisi, sedangkan nilai berbobot memakai lima kriteria.

## Alur ringan tanpa API (19 November 2026)

1. Buka Hasil Evaluasi Pasca, pilih pelatihan, lalu Prompt ChatGPT & Edit Narasi. Tombol yang sama tersedia pada toolbar ekspose.
2. Prompt berisi metadata pelatihan, lima grafik, data terisi/kosong/parsial, rekap YA/TIDAK, serta catatan evaluator tersamarkan. Catatan tambahan opsional langsung memperbarui prompt di browser.
3. Salin Prompt + Data, buka ChatGPT, lalu tempel prompt. Aplikasi tidak mengirim prompt secara otomatis. Clipboard yang tidak tersedia memakai pilihan teks untuk Ctrl+C.
4. Tempel hasil pada pembahasan slide 4-8, kesimpulan, dan rekomendasi slide 9. Penghitung kata membantu membatasi panjang sekitar 120 kata; batas penyimpanan 1600 karakter per kolom.
5. Simpan dan Pratinjau menerapkan narasi pada ekspose dan PDF. Kolom kosong memakai ringkasan dasar berbasis angka. Ringkasan dasar dan prompt dibuat lokal, tanpa API.
6. Jika sumber berubah, ekspose/PDF menandai narasi agar ditinjau. Penyimpanan dari formulir dengan sidik sumber lama ditolak dengan mempertahankan input. Muat ulang prompt terbaru setelah menyalin draf.
7. Kembali ke Ringkasan Dasar menghapus hanya narasi manual dan catatan tambahan, bukan wawancara, survei, tim, atau dokumen.

Narasi manual disimpan per pelatihan pada post_evaluation_settings.report_narrative, termasuk sidik sumber, waktu, dan penyimpan. Hanya superadmin yang dapat menyiapkan prompt, menyimpan, atau menghapus narasi. Hak evaluator tetap terbatas ke ringkasan dan wawancara timnya.

## Gemini opsional

Generate Gemini dijalankan hanya saat tombol diklik. Tidak ada fetch otomatis saat membuka ringkasan atau ekspose. Halaman dan ekspor PDF tidak menunggu layanan AI. Hasil Gemini yang masih cocok dengan data dapat dipakai dari cache; narasi manual yang sudah ditinjau diprioritaskan.

Alur API lama tetap tersedia untuk kompatibilitas, dengan CSRF, otorisasi, throttle, lock per pelatihan, fingerprint data, dan penanganan kegagalan. API key tidak ditampilkan. Pengujian menggunakan respons palsu; pekerjaan implementasi ini tidak memanggil provider AI nyata.

## Data dan privasi

Prompt memakai nomor peserta anonim dan menyamarkan nama, NIP, NIK, email serta nomor panjang pada catatan. Dokumen dan isi Proper tidak disertakan. Catatan draf tetap dinyatakan sementara; temuan tidak digeneralisasi, nilai kosong bukan bukti ketidakhadiran. Catatan tambahan perlu ditinjau pengguna sebelum disalin ke layanan luar.

Catatan mentah dan catatan tambahan tidak masuk snapshot publik; hanya narasi yang diterapkan. Snapshot lama tetap stabil sampai dipublikasikan ulang melalui endpoint yang sudah ada. UI publikasi tetap tidak ditampilkan.
