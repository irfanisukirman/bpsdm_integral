<table>
    <tr><th colspan="4" style="background-color: #FFA500; font-weight: bold;">INFORMASI</th></tr>
    <tr><td>NAMA PELATIHAN</td><td colspan="3">{{ $training->nama_pelatihan }}</td></tr>
    <tr><td>Angkatan</td><td colspan="3">{{ $training->angkatan }}</td></tr>
    <tr><td>Tanggal</td><td colspan="3">{{ $training->tgl_mulai }} - {{ $training->tgl_selesai }}</td></tr>
    <tr></tr>
    <tr><td>Peserta Angkatan {{ $training->angkatan }}</td><td colspan="3">{{ $totalPeserta }}</td></tr>
    <tr></tr>
    <tr><td>Responden</td><td>Sudah Isi</td><td>Belum Isi</td></tr>
    <tr><td>Peserta (Mandiri)</td><td>{{ $stats['mandiri'] }}</td><td>{{ $totalPeserta - $stats['mandiri'] }}</td></tr>
    <tr><td>Atasan</td><td>{{ $stats['atasan'] }}</td><td>{{ $totalPeserta - $stats['atasan'] }}</td></tr>
    <tr><td>Rekan</td><td>{{ $stats['rekan'] }}</td><td>{{ $totalPeserta - $stats['rekan'] }}</td></tr>
    <tr></tr>
    <tr><th style="background-color: #FFA500; font-weight: bold;">Asal Instansi Peserta</th><th style="background-color: #FFA500; font-weight: bold;">Jumlah</th></tr>
    @foreach($instansi as $ins)
    <tr><td>{{ $ins->instansi }}</td><td>{{ $ins->total }}</td></tr>
    @endforeach
    <tr><td style="background-color: #FFA500; font-weight: bold;">TOTAL</td><td style="background-color: #FFA500;">{{ $totalPeserta }}</td></tr>
</table>