# Arsip dokumen per pelatihan

Menu Dokumen Pelatihan pada Kelola Pelatihan membuka `/trainings/{training}/documents`, menyinkronkan sumber arsip, lalu mengarahkan ke folder induk pelatihan pada `/documents?folder={id}`. Di dalam folder tersedia tautan kembali ke pelatihan. Superadmin dan admin bidang terkait dapat membuka pintasan ini.

## Arsip otomatis

`DocumentController::archiveInternal` memakai TrainingDocumentArchiveService. Semua ekspor yang sudah memakai fungsi tersebut tetap masuk kategori masing-masing. Middleware ArchiveTrainingDocuments melengkapi keluaran unduhan dari route yang terkait pelatihan, termasuk respons PDF biasa, BinaryFileResponse dan StreamedResponse. Berkas unggahan yang berhasil pada route pelatihan disalin sebagai BERKAS UNGGAHAN. Ekspor global lintas pelatihan tidak dipaksakan ke satu pelatihan. Berkas pada modul Dokumen sendiri sudah berada di arsip dan tidak disalin ulang ketika diunduh.

Sinkronisasi saat membuka folder menghimpun dokumentasi kegiatan, template dan versi laporan kegiatan, evidence tindak lanjut, dokumen Proper dan evidence, template serta sertifikat peserta, dan surat peminjaman sarana yang masih tersedia. Dokumen administrasi peserta, pengajar, penyelenggara dan mutu yang sudah ditulis oleh modul masing-masing ke pohon folder pelatihan tetap digunakan. Pembuatan sertifikat juga memicu sinkronisasi setelah berhasil.

## Penyimpanan dan riwayat

Arsip otomatis baru disimpan pada disk local privat, di documents/trainings/{training_id}/{uuid}.{ext}. Original berkas sumber tidak dipindah atau dihapus. Nama unduhan tetap nama dokumen asli. Nama fisik unik mencegah berkas pelatihan berbeda atau versi baru saling menimpa. Isi identik dengan nama yang sama dalam satu pelatihan tidak digandakan; isi berbeda disimpan sebagai berkas arsip tersendiri. File lama pada public tetap dapat diakses sesuai izin sebelumnya.

Kolom files.storage_disk, files.content_hash dan file_versions.storage_disk mendukung arsip privat dan versi lama. Pratinjau, download, versi dan pemulihan memakai disk masing-masing. Konten folder tidak otomatis dipublikasikan. Sinkronisasi membaca sumber yang masih tersedia, bukan merekonstruksi berkas lama yang sudah hilang dari penyimpanan.

Migration: 2026_11_17_140000_add_archive_storage_metadata_to_files. Pelatihan 13 menggunakan folder induk ID 112; sinkronisasi awal dilakukan tanpa mengubah dokumen sumber.

## Pemulihan nama subfolder

Perubahan nama pelatihan hanya mengganti nama folder induk (parent_id null). Nama kategori dan folder peserta/pengajar tidak ikut diubah. Perbaikan data lama: `php artisan documents:repair-training-folders 13` untuk pratinjau; tambahkan `--apply` untuk menyimpan. Kategori dipulihkan dari pola nama/path dokumen. Jika kategori sudah ada, folder sumber ditempatkan sebagai Arsip sebelumnya di dalam kategori tersebut agar tidak berulang di tingkat utama. Folder sumber, pemilik, file ID, lokasi fisik dan tautan lama tetap dipertahankan. Folder peserta/pengajar dipulihkan berdasarkan pemilik dan sesi yang terkait. Cadangan metadata sebelum perubahan disimpan privat di folder-repair-backups.
