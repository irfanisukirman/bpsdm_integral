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
                        <div class="mb-3">
                            <label for="filterProvinsi" class="form-label">Pilih Provinsi</label>
                            <select id="filterProvinsi" class="form-select" onchange="filterProvinsi(this.value)">
                                <option value="">-- Semua Provinsi --</option>
                            </select>
                            </label>
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

            // 3. Chart Provinsi
            new Chart(document.getElementById('chartProvinsi'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($provinsiStats->keys()) !!},
                    datasets: [{
                        label: 'Jumlah Alumni',
                        data: {!! json_encode($provinsiStats->values()) !!},
                        backgroundColor: '#03c3ec'
                    }]
                },
                options: {
                    indexAxis: 'y'
                }
            });

            // 4. Chart Pendidikan
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

            // 5. Chart Status
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

            // 6. Peta Sebaran Alumni
            const map = L.map('mapAlumni').setView([-2.5, 118.0], 5);

            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'
            }).addTo(map);

            // 7. Data koordinat provinsi (untuk memindahkan peta)
            const provinsiData = {
                "Aceh": [4.695135, 96.749397],
                "Sumatera Utara": [2.115354, 99.545097],
                "Sumatera Barat": [-0.739940, 100.800005],
                "Riau": [0.293347, 101.706829],
                "Kepulauan Riau": [3.945651, 108.142867],
                "Jambi": [-1.610123, 103.613121],
                "Sumatera Selatan": [-3.319437, 103.914398],
                "Bangka Belitung": [-2.741051, 106.440587],
                "Bengkulu": [-3.792845, 102.260765],
                "Lampung": [-4.558585, 105.406807],
                "DKI Jakarta": [-6.208763, 106.845599],
                "Jawa Barat": [-6.914744, 107.609810],
                "Banten": [-6.405817, 106.064018],
                "Jawa Tengah": [-7.150975, 110.140259],
                "DI Yogyakarta": [-7.797068, 110.370529],
                "Jawa Timur": [-7.536064, 112.238402],
                "Bali": [-8.409518, 115.188919],
                "Nusa Tenggara Barat": [-8.652933, 117.361648],
                "Nusa Tenggara Timur": [-8.657382, 121.079370],
                "Kalimantan Barat": [-0.278781, 111.475285],
                "Kalimantan Tengah": [-1.681488, 113.382355],
                "Kalimantan Selatan": [-3.092642, 115.283758],
                "Kalimantan Timur": [0.538659, 116.419389],
                "Kalimantan Utara": [3.073030, 116.041887],
                "Sulawesi Utara": [0.624693, 123.975002],
                "Gorontalo": [0.699944, 122.446724],
                "Sulawesi Tengah": [-1.430025, 121.445618],
                "Sulawesi Barat": [-2.844137, 119.232078],
                "Sulawesi Selatan": [-3.668800, 119.974053],
                "Sulawesi Tenggara": [-4.144910, 122.174605],
                "Maluku": [-3.238462, 130.145273],
                "Maluku Utara": [1.570999, 127.808769],
                "Papua Barat": [-1.336115, 133.174716],
                "Papua": [-4.269928, 138.080353]
            };

            // Isi dropdown dari data di atas
            const dropdown = document.getElementById('filterProvinsi');
            Object.keys(provinsiData).forEach(function(nama) {
                const opt = document.createElement('option');
                opt.value = nama;
                opt.textContent = nama;
                dropdown.appendChild(opt);
            });


            // 8. Marker aktif (biar bisa dihapus saat ganti provinsi)
            let markerAktif = null;

            // 9. Saat dropdown provinsi dipilih
            dropdown.addEventListener('change', function() {
                const nama = this.value;

                // Kalau pilih "Semua Provinsi" -> kembali ke tampilan Indonesia
                if (nama === '') {
                    map.setView([-2.5, 118.0], 5);
                    if (markerAktif) {
                        map.removeLayer(markerAktif);
                        markerAktif = null;
                    }
                    return;
                }

                // Ambil koordinat provinsi terpilih
                const koordinat = provinsiData[nama];

                // Pindahkan peta ke provinsi itu (zoom lebih dekat)
                map.setView(koordinat, 8);

                // Hapus marker lama kalau ada, lalu pasang marker baru
                if (markerAktif) {
                    map.removeLayer(markerAktif);
                }
                markerAktif = L.marker(koordinat).addTo(map)
                    .bindPopup('<b>' + nama + '</b>')
                    .openPopup();
            });


            // Data jumlah alumni per kabupaten (dari controller, dinamis)
            const kabupatenStats = {!! json_encode($kabupatenStats) !!};

            // Kamus koordinat kabupaten (statis, ditambah bertahap sesuai data)
            // PENTING: nama kunci harus SAMA PERSIS dengan di database (huruf kapital)
            const kabupatenKoordinat = {
                "KOTA CIREBON": [-6.7063, 108.5571],
                "KOTA BANDUNG": [-6.9175, 107.6191],
                "KABUPATEN BANDUNG": [-7.0250, 107.5688]
            };


            // Daftar daerah 3T dari controller (statis), disamakan jadi HURUF BESAR biar cocok
            const list3T = {!! json_encode($list3T) !!}.map(function(nama) {
                return nama.toUpperCase();
            });

            // Kumpulan zona, supaya labelnya bisa diatur muncul/sembunyi
            const zonaList = [];

            // Gambar marker untuk tiap kabupaten yang ada datanya
            Object.keys(kabupatenStats).forEach(function(nama) {
                const koordinat = kabupatenKoordinat[nama];

                // Lewati kalau koordinat kabupaten ini belum ada di kamus
                if (!koordinat) {
                    console.warn('Koordinat belum ada untuk:', nama);
                    return;
                }

                const jumlah = kabupatenStats[nama];
                const is3T = list3T.includes(nama.toUpperCase());
                const warna = is3T ? '#ff3e1d' : '#696cff'; // merah = 3T, biru = non-3T

                // Zona wilayah (lingkaran berwarna, radius dalam meter)
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

                // Simpan zona ke wadah
                zonaList.push(zona);
            });


            // === Opsi 2: label hanya muncul saat zoom cukup dekat ===
            const ZOOM_LABEL_MIN = 9; // makin besar angkanya = makin dekat baru label muncul

            function aturLabel() {
                const tampilkan = map.getZoom() >= ZOOM_LABEL_MIN;
                zonaList.forEach(function(zona) {
                    if (tampilkan) {
                        zona.openTooltip(); // munculkan label
                    } else {
                        zona.closeTooltip(); // sembunyikan label
                    }
                });
            }

            map.on('zoomend', aturLabel); // tiap selesai zoom, cek ulang
            aturLabel(); // jalankan sekali saat halaman dibuka
        </script>
    @endpush
@endsection
