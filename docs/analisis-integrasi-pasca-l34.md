# Analisis integrasi hasil evaluasi pasca Level 3 dan 4

Tanggal: 11 November 2026. Analisis source F:\pasca dan F:\integral; belum mengubah fitur atau database.

## Kesimpulan

Pola Pasca dapat dipindahkan menjadi satu modul di Integral. Peserta dan jawaban survei dibaca langsung dari Integral, tanpa ekspor-impor workbook. Tidak disarankan menggunakan database peserta kedua atau menyimulasikan sel Excel sebagai sumber utama. Ini adalah usulan integrasi dalam aplikasi Integral, bukan sinkronisasi dua aplikasi yang berjalan terpisah.

Satu menu utama: **Hasil Evaluasi Pasca Level 3 & 4**. Di dalamnya daftar pelatihan dan ruang hasil per pelatihan dengan tab Ringkasan, Jawaban, Peserta & Progres, Wawancara, Ekspose, serta Pengaturan. Tim dan bukti pendukung ditempatkan di dalam ruang pelatihan, bukan menu utama tambahan.

## Temuan source Pasca

- routes/web.php: kegiatan, survei, tim, wawancara, ekspose, dan laporan berada di middleware auth. Link publik yang tersedia adalah unggah-inovasi dan verifikasi pesertanya; hasil/ekspose publik belum tersedia pada route yang diperiksa.
- app/Services/Evaluation.php: membaca survei dari sheet Jawaban pada pasangan kolom AN/AO, AP/AQ, AR/AS. Tiga indikator adalah jangka panjang, ketercapaian tujuan, manfaat aksi perubahan.
- Evaluation::surveyScore(): Tidak Sesuai=1, Kurang Sesuai=2, Sesuai=3, Sangat Sesuai=4. Rata-rata survei dikali 25 untuk skala 100.
- Evaluation::calculate(): rata-rata wawancara untuk perbandingan memakai kriteria indeks 1,2,3 (jangka panjang, tujuan, manfaat). Nilai berbobot lima kriteria adalah metrik terpisah, bukan pengganti rata-rata tersebut.
- Lima kriteria wawancara: target jangka menengah, target jangka panjang, ketercapaian tujuan, manfaat aksi perubahan, teknik komunikasi. Bobot 20/20/20/30/10. Nilai berbobot hanya dihitung setelah kelimanya terisi.
- app/Services/PresentationReport.php: grafik, narasi, kesimpulan, dan rekomendasi berbasis data tanpa API eksternal; hasil dapat diekspor PDF/presentasi. Kategori histogram <=60, >60 sampai 79, >79 sampai 89, >89. Implementasi memakai batas 79/89; label rentang bilangan bulat perlu diperjelas jika ada nilai desimal.
- app/Services/SurveyWorkbook.php dan SurveyController.php masih bergantung pada workbook tersimpan untuk ringkasan dan rincian sheet.
- Wawancara memiliki tim, pembagian peserta, draft/completed, dan revision untuk mencegah penimpaan perubahan bersamaan.
- PublicInnovationController.php menyediakan unggah dokumen inovasi dan bukti capaian jangka menengah/panjang; ini berbeda dari link publik untuk membaca hasil.

## Sumber data Integral

| Kebutuhan | Sumber langsung | Penyesuaian |
|---|---|---|
| Kegiatan | trainings | Gunakan training_id, tidak perlu activities kedua |
| Peserta | participants + user terkait | Gunakan participant_id, bukan pencocokan nama |
| Pertanyaan | evaluation_questions / Question::forTraining | Ikuti program, metode, bidang, dan peran yang berlaku |
| Jawaban dan status | evaluation_results_l34 | Pertahankan pemisahan mandiri, atasan, rekan |
| Profil sebelum/sesudah | alumni_profiles | Hubungkan ke peserta yang sama |
| Nilai wawancara | Belum ada padanan modul Pasca yang ditemukan dalam alur hasil Integral yang ditinjau | Tambahkan penyimpanan tersendiri terkait training_id + participant_id |
| Ekspose | Hasil perhitungan layanan bersama | HTML/presentasi/PDF harus memakai perhitungan yang sama |

## Hal yang harus diselesaikan sebelum nilai disamakan

1. Pemetaan indikator harus eksplisit per program dan peran, melalui ID pertanyaan stabil atau kode indikator. Jangan mengambil pertanyaan berdasarkan nomor kolom Excel, urutan import, atau dugaan kata pada teks.
2. Pertanyaan pilihan 1-4 bisa mengikuti pemetaan Pasca jika labelnya sesuai rubrik. Slider 10-100 tidak otomatis dibagi 25; skala dan metriknya harus ditetapkan. Teks, checkbox, dan pilihan tanpa rubrik numerik tidak otomatis menjadi skor.
3. Tentukan sumber tiga indikator survei: mandiri, atasan, atau gabungan dengan bobot yang jelas. Pasca membaca kolom khusus dalam workbook; source itu tidak cukup membuktikan bahwa semua peran boleh dirata-ratakan.
4. Kosong tetap kosong; bedakan belum mengisi, jawaban kualitatif, belum dipetakan, dan skor nol. Tampilkan cakupan indikator serta jumlah responden per peran.
5. Pertahankan LATSAR, PKP, PKA, PKN, Teknis sebagai program terpisah. LATSAR/PKP/PKA/PKN memakai mandiri+atasan, Teknis menambah rekan. Rubrik aksi perubahan Pasca tidak otomatis cocok untuk seluruh program; konfigurasi indikator wawancara harus menyesuaikan program.
6. Ambang kategori Integral saat ini berbeda dari Pasca (contoh skor 90 dan 80). Modul hasil baru harus memakai aturan yang jelas dan seragam antara layar, ekspose, dan PDF; jangan diam-diam mengubah laporan lama.
7. Nilai survei tidak mengisi nilai wawancara. Perbandingan keduanya baru lengkap setelah tim melakukan penilaian wawancara.

## Pola akses yang diusulkan

- Superadmin: semua pelatihan, pengaturan rubrik/pemetaan, pengelolaan hasil dan publikasi.
- Admin bidang: hasil pelatihan bidangnya, pembagian tim dan operasional sesuai kewenangan yang ditetapkan; tidak memperoleh akses kembali ke Kelola Instrumen.
- Tim penilai: hanya peserta tugasnya; kredensial dan izin gunakan akun Integral agar tidak membuat sistem login kedua.
- Publik: pertahankan gateway/form pengisian yang sudah ada; tambah link hasil agregat/ekspose terpisah jika diaktifkan pengelola. Jangan menyertakan NIP, identitas pribadi, jawaban individu, atau catatan wawancara pada hasil publik.
- Link hasil publik dapat dinonaktifkan/diganti; gunakan token acak per pelatihan atau snapshot publikasi. Publikasi final sebaiknya memakai snapshot agar hasil rapat dapat ditelusuri meskipun jawaban baru masuk.

## Rute usulan, belum dibuat

- /hasil-evaluasi-pasca : satu menu daftar pelatihan.
- /hasil-evaluasi-pasca/{training} : tab hasil, progres, jawaban dan pengaturan.
- /hasil-evaluasi-pasca/{training}/wawancara : ruang kerja tim.
- /hasil-evaluasi-pasca/{training}/ekspose : tampilan ekspose internal.
- /hasil-evaluasi-pasca/{training}/laporan : unduhan PDF.
- /hasil-pasca/{token} : hasil agregat publik yang diaktifkan pengelola.
- /hasil-pasca/{token}/ekspose : presentasi publik dengan data yang diizinkan.
- /evaluasi-dampak/gateway/{training_id} dan form/store terkait tetap menjadi jalur pengumpulan jawaban.

## Tahapan implementasi yang diusulkan

1. Bangun layanan pembacaan hasil langsung dan pemetaan indikator per program, lalu satu menu hasil dengan ringkasan, jawaban, peserta, dan progres.
2. Port pola tim, wawancara, rubrik, status, penyimpanan perubahan dan dokumen bukti dari Pasca dengan relasi ke peserta Integral.
3. Port ekspose, grafik perbandingan, narasi, kesimpulan, rekomendasi, dan PDF dengan sumber perhitungan bersama.
4. Tambah publikasi/link hasil dan ekspose publik, serta uji pembatasan data.

Pengujian wajib: hasil rubrik sama dengan contoh Pasca; pemisahan program/peran; nol vs kosong; tidak ada peserta ganda; jawaban baru langsung terbaca; nilai wawancara tidak tertimpa perubahan survei; otorisasi bidang/tim; pencabutan link publik; konsistensi angka layar dan PDF.

## Batas analisis

Analisis dilakukan terhadap source. Tidak menjalankan aplikasi Pasca, tidak membaca rahasia .env, tidak mengubah F:\pasca, dan tidak menyalin atau mengimpor data produksi. Belum ada implementasi menu atau link baru. Kesesuaian angka pada workbook nyata perlu diuji pada tahap implementasi menggunakan contoh data acuan.
