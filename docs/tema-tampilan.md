# Light Mode dan Dark Mode

Pilihan Light/Dark berada di navbar utama di sebelah notifikasi, tersedia pada semua halaman yang menggunakan layouts.master. Pada layar kecil, teks dipadatkan menjadi ikon matahari/bulan; nama aksesibel dan aria-pressed tetap tersedia.

Preferensi memakai localStorage integral:appearance pada browser. Tanpa pilihan tersimpan, tema mengikuti preferensi perangkat. Pilihan diterapkan di head sebelum halaman dilukis untuk mengurangi kilatan tema awal. Storage yang diblokir tidak menghalangi pergantian tema pada halaman. Pilihan juga mengikuti perubahan dari tab browser lain.

public/assets/js/integral-theme.js mengatur data-integral-theme dan data-bs-theme; CSS di public/assets/css/integral-theme.css diterapkan setelah stylesheet halaman. Tema gelap menyesuaikan komponen Bootstrap dan panel aplikasi, termasuk navbar, sidebar, tabel, isian, modal dan dashboard. Grafik ApexCharts/Chart.js yang terdaftar diperbarui saat tema berubah; label dan tooltip ApexCharts juga memiliki gaya gelap. Nilai evaluasi dan data aplikasi tidak berubah.

Penyesuaian gelap dibatasi media screen agar cetak tetap memakai gaya laporan terang. Halaman publik dan ekspose mandiri yang tidak memakai layout utama tidak diubah. Tidak ada perubahan database.

Verifikasi browser lokal: desktop 1440 piksel dan perangkat seluler emulasi 390 piksel; pilihan Light/Dark, aria-pressed, warna latar dan kartu, penyimpanan/reload preferensi, serta lebar navbar tanpa overflow. Pratinjau privat tersedia di storage/app/private/theme-ui-check.
