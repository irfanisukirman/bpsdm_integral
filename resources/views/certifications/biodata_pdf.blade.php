<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: 215mm 330mm; margin: 14mm 18mm 18mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5pt; color: #000; line-height: 1.35; }
        .header { position: relative; min-height: 83px; text-align: center; border-bottom: 3px solid #000; padding: 0 0 7px 82px; margin-bottom: 18px; }
        .header-logo { position: absolute; left: 8px; top: 0; width: 58px; height: 70px; object-fit: contain; }
        .header .province { font-size: 13pt; font-weight: bold; }
        .header .agency { font-size: 15pt; font-weight: bold; }
        .header .contact { font-size: 8.5pt; margin-top: 3px; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin: 0; }
        .activity { text-align: center; font-weight: bold; margin: 4px 0 14px; }
        table.identity { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.identity td { border: 1px solid #000; padding: 5px 7px; vertical-align: top; }
        table.identity .number { width: 6%; text-align: center; }
        table.identity .label { width: 36%; }
        table.identity .separator { width: 3%; text-align: center; }
        .preserve-lines { white-space: pre-line; }
        .signature { width: 45%; margin-left: 55%; margin-top: 16px; text-align: center; }
        .signature-image { height: 62px; max-width: 210px; margin: 5px auto 1px; }
        .signature-name { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header">
        @if (!empty($logoData))
            <img class="header-logo" src="{{ $logoData }}" alt="Logo Pemerintah Provinsi Jawa Barat">
        @endif
        <div class="province">PEMERINTAH DAERAH PROVINSI JAWA BARAT</div>
        <div class="agency">BADAN PENGEMBANGAN SUMBER DAYA MANUSIA</div>
        <div class="contact">
            Jalan Kolonel Masturi KM 3,5 No. 11 Kota Cimahi Telepon (022) 6649471<br>
            Website: bpsdm.jabarprov.go.id &nbsp; Email: bpsdm@jabarprov.go.id<br>
            KOTA CIMAHI 40511
        </div>
    </div>

    <p class="title">BIODATA PESERTA FASILITASI SERTIFIKASI KOMPETENSI</p>
    <p class="activity">{{ $values['nama_kegiatan'] ?? '' }}</p>

    @php
        $rows = [
            ['Nama (lengkap dengan gelar)', 'nama'],
            ['Tempat/Tanggal Lahir', 'tempat_tanggal'],
            ['NIP/NIK', 'nip'],
            ['Pangkat/Golongan', 'pangkat_gol'],
            ['Jabatan', 'jabatan'],
            ['Instansi', 'instansi'],
            ['Agama', 'agama'],
            ['Jenis Kelamin', 'jenis_kelamin'],
            ['Pendidikan Terakhir/Tahun', 'pendidikan'],
            ['Alamat Kantor dan Telepon', 'alamat_kantor'],
            ['Nomor HP/WhatsApp', 'nomor_wa'],
            ['Alamat Email', 'email'],
            ['Diklat yang Pernah Diikuti', 'diklat'],
        ];
    @endphp

    <table class="identity">
        @foreach ($rows as $index => [$label, $key])
            <tr>
                <td class="number">{{ $index + 1 }}</td>
                <td class="label">{{ $label }}</td>
                <td class="separator">:</td>
                <td class="preserve-lines">{{ $values[$key] ?? '-' }}</td>
            </tr>
        @endforeach
    </table>

    <div class="signature">
        <div>Cimahi, {{ $values['tanggal_buat'] ?? '' }}</div>
        <div>Peserta,</div>
        @if (!empty($signatureData))
            <img class="signature-image" src="{{ $signatureData }}" alt="Tanda tangan">
        @else
            <div style="height: 68px"></div>
        @endif
        <div class="signature-name">{{ $values['nama'] ?? '' }}</div>
        <div>NIP/NIK. {{ $values['nip'] ?? '-' }}</div>
    </div>
</body>
</html>
