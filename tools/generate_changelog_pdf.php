<?php

require __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$repo = realpath(__DIR__ . '/..');

function gitLog($repo)
{
    $cmd = sprintf(
        'git -C "%s" log --pretty=format:"%%h%%x1f%%ad%%x1f%%an%%x1f%%s" --date=short',
        $repo
    );
    $raw = shell_exec($cmd);
    if ($raw === null || trim($raw) === '') {
        return [];
    }

    $commits = [];
    foreach (explode("\n", trim($raw)) as $line) {
        $parts = explode("\x1f", trim($line));
        if (count($parts) < 4) {
            continue;
        }
        $commits[] = [
            'hash'    => $parts[0],
            'date'    => $parts[1],
            'author'  => $parts[2],
            'subject' => $parts[3],
        ];
    }

    return $commits;
}

$commits = gitLog($repo);

if (empty($commits)) {
    fwrite(STDERR, "Tidak ada commit ditemukan.\n");
    exit(1);
}

$authors = [];
$byDate = [];
foreach ($commits as $c) {
    $byDate[$c['date']][] = $c;
    $authors[$c['author']] = ($authors[$c['author']] ?? 0) + 1;
}
krsort($byDate); // terbaru dulu

$total    = count($commits);
$_keys    = array_keys($byDate);
$firstDate = end($_keys);
$lastDate  = key($byDate);
$authorCount = count($authors);

$fileList  = shell_exec(sprintf('git -C "%s" ls-files', $repo));
$totalFiles = $fileList !== null ? substr_count(rtrim($fileList, "\r\n"), "\n") + 1 : 0;

function esc($str)
{
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

$dateRows = '';
$now = date('d F Y');
foreach ($byDate as $date => $items) {
    $d = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    $label = $d ? $d->format('d F Y') : $date;
    $dateRows .= '<tr>'
        . '<td style="white-space:nowrap;font-weight:bold;color:#2c5282;">' . esc($label) . '</td>'
        . '<td>' . count($items) . ' commit</td>'
        . '</tr>';
}

$authorRows = '';
arsort($authors);
foreach ($authors as $name => $count) {
    $authorRows .= '<tr><td>' . esc($name) . '</td><td style="text-align:center;">' . $count . ' commit</td></tr>';
}

$body = '';
foreach ($byDate as $date => $items) {
    $d = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    $label = $d ? $d->format('d F Y') : $date;

    $rows = '';
    foreach ($items as $c) {
        $rows .= '<tr>'
            . '<td style="white-space:nowrap;color:#718096;font-family:monospace;">' . esc($c['hash']) . '</td>'
            . '<td>' . esc($c['subject']) . '</td>'
            . '<td style="white-space:nowrap;">' . esc($c['author']) . '</td>'
            . '</tr>';
    }

    $body .= '<h2 class="page-break" style="margin-top:0;">' . esc($label) . '</h2>'
        . '<p style="color:#4a5568;font-size:9.5pt;margin:2px 0 8px;">' . count($items) . ' commit</p>'
        . '<table>'
        . '<thead><tr><th style="width:12%;">Hash</th><th>Keterangan</th><th style="width:22%;">Author</th></tr></thead>'
        . '<tbody>' . $rows . '</tbody>'
        . '</table>';
}

$latestLabel = DateTimeImmutable::createFromFormat('Y-m-d', $lastDate)->format('d F Y');

$html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Changelog - BPSDM Integral</title>
    <style>
        @page {
            size: A4;
            margin: 2cm;
            @bottom-center { content: "BPSDM Integral — Changelog"; font-size: 8pt; color: #a0aec0; }
            @bottom-right { content: "Hal. " counter(page); font-size: 8pt; color: #a0aec0; }
        }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; line-height: 1.55; color: #1a1a1a; }
        h1 { text-align: center; color: #1a365d; border-bottom: 3px solid #2c5282; padding-bottom: 10px; font-size: 20pt; }
        h2 { color: #2c5282; border-bottom: 2px solid #bee3f8; padding-bottom: 5px; margin-top: 24px; font-size: 13pt; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0; font-size: 9pt; }
        th, td { border: 1px solid #cbd5e0; padding: 5px 8px; text-align: left; vertical-align: top; word-wrap: break-word; }
        th { background-color: #ebf8ff; color: #2c5282; font-weight: bold; }
        tr:nth-child(even) { background-color: #f7fafc; }
        .cover { text-align: center; padding: 90px 20px; page-break-after: always; }
        .cover .org { font-size: 13pt; color: #2c5282; font-weight: bold; letter-spacing: 2px; }
        .cover h1 { font-size: 26pt; border: none; margin-top: 30px; }
        .cover .subtitle { font-size: 12pt; color: #4a5568; margin-top: 14px; }
        .cover .date { font-size: 11pt; color: #718096; margin-top: 46px; }
        .stat { margin: 14px auto; width: 70%; font-size: 9.5pt; }
        .stat td { padding: 6px 10px; }
        .stat td.k { color: #2c5282; font-weight: bold; width: 40%; background: #f7fafc; }
        .page-break { page-break-before: always; }
        ul { margin: 4px 0; }
    </style>
</head>
<body>

<!-- COVER -->
<div class="cover">
    <div class="org">BADAN PENGEMBANGAN SUMBER DAYA MANUSIA</div>
    <h1>CHANGELOG APLIKASI<br>BPSDM INTEGRAL</h1>
    <div class="subtitle">Riwayat Perubahan &amp; Pengembangan Sistem Informasi Terintegrasi<br>Pelatihan, Sertifikasi, Aset, Buku Tamu, Magang, dan Layanan Publik</div>
    <div class="date">Periode: {$firstDate} &mdash; {$lastDate}<br>Diterbitkan: {$now}</div>
</div>

<!-- RINGKASAN -->
<h1>Ringkasan</h1>
<table class="stat">
    <tr><td class="k">Total Commit</td><td>{$total}</td></tr>
    <tr><td class="k">Periode Pengembangan</td><td>{$firstDate} &mdash; {$lastDate}</td></tr>
    <tr><td class="k">Versi Terakhir Dipublikasikan</td><td>Per {$latestLabel}</td></tr>
    <tr><td class="k">Jumlah Kontributor</td><td>{$authorCount} orang</td></tr>
    <tr><td class="k">Jumlah Berkas (tracked)</td><td>{$totalFiles} file</td></tr>
</table>

<h2>Kontributor</h2>
<table>
    <thead><tr><th>Author</th><th style="width:25%;">Jumlah Commit</th></tr></thead>
    <tbody>{$authorRows}</tbody>
</table>

<div class="page-break"></div>

<!-- RIWAYAT -->
<h1>Riwayat Perubahan</h1>
<p style="color:#4a5568;font-size:9.5pt;">Perubahan dikelompokkan berdasarkan tanggal commit, dimulai dari yang terbaru.</p>

<table>
    <thead><tr><th style="width:24%;">Tanggal</th><th>Jumlah Commit</th></tr></thead>
    <tbody>{$dateRows}</tbody>
</table>

<!-- detail terbaru di halaman berikutnya -->
{$body}

</body>
</html>
HTML;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');
$options->set('dpi', 96);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$outDir = dirname(__DIR__) . '/docs';
$outFile = $outDir . '/CHANGELOG_BPSDM_INTEGRAL.pdf';
file_put_contents($outFile, $dompdf->output());

fwrite(STDOUT, "OK: {$outFile}\n");
fwrite(STDOUT, "Commit: {$total} | Periode: {$firstDate} - {$lastDate} | Kontributor: {$authorCount}\n");