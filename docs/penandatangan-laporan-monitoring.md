# Penandatangan laporan monitoring

Dari Kelola Pelatihan > Pelaksanaan > Download Laporan Monitoring > Laporan Monitoring, atau daftar Monitoring > Laporan Monitoring, pengguna membuka popup pengaturan penandatangan sebelum unduh. URL pengaturan: `/monitoring/{id}/laporan/pengaturan`.

Jabatan/sebutan, nama dan NIP Kepala Bidang serta Tim Monitoring disimpan per pelatihan. Satu isian Tim Monitoring mengisi kedua lokasi dalam template. Simpan Pengaturan menyimpan tanpa mengunduh; Simpan & Unduh Word mengarahkan ke ekspor asli. Parameter stage_id tetap diteruskan dan harus merupakan tahapan pelatihan terkait. Unduhan langsung melalui URL ekspor menggunakan data tersimpan atau nilai awal template.

Tiga gambar tanda tangan pada template laporan_monitoring.docx dan dua file PNG sumber telah dihapus; ilustrasi sampul tetap tersedia. Tanggal penandatangan tetap mengikuti tanggal selesai pelatihan seperti alur sebelumnya. Pengaturan terpisah dari penandatangan evaluasi Level 3 & 4 dan digunakan juga oleh laporan tindak lanjut serta ceklis Excel.

Migration: 2026_11_17_130000_add_monitoring_report_signatories_to_trainings. Hanya superadmin dan admin bidang pelatihan terkait yang boleh mengubah. NIP harus 18 angka, disimpan sebagai teks.

## Satu pengaturan untuk tiga dokumen

Parameter `document` pada halaman pengaturan menentukan tujuan unduhan: `monitoring` (Word), `tindaklanjut` (Word), atau `ceklis` (Excel). Semua menggunakan `monitoring_report_signatories` pada pelatihan yang sama; mengganti salah satu langsung berlaku pada ketiganya. Kedua gambar tanda tangan pada template tindak lanjut telah dihapus. Ceklis mencetak jabatan, nama dan NIP kedua penandatangan, menggantikan nama akun yang mengunduh.
