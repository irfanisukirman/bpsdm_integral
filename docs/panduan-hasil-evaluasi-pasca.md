# Hasil Evaluasi Pasca Level 3 & 4

Modul Integral mengikuti pola alur survei, wawancara, tim, bukti dan ekspose dari F:/pasca. Tidak membutuhkan import workbook hasil atau database peserta kedua. Source F:/pasca tidak diubah.

## Membuka modul

Menu **Hasil Evaluasi Pasca Level 3 & 4** atau `/hasil-evaluasi-pasca`. Tersedia juga dari tab Evaluasi di kelola pelatihan. Superadmin mengelola seluruh pelatihan. Admin bidang tidak memiliki menu atau akses modul ini, termasuk jika tercatat sebagai anggota tim. Anggota tim selain admin bidang hanya mengakses pelatihan dan peserta tugasnya.

## Memulai per pelatihan

1. Pastikan peserta disetujui dan pertanyaan sudah sesuai LATSAR, PKP, PKA, PKN atau Teknis.
2. Jawaban dari link evaluasi publik yang sudah ada langsung terbaca di tab Jawaban, tanpa import ulang. Tab peserta memperlihatkan progres per penilai. Tabel memiliki pencarian dan paginasi.
3. Ringkasan mengambil jawaban langsung dari database. Urutannya perubahan jabatan, metode pelatihan, perbandingan alumni dan atasan, lalu sebaran instansi. Kelompok indikator mengikuti bagian pada bank soal program tersebut; tidak ada pemetaan manual.
4. Untuk LATSAR, PKP, PKA dan PKN, Tidak Sesuai=1, Kurang Sesuai=2, Sesuai=3, Sangat Sesuai=4; konversi 100 dikali 25. Tiga indikator survei pembanding wawancara/ekspose diambil otomatis berdasarkan judul pertanyaan jangka panjang, tujuan dan manfaat. Nilai pembanding menggunakan jawaban alumni saja; grafik ringkasan memisahkan alumni dan atasan. Teknis menggunakan slider 0-100.
5. Penilaian wawancara tetap menggunakan lima kriteria dan bobot yang tersimpan (awal 20/20/20/30/10); pengaturannya tidak ditampilkan pada tab Pengaturan. Urutan kriteria kedua sampai keempat menjadi pembanding tiga indikator survei.
6. Tambah tim menggunakan akun Integral. Pada tab Wawancara centang peserta, pilih tim dan simpan pembagian. Tidak perlu membuat akun/login Pasca kedua.
7. Buka wawancara peserta untuk membaca Proper/target/kondisi dari alumni, lalu isi catatan dan nilai tim. Perubahan disimpan otomatis setelah jeda mengetik. Tombol Simpan tetap tersedia. Status selesai mensyaratkan kelima nilai. Konflik revisi menghentikan simpan otomatis agar catatan dari perangkat lain tidak tertimpa; salin isian sebelum memuat ulang.
8. Pengisian Proper dan semua dokumen dipusatkan pada satu link publik pelatihan berbasis UUID. Alumni memverifikasi NIP/NIK sebelum mengisi. Di wawancara, tim hanya membaca data dan mengunduh dokumen; tidak ada upload atau pembuatan link per peserta.
9. Buka Ekspose untuk presentasi sembilan bagian, kesimpulan, rekomendasi dan PDF. Hasil internal selalu memakai data terkini sesuai cakupan akses. Excel survei menggunakan ekspor evaluasi Integral yang sudah ada.

## Rumus dan cakupan

- Rerata survei keseluruhan = rata-rata indikator jangka panjang, tujuan, manfaat yang terisi, skala 100.
- Rerata wawancara pembanding = rata-rata kriteria 2, 3, 4 yang terisi.
- Nilai berbobot = jumlah nilai lima kriteria dikali bobot; tersedia hanya bila kelima nilai terisi. Bobot awal 20/20/20/30/10 seperti Pasca.
- Kosong bukan nol. Nilai nol tetap dihitung. Rerata parsial ditandai, dan jumlah terisi/kosong diperlihatkan pada grafik.
- Kategori mengikuti batas perhitungan Pasca: <=60, >60 sampai 79, >79 sampai 89, >89 sampai 100. Label memakai batas ini agar nilai desimal tidak ambigu.
- Survei dan wawancara tetap dua sumber yang berbeda. Perbandingannya bukan bukti sebab-akibat atau perubahan dampak dari waktu ke waktu.
- Survei baru tidak menimpa nilai wawancara, pembagian tim atau dokumen. ID peserta Integral menjadi kunci, bukan nama/NIP dari spreadsheet.
- Sumber pertanyaan otomatis dibatasi program, bidang, metode dan peran pelatihan. Versi pertanyaan duplikat dihitung satu kali menggunakan jawaban terakhir per peserta dan peran.

## Link hasil publik

Ringkasan > Publikasikan Hasil membuat link hasil dan ekspose agregat. Belum ada hasil yang diterbitkan otomatis. Publikasi menyimpan snapshot yang hanya berisi metadata pelatihan, angka, grafik, narasi dan rekomendasi. Identitas, NIP, jawaban individu, catatan dan dokumen tidak disertakan.

Hasil publik tidak berubah hanya karena jawaban/wawancara baru masuk. Klik Perbarui Hasil Publik untuk menerbitkan angka terkini. Ganti Link mengganti token dan menonaktifkan tautan sebelumnya; Nonaktifkan Link mencabut akses publik. PDF publik memakai snapshot yang sama. Link pengisian evaluasi tetap terpisah dan tidak berubah.

## Penyimpanan dan pengujian

Migrasi `2026_11_11_140000_create_post_evaluation_module.php` menambah tabel pengaturan, tim/anggota, wawancara dan bukti dengan relasi ke training/participant/user Integral. Migrasi sudah dijalankan pada database lokal sesi ini.

Feature test `PostEvaluationTest` memeriksa pembacaan langsung, pemisahan program/peran, sumber otomatis/skala dan ringkasan, nol vs kosong, rumus Pasca, seluruh tab, PDF, akses tim, penyimpanan otomatis/revisi, snapshot anonim dan pencabutan link, serta dokumen privat dan penolakan URL publik berbasis angka.

## Pengisian Proper melalui link publik

Tombol **Link Pengisian Proper** pada halaman hasil menuju `/isi-proper/{UID-pelatihan}`. Link ini berbeda dari survei evaluasi L3/L4. Alumni memilih pelatihan melalui link yang dibagikan, memasukkan NIP/NIK, lalu mengisi judul inovasi/Proper, milestone menengah dan panjang, status pelaksanaan setiap periode, kondisi terkini dan dokumen Proper. Verifikasi hanya menerima peserta disetujui pada pelatihan tersebut.

Foto JPG/PNG/WebP maksimal 10 MB wajib untuk periode berstatus sudah dilaksanakan. Dokumen Proper mendukung PDF, Word, PowerPoint, Excel, ZIP dan RAR, dengan aturan aplikasi maksimal 100 MB serta mengikuti batas upload server. Pengiriman ulang mengganti dokumen/data Proper; memilih belum dilaksanakan menghapus foto evidence periode itu. Form publik tidak menampilkan catatan atau nilai wawancara.

Data Proper tersedia pada form wawancara, dengan status pengisian di daftar peserta. Pengiriman alumni menaikkan revisi data sehingga sesi wawancara yang masih terbuka mendeteksi perubahan; nilai, status penilaian, catatan tim dan penugasannya tetap tersimpan. Tidak ada link pengisian per alumni. Link angka lama `/isi-proper/13` dan endpoint unggah per peserta tidak lagi tersedia; salin link UID terbaru dari halaman hasil.

Pembaruan 15 November 2026: pengisian Proper hanya melalui satu URL UUID per pelatihan. UUID pelatihan lama diisi melalui migrasi dan pelatihan baru mendapat UUID otomatis. Halaman internal tetap memakai ID biasa. Form wawancara hanya membaca detail Proper dan dokumen; request simpan nilai tidak dapat mengubah detail Proper. Semua rute pembuatan link per peserta dan upload dari wawancara sudah dihapus. Dokumen lama tetap tersimpan dan dapat diunduh sesuai hak akses.

## Tim dan akun evaluator

Tab Pengaturan hanya berisi operasional tim: pilih evaluator dari akun yang sudah ada, daftarkan evaluator baru, dan bagi peserta ke tim. Publikasi hasil tersedia pada Ringkasan. Indikator survei dibaca otomatis tanpa pengaturan pemetaan. Tidak ada editor rubrik pada Pengaturan.

Untuk akun baru, masukkan nama evaluator, email login, password minimal 8 karakter dan konfirmasi password. Dapat langsung ditambahkan ke tim yang sudah ada atau dipilih saat membuat tim berikutnya. Mengikuti struktur akun Integral, email login disimpan sebagai username unik; password disimpan sebagai hash. Tidak ada email yang dikirim otomatis. Evaluator login di halaman login biasa menggunakan email/password, lalu diarahkan ke daftar hasil pasca dan hanya melihat pelatihan/peserta tugasnya.

Akun yang sudah ada dipilih dari daftar saat membuat tim tanpa membuat akun duplikat, mengubah peran, atau mengganti passwordnya. Admin bidang tetap tidak mendapat akses ke modul ini dan tidak tersedia untuk dipilih sebagai evaluator. Hanya superadmin yang dapat mendaftarkan evaluator serta mengelola tim.

## Tampilan wawancara mengikuti Pasca

Bagian atas memuat informasi wawancara, nama penilai, nama aksi perubahan serta judul/kondisi Proper dari alumni. Dokumen Proper dapat dilihat atau diunduh di sana. Jawaban survei menampilkan tiga pertanyaan jangka panjang, tujuan dan manfaat yang judulnya sesuai bank soal, langsung dari evaluasi Integral. Jawaban dan sumber penilai ditampilkan terpisah. Skala empat tingkat hanya untuk LATSAR, PKP, PKA dan PKN. Slider Teknis tetap memakai skor asli 0-100.

Milestone dan status/foto evidence jangka menengah ditempatkan pada kriteria pertama. Data jangka panjang ditempatkan pada kriteria kedua. Lima kriteria tetap memiliki nilai wawancara, catatan hasil/bukti, bobot dan keterangan rentang nilai. Seluruh referensi Proper berasal dari link publik terpusat, bukan kolom upload atau isian evaluator. Autosave, pemeriksaan revisi, serta hak akses dokumen tetap berlaku.

### Urutan pengaturan tim melalui popup

Halaman Pengaturan menampilkan tabel tim dan tombol tindakan, tanpa deretan form panjang. Mulai dari **Buat Nama Tim** (nama saja), kemudian **Tambah Evaluator dari User** untuk mencari/menandai akun database yang sudah ada, atau **Buat Akun Evaluator** untuk mendaftarkan akun baru langsung ke tim. Setelah itu gunakan **Bagi Peserta ke Tim**. Tombol penambahan evaluator dan pembagian peserta aktif setelah ada tim. Memilih user yang sudah menjadi anggota tidak menggandakan keanggotaannya. Jika validasi gagal, popup terkait dibuka kembali; password tidak diisi ulang.

### Pembagian peserta otomatis

Pada Pengaturan, klik **Bagi Peserta Otomatis** setelah tim dan evaluator tersedia. Sistem membagi peserta berstatus disetujui yang belum memiliki tim, dengan selalu memilih tim yang bebannya paling sedikit. Tim tanpa evaluator tidak menerima peserta. Pembagian yang sudah ada tidak diganti dan seluruh nilai, catatan, dokumen serta Proper tetap tersimpan. Klik kembali untuk membagi peserta baru; pengulangan tidak menggandakan data atau mengubah pembagian lama. Penyesuaian manual tetap dapat dilakukan dari tab Wawancara bila diperlukan.

### Mengganti atau memindahkan evaluator

Pada tabel tim, klik **Kelola** di samping nama evaluator. Popup menyediakan pilihan tim tujuan atau **Keluarkan dari tim saat ini**. Untuk mengganti evaluator, keluarkan anggota lama kemudian gunakan Tambah Evaluator dari User atau Buat Akun Evaluator. Pemindahan hanya berlaku antartim di pelatihan yang sama; akun pengguna tidak dihapus. Peserta, dokumen, nilai dan catatan tetap pada tim semula. Evaluator berpindah hak akses sesuai tim tujuannya, kecuali hak lain yang memang melekat pada peran akunnya.

## Import hasil evaluasi historis

Superadmin membuka pelatihan tujuan > Import Data. Daftarkan/setujui peserta melalui menu peserta pelatihan terlebih dahulu. Unggah XLSX pola Pasca dengan sheet Jawaban dan Data Peserta; Informasi Umum digunakan untuk memeriksa program. Nama Peserta pada Data Peserta harus menyertakan NIP/NIK 16 atau 18 digit, misalnya Nama Alumni | NIP. Nama penilaian atasan tanpa NIP hanya digunakan untuk mencari NIP pada roster workbook yang unik, bukan untuk mencocokkan akun database berdasarkan nama.

Klik Periksa File untuk melihat peserta cocok, pertanyaan yang cocok, jawaban baru, jawaban sama dan jawaban berbeda yang akan dilewati. Judul pertanyaan dibaca tanpa nomor urut/markup, dengan pemisahan peran dan pembatasan program/bidang bank soal. Kolom kosong tidak dianggap nilai nol. Pilihan empat tingkat dikonversi sesuai program dan opsi soal; slider tetap skala 0-100. Sistem menolak peserta belum terdaftar/disetujui, NIP ganda, program berbeda, soal tak dikenal/ambigu dan nilai di luar pilihan/rentang.

Pratinjau belum menyimpan jawaban evaluasi. Setelah seluruh masalah diperbaiki, periksa file kembali, centang konfirmasi pelatihan dan klik Simpan Hasil Import. Import hanya mengisi jawaban kosong; jawaban terisi tetap dipertahankan. Peserta, akun, bank soal, Proper, tim, penilaian wawancara dan snapshot hasil publik tidak diubah. Hasil masuk evaluation_results_l34 dan langsung digunakan tab Jawaban, progres, ringkasan serta ekspose internal. AI yang sudah tidak sesuai data perlu digenerate ulang; hasil publik perlu dipublikasikan ulang secara eksplisit.

Workbook disimpan pada disk privat dan pratinjau hanya dapat dibuka/disimpan oleh superadmin pengunggah untuk pelatihan yang sama. Pratinjau berlaku 24 jam. Jika data berubah sebelum konfirmasi, sistem meminta pemeriksaan ulang. Riwayat import menyimpan waktu, pengunggah dan hasil pencocokan dalam post_evaluation_imports; import ulang tidak menggandakan peserta atau menimpa nilai. Batas file 20 MB, isi ZIP 100 MB, 5.000 peserta, 300 kolom dan total 250.000 sel pada sheet sumber.
