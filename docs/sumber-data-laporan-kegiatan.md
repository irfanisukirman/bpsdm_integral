# Penggabungan sumber laporan kegiatan

Laporan kegiatan memakai data pelatihan saat halaman dibuka, draf AI dibuat, dan dokumen baru diunduh. Tidak membutuhkan impor dari dashboard.

- Level 1: rerata seluruh nilai, penyelenggara, pengajar, indikator dan responden, mengikuti sumber dashboard Level 1?2.
- Level 2: pre-test, post-test, selisih rerata dan jumlah peserta dinilai.
- Level 3?4: layanan EvaluationL34DashboardService yang sama dengan dashboard; instrumen/peran/program aktif, peserta disetujui, deduplikasi jawaban dan konversi kesesuaian mengikuti layanan tersebut. Jawaban Ya/Tidak bukan skor skala.
- Rangkuman penyelenggara: kesimpulan serta follow_up tersimpan pada evaluation_l1_text_summaries.
- Kehadiran: hanya peserta disetujui, jadwal selain break, satu catatan terbaru per peserta-sesi. Tanpa sesi tidak disimpulkan 0%; slot tanpa status hadir/izin/sakit ditulis tanpa keterangan/belum tercatat.
- Jadwal: tanggal, waktu, kegiatan/materi, pengajar atau PIC, dan JP/OJ.
- Pengajar: nama di laporan lokal, materi, jumlah sesi, JP dan OJ; payload AI menggunakan label Pengajar 1 dan seterusnya tanpa nama.

Template standar mencetak tabel jadwal, daftar pengajar, kehadiran per peserta, peserta, rincian evaluasi dan rangkuman penyelenggara. Template khusus dapat menggunakan kode baru di tab Template, termasuk teacher_no dan attendance_no sebagai anchor tabel. Laporan lama/versi arsip tidak diubah; buat draf AI lalu simpan narasi dan generate dokumen baru untuk memakai sumber terkini.
