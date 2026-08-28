@extends('layouts.master')

@section('title', 'Statistik Alumni')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">INTEGRAL /</span> Analitik Data Alumni
            </h4>
            <div class="d-flex gap-2">
                <a href="{{ route('alumni.export') }}" class="btn btn-success shadow-sm">
                    <i class="bx bxs-file-export me-1"></i> Export Statistik Excel
                </a>
                <span class="badge bg-primary">Total: {{ $totalAlumni }} Alumni</span>
            </div>
        </div>

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
                        <small class="text-muted">Ref: Kota Cimahi</small>
                    </div>
                    <div class="card-body">
                        <canvas id="chart3T" height="250"></canvas>
                        <div class="mt-3 text-center small text-muted">
                            Berdasarkan domisili peserta terhadap indeks wilayah terpencil.
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
                        <div class="d-flex align-items-end gap-3 mb-3">
                            <div class="flex-grow-1">
                                <label for="filterProvinsi" class="form-label">Pilih Provinsi</label>
                                <select id="filterProvinsi" class="form-select">
                                    <option value="">-- Semua Provinsi --</option>
                                </select>
                            </div>
                            <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                                style="height: 38px; width: 64px;" title="Ganti tema peta">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="btnTema"
                                        style="cursor: pointer;">
                                    <label class="form-check-label" for="btnTema" id="labelTema"><i
                                            class="bx bx-moon"></i></label>
                                </div>
                            </div>
                        </div>

                        <!-- Peta Sebaran Alumni -->
                        <div id="mapAlumni" style="height: 400px; width: 100%; border-radius: 8px;"></div>
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
        </style>
    @endpush

    @push('js')
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

            // Tema dasar: gelap
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
            }).addTo(map);

            // Data koordinat provinsi (dari config)
            const provinsiData = {!! json_encode($koordinatProvinsi) !!};

            // Isi dropdown dari data provinsi
            const dropdown = document.getElementById('filterProvinsi');
            Object.keys(provinsiData).forEach(function(nama) {
                const opt = document.createElement('option');
                opt.value = nama;
                opt.textContent = nama;
                dropdown.appendChild(opt);
            });

            // Marker & batas provinsi aktif
            let markerAktif = null;
            let batasProvinsi = null;
            let geojsonProvinsi = null;

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
                const koordinat = provinsiData[nama];
                map.setView(koordinat, 8);

                if (markerAktif) {
                    map.removeLayer(markerAktif);
                }
                markerAktif = L.marker(koordinat).addTo(map)
                    .bindPopup('<b>' + nama + '</b>')
                    .openPopup();

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
                        }).addTo(map);
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
    @endpush
@endsection