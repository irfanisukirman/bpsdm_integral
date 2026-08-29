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
$word->addTitleStyle(2, ['name' => 'Arial', 'size' => 12, 'bold' => true], ['spaceBefore' => 160, 'spaceAfter' => 80]);

$section = $word->addSection([
    'marginTop' => Converter::cmToTwip(2.2),
    'marginBottom' => Converter::cmToTwip(2),
    'marginLeft' => Converter::cmToTwip(2.3),
    'marginRight' => Converter::cmToTwip(2.1),
]);
$section->addFooter()->addPreserveText(
    'Laporan Evaluasi Pelatihan Level 1 dan 2 | Halaman {PAGE} dari {NUMPAGES}',
    ['size' => 8, 'color' => '666666'],
    ['alignment' => Jc::CENTER]
);

$paragraph = static fn ($container, string $text) => $container->addText(
    $text,
    [],
    ['alignment' => Jc::BOTH, 'lineHeight' => 1.15, 'spaceAfter' => 90]
);
$infoTable = static function ($container, array $rows): void {
    $table = $container->addTable(['borderSize' => 4, 'borderColor' => 'B7C9E2', 'cellMargin' => 90]);
    foreach ($rows as [$label, $value]) {
        $table->addRow();
        $table->addCell(3300, ['bgColor' => 'DCE6F1'])->addText($label, ['bold' => true]);
        $table->addCell(6300)->addText($value);
    }
};
$headerCell = static fn ($row, string $text, int $width) => $row->addCell($width, ['bgColor' => '1F4E78'])
    ->addText($text, ['bold' => true, 'color' => 'FFFFFF', 'size' => 8], ['alignment' => Jc::CENTER]);

$section->addTextBreak(4);
$section->addText('LAPORAN EVALUASI PELATIHAN', ['bold' => true, 'size' => 19, 'color' => '1F4E78'], ['alignment' => Jc::CENTER]);
$section->addText('LEVEL 1 (REAKSI) DAN LEVEL 2 (PEMBELAJARAN)', ['bold' => true, 'size' => 13], ['alignment' => Jc::CENTER]);
$section->addTextBreak(2);
$section->addText('${nama_pelatihan}', ['bold' => true, 'size' => 16], ['alignment' => Jc::CENTER]);
$section->addText('Tahun Pelaksanaan ${tahunpelaksanaan}', ['size' => 12], ['alignment' => Jc::CENTER]);
$section->addTextBreak(7);
$section->addText('BADAN PENGEMBANGAN SUMBER DAYA MANUSIA', ['bold' => true, 'size' => 12], ['alignment' => Jc::CENTER]);
$section->addText('PROVINSI JAWA BARAT', ['bold' => true, 'size' => 12], ['alignment' => Jc::CENTER]);
$section->addText('Tahun ${tahunberjalan}', ['size' => 11], ['alignment' => Jc::CENTER]);
$section->addPageBreak();

$section->addTitle('RINGKASAN EKSEKUTIF', 1);
$paragraph($section, 'Laporan ini menyajikan hasil evaluasi ${nama_pelatihan} pada Level 1 (reaksi peserta terhadap penyelenggaraan dan narasumber) serta Level 2 (perubahan hasil pembelajaran berdasarkan pretest dan posttest).');
$infoTable($section, [
    ['Periode pelatihan', '${tanggal_mulai} s.d. ${tanggal_selesai}'],
    ['Bidang penyelenggara', '${bidang}'],
    ['Metode pelatihan', '${metode}'],
    ['Jumlah peserta', '${jumlah_peserta} orang'],
    ['Responden Level 1', '${jumlah_responden_l1} orang (${response_rate_l1})'],
    ['Peserta bernilai Level 2', '${jumlah_responden_l2} orang (${response_rate_l2})'],
    ['Indeks Level 1', '${skor_l1} / 100 (${kategori_l1})'],
    ['Rerata pretest / posttest', '${rerata_pretest} / ${rerata_posttest}'],
    ['Rerata perubahan nilai', '${rerata_gain} poin'],
]);
$section->addTitle('Temuan Utama', 2);
$paragraph($section, '${ringkasan_temuan}');
$section->addTitle('Rekomendasi Prioritas', 2);
$paragraph($section, '${rekomendasi}');
$section->addPageBreak();

$section->addTitle('BAB I PENDAHULUAN', 1);
$section->addTitle('1.1 Latar Belakang', 2);
$paragraph($section, 'Evaluasi merupakan bagian dari siklus penjaminan mutu pengembangan kompetensi. Model Kirkpatrick menempatkan Level 1 untuk menilai reaksi peserta terhadap pengalaman belajar dan Level 2 untuk menilai perubahan pengetahuan atau hasil pembelajaran. Keduanya memberi informasi awal untuk perbaikan desain, penyelenggaraan, fasilitasi, dan asesmen pelatihan.');
$section->addTitle('1.2 Tujuan', 2);
$paragraph($section, 'Laporan bertujuan untuk: (1) mengukur reaksi peserta terhadap penyelenggara dan narasumber; (2) mengidentifikasi indikator layanan atau fasilitasi yang perlu dipertahankan dan diperbaiki; (3) membandingkan hasil pretest dan posttest; serta (4) merumuskan rekomendasi peningkatan mutu pelatihan.');
$section->addTitle('1.3 Ruang Lingkup', 2);
$paragraph($section, 'Objek laporan adalah ${nama_pelatihan}, yang diselenggarakan pada ${tanggal_mulai} sampai dengan ${tanggal_selesai}. Analisis menggunakan data yang tersimpan pada aplikasi sampai dokumen diunduh.');

$section->addTitle('BAB II METODOLOGI', 1);
$section->addTitle('2.1 Sumber Data', 2);
$paragraph($section, 'Data Level 1 bersumber dari formulir reaksi peserta untuk penyelenggara dan narasumber pada sesi terkait. Data Level 2 bersumber dari pasangan nilai pretest dan posttest peserta. Analisis dilakukan secara deskriptif dan tidak mengasumsikan hubungan sebab-akibat.');
$section->addTitle('2.2 Pengolahan Level 1', 2);
$paragraph($section, 'Skor numerik dihitung sebagai rerata jawaban valid pada skala 10–100. Jawaban teks dan pilihan nonnumerik tetap tersimpan sebagai bahan telaah kualitatif, tetapi tidak dipaksakan menjadi skor.');
$infoTable($section, [
    ['91-100', 'Sangat Baik'],
    ['81-90', 'Baik'],
    ['71-80', 'Cukup'],
    ['61-70', 'Kurang'],
    ['10-60', 'Sangat Kurang'],
]);
$section->addTitle('2.3 Pengolahan Level 2', 2);
$paragraph($section, 'Perubahan pembelajaran dihitung dari nilai posttest dikurangi pretest pada peserta yang memiliki rekaman nilai. Laporan menampilkan rerata pretest, rerata posttest, rerata selisih, serta jumlah peserta yang meningkat, tetap, atau menurun. Karena data skor maksimum dan minimum instrumen khusus tidak tersedia, laporan tidak menyebut selisih tersebut sebagai normalized gain.');
$section->addTitle('2.4 Keterbatasan', 2);
$paragraph($section, '${keterbatasan}');

$section->addTitle('BAB III HASIL EVALUASI LEVEL 1 - REAKSI', 1);
$infoTable($section, [
    ['Rerata keseluruhan', '${skor_l1} (${kategori_l1})'],
    ['Rerata penyelenggara', '${skor_penyelenggara}'],
    ['Rerata narasumber', '${skor_narasumber}'],
    ['Tingkat respons', '${jumlah_responden_l1} dari ${jumlah_peserta} peserta (${response_rate_l1})'],
]);
$section->addTitle('3.1 Rekap Objek Evaluasi', 2);
$objects = $section->addTable(['borderSize' => 5, 'borderColor' => '7F8C8D', 'cellMargin' => 55]);
$row = $objects->addRow();
foreach ([['No.', 450], ['Jenis', 1000], ['Objek', 1700], ['Materi', 2500], ['N', 550], ['Skor', 750], ['Kategori', 1100]] as [$label, $width]) {
    $headerCell($row, $label, $width);
}
$row = $objects->addRow();
foreach ([['${l1_obj_no}', 450], ['${l1_obj_jenis}', 1000], ['${l1_obj_objek}', 1700], ['${l1_obj_materi}', 2500], ['${l1_obj_responden}', 550], ['${l1_obj_skor}', 750], ['${l1_obj_kategori}', 1100]] as [$value, $width]) {
    $row->addCell($width)->addText($value, ['size' => 7]);
}
$section->addTitle('3.2 Rekap per Indikator', 2);
$indicators = $section->addTable(['borderSize' => 5, 'borderColor' => '7F8C8D', 'cellMargin' => 55]);
$row = $indicators->addRow();
foreach ([['No.', 450], ['Objek', 1600], ['Indikator', 4400], ['N', 550], ['Skor', 750], ['Kategori', 1100]] as [$label, $width]) {
    $headerCell($row, $label, $width);
}
$row = $indicators->addRow();
foreach ([['${l1_ind_no}', 450], ['${l1_ind_objek}', 1600], ['${l1_ind_indikator}', 4400], ['${l1_ind_responden}', 550], ['${l1_ind_skor}', 750], ['${l1_ind_kategori}', 1100]] as [$value, $width]) {
    $row->addCell($width)->addText($value, ['size' => 7]);
}
$section->addTitle('3.3 Interpretasi Level 1', 2);
$paragraph($section, '${narasi_l1}');

$section->addTitle('BAB IV HASIL EVALUASI LEVEL 2 - PEMBELAJARAN', 1);
$infoTable($section, [
    ['Peserta memiliki nilai', '${jumlah_responden_l2} dari ${jumlah_peserta} peserta (${response_rate_l2})'],
    ['Rerata pretest', '${rerata_pretest}'],
    ['Rerata posttest', '${rerata_posttest}'],
    ['Rerata perubahan', '${rerata_gain} poin'],
    ['Meningkat / Tetap / Menurun', '${jumlah_meningkat} / ${jumlah_tetap} / ${jumlah_menurun}'],
    ['Persentase meningkat', '${persentase_meningkat}'],
]);
$section->addTitle('4.1 Interpretasi Level 2', 2);
$paragraph($section, '${narasi_l2}');
$section->addTitle('4.2 Rincian Nilai Peserta', 2);
$scores = $section->addTable(['borderSize' => 5, 'borderColor' => '7F8C8D', 'cellMargin' => 55]);
$row = $scores->addRow();
foreach ([['No.', 450], ['Nama', 2100], ['NIP/NIK', 1800], ['Pre', 700], ['Post', 700], ['Selisih', 750], ['Status', 1000]] as [$label, $width]) {
    $headerCell($row, $label, $width);
}
$row = $scores->addRow();
foreach ([['${l2_no}', 450], ['${l2_nama}', 2100], ['${l2_nip}', 1800], ['${l2_pre}', 700], ['${l2_post}', 700], ['${l2_delta}', 750], ['${l2_status}', 1000]] as [$value, $width]) {
    $row->addCell($width)->addText($value, ['size' => 7]);
}

$section->addTitle('BAB V KESIMPULAN DAN TINDAK LANJUT', 1);
$section->addTitle('5.1 Kesimpulan', 2);
$paragraph($section, '${kesimpulan}');
$section->addTitle('5.2 Rekomendasi', 2);
$paragraph($section, '${rekomendasi}');
$section->addTitle('5.3 Matriks Rencana Tindak Lanjut', 2);
$followUp = $section->addTable(['borderSize' => 5, 'borderColor' => '7F8C8D', 'cellMargin' => 60]);
$row = $followUp->addRow();
foreach (['Temuan prioritas', 'Tindakan perbaikan', 'Penanggung jawab', 'Target waktu', 'Bukti keberhasilan'] as $label) {
    $headerCell($row, $label, 1800);
}
$row = $followUp->addRow();
foreach ([
    '${rtl12_temuan}',
    '${rtl12_tindakan}',
    '${rtl12_penanggung}',
    '${rtl12_waktu}',
    '${rtl12_indikator}',
] as $value) {
    $row->addCell(1800)->addText($value, ['size' => 8]);
}

$path = dirname(__DIR__) . '/public/templates/template_laporan_lv12.docx';
$word->save($path, 'Word2007');
echo "Generated: {$path}" . PHP_EOL;
