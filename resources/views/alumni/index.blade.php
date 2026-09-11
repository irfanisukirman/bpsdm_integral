@extends('layouts.master')

@section('title', 'Statistik Alumni')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">INTEGRAL /</span> Analitik Data Alumni
            </h4>
            <div class="d-flex gap-2">
                <a href="{{ route('alumni.export', ['year' => $year, 'institution' => $institutionFilter, 'minimum_frequency' => $minimumFrequency]) }}" class="btn btn-success shadow-sm">
                    <i class="bx bxs-file-export me-1"></i> Export Statistik Excel
                </a>
                <span class="badge bg-primary"><br>Total: {{ $totalAlumni }} Alumni</span>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-2">
                <ul class="nav nav-pills flex-column flex-md-row gap-2">
                    <li class="nav-item"><a class="nav-link {{ $analysisTab === 'overview' ? 'active' : '' }}" href="{{ route('alumni.index', ['analysis_tab' => 'overview']) }}"><i class="bx bx-bar-chart-alt-2 me-1"></i>Data Alumni</a></li>
                    <li class="nav-item"><a class="nav-link {{ $analysisTab === 'repeat' ? 'active' : '' }}" href="{{ route('alumni.index', ['analysis_tab' => 'repeat', 'year' => $year]) }}"><i class="bx bx-repeat me-1"></i>Peserta Berulang</a></li>
                    <li class="nav-item"><a class="nav-link {{ $analysisTab === 'institution' ? 'active' : '' }}" href="{{ route('alumni.index', ['analysis_tab' => 'institution', 'year' => $year]) }}"><i class="bx bx-buildings me-1"></i>Pemerataan Instansi</a></li>
                </ul>
            </div>
        </div>

        @if($analysisTab === 'overview')
        <div class="row">
            <!-- 1. GENDER & 3T (Pie & Donut) -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0">Komposisi Gender</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartGender" height="250"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title m-0">Wilayah 3T vs Non-3T</h5>
                        <small class="text-muted">Berdasarkan kabupaten/kota</small>
                    </div>
                    <div class="card-body">
                        <canvas id="chart3T" height="250"></canvas>
                        <div class="mt-3 text-center small text-muted">
                            Kategori Tertinggal, Terdepan, dan Terluar mengikuti daftar pada konfigurasi wilayah.
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. STATUS KEPEGAWAIAN -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title m-0">Status Kepegawaian</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartStatus" height="250"></canvas>
                    </div>
                </div>
            </div>

            <!-- 3. SEBARAN WILAYAH (PROVINSI) -->
            <div class="col-md-12 col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-label-primary py-3">
                        <h5 class="card-title mb-0">Sebaran Alumni Seluruh Indonesia</h5>
                    </div>
                    <div class="card-body mt-3">
                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-md-6 col-xl-3">
                                <label for="filterProvinsi" class="form-label">Pilih Provinsi</label>
                                <select id="filterProvinsi" class="form-select">
                                    <option value="">-- Semua Provinsi --</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label for="filterKota" class="form-label">Kabupaten/Kota</label>
                                <select id="filterKota" class="form-select" disabled>
                                    <option value="">-- Semua Kabupaten/Kota --</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label for="filterKecamatan" class="form-label">Kecamatan</label>
                                <select id="filterKecamatan" class="form-select" disabled>
                                    <option value="">-- Semua Kecamatan --</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-3">
                                <label for="filterKelurahan" class="form-label">Kelurahan/Desa</label>
                                <select id="filterKelurahan" class="form-select" disabled>
                                    <option value="">-- Semua Kelurahan/Desa --</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-between align-items-center">
                                <small id="wilayahSummary" class="text-muted"></small>
                                <div class="d-flex align-items-center gap-3">
                                    <button type="button" id="resetWilayah" class="btn btn-sm btn-outline-secondary">
                                        <i class="bx bx-reset me-1"></i> Reset Filter
                                    </button>
                                    <div class="form-check form-switch mb-0" title="Ganti tema peta">
                                    <input class="form-check-input" type="checkbox" role="switch" id="btnTema"
                                        style="cursor: pointer;">
                                    <label class="form-check-label" for="btnTema" id="labelTema"><i
                                            class="bx bx-moon"></i></label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Peta Sebaran Alumni -->
                        <div id="mapAlumni" style="height: 400px; width: 100%; border-radius: 8px;"></div>

                        <div class="table-responsive mt-4 wilayah-table-wrapper">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Peserta</th>
                                        <th>Provinsi</th>
                                        <th>Kabupaten/Kota</th>
                                        <th>Kecamatan</th>
                                        <th>Kelurahan/Desa</th>
                                        <th>Status 3T</th>
                                    </tr>
                                </thead>
                                <tbody id="wilayahTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. DATA PENDIDIKAN (BAR) -->
            <div class="col-md-12 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title m-0">Tingkat Pendidikan Terakhir</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartEdu" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @else
            <div class="alert alert-info border-0 shadow-sm d-flex gap-2 align-items-start">
                <i class="bx bx-info-circle fs-4 flex-shrink-0"></i>
                <div><strong>Indikator informasi, bukan larangan.</strong><div class="small">Data ini membantu melihat pemerataan kesempatan. Keikutsertaan berulang tetap dapat dibenarkan berdasarkan kebutuhan kompetensi dan penugasan.</div></div>
            </div>

            <div class="row g-3 mb-4">
                @foreach([
                    ['label'=>'Peserta Unik','value'=>$fairnessSummary['unique_people'],'suffix'=>'orang','icon'=>'bx-group','color'=>'primary'],
                    ['label'=>'Mengikuti > 1 Pelatihan','value'=>$fairnessSummary['repeat_people'],'suffix'=>'orang','icon'=>'bx-repeat','color'=>'warning'],
                    ['label'=>'Persentase Berulang','value'=>$fairnessSummary['repeat_percentage'],'suffix'=>'%','icon'=>'bx-pie-chart-alt','color'=>'info'],
                    ['label'=>'Frekuensi Tertinggi','value'=>$fairnessSummary['highest_frequency'],'suffix'=>'pelatihan','icon'=>'bx-up-arrow-alt','color'=>'danger'],
                ] as $stat)
                    <div class="col-6 col-xl-3"><div class="card h-100 border-0 shadow-sm"><div class="card-body p-3 p-md-4"><span class="avatar-initial rounded bg-label-{{ $stat['color'] }} p-2 d-inline-flex mb-3"><i class="bx {{ $stat['icon'] }} fs-4"></i></span><small class="text-muted d-block">{{ $stat['label'] }}</small><h3 class="fw-bold mb-0">{{ number_format($stat['value'], $stat['suffix']==='%' ? 1 : 0, ',', '.') }} <small class="fs-6 text-muted">{{ $stat['suffix'] }}</small></h3></div></div></div>
                @endforeach
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom"><h6 class="fw-bold mb-0"><i class="bx bx-filter-alt me-1"></i>Filter Analisis</h6></div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <input type="hidden" name="analysis_tab" value="{{ $analysisTab }}">
                        <div class="col-md-2"><label class="form-label">Tahun</label><select name="year" class="form-select">@foreach($availableYears as $yearOption)<option value="{{ $yearOption }}" @selected($year === $yearOption)>{{ $yearOption }}</option>@endforeach</select></div>
                        <div class="col-md-4"><label class="form-label">Instansi</label><select name="institution" class="form-select"><option value="">Semua instansi</option>@foreach($institutionOptions as $institution)<option value="{{ $institution }}" @selected($institutionFilter === $institution)>{{ $institution }}</option>@endforeach</select></div>
                        @if($analysisTab === 'repeat')<div class="col-md-2"><label class="form-label">Minimal Pelatihan</label><input type="number" name="minimum_frequency" min="2" max="20" class="form-control" value="{{ $minimumFrequency }}"></div>@endif
                        <div class="col-md-3"><label class="form-label">Cari Peserta</label><input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Nama atau NIP/NIK"></div>
                        <div class="col-md-auto"><button class="btn btn-primary"><i class="bx bx-search me-1"></i>Terapkan</button></div>
                    </form>
                </div>
            </div>

            @if($analysisTab === 'repeat')
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center"><div><h5 class="fw-bold mb-1">Frekuensi Keikutsertaan</h5><small class="text-muted">Peserta yang mengikuti minimal {{ $minimumFrequency }} pelatihan pada {{ $year }}.</small></div><span class="badge bg-label-primary">{{ $repeatedPeople->count() }} peserta</span></div>
                    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Peserta</th><th>Instansi</th><th class="text-center">Jumlah</th><th>Bidang Pelatihan</th><th>Pelatihan Terakhir</th><th>Indikator</th><th>Aksi</th></tr></thead><tbody>
                        @forelse($repeatedPeople as $index => $person)
                            @php $indicator = $person['frequency'] >= 4 ? ['Prioritas Pemeriksaan','danger'] : ($person['frequency'] >= 3 ? ['Sering Mengikuti','warning'] : ['Perlu Diperhatikan','info']); @endphp
                            <tr><td><strong>{{ $person['name'] }}</strong><br><small class="text-muted">NIP/NIK: {{ $person['nip_nik'] }}</small></td><td>{{ $person['institution'] }}</td><td class="text-center"><span class="badge bg-label-primary fs-6">{{ $person['frequency'] }}</span></td><td>@foreach($person['fields'] as $field)<span class="badge bg-label-secondary me-1 mb-1">{{ $field }}</span>@endforeach</td><td>{{ $person['last_training']?->nama_pelatihan ?: '-' }}<br><small class="text-muted">{{ $person['last_training']?->tgl_mulai ? \Carbon\Carbon::parse($person['last_training']->tgl_mulai)->translatedFormat('d M Y') : '-' }}</small></td><td><span class="badge bg-label-{{ $indicator[1] }}">{{ $indicator[0] }}</span></td><td><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#historyModal{{ $index }}"><i class="bx bx-history me-1"></i>Riwayat</button></td></tr>
                        @empty<tr><td colspan="7" class="text-center text-muted py-5"><i class="bx bx-check-circle fs-1 d-block mb-2"></i>Tidak ada peserta berulang sesuai filter.</td></tr>@endforelse
                    </tbody></table></div>
                </div>
                @foreach($repeatedPeople as $index => $person)
                    <div class="modal fade" id="historyModal{{ $index }}" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><h5 class="modal-title">Riwayat Pelatihan {{ $year }}</h5><small class="text-muted">{{ $person['name'] }} | {{ $person['nip_nik'] }}</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="list-group list-group-flush">@foreach($person['trainings'] as $record)<div class="list-group-item px-0 py-3"><div class="d-flex justify-content-between gap-3"><div><strong>{{ $record->training?->nama_pelatihan }}</strong><div class="small text-muted mt-1">{{ $record->training?->bidang }} | {{ $record->training?->tgl_mulai ? \Carbon\Carbon::parse($record->training->tgl_mulai)->translatedFormat('d F Y') : '-' }}</div></div><span class="badge bg-label-primary align-self-start">{{ ucfirst($record->registration_status) }}</span></div></div>@endforeach</div></div></div></div></div>
                @endforeach
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-header border-bottom"><h5 class="fw-bold mb-1">Pemerataan Perwakilan Instansi</h5><small class="text-muted">Rasio = peserta unik dibagi total keikutsertaan. Semakin rendah, semakin sering kuota diisi orang yang sama.</small></div>
                    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Instansi</th><th class="text-center">Total Keikutsertaan</th><th class="text-center">Peserta Unik</th><th class="text-center">Peserta Berulang</th><th style="min-width:220px">Rasio Pemerataan</th><th>Status</th></tr></thead><tbody>
                        @forelse($institutionRows as $row)<tr><td><strong>{{ $row['institution'] }}</strong></td><td class="text-center">{{ $row['participations'] }}</td><td class="text-center">{{ $row['uniquePeople'] }}</td><td class="text-center">{{ $row['repeatPeople'] }}</td><td><div class="d-flex justify-content-between small mb-1"><span>Peserta unik</span><strong>{{ $row['ratio'] }}%</strong></div><div class="progress" style="height:8px"><div class="progress-bar bg-{{ $row['color'] }}" style="width:{{ $row['ratio'] }}%"></div></div></td><td><span class="badge bg-label-{{ $row['color'] }}">{{ $row['label'] }}</span></td></tr>@empty<tr><td colspan="6" class="text-center text-muted py-5">Data instansi tidak ditemukan.</td></tr>@endforelse
                    </tbody></table></div>
                </div>
            @endif
        @endif
    </div>

    @push('css')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            /* Label jumlah alumni di tengah zona */
            .label-kabupaten {
                background: transparent;
                border: none;
                box-shadow: none;
                color: #ffffff;
                font-weight: 600;
                text-align: center;
                text-shadow: 0 1px 3px rgba(0, 0, 0, 0.9);
                white-space: nowrap;
            }

            /* buang panah kecil bawaan tooltip */
            .label-kabupaten::before {
                display: none;
            }

            .tooltip-provinsi {
                border: 0;
                border-radius: 10px;
                box-shadow: 0 8px 24px rgba(0, 0, 0, .22);
                padding: 10px 12px;
                color: #233446;
            }

            .wilayah-table-wrapper {
                max-height: 420px;
                overflow-y: auto;
            }

            .wilayah-table-wrapper thead th {
                position: sticky;
                top: 0;
                z-index: 1;
            }
        </style>
    @endpush

    @push('js')
        @if($analysisTab === 'overview')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            // Konfigurasi Warna
            const colors = ['#696cff', '#03c3ec', '#71dd37', '#ffab00', '#ff3e1d', '#233446'];

            // 1. Chart Gender
            new Chart(document.getElementById('chartGender'), {
                type: 'pie',
                data: {
                    labels: {!! json_encode(array_keys($genderStats)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($genderStats)) !!},
                        backgroundColor: ['#696cff', '#ff3e1d']
                    }]
                }
            });

            // 2. Chart 3T
            new Chart(document.getElementById('chart3T'), {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($stats3T)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($stats3T)) !!},
                        backgroundColor: ['#ffab00', '#71dd37']
                    }]
                },
                options: {
                    cutout: '70%'
                }
            });

            // 3. Chart Pendidikan
            new Chart(document.getElementById('chartEdu'), {
                type: 'polarArea',
                data: {
                    labels: {!! json_encode($eduStats->pluck('edu_current')) !!},
                    datasets: [{
                        data: {!! json_encode($eduStats->pluck('total')) !!},
                        backgroundColor: colors
                    }]
                }
            });

            // 4. Chart Status
            new Chart(document.getElementById('chartStatus'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($statusStats->keys()) !!},
                    datasets: [{
                        label: 'Peserta',
                        data: {!! json_encode($statusStats->values()) !!},
                        backgroundColor: '#696cff'
                    }]
                }
            });

            // =========================================================
            // PETA SEBARAN ALUMNI
            // =========================================================

            // Inisialisasi peta
            const map = L.map('mapAlumni').setView([-2.5, 118.0], 5);
            const detailMarkerLayer = L.layerGroup().addTo(map);

            // Tema dasar: gelap
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
            }).addTo(map);

            // Data koordinat provinsi (dari config)
            const provinsiData = {!! json_encode($koordinatProvinsi) !!};
            const wilayahData = {!! json_encode($wilayahRows->values()) !!};

            const dropdown = document.getElementById('filterProvinsi');
            const kotaDropdown = document.getElementById('filterKota');
            const kecamatanDropdown = document.getElementById('filterKecamatan');
            const kelurahanDropdown = document.getElementById('filterKelurahan');
            const wilayahTableBody = document.getElementById('wilayahTableBody');
            const wilayahSummary = document.getElementById('wilayahSummary');

            function uniqueValues(rows, key) {
                return [...new Set(rows.map(row => row[key]).filter(Boolean))]
                    .sort((a, b) => a.localeCompare(b, 'id'));
            }

            function fillSelect(select, values, placeholder) {
                select.innerHTML = `<option value="">${placeholder}</option>`;
                values.forEach(value => select.add(new Option(value, value)));
                select.disabled = values.length === 0;
            }

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value ?? '';
                return div.innerHTML;
            }

            function filteredWilayahRows() {
                return wilayahData.filter(row =>
                    (!dropdown.value || row.provinsi === dropdown.value) &&
                    (!kotaDropdown.value || row.kota === kotaDropdown.value) &&
                    (!kecamatanDropdown.value || row.kecamatan === kecamatanDropdown.value) &&
                    (!kelurahanDropdown.value || row.kelurahan === kelurahanDropdown.value)
                );
            }

            function renderWilayahTable() {
                const rows = filteredWilayahRows();
                const total3T = rows.filter(row => row.is_3t).length;
                const totalBertitik = rows.filter(hasExactCoordinate).length;
                wilayahSummary.textContent = `${rows.length} alumni ditemukan · ${totalBertitik} memiliki titik lokasi${total3T ? ` · ${total3T} dari wilayah 3T` : ''}`;
                renderDetailMarkers(rows);

                if (!rows.length) {
                    wilayahTableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data alumni pada wilayah ini.</td></tr>';
                    return;
                }

                wilayahTableBody.innerHTML = rows.map(row => {
                    const status3T = row.is_3t
                        ? row.kategori_3t.map(item => `<span class="badge bg-label-danger me-1">${escapeHtml(item)}</span>`).join('')
                        : '<span class="badge bg-label-success">Non-3T</span>';

                    return `<tr>
                        <td><strong>${escapeHtml(row.nama)}</strong><br><small class="text-muted">${escapeHtml(row.nip_nik)}</small></td>
                        <td>${escapeHtml(row.provinsi)}</td>
                        <td>${escapeHtml(row.kota)}</td>
                        <td>${escapeHtml(row.kecamatan)}</td>
                        <td>${escapeHtml(row.kelurahan)}</td>
                        <td>${status3T}</td>
                    </tr>`;
                }).join('');
            }

            function hasExactCoordinate(row) {
                return row.latitude !== null && row.latitude !== '' &&
                    row.longitude !== null && row.longitude !== '' &&
                    Number.isFinite(Number(row.latitude)) && Number.isFinite(Number(row.longitude));
            }

            function renderDetailMarkers(rows) {
                detailMarkerLayer.clearLayers();
                const groups = new Map();
                rows.filter(hasExactCoordinate).forEach(row => {
                    const lat = Number(row.latitude);
                    const lng = Number(row.longitude);
                    const key = lat.toFixed(5) + '|' + lng.toFixed(5) + '|' + row.kelurahan;
                    if (!groups.has(key)) groups.set(key, { lat, lng, rows: [] });
                    groups.get(key).rows.push(row);
                });

                groups.forEach(group => {
                    const sample = group.rows[0];
                    const count = group.rows.length;
                    const content = '<div style="min-width:190px"><strong>' + escapeHtml(sample.kelurahan) +
                        '</strong><br><span>' + escapeHtml(sample.kecamatan) + ', ' + escapeHtml(sample.kota) +
                        '</span><br><span style="font-size:17px;font-weight:700;color:#696cff">' + count +
                        '</span> alumni</div>';
                    L.circleMarker([group.lat, group.lng], {
                        radius: Math.min(7 + count, 15),
                        color: '#ffffff',
                        weight: 2,
                        fillColor: '#03c3ec',
                        fillOpacity: 0.9
                    }).bindTooltip(content, { direction: 'top' }).bindPopup(content).addTo(detailMarkerLayer);
                });
            }

            fillSelect(dropdown, uniqueValues(wilayahData, 'provinsi'), '-- Semua Provinsi --');
            renderWilayahTable();

            dropdown.addEventListener('change', function() {
                const rows = wilayahData.filter(row => !this.value || row.provinsi === this.value);
                fillSelect(kotaDropdown, uniqueValues(rows, 'kota'), '-- Semua Kabupaten/Kota --');
                fillSelect(kecamatanDropdown, [], '-- Semua Kecamatan --');
                fillSelect(kelurahanDropdown, [], '-- Semua Kelurahan/Desa --');
                renderWilayahTable();
            });

            kotaDropdown.addEventListener('change', function() {
                const rows = wilayahData.filter(row =>
                    (!dropdown.value || row.provinsi === dropdown.value) &&
                    (!this.value || row.kota === this.value)
                );
                fillSelect(kecamatanDropdown, uniqueValues(rows, 'kecamatan'), '-- Semua Kecamatan --');
                fillSelect(kelurahanDropdown, [], '-- Semua Kelurahan/Desa --');
                renderWilayahTable();
                updateProvinsiTooltip();
            });

            kecamatanDropdown.addEventListener('change', function() {
                const rows = wilayahData.filter(row =>
                    (!dropdown.value || row.provinsi === dropdown.value) &&
                    (!kotaDropdown.value || row.kota === kotaDropdown.value) &&
                    (!this.value || row.kecamatan === this.value)
                );
                fillSelect(kelurahanDropdown, uniqueValues(rows, 'kelurahan'), '-- Semua Kelurahan/Desa --');
                renderWilayahTable();
                updateProvinsiTooltip();
            });

            kelurahanDropdown.addEventListener('change', function() {
                renderWilayahTable();
                updateProvinsiTooltip();
            });

            document.getElementById('resetWilayah').addEventListener('click', function() {
                dropdown.value = '';
                dropdown.dispatchEvent(new Event('change'));
            });

            // Marker & batas provinsi aktif
            let markerAktif = null;
            let batasProvinsi = null;
            let geojsonProvinsi = null;

            function provinsiTooltipContent() {
                const namaProvinsi = dropdown.value || 'SEMUA PROVINSI';
                const jumlah = filteredWilayahRows().length;
                return `<div class="text-center">
                    <strong>${escapeHtml(namaProvinsi)}</strong><br>
                    <span style="font-size:18px;font-weight:700;color:#696cff;">${jumlah}</span>
                    <span> peserta</span>
                </div>`;
            }

            function updateProvinsiTooltip() {
                const content = provinsiTooltipContent();
                if (batasProvinsi) {
                    batasProvinsi.bindTooltip(content, {
                        sticky: true,
                        direction: 'top',
                        className: 'tooltip-provinsi'
                    });
                }
                if (markerAktif) {
                    markerAktif.bindPopup(content).bindTooltip(content, {
                        direction: 'top',
                        className: 'tooltip-provinsi'
                    });
                }
            }

            // Muat file GeoJSON provinsi sekali saat halaman dibuka
            fetch("{{ asset('geojson/indonesia-38-provinces.geojson') }}")
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    geojsonProvinsi = data;
                    // BANTUAN: lihat nama properti & ejaan provinsi di file kamu
                    console.log('Contoh properties:', data.features[0].properties);
                })
                .catch(function(err) {
                    console.error('Gagal memuat GeoJSON:', err);
                });

            // Saat dropdown provinsi dipilih
            dropdown.addEventListener('change', function() {
                const nama = this.value;

                // Pilih "Semua Provinsi" -> kembali ke tampilan Indonesia
                if (nama === '') {
                    map.setView([-2.5, 118.0], 5);
                    if (markerAktif) {
                        map.removeLayer(markerAktif);
                        markerAktif = null;
                    }
                    if (batasProvinsi) {
                        map.removeLayer(batasProvinsi);
                        batasProvinsi = null;
                    }
                    return;
                }

                // Pindahkan peta + pasang marker
                const provinsiKey = Object.keys(provinsiData)
                    .find(key => key.toUpperCase() === nama.toUpperCase());
                const koordinat = provinsiKey ? provinsiData[provinsiKey] : null;

                if (koordinat) {
                    map.setView(koordinat, 8);
                    if (markerAktif) {
                        map.removeLayer(markerAktif);
                    }
                    markerAktif = L.marker(koordinat).addTo(map)
                        .bindPopup(provinsiTooltipContent())
                        .bindTooltip(provinsiTooltipContent(), {
                            direction: 'top',
                            className: 'tooltip-provinsi'
                        })
                        .openPopup();
                }

                // Hapus batas lama, gambar batas provinsi terpilih
                if (batasProvinsi) {
                    map.removeLayer(batasProvinsi);
                    batasProvinsi = null;
                }

                if (geojsonProvinsi) {
                    const fitur = geojsonProvinsi.features.filter(function(f) {
                        // GANTI 'Propinsi' sesuai nama properti di file GeoJSON-mu
                        const namaGeo = (f.properties.Propinsi || f.properties.PROVINSI || f.properties.state ||
                            '');
                        return namaGeo.toUpperCase() === nama.toUpperCase();
                    });

                    if (fitur.length) {
                        batasProvinsi = L.geoJSON(fitur, {
                            style: {
                                color: '#ffab00',
                                weight: 3,
                                fill: false
                            } // outline saja
                        }).addTo(map)
                            .bindTooltip(provinsiTooltipContent(), {
                                sticky: true,
                                direction: 'top',
                                className: 'tooltip-provinsi'
                            });
                        map.fitBounds(batasProvinsi.getBounds()); // pas-kan zoom ke provinsi
                    }
                }
            });

            // Data jumlah alumni per kabupaten (dari controller, dinamis)
            const kabupatenStats = {!! json_encode($kabupatenStats) !!};

            // Kamus koordinat kabupaten/kota (dari config)
            const kabupatenKotaKoordinat = {!! json_encode($koordinatKabupaten) !!};

            // Daftar daerah 3T dari controller, disamakan jadi HURUF BESAR biar cocok
            const list3T = {!! json_encode($list3T) !!}.map(function(nama) {
                return nama.toUpperCase();
            });

            // Kumpulan zona, supaya labelnya bisa diatur muncul/sembunyi
            const zonaList = [];

            // Gambar zona untuk tiap kabupaten yang ada datanya
            Object.keys(kabupatenStats).forEach(function(nama) {
                const koordinat = kabupatenKotaKoordinat[nama];

                // Lewati kalau koordinat kabupaten ini belum ada di kamus
                if (!koordinat) {
                    console.warn('Koordinat belum ada untuk:', nama);
                    return;
                }

                const jumlah = kabupatenStats[nama];
                const is3T = list3T.includes(nama.toUpperCase());
                const warna = is3T ? '#ff3e1d' : '#696cff'; // merah = 3T, biru = non-3T

                const zona = L.circle(koordinat, {
                        radius: 8000, // 8 km
                        color: warna,
                        weight: 2,
                        fillColor: warna,
                        fillOpacity: 0.35
                    }).addTo(map)
                    .bindPopup(
                        '<b>' + nama + '</b><br>' +
                        'Jumlah Alumni: ' + jumlah + '<br>' +
                        (is3T ? '<span style="color:#ff3e1d;">Wilayah 3T</span>' : 'Non-3T')
                    )
                    .bindTooltip(
                        '<b>' + jumlah + '</b> alumni<br>' + (is3T ? '3T' : 'Non-3T'), {
                            permanent: true,
                            direction: 'center',
                            className: 'label-kabupaten'
                        }
                    );

                zonaList.push(zona);
            });

            // Label hanya muncul saat zoom cukup dekat (anti-tumpuk)
            const ZOOM_LABEL_MIN = 9; // makin besar = makin dekat baru label muncul

            function aturLabel() {
                const tampilkan = map.getZoom() >= ZOOM_LABEL_MIN;
                zonaList.forEach(function(zona) {
                    if (tampilkan) {
                        zona.openTooltip();
                    } else {
                        zona.closeTooltip();
                    }
                });
            }

            map.on('zoomend', aturLabel);
            aturLabel();

            // Toggle tema: layer terang ditumpuk di atas peta gelap
            const tileLight = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
            });

            const labelTema = document.getElementById('labelTema');
            document.getElementById('btnTema').addEventListener('change', function() {
                if (this.checked) {
                    tileLight.addTo(map); // tampilkan terang (menutup gelap)
                    labelTema.innerHTML = '<i class="bx bx-sun"></i>';
                } else {
                    map.removeLayer(tileLight); // lepas terang -> gelap muncul lagi
                    labelTema.innerHTML = '<i class="bx bx-moon"></i>';
                }
            });
        </script>
        @endif
    @endpush
@endsection
