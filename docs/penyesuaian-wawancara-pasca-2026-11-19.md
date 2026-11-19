# Penyesuaian wawancara Pasca

Mengikuti F:/pasca commit 9110d04 (perbaikan wawancara).

- Grafik memakai empat kategori: 0-25%, >25-50%, >50-75%, >75-100%. Nol masuk kategori pertama; nilai kosong tidak dihitung sebagai nol.
- Rubrik dan keterangan nilai memakai rentang yang sama.
- Lima kriteria mempunyai konfirmasi YA/TIDAK/belum dikonfirmasi. YA membutuhkan persentase saat diselesaikan. TIDAK menjadi 0%, kecuali nilai tersimpan 0-25% yang tetap dipertahankan. Belum dikonfirmasi mengosongkan nilai.
- Rekap hanya merata-ratakan persentase dari jawaban YA yang terisi. Cakupan evaluator tetap berdasarkan penugasan tim.
- Rata-rata ekspose tetap tiga indikator (jangka panjang, tujuan, manfaat); nilai berbobot tetap lima kriteria.
- Rekap keberlanjutan masuk sumber narasi AI dan ringkasan ekspose. Perubahan konfirmasi membatalkan kecocokan cache narasi lama.

## Migrasi data lama

Migrasi 2026_11_19_090000 menambahkan continuation dan tabel post_evaluation_score_backups. Mengikuti konversi migrasi Pasca: nilai lama <=60 menjadi 25; <=79 menjadi 50; <=89 menjadi 75; sisanya menjadi 100. Nilai kosong tetap kosong. Ini juga berarti nilai nol lama termasuk kategori <=60 dan menjadi 25, sesuai migrasi sumber. Nilai baru nol tetap nol.

Nilai asli, hasil konversi, revisi asli, dan waktu asli dicadangkan di tabel sebelum konversi. Catatan, status penyelesaian, tim, dan dokumen Proper dipertahankan. Revisi dinaikkan untuk menolak penyimpanan dari formulir lama. Migrasi tidak mengonversi ulang baris yang sudah tercatat dalam backup. Rollback tidak otomatis mengembalikan nilai karena dapat menimpa perubahan evaluator setelah migrasi.

Backup tambahan sebelum migrasi tersedia secara privat di storage/app/private/post-evaluation-upgrade-backups. Data survei tetap bersumber dari database Integral, tidak mengambil data pribadi dari Pasca.
