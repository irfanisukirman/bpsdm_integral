# Hasil Evaluasi Pasca Level 3 & 4

Modul Integral mengikuti pola alur survei, wawancara, tim, bukti dan ekspose dari F:/pasca. Tidak membutuhkan import workbook hasil atau database peserta kedua. Source F:/pasca tidak diubah.

## Membuka modul

Menu **Hasil Evaluasi Pasca Level 3 & 4** atau `/hasil-evaluasi-pasca`. Tersedia juga dari tab Evaluasi di kelola pelatihan. Superadmin mengelola seluruh pelatihan. Admin bidang tidak memiliki menu atau akses modul ini, termasuk jika tercatat sebagai anggota tim. Anggota tim selain admin bidang hanya mengakses pelatihan dan peserta tugasnya.

## Memulai per pelatihan

1. Pastikan peserta disetujui dan pertanyaan sudah sesuai LATSAR, PKP, PKA, PKN atau Teknis.
2. Jawaban dari link evaluasi publik yang sudah ada langsung terbaca di tab Jawaban, tanpa import ulang. Tab peserta memperlihatkan progres per penilai. Tabel memiliki pencarian dan paginasi.
3. Buka Pengaturan. Pilih pertanyaan untuk indikator jangka panjang, tujuan dan manfaat, beserta sumber penilainya. Tidak ada pemetaan berdasarkan dugaan teks pertanyaan. Pilihan pertanyaan dibatasi sesuai program, bidang, metode dan peran yang berlaku.
4. Skala pilihan empat tingkat: Tidak Sesuai=25, Kurang Sesuai=50, Sesuai=75, Sangat Sesuai=100. Alternatifnya pilihan angka 1-4. Slider memakai skor asli 0-100. Teks dan checkbox tidak diberi angka otomatis. Jika lebih dari satu peran dipetakan pada indikator, bobotnya sama; hanya nilai terisi yang dirata-ratakan dan kekurangannya ditandai parsial.
5. Sesuaikan lima nama kriteria wawancara, bobot (jumlah 100%), dan pedoman nilai per kriteria menurut program. Urutan kriteria kedua sampai keempat menjadi pembanding tiga indikator survei.
6. Tambah tim menggunakan akun Integral. Pada tab Wawancara centang peserta, pilih tim dan simpan pembagian. Tidak perlu membuat akun/login Pasca kedua.
7. Buka wawancara peserta, isi catatan, target, kondisi dan nilai. Perubahan disimpan otomatis setelah jeda mengetik. Tombol Simpan tetap tersedia. Status selesai mensyaratkan kelima nilai. Konflik revisi menghentikan simpan otomatis agar catatan dari perangkat lain tidak tertimpa; salin isian sebelum memuat ulang.
8. Tim atau pengelola dapat mengunggah PDF dokumen inovasi dan bukti jangka menengah/panjang (10 MB per dokumen). Berkas tersimpan privat. Pengelola juga dapat membuat link unggah bukti khusus per peserta, mengganti atau mencabutnya. Siapa pun yang menerima link ini dapat mengunggah bukti peserta tersebut, jadi bagikan hanya kepada alumni yang dimaksud. Link tidak memperlihatkan identitas atau dokumen peserta.
9. Buka Ekspose untuk presentasi sembilan bagian, kesimpulan, rekomendasi dan PDF. Hasil internal selalu memakai data terkini sesuai cakupan akses. Excel survei menggunakan ekspor evaluasi Integral yang sudah ada.

## Rumus dan cakupan

- Rerata survei keseluruhan = rata-rata indikator jangka panjang, tujuan, manfaat yang terisi, skala 100.
- Rerata wawancara pembanding = rata-rata kriteria 2, 3, 4 yang terisi.
- Nilai berbobot = jumlah nilai lima kriteria dikali bobot; tersedia hanya bila kelima nilai terisi. Bobot awal 20/20/20/30/10 seperti Pasca.
- Kosong bukan nol. Nilai nol tetap dihitung. Rerata parsial ditandai, dan jumlah terisi/kosong diperlihatkan pada grafik.
- Kategori mengikuti batas perhitungan Pasca: <=60, >60 sampai 79, >79 sampai 89, >89 sampai 100. Label memakai batas ini agar nilai desimal tidak ambigu.
- Survei dan wawancara tetap dua sumber yang berbeda. Perbandingannya bukan bukti sebab-akibat atau perubahan dampak dari waktu ke waktu.
- Survei baru tidak menimpa nilai wawancara, pembagian tim atau dokumen. ID peserta Integral menjadi kunci, bukan nama/NIP dari spreadsheet.
- Mengubah program pelatihan menyebabkan pemetaan pertanyaan yang tidak lagi berlaku diabaikan. Tinjau kembali pengaturan indikator setelah mengubah program.

## Link hasil publik

Pengaturan > Publikasikan Hasil membuat link hasil dan ekspose agregat. Belum ada hasil yang diterbitkan otomatis. Publikasi menyimpan snapshot yang hanya berisi metadata pelatihan, angka, grafik, narasi dan rekomendasi. Identitas, NIP, jawaban individu, catatan dan dokumen tidak disertakan.

Hasil publik tidak berubah hanya karena jawaban/wawancara baru masuk. Klik Perbarui Hasil Publik untuk menerbitkan angka terkini. Ganti Link mengganti token dan menonaktifkan tautan sebelumnya; Nonaktifkan Link mencabut akses publik. PDF publik memakai snapshot yang sama. Link pengisian evaluasi tetap terpisah dan tidak berubah.

## Penyimpanan dan pengujian

Migrasi `2026_11_11_140000_create_post_evaluation_module.php` menambah tabel pengaturan, tim/anggota, wawancara dan bukti dengan relasi ke training/participant/user Integral. Migrasi sudah dijalankan pada database lokal sesi ini.

Feature test `PostEvaluationTest` memeriksa pembacaan langsung, pemisahan program/peran, pemetaan/skala, nol vs kosong, rumus Pasca, seluruh tab, PDF, akses tim, penyimpanan otomatis/revisi, snapshot anonim dan pencabutan link, serta dokumen privat.

## Pengisian Proper melalui link publik

Tombol **Link Pengisian Proper** pada halaman hasil menuju `/isi-proper/{training}`. Link ini berbeda dari survei evaluasi L3/L4. Alumni memilih pelatihan melalui link yang dibagikan, memasukkan NIP/NIK, lalu mengisi judul inovasi/Proper, milestone menengah dan panjang, status pelaksanaan setiap periode, kondisi terkini dan dokumen Proper. Verifikasi hanya menerima peserta disetujui pada pelatihan tersebut.

Foto JPG/PNG/WebP maksimal 10 MB wajib untuk periode berstatus sudah dilaksanakan. Dokumen Proper mendukung PDF, Word, PowerPoint, Excel, ZIP dan RAR, dengan aturan aplikasi maksimal 100 MB serta mengikuti batas upload server. Pengiriman ulang mengganti dokumen/data Proper; memilih belum dilaksanakan menghapus foto evidence periode itu. Form publik tidak menampilkan catatan atau nilai wawancara.

Data Proper tersedia pada form wawancara, dengan status pengisian di daftar peserta. Pengiriman alumni menaikkan revisi data sehingga sesi wawancara yang masih terbuka mendeteksi perubahan; nilai, status penilaian, catatan tim dan penugasannya tetap tersimpan. Link per alumni dari tabel Wawancara juga membuka form Proper yang lengkap, tanpa perlu mengetik NIP/NIK. Link per alumni tetap dapat diganti atau dicabut.
