# Penandatangan laporan Word Level 3 dan 4

Pada dashboard evaluasi Level 3 & 4, tombol Laporan Word membuka popup berisi jabatan/sebutan, nama dan gelar, serta NIP untuk dua penandatangan. Nilai awal mengikuti template lama. Perubahan disimpan per pelatihan, tidak mengubah pelatihan lain.

Simpan Pengaturan menyimpan tanpa mengunduh. Simpan & Unduh Word menyimpan lalu mengunduh laporan dengan data terbaru. Unduhan langsung melalui URL ekspor juga memakai pengaturan tersimpan. NIP divalidasi sebagai teks 18 angka agar digitnya utuh. Superadmin dan admin bidang pelatihan terkait dapat menyimpan pengaturan.

Kolom JSON `trainings.l34_report_signatories` ditambahkan melalui migration 2026_11_17_120000. Template `public/templates/templateevaluasi34_integral.docx` menggunakan enam placeholder `l34_sign_*`; tabel, format nama dan awalan NIP tetap mengikuti template. Karakter XML seperti ampersand pada nama/jabatan ditangani saat ekspor.
