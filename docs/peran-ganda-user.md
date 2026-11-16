# Beberapa peran dalam satu akun

Superadmin membuka `/users`, lalu memilih **Kelola Peran** pada akun yang diinginkan. Centang peran tambahan, tentukan bidang untuk Admin Bidang atau Pengelola Keuangan, lalu Simpan Peran. Peran utama tetap disimpan; peserta, NIP, password dan riwayat pelatihan tidak diganti. Peran tambahan dapat dicabut dengan menghapus centangnya. Pengaturan nama bidang tetap khusus Superadmin.

Pengguna melihat bagian **Akses Peran** di sidebar. Klik salah satu peran untuk membuka halaman awal dan menu peran tersebut, tanpa login ulang. Kewenangan aktif mengikuti peran dan bidang yang dipilih; satu peran aktif per sesi browser. Perpindahan berlaku juga pada tab lain dalam sesi yang sama, jadi muat ulang halaman sebelum melanjutkan form di tab lama. Pilih Peserta untuk kembali ke portal peserta.

Superadmin memberikan akses penuh. Peran pengelola lain tetap mengikuti pembatasan modul: Admin Bidang sesuai bidang, pengelola magang sesuai program yang ditugaskan, evaluator sesuai tim, dan pengelola PPPK-PW sesuai pegawai yang ditugaskan. Pemberian peran tidak otomatis membuat penugasan program, tim, pegawai atau data pendaftaran magang. Narasumber tambahan tetap melengkapi administrasi pengajar sebelum mengajar.

Daftar pemilihan pengelola magang, supervisor PPPK-PW, penandatangan, pengajar, serta PIC bidang membaca peran tambahan. Akun publik dengan tambahan peran administratif dapat login menggunakan username administratif atau identitas publik yang sebelumnya berlaku. Sistem memeriksa ulang peran pada setiap request; pencabutan peran langsung mencabut akses sesi terkait. Pengguna tidak dapat memberikan peran kepada dirinya sendiri. Akun peserta yang mempunyai peran tambahan tidak dapat dihapus menyeluruh melalui tombol Hapus Peserta.

Implementasi menggunakan tabel `user_role_assignments`. Kolom `users.role` dan `users.bidang` tetap menyimpan peran/bidang utama; pilihan peran aktif disimpan pada sesi dan diterapkan selama request, bukan ditulis ulang ke kolom utama. Migrasi `2026_11_15_180000_create_user_role_assignments_table.php` bersifat tambahan dan tidak mengganti peran akun lama.
