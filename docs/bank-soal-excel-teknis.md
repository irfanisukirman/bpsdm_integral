# Bank soal evaluasi Teknis

Katalog `database/data/technical_questions_2026_11_15.json` disesuaikan dengan header sheet Jawaban pada Rekap Evaluasi Level TEKNIS.xlsx. SHA-256 file sumber tercatat di katalog.

74 soal aktif: mandiri 28, atasan 23, rekan 23. Delapan kolom profil PNS/non-ASN yang sama digabung; kolom pemilihan alumni tidak menjadi soal karena sudah tersedia pada formulir publik. Pertanyaan yang sama pada perspektif berbeda tetap dipertahankan. Skala Teknis tetap lima pilihan Sangat Kurang sampai Sangat Baik.

Pratinjau: `php artisan questions:sync-technical-excel`. Terapkan: tambahkan `--apply`. Sinkronisasi menyimpan cadangan privat, mempertahankan ID soal yang cocok, dan menonaktifkan soal lama/duplikat tanpa menghapus jawaban historis. Bank program lain, Level 1, dan monitoring tidak disentuh. Soal Teknis dibagikan ke Semua Bidang.

Sinkronisasi bank soal tidak mengimpor data peserta atau jawaban dari workbook.
