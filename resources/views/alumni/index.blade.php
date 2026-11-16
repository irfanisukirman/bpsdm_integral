@extends('layouts.master')

@section('title', 'Statistik Alumni')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y alumni-page">
        <header class="alumni-hero mb-4">
            <div><span class="alumni-eyebrow">INTEGRAL / ANALITIK ALUMNI</span><h3 class="fw-bold text-white mt-2 mb-2">Profil alumni, dalam satu pandangan.</h3><p class="mb-3">Pantau komposisi alumni dan telusuri sebarannya hingga kelurahan.</p><span class="alumni-scope"><i class="bx bx-buildings me-1" aria-hidden="true"></i>{{ Auth::user()->role==='superadmin'?'Semua bidang':Auth::user()->bidang }}</span></div>
            <a href="{{ route('alumni.export', ['year' => $year, 'institution' => $institutionFilter, 'minimum_frequency' => $minimumFrequency]) }}" class="btn btn-light alumni-export"><i class="bx bx-download me-2" aria-hidden="true"></i>Export Statistik Excel</a>
        </header>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-2">
                <ul class="nav nav-pills alumni-tabs gap-2" aria-label="Analisis alumni">
                    <li class="nav-item"><a class="nav-link {{ $analysisTab === 'overview' ? 'active' : '' }}" href="{{ route('alumni.index', ['analysis_tab' => 'overview']) }}"><i class="bx bx-bar-chart-alt-2 me-1"></i>Data Alumni</a></li>
                    <li class="nav-item"><a class="nav-link {{ $analysisTab === 'repeat' ? 'active' : '' }}" href="{{ route('alumni.index', ['analysis_tab' => 'repeat', 'year' => $year]) }}"><i class="bx bx-repeat me-1"></i>Peserta Berulang</a></li>
                    <li class="nav-item"><a class="nav-link {{ $analysisTab === 'institution' ? 'active' : '' }}" href="{{ route('alumni.index', ['analysis_tab' => 'institution', 'year' => $year]) }}"><i class="bx bx-buildings me-1"></i>Pemerataan Instansi</a></li>
                </ul>
            </div>
        </div>

        @if($analysisTab === 'overview')
            @include('alumni.overview')
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
            .alumni-page{--alumni-indigo:#6366f1;--alumni-teal:#14b8a6;--alumni-amber:#f4b740;--alumni-rose:#ec7897;--alumni-ink:#233752}
            .alumni-page .alumni-hero{display:flex;align-items:center;justify-content:space-between;gap:28px;position:relative;overflow:hidden;padding:32px;border-radius:18px;background:radial-gradient(ellipse at 95% 10%,rgba(75,156,244,.45),transparent 50%),linear-gradient(120deg,#172e55,#39488f);box-shadow:0 12px 30px rgba(37,57,109,.12)}
            .alumni-page .alumni-hero h3{font-size:clamp(1.35rem,2.4vw,1.9rem);letter-spacing:-.035em}.alumni-page .alumni-hero p{color:#d3ddf3;font-size:.9rem}.alumni-page .alumni-eyebrow{color:#b2c9f1;font-size:.68rem;font-weight:700;letter-spacing:.12em}.alumni-page .alumni-scope{display:inline-flex;align-items:center;padding:6px 10px;border-radius:7px;background:rgba(255,255,255,.1);color:#edf2ff;font-size:.75rem}.alumni-page .alumni-export{flex-shrink:0;min-height:42px;border:0;color:#2e4275;font-weight:600;box-shadow:0 4px 15px rgba(0,0,0,.06)}
            .alumni-page .alumni-tabs{flex-wrap:nowrap;overflow-x:auto;padding:3px}.alumni-page .alumni-tabs .nav-link{white-space:nowrap;border-radius:8px;padding:11px 18px;color:#617089;font-weight:600;font-size:.85rem}.alumni-page .alumni-tabs .nav-link.active{background:#6366f1;color:#fff;box-shadow:0 4px 12px rgba(99,102,241,.16)}
            .alumni-page .alumni-stat{background:#fff;border:1px solid #e8edf4;border-radius:13px;padding:20px;box-shadow:0 3px 10px rgba(35,55,82,.025)}.alumni-page .alumni-stat small{font-size:.8rem;color:#6c7b90}.alumni-page .alumni-stat>strong{display:block;font-size:2rem;color:#233752;line-height:1.2;letter-spacing:-.04em;margin:10px 0 6px}.alumni-page .alumni-stat p{font-size:.72rem;color:#8290a3;margin:0;line-height:1.5}.alumni-page .alumni-stat-icon{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;flex-shrink:0;font-size:1.25rem}.alumni-page .alumni-stat-icon.is-indigo{color:#6366f1;background:#eeeefe}.alumni-page .alumni-stat-icon.is-teal{color:#0f9c8b;background:#e5f7f2}.alumni-page .alumni-stat-icon.is-amber{color:#bc8616;background:#fff5db}.alumni-page .alumni-stat-icon.is-blue{color:#4587ce;background:#eaf3ff}
            .alumni-page .alumni-panel{border:1px solid #e8edf4;border-radius:14px;box-shadow:0 3px 12px rgba(35,55,82,.025);overflow:hidden}.alumni-page .alumni-panel .card-header{padding:24px;border:0;background:#fff}.alumni-page .alumni-panel h5{color:#233752;font-size:1rem}.alumni-page .alumni-panel-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px}.alumni-page .alumni-section-kicker{font-size:.66rem;font-weight:700;letter-spacing:.1em;color:#8190a7}.alumni-page .alumni-soft-badge{display:inline-flex;align-items:center;white-space:nowrap;padding:7px 11px;border-radius:8px;background:#f0f1ff;color:#6267c4;font-size:.75rem}.alumni-page .alumni-map-filters{background:#f8fafe;border:1px solid #eaf0f6;border-radius:10px;padding:18px}.alumni-page .alumni-map-filters .form-select{background-color:#fff;font-size:.8rem;min-height:42px}.alumni-page .alumni-map-frame{border-radius:12px;overflow:hidden;border:1px solid #e7ecf4}.alumni-page #mapAlumni{width:100%;height:440px;z-index:0;background:#edf2f8}.alumni-page .alumni-map-caption{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-top:16px;font-size:.73rem;color:#73829a}.alumni-page .alumni-map-caption .form-check-label{font-size:.75rem}.alumni-page .alumni-legend-dot{display:inline-block;flex-shrink:0;width:8px;height:8px;border-radius:50%;margin-right:7px;vertical-align:middle}.alumni-page .alumni-legend-dot.is-indigo{background:#6366f1}.alumni-page .alumni-legend-dot.is-teal{background:#14b8a6}.alumni-page .alumni-legend-dot.is-amber{background:#f4b740}.alumni-page .alumni-legend-dot.is-rose{background:#ff3e1d}
            .alumni-page .alumni-search{position:relative;width:280px;max-width:100%}.alumni-page .alumni-search>.bx{position:absolute;top:12px;left:12px;color:#8190a7}.alumni-page .alumni-search .form-control{padding-left:36px;font-size:.8rem;min-height:40px}.alumni-page .alumni-region-table{min-width:900px;font-size:.8rem}.alumni-page .alumni-region-table th{background:#f7f9fc!important;font-size:.67rem;letter-spacing:.035em;white-space:nowrap;color:#75839a}.alumni-page .alumni-region-table td,.alumni-page .alumni-region-table th{padding:14px 18px;vertical-align:middle}.alumni-page .alumni-region-table td{white-space:normal;overflow-wrap:anywhere;max-width:220px}.alumni-page .alumni-region-table .badge{white-space:normal;line-height:1.45}.alumni-page .alumni-table-footer{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;padding:16px 24px;border-top:1px solid #edf0f5}.alumni-page .alumni-table-footer .btn{min-height:36px}.alumni-page .alumni-section-line{height:1px;background:#e0e7f0;flex:1}
            .alumni-page .alumni-donut-layout{display:flex;align-items:center;justify-content:center;gap:28px;min-height:220px;flex-wrap:wrap}.alumni-page .alumni-donut{position:relative;width:190px;height:190px;flex-shrink:0}.alumni-page .alumni-donut-center{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;pointer-events:none}.alumni-page .alumni-donut-center strong{font-size:1.6rem;letter-spacing:-.04em;color:#233752}.alumni-page .alumni-donut-center small{font-size:.7rem;color:#8290a4}.alumni-page .alumni-chart-legend{flex:1;min-width:150px}.alumni-page .alumni-chart-legend>div{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:12px 0;border-bottom:1px solid #edf1f6;font-size:.8rem}.alumni-page .alumni-chart-legend strong{color:#233752}.alumni-page .alumni-chart-details summary{cursor:pointer;padding:8px 0}.alumni-page .alumni-chart-details .alumni-chart-legend>div{padding:8px 0}.alumni-page .alumni-bar-chart{position:relative;height:270px;width:100%}
            @media(max-width:767px){.alumni-page .alumni-hero{padding:24px;align-items:flex-start;flex-direction:column;gap:18px}.alumni-page .alumni-export{width:100%;justify-content:center}.alumni-page .alumni-stat{padding:14px}.alumni-page .alumni-stat>strong{font-size:1.7rem}.alumni-page .alumni-stat-icon{width:30px;height:30px;font-size:1.1rem}.alumni-page .alumni-stat small{font-size:.72rem}.alumni-page .alumni-panel .card-header{padding:18px}.alumni-page .alumni-panel .card-body{padding:18px}.alumni-page .alumni-map-filters{padding:14px}.alumni-page #mapAlumni{height:340px}.alumni-page .alumni-search{width:100%}.alumni-page .alumni-table-footer{padding:14px 18px}.alumni-page .alumni-donut-layout{gap:16px}.alumni-page .alumni-tabs .nav-link{padding:10px 12px;font-size:.78rem}}

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
            const colors = ['#6366f1', '#14b8a6', '#f4b740', '#4b9cf4', '#ec7897', '#8b82d8'];
            const numberLabel = value => Number(value || 0).toLocaleString('id-ID');
            const chartFont = { family: "'Public Sans', sans-serif", size: 12 };
            if (typeof Chart !== 'undefined') {
                function donutChart(id, labels, values, palette) {
                    new Chart(document.getElementById(id), {
                        type: 'doughnut',
                        data: { labels, datasets: [{ data: values, backgroundColor: palette, borderWidth: 3, borderColor: '#ffffff', hoverOffset: 5 }] },
                        options: { responsive: true, maintainAspectRatio: false, cutout: '76%', plugins: { legend: { display: false }, tooltip: { padding: 12, backgroundColor: '#172b4d', callbacks: { label: context => context.label + ': ' + numberLabel(context.parsed) } } } }
                    });
                }
                function barChart(id, labels, values, color) {
                    new Chart(document.getElementById(id), {
                        type: 'bar',
                        data: { labels, datasets: [{ label: 'Data alumni', data: values, backgroundColor: color, borderRadius: 5, borderSkipped: false, maxBarThickness: 22 }] },
                        options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', layout: { padding: { right: 16 } }, plugins: { legend: { display: false }, tooltip: { padding: 12, backgroundColor: '#172b4d', callbacks: { label: context => numberLabel(context.parsed.x) + ' data' } } }, scales: { x: { beginAtZero: true, border: { display: false }, grid: { color: '#edf1f7' }, ticks: { precision: 0, color: '#8290a4', font: chartFont } }, y: { border: { display: false }, grid: { display: false }, ticks: { color: '#52627a', font: chartFont, callback: function(value) { const label = this.getLabelForValue(value); return label.length > 32 ? label.slice(0, 30) + '?' : label; } } } } }
                    });
                }
                donutChart('chartGender', {!! json_encode(array_keys($genderStats)) !!}, {!! json_encode(array_values($genderStats)) !!}, ['#6366f1', '#14b8a6']);
                donutChart('chart3T', {!! json_encode(array_keys($stats3T)) !!}, {!! json_encode(array_values($stats3T)) !!}, ['#f4b740', '#14b8a6']);
                barChart('chartEdu', {!! json_encode($eduStats->pluck('edu_current')->map(fn($label)=>filled($label)?$label:'Belum diisi')) !!}, {!! json_encode($eduStats->pluck('total')) !!}, '#14b8a6');
                barChart('chartStatus', {!! json_encode($statusStats->keys()->map(fn($label)=>filled($label)?$label:'Belum diisi')) !!}, {!! json_encode($statusStats->values()) !!}, '#6366f1');
            }

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
            const alumniSearch = document.getElementById('searchAlumniWilayah');
            const wilayahPrevious = document.getElementById('wilayahPrevious');
            const wilayahNext = document.getElementById('wilayahNext');
            const wilayahPageInfo = document.getElementById('wilayahPageInfo');
            let wilayahPage = 1;
            const wilayahPageSize = 10;


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
                    (!kelurahanDropdown.value || row.kelurahan === kelurahanDropdown.value) &&
                    (!alumniSearch.value.trim() || (row.nama + ' ' + (row.nip_nik || '')).toLocaleLowerCase('id-ID').includes(alumniSearch.value.trim().toLocaleLowerCase('id-ID')))
                );
            }

            function renderWilayahTable(resetPage = true) {
                if (resetPage) wilayahPage = 1;
                const rows = filteredWilayahRows();
                const total3T = rows.filter(row => row.is_3t).length;
                const totalBertitik = rows.filter(hasExactCoordinate).length;
                wilayahSummary.textContent = `${numberLabel(rows.length)} data alumni / ${numberLabel(totalBertitik)} memiliki koordinat / ${numberLabel(total3T)} wilayah 3T`;
                const pages = Math.max(1, Math.ceil(rows.length / wilayahPageSize));
                wilayahPage = Math.min(wilayahPage, pages);
                wilayahPrevious.disabled = wilayahPage <= 1;
                wilayahNext.disabled = wilayahPage >= pages;
                wilayahPageInfo.textContent = rows.length ? `${(wilayahPage-1)*wilayahPageSize+1}-${Math.min(wilayahPage*wilayahPageSize,rows.length)} dari ${numberLabel(rows.length)} data / Halaman ${wilayahPage} dari ${pages}` : 'Tidak ada data untuk ditampilkan';
                renderDetailMarkers(rows);

                if (!rows.length) {
                    wilayahTableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data alumni pada wilayah ini.</td></tr>';
                    return;
                }

                wilayahTableBody.innerHTML = rows.slice((wilayahPage-1)*wilayahPageSize, wilayahPage*wilayahPageSize).map(row => {
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
                        fillColor: '#14b8a6',
                        fillOpacity: 0.9
                    }).bindTooltip(content, { direction: 'top' }).bindPopup(content).addTo(detailMarkerLayer);
                });
            }

            alumniSearch.addEventListener('input', () => { renderWilayahTable(); updateProvinsiTooltip(); });
            wilayahPrevious.addEventListener('click', () => { wilayahPage--; renderWilayahTable(false); });
            wilayahNext.addEventListener('click', () => { wilayahPage++; renderWilayahTable(false); });

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
                alumniSearch.value = '';
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
                const warna = is3T ? '#ff3e1d' : '#6366f1'; // merah = 3T, biru = non-3T

                const zona = L.circle(koordinat, {
                        radius: 8000, // 8 km
                        color: warna,
                        weight: 2,
                        fillColor: warna,
                        fillOpacity: 0.35
                    }).addTo(map)
                    .bindPopup(
                        '<b>' + escapeHtml(nama) + '</b><br>' +
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
                    labelTema.innerHTML = '<i class="bx bx-sun me-1" aria-hidden="true"></i>Peta terang';
                } else {
                    map.removeLayer(tileLight); // lepas terang -> gelap muncul lagi
                    labelTema.innerHTML = '<i class="bx bx-moon me-1" aria-hidden="true"></i>Peta gelap';
                }
            });
        </script>
        @endif
    @endpush
@endsection
