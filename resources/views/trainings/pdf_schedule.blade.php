<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $pdfTitle ?? 'Jadwal Pelatihan' }} - {{ $training->nama_pelatihan }}</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.3;
            color: #222;
        }

        /* HEADER / KOP */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header-title {
            text-align: center;
        }
        .header-title h3 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
            font-weight: bold;
            color: #111;
        }
        .header-title p {
            margin: 3px 0 0 0;
            font-size: 9pt;
            color: #555;
        }

        /* META DATA PELATIHAN */
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 9pt;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            width: 16%;
        }

        /* TABEL UTAMA JADWAL */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 8.5pt;
        }
        .schedule-table th, 
        .schedule-table td {
            border: 1px solid #444;
            padding: 6px 5px;
            vertical-align: top;
        }
        .schedule-table th {
            background-color: #2d3748;
            color: #ffffff;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }
        .schedule-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }

        /* LINK ZOOM */
        .zoom-link {
            color: #1d4ed8;
            text-decoration: underline;
            font-size: 7.5pt;
            word-break: break-all;
        }

        /* TANDA TANGAN */
        .signature-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
            font-size: 9pt;
        }
    </style>
</head>
<body>

    <!-- KOP / HEADER DOKUMEN -->
    <table class="header-table">
        <tr>
            <td class="header-title">
                <h3>{{ $pdfTitle ?? 'JADWAL KEGIATAN PELATIHAN' }}</h3>
                <p>INTEGRAL - Sistem Pengelolaan Pelatihan BPSDM Provinsi Jawa Barat</p>
            </td>
        </tr>
    </table>

    <!-- INFORMASI DETAIL PELATIHAN -->
    <table class="meta-table">
        {{-- JIKA DIDOWNLOAD OLEH PENGAJAR, TAMPILKAN IDENTITASNYA DI KOP --}}
        @if(isset($isPengajar) && $isPengajar)
        <tr style="background-color: #eff6ff;">
            <td class="meta-label" style="color: #1e40af;">Tenaga Pengajar</td>
            <td width="1%">:</td>
            <td width="45%" class="text-bold" style="color: #1e40af;">
                {{ $user->name }} (NIP: {{ $user->nip_nik ?? '-' }})
            </td>

            <td class="meta-label" style="color: #1e40af;">Instansi Pengajar</td>
            <td width="1%">:</td>
            <td style="color: #1e40af;">{{ $user->pengajar->instansi ?? $user->instansi ?? 'BPSDM Jabar' }}</td>
        </tr>
        @endif

        <tr>
            <td class="meta-label">Nama Pelatihan</td>
            <td width="1%">:</td>
            <td width="45%" class="text-bold">{{ $training->nama_pelatihan }}</td>

            <td class="meta-label">Bidang</td>
            <td width="1%">:</td>
            <td>{{ $training->bidang }}</td>
        </tr>
        <tr>
            <td class="meta-label">Angkatan / Model</td>
            <td>:</td>
            <td>Angkatan {{ $training->angkatan }} ({{ ucfirst($training->model ?? 'Standar') }})</td>

            <td class="meta-label">Periode</td>
            <td>:</td>
            <td>
                {{ \Carbon\Carbon::parse($training->tgl_mulai)->translatedFormat('d M Y') }} s.d 
                {{ \Carbon\Carbon::parse($training->tgl_selesai)->translatedFormat('d M Y') }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">Lokasi</td>
            <td>:</td>
            <td>{{ $training->lokasi ?? 'BPSDM Provinsi Jawa Barat' }}</td>

            <td class="meta-label">Total JP Pelatihan</td>
            <td>:</td>
            <td>{{ $training->jp }} JP (Kuota: {{ $training->jumlah_peserta }} Peserta)</td>
        </tr>
    </table>

    <!-- TABEL JADWAL SESI -->
    <table class="schedule-table">
        <thead>
            <tr @if(($s->schedule_type ?? 'learning') === 'break') style="background-color:#fff3cd;" @endif>
                <th width="4%">No</th>
                <th width="14%">Hari / Tanggal</th>
                <th width="11%">Waktu (WIB)</th>
                <th width="24%">Materi / Kegiatan</th>
                <th width="7%">Durasi</th>
                <th width="18%">Tenaga Pengajar / Fasilitator</th>
                <th width="13%">Link Virtual / Zoom</th>
                <th width="11%">PIC Sesi</th>
            </tr>
        </thead>
        <tbody>
            @php $durationTotals = collect(); @endphp
            @forelse($schedules as $index => $s)
            @php $unit = strtoupper($s->duration_unit ?: 'JP'); $durationTotals[$unit] = ($durationTotals[$unit] ?? 0) + (int) $s->jp; @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <span class="text-bold">{{ \Carbon\Carbon::parse($s->date)->translatedFormat('l') }}</span><br>
                    {{ \Carbon\Carbon::parse($s->date)->translatedFormat('d F Y') }}
                </td>
                <td class="text-center">
                    {{ $s->start_time }} - {{ $s->end_time }}
                </td>
                <td>
                    <span class="text-bold">{{ $s->activity }}</span>
                    @if(($s->schedule_type ?? 'learning') === 'break')<br><span style="font-size:7.5pt;color:#9a6700;">ISTIRAHAT / JEDA</span>@endif
                </td>
                <td class="text-center text-bold">
                    {{ ($s->schedule_type ?? 'learning') === 'break' ? '-' : $s->duration_label }}
                </td>
                <td>
                    @if(($s->schedule_type ?? 'learning') === 'break')
                        <span style="color:#777;">-</span>
                    @elseif($s->pengajar)
                        <span class="text-bold">{{ $s->pengajar->name }}</span>
                        @if($s->pengajar->nip_nik)
                            <br><span style="font-size: 7.5pt; color: #555;">NIP: {{ $s->pengajar->nip_nik }}</span>
                        @endif
                    @else
                        <span style="color: #777; font-style: italic;">-</span>
                    @endif
                </td>
                <td>
                    @if($s->link_zoom)
                        <a href="{{ $s->link_zoom }}" class="zoom-link" target="_blank">
                            {{ Str::limit($s->link_zoom, 30) }}
                        </a>
                    @else
                        <span style="color: #888; font-size: 7.5pt;">Tatap Muka / -</span>
                    @endif
                </td>
                <td>
                    {{ $s->pic }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="padding: 25px; color: #777; font-style: italic;">
                    @if(isset($isPengajar) && $isPengajar)
                        Tidak ada sesi jadwal mengajar yang ditugaskan kepada Anda pada pelatihan ini.
                    @else
                        Belum ada sesi jadwal yang ditambahkan untuk pelatihan ini.
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
        @if($schedules->isNotEmpty())
        <tfoot>
            <tr style="background-color: #edf2f7; font-weight: bold;">
                <td colspan="4" style="text-align: right; padding-right: 10px;">
                    TOTAL DURASI {{ isset($isPengajar) && $isPengajar ? 'YANG DIAJARKAN' : 'PELATIHAN' }}:
                </td>
                <td class="text-center">{{ $durationTotals->map(fn($total,$unit) => $total.' '.$unit)->join(' + ') }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
        @endif
    </table>
    
</body>
</html>
