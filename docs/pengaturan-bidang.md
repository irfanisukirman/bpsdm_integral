# Pengaturan bidang

Superadmin membuka Pengaturan Sistem > Kelola Bidang (`/pengaturan/bidang`). Klik Ganti Nama untuk membuka popup. Perubahan nama memperbarui semua kolom bidang/target_bidang serta training_type soal yang bernilai sama, dalam satu transaksi database. Identitas awal bidang disimpan agar hak akses sertifikasi, kerja sama dan pemilihan program tidak hilang ketika nama diganti.

Tombol Hapus hanya tersedia jika tidak ada akun atau data yang memakai bidang tersebut dan masih ada bidang lain. Penghapusan tidak menghapus akun, pelatihan, dokumen maupun transaksi. Jika bidang masih digunakan, pindahkan data terkait terlebih dahulu; penghapusan melalui request langsung juga ditolak.

Daftar pilihan pada akun, pelatihan, bank soal, buku tamu, keuangan dan pengajuan mitra membaca nama dari tabel bidang_units. Migrasi 2026_11_15_170000 membuat daftar awal dan menyertakan bidang lama yang sudah dipakai data. Semua Bidang dan Pengelola Aset merupakan label khusus, bukan nama bidang yang dapat dikelola di menu ini.
