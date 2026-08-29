<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

$word = new PhpWord();
$word->setDefaultFontName('Arial');
$word->setDefaultFontSize(10);
$word->addTitleStyle(1, ['name' => 'Arial', 'size' => 14, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceBefore' => 180, 'spaceAfter' => 120]);
$word->addTitleStyle(2, ['name' => 'Arial', 'size' => 12, 'bold' => true], ['spaceBefore' => 180, 'spaceAfter' => 90]);
$word->addTitleStyle(3, ['name' => 'Arial', 'size' => 10, 'bold' => true], ['spaceBefore' => 120, 'spaceAfter' => 60]);

$section = $word->addSection([
    'marginTop' => Converter::cmToTwip(2.2),
    'marginBottom' => Converter::cmToTwip(2),
    'marginLeft' => Converter::cmToTwip(2.4),
    'marginRight' => Converter::cmToTwip(2.2),
]);

$footer = $section->addFooter();
$footer->addPreserveText('Laporan Evaluasi Pascapelatihan Level 3 dan 4 | Halaman {PAGE} dari {NUMPAGES}', ['size' => 8, 'color' => '666666'], ['alignment' => Jc::CENTER]);

$addParagraph = static fn ($container, string $text, array $font = [], array $paragraph = []) =>
    $container->addText($text, $font, array_merge(['alignment' => Jc::BOTH, 'lineHeight' => 1.15, 'spaceAfter' => 90], $paragraph));

$addInfoTable = static function ($container, array $rows): void {
    $table = $container->addTable(['borderSize' => 4, 'borderColor' => 'B7C9E2', 'cellMargin' => 90, 'width' => 100 * 50]);
    foreach ($rows as [$label, $value]) {
        $table->addRow();
        $table->addCell(3200, ['bgColor' => 'DCE6F1'])->addText($label, ['bold' => true]);
        $table->addCell(6400)->addText($value);
    }
};

$addResultTable = static function ($container, string $prefix): void {
    $table = $container->addTable(['borderSize' => 5, 'borderColor' => '7F8C8D', 'cellMargin' => 70]);
    $table->addRow();
    foreach ([['No.', 500], ['Indikator', 4200], ['Mandiri', 950], ['Atasan', 950], ['Rekan', 950], ['Gabungan', 950], ['N', 550]] as [$label, $width]) {
        $table->addCell($width, ['bgColor' => '1F4E78'])->addText($label, ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['alignment' => Jc::CENTER]);
    }
    $table->addRow();
    foreach ([
        ["\${{$prefix}_no}", 500],
        ["\${{$prefix}_indikator}", 4200],
        ["\${{$prefix}_mandiri}", 950],
        ["\${{$prefix}_atasan}", 950],
        ["\${{$prefix}_rekan}", 950],
        ["\${{$prefix}_gabungan}", 950],
        ["\${{$prefix}_n}", 550],
    ] as [$value, $width]) {
        $table->addCell($width)->addText($value, ['size' => 8]);
    }
};

$section->addTextBreak(4);
$section->addText('LAPORAN EVALUASI PASCAPELATIHAN', ['bold' => true, 'size' => 18, 'color' => '1F4E78'], ['alignment' => Jc::CENTER]);
$section->addText('LEVEL 3 (PERILAKU) DAN LEVEL 4 (HASIL/DAMPAK)', ['bold' => true, 'size' => 13], ['alignment' => Jc::CENTER]);
$section->addTextBreak(2);
$section->addText('${nama_pelatihan}', ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER]);
$section->addText('Tahun Pelaksanaan ${tahunpelaksanaan}', ['size' => 12], ['alignment' => Jc::CENTER]);
$section->addTextBreak(7);
$section->addText('BADAN PENGEMBANGAN SUMBER DAYA MANUSIA', ['bold' => true, 'size' => 12], ['alignment' => Jc::CENTER]);
$section->addText('PROVINSI JAWA BARAT', ['bold' => true, 'size' => 12], ['alignment' => Jc::CENTER]);
$section->addText('Tahun ${tahunberjalan}', ['size' => 11], ['alignment' => Jc::CENTER]);
$section->addPageBreak();

$section->addTitle('RINGKASAN EKSEKUTIF', 1);
$addParagraph($section, 'Laporan ini menyajikan hasil Evaluasi Pascapelatihan Level 3 dan Level 4 atas ${nama_pelatihan}. Evaluasi berfokus pada penerapan hasil pembelajaran dalam perilaku kerja serta hasil atau dampaknya terhadap pekerjaan dan unit kerja.');
$addInfoTable($section, [
    ['Periode pelatihan', '${tanggal_mulai} s.d. ${tanggal_selesai}'],
    ['Bidang penyelenggara', '${bidang}'],
    ['Metode pelatihan', '${metode}'],
    ['Populasi alumni', '${jumlah_peserta} orang'],
    ['Respons alumni/mandiri', '${jumlah_alumni} orang (${response_rate_mandiri})'],
    ['Respons atasan', '${jumlah_atasan} orang (${response_rate_atasan})'],
    ['Respons rekan kerja', '${jumlah_rekan} orang (${response_rate_rekan})'],
    ['Indeks Level 3', '${skor_l3} / 100 (${kategori_l3})'],
    ['Indeks Level 4', '${skor_l4} / 100 (${kategori_l4})'],
]);
$section->addTitle('Temuan Utama', 2);
$addParagraph($section, '${ringkasan_temuan}');
$section->addTitle('Rekomendasi Prioritas', 2);
$addParagraph($section, '${rekomendasi}');
$section->addPageBreak();

$section->addTitle('BAB I PENDAHULUAN', 1);
$section->addTitle('1.1 Latar Belakang', 2);
$addParagraph($section, 'Pengembangan kompetensi perlu dinilai tidak hanya dari reaksi dan pembelajaran selama pelatihan, tetapi juga dari penerapan kompetensi di tempat kerja dan kontribusinya terhadap hasil organisasi. Oleh karena itu, evaluasi ini menggunakan kerangka Kirkpatrick pada Level 3 (behavior/perilaku) dan Level 4 (results/hasil atau dampak).');
$addParagraph($section, 'Pendekatan laporan disesuaikan dengan prinsip Evaluasi Pascapelatihan LAN: menghimpun perspektif alumni dan lingkungan kerja, menilai implementasi hasil pelatihan, dukungan organisasi, perubahan kompetensi individu, serta dampaknya terhadap kinerja unit kerja.');
$section->addTitle('1.2 Maksud dan Tujuan', 2);
$addParagraph($section, 'Evaluasi dimaksudkan untuk menyediakan bukti mengenai transfer pembelajaran dan dampak pelatihan. Tujuannya adalah: (1) mengukur penerapan hasil pelatihan; (2) membandingkan persepsi alumni, atasan, dan rekan kerja; (3) mengidentifikasi faktor pendukung dan hambatan; dan (4) merumuskan rekomendasi perbaikan pelatihan dan dukungan pascapelatihan.');
$section->addTitle('1.3 Ruang Lingkup', 2);
$addParagraph($section, 'Objek evaluasi adalah alumni ${nama_pelatihan} yang dilaksanakan pada ${tanggal_mulai} sampai dengan ${tanggal_selesai}. Pengumpulan data mulai dilakukan pada ${tanggalsebarlink}.');

$section->addTitle('BAB II METODOLOGI', 1);
$section->addTitle('2.1 Desain dan Sumber Data', 2);
$addParagraph($section, 'Evaluasi menggunakan pendekatan deskriptif kuantitatif yang dilengkapi jawaban kualitatif. Data primer berasal dari kuesioner mandiri alumni, atasan langsung, dan rekan kerja. Profil alumni dan data pelatihan pada aplikasi digunakan sebagai data pendukung.');
$section->addTitle('2.2 Instrumen dan Skoring', 2);
$addParagraph($section, 'Instrumen dikelompokkan ke dalam Data Diri Alumni, Penempatan Tugas dan Transfer Learning, Perubahan Perilaku, serta Dampak Pelatihan. Jawaban berskala dikonversi ke indeks 0–100. Nilai gabungan dihitung dari seluruh jawaban valid; jawaban kosong dan pertanyaan kualitatif tidak dimasukkan ke penyebut indeks.');
$addInfoTable($section, [
    ['91-100', 'Sangat Baik'],
    ['81-90', 'Baik'],
    ['71-80', 'Cukup'],
    ['61-70', 'Kurang'],
    ['10-60', 'Sangat Kurang'],
]);
$section->addTitle('2.3 Keterbatasan', 2);
$addParagraph($section, '${catatan_keterbatasan}');

$section->addTitle('BAB III PROFIL DAN PARTISIPASI RESPONDEN', 1);
$section->addTitle('3.1 Tingkat Respons', 2);
$addParagraph($section, 'Dari ${jumlah_peserta} alumni, respons yang diterima terdiri atas ${jumlah_alumni} mandiri, ${jumlah_atasan} atasan, dan ${jumlah_rekan} rekan kerja. Perbedaan jumlah respons antar-perspektif perlu diperhatikan ketika menafsirkan hasil.');
$section->addTitle('3.2 Mobilitas Alumni', 2);
$addInfoTable($section, [
    ['Jabatan berubah / tetap', '${jab_berubah} / ${jab_tetap}'],
    ['Unit kerja berubah / tetap', '${unit_berubah} / ${unit_tetap}'],
    ['Perangkat daerah berubah / tetap', '${dept_berubah} / ${dept_tetap}'],
]);
$section->addTitle('3.3 Sebaran Instansi', 2);
$instansi = $section->addTable(['borderSize' => 5, 'borderColor' => '7F8C8D', 'cellMargin' => 80]);
$instansi->addRow();
$instansi->addCell(7500, ['bgColor' => '1F4E78'])->addText('Instansi', ['bold' => true, 'color' => 'FFFFFF']);
$instansi->addCell(1500, ['bgColor' => '1F4E78'])->addText('Alumni', ['bold' => true, 'color' => 'FFFFFF']);
$instansi->addRow();
$instansi->addCell(7500)->addText('${ins_nama}');
$instansi->addCell(1500)->addText('${ins_jml}');

$section->addTitle('BAB IV HASIL EVALUASI LEVEL 3 – PERUBAHAN PERILAKU', 1);
$addParagraph($section, 'Level 3 menilai sejauh mana kompetensi hasil pelatihan diterapkan dalam pelaksanaan tugas. Tabel berikut membandingkan tiga perspektif. Tanda “–” menunjukkan belum tersedia jawaban terukur.');
$addResultTable($section, 'l3');
$section->addTitle('Interpretasi Level 3', 2);
$addParagraph($section, '${narasi_l3}');

$section->addTitle('BAB V HASIL EVALUASI LEVEL 4 – HASIL/DAMPAK', 1);
$addParagraph($section, 'Level 4 menilai kontribusi penerapan hasil pelatihan terhadap kualitas, produktivitas, efektivitas pekerjaan, dan hasil unit kerja sesuai indikator yang tersedia dalam instrumen.');
$addResultTable($section, 'l4');
$section->addTitle('Interpretasi Level 4', 2);
$addParagraph($section, '${narasi_l4}');

$section->addTitle('BAB VI KESIMPULAN DAN RENCANA TINDAK LANJUT', 1);
$section->addTitle('6.1 Kesimpulan', 2);
$addParagraph($section, '${kesimpulan}');
$section->addTitle('6.2 Rekomendasi', 2);
$addParagraph($section, '${rekomendasi}');
$section->addTitle('6.3 Rencana Tindak Lanjut', 2);
$rtl = $section->addTable(['borderSize' => 5, 'borderColor' => '7F8C8D', 'cellMargin' => 80]);
$rtl->addRow();
foreach (['Prioritas', 'Tindak lanjut', 'Penanggung jawab', 'Waktu', 'Indikator keberhasilan'] as $label) {
    $rtl->addCell(1800, ['bgColor' => '1F4E78'])->addText($label, ['bold' => true, 'color' => 'FFFFFF', 'size' => 8]);
}
for ($i = 1; $i <= 3; $i++) {
    if ($i > 1) {
        continue;
    }
    $rtl->addRow();
    foreach (['${rtl34_prioritas}', '${rtl34_tindakan}', '${rtl34_penanggung}', '${rtl34_waktu}', '${rtl34_indikator}'] as $value) {
        $rtl->addCell(1800)->addText($value, ['size' => 8]);
    }
}

$section->addPageBreak();
$section->addTitle('LAMPIRAN - REKAP STATUS RESPONDEN', 1);
$respondent = $section->addTable(['borderSize' => 5, 'borderColor' => '7F8C8D', 'cellMargin' => 60]);
$respondent->addRow();
foreach (['Nama/NIP', 'Jabatan', 'Instansi', 'Mandiri', 'Atasan', 'Rekan'] as $label) {
    $respondent->addCell(1600, ['bgColor' => '1F4E78'])->addText($label, ['bold' => true, 'color' => 'FFFFFF', 'size' => 7]);
}
$respondent->addRow();
foreach (['res_nama', 'res_jabatan', 'res_instansi', 'res_m', 'res_a', 'res_r'] as $key) {
    $respondent->addCell(1600)->addText('${' . $key . '}', ['size' => 7]);
}

$path = dirname(__DIR__) . '/public/templates/template_laporan_lv34.docx';
$word->save($path, 'Word2007');
echo "Generated: {$path}" . PHP_EOL;
