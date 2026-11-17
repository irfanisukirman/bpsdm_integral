# Ekspose pasca dan narasi AI

Ekspose Integral mengikuti referensi Ekspose-Kegiatan-4 (8).pdf: sembilan halaman 16:9, sampul, tujuan, mekanisme konfirmasi, lima grafik dengan pembahasan di kanan, lalu pembahasan keseluruhan dan rekomendasi. Aset sampul/tujuan/logo menggunakan aset lokal proyek Pasca yang sudah ada. Tidak memerlukan akses gambar eksternal.

## Grafik

Grafik ekspose menggunakan lima rentang sama dengan referensi: nilai 0, lebih dari 0 sampai 25, lebih dari 25 sampai 50, lebih dari 50 sampai 75, lebih dari 75 sampai 100. Label singkat 1-25 dan seterusnya adalah rentang visual; nilai desimal mengikuti batas tepat ini. Nilai kosong tetap tidak masuk rentang mana pun dan dicantumkan sebagai belum dinilai.

Pengelompokan grafik ini terpisah dari kategori rubrik Kurang/Cukup/Baik/Sangat Baik yang sudah digunakan ringkasan. Nilai survei dan wawancara tidak diubah. Rerata keseluruhan tetap menggunakan tiga indikator terisi, bukan nilai berbobot lima kriteria. Snapshot publik lama yang belum memiliki lima rentang tetap menampilkan kategori lamanya tanpa merekayasa data; perbarui publikasi untuk memakai data grafik terbaru.

## Membuat narasi

1. Buka Hasil Evaluasi Pasca, pilih pelatihan lalu tab Ringkasan.
2. Pastikan jawaban survei untuk tiga indikator otomatis dan nilai wawancara sudah tersedia.
3. Narasi otomatis dibuat saat superadmin membuka tab Ringkasan jika belum tersedia atau sumber berubah. Tombol Buat ulang narasi AI tersedia untuk mencoba ulang. Layanan memakai provider/model AI yang sudah dikonfigurasi pada aplikasi (OpenAI atau Gemini), bukan konfigurasi atau API key baru.
4. AI menulis pembahasan lima grafik, kesimpulan dan tiga rekomendasi yang lebih terjelaskan. Angka grafik tetap dihitung aplikasi; API tidak diminta membuat atau mengubah grafik.
5. Tinjau draf melalui Ekspose atau PDF. Jika sesuai, gunakan Perbarui Hasil Publik untuk menerbitkan snapshot beserta narasinya. Link publik tidak otomatis berubah saat generate.

Yang dikirim ke AI adalah program, jumlah peserta, cakupan/progres, histogram, rerata, penjelasan rumus, dan catatan evaluator lima kriteria yang disamarkan (nama/NIP/email peserta terdaftar dan nomor panjang dihapus). Judul/isi Proper dan file tidak dikirim. Catatan draf disebut sementara, temuan tidak digeneralisasi ke seluruh peserta. Seluruh nilai kosong berarti belum dinilai, bukan bukti tidak hadir. Narasi diminta membedakan sumber survei dan wawancara, menyebut keterbatasan cakupan, serta tidak mengarang sebab-akibat, bukti atau angka. Setiap rekomendasi harus berupa usulan tindakan dan pemantauan, bukan klaim kegiatan telah dilakukan.

Narasi tersimpan pada ai_generations beserta model, waktu, status dan sidik angka dan catatan evaluator. Ekspose otomatis memakai hasil AI yang sesuai angka terkini. Jika angka berubah, hasil lama tidak dicampur dengan data baru; status meminta generate ulang dan tampilan menggunakan narasi dasar berbasis angka sementara. Kegagalan API tidak menghapus hasil sebelumnya. Konfigurasi AI yang belum siap atau belum adanya nilai menghasilkan pesan yang jelas. Pengguna tidak perlu memanggil API setiap kali membuka/mengekspor PDF.

Pengujian memalsukan respons API, memeriksa payload agregat tanpa identitas, penyimpanan narasi, hasil gagal, deteksi data berubah, snapshot publik tetap, otorisasi, batas rentang, dan PDF sembilan halaman. Panggilan API nyata dilakukan otomatis dari tab Ringkasan superadmin bila narasi belum sesuai, atau saat tombol buat ulang dijalankan dan bergantung pada kredensial/ketersediaan provider.

## Otomatisasi 17 November 2026

Permintaan otomatis memakai POST ber-CSRF, otorisasi superadmin, lock per pelatihan, dan cache hasil berdasarkan sidik sumber. Kegagalan otomatis ditahan lima menit agar tidak berulang saat halaman dimuat ulang; tombol manual dapat mencoba kembali. Catatan mentah tidak masuk snapshot publik. Perubahan catatan atau status wawancara membuat narasi lama kedaluwarsa meskipun angka tetap. API tidak dijalankan dari GET, ekspor PDF, atau link publik.
