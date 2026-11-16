@push('css')
<style>
.l34-dashboard{--dash-ink:#233752;--dash-muted:#78879b;--dash-indigo:#6366f1}.l34-dashboard .dashboard-section{scroll-margin-top:24px}.l34-dashboard .dashboard-hero{display:flex;justify-content:space-between;gap:32px;padding:30px;border-radius:16px;background:radial-gradient(ellipse at 100% 0%,rgba(20,184,166,.09),transparent 60%),linear-gradient(125deg,#edf3ff,#f4f6ff);color:#233752;border:1px solid #dde5f3}.l34-dashboard .dashboard-kicker{font-size:.67rem;font-weight:700;letter-spacing:.1em;color:#63718b}.l34-dashboard .dashboard-hero-copy{min-width:0;flex:1}.l34-dashboard .dashboard-hero h3{font-size:clamp(1.3rem,2.2vw,1.8rem);letter-spacing:-.03em;line-height:1.5;overflow-wrap:anywhere}.l34-dashboard .dashboard-hero-copy>.d-flex>span{font-size:.72rem;padding:5px 9px;border-radius:6px;background:rgba(99,102,241,.08);color:#54638c}.l34-dashboard .dashboard-hero p{font-size:.85rem;line-height:1.8;color:#566984}.l34-dashboard .dashboard-hero-meta{align-self:center;flex:0 0 235px;padding:18px;border-radius:10px;background:rgba(255,255,255,.7)}.l34-dashboard .dashboard-hero-meta small{display:block;color:#687b93;font-size:.73rem;line-height:1.7}.l34-dashboard .dashboard-hero-meta strong{font-size:.85rem;display:block;margin-top:6px}.l34-dashboard .dashboard-jump{display:flex;gap:8px;overflow-x:auto;padding:3px 0}.l34-dashboard .dashboard-jump a{white-space:nowrap;color:#63718b;border:1px solid #e4e9f1;background:#fff;padding:8px 13px;border-radius:8px;font-size:.77rem}.l34-dashboard .dashboard-jump a:hover{color:#6366f1;border-color:#b9baf8}.l34-dashboard .dashboard-stat{height:100%;padding:20px;border:1px solid #e5eaf2;border-radius:12px;background:#fff;box-shadow:0 3px 12px rgba(30,50,85,.025)}.l34-dashboard .dashboard-stat small{color:var(--dash-muted);font-size:.73rem}.l34-dashboard .dashboard-stat>strong{display:block;margin:12px 0 6px;font-size:1.8rem;color:var(--dash-ink);letter-spacing:-.03em}.l34-dashboard .dashboard-stat .d-flex>span{display:grid;place-items:center;width:34px;height:34px;border-radius:9px;flex-shrink:0;font-size:1.15rem}.l34-dashboard .dashboard-note{padding:16px 20px;background:#f1f5ff;border:1px solid #dce7fa;border-radius:10px;display:flex;gap:12px;color:#536786}.l34-dashboard .dashboard-note>.bx{font-size:1.4rem;flex-shrink:0}.l34-dashboard .dashboard-note strong{font-size:.82rem}.l34-dashboard .dashboard-note small{font-size:.76rem;line-height:1.7}.l34-dashboard .dashboard-section-label{font-size:.65rem;color:#8090a8;font-weight:700;letter-spacing:.1em}.l34-dashboard h5{color:var(--dash-ink);font-size:1rem}.l34-dashboard .dashboard-panel{border:1px solid #e5eaf2;border-radius:13px;box-shadow:0 3px 12px rgba(30,50,85,.025);overflow:hidden}.l34-dashboard .dashboard-panel .card-header{background:#fff;padding:22px;border:0}.l34-dashboard .dashboard-panel .card-header small{font-size:.75rem;line-height:1.6}.l34-dashboard .dashboard-panel .card-body{padding:22px}.l34-dashboard .dashboard-progress{display:flex;height:9px;border-radius:8px;overflow:hidden;background:#edf1f6}.l34-dashboard .dashboard-progress-counts{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:18px}.l34-dashboard .dashboard-progress-counts strong{display:block;font-size:1.5rem;color:#8794a6}.l34-dashboard .dashboard-progress-counts small{display:block;color:var(--dash-muted);font-size:.72rem}.l34-dashboard .dashboard-chart{position:relative;width:100%;min-width:0;height:310px}.l34-dashboard .dashboard-chart>div,.l34-dashboard .dashboard-donut>div{width:100%;height:100%}.l34-dashboard .chart-indicator{height:360px}.l34-dashboard .dashboard-donut{height:200px;position:relative}.l34-dashboard .dashboard-distribution>div{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:8px 0;font-size:.76rem;border-bottom:1px solid #f0f2f6}.l34-dashboard .dashboard-distribution i{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:8px}.l34-dashboard .dashboard-summary-table{min-width:760px;font-size:.8rem}.l34-dashboard .dashboard-summary-table th,.l34-dashboard .dashboard-indicator-table th{background:#f7f9fc;color:#7b8aa0;font-size:.68rem;letter-spacing:.025em;white-space:nowrap}.l34-dashboard .dashboard-summary-table th,.l34-dashboard .dashboard-summary-table td,.l34-dashboard .dashboard-indicator-table td,.l34-dashboard .dashboard-indicator-table th{padding:14px 18px;vertical-align:top;line-height:1.7}.l34-dashboard .dashboard-summary-table td:first-child{min-width:240px;max-width:340px;white-space:normal}.l34-dashboard .dashboard-summary-table td small{font-size:.71rem}.l34-dashboard .dashboard-indicator-table{min-width:720px;font-size:.8rem}.l34-dashboard .dashboard-indicator-table td:first-child{width:52%;min-width:260px;white-space:normal;overflow-wrap:anywhere}.l34-dashboard .dashboard-table-scroll{max-height:480px;overflow:auto}.l34-dashboard .dashboard-table-scroll th{position:sticky;top:0;z-index:1}.l34-dashboard .dashboard-priority{display:flex;align-items:start;gap:12px;padding:14px 0;border-bottom:1px solid #edf0f5}.l34-dashboard .dashboard-priority>div{flex:1;min-width:0}.l34-dashboard .dashboard-priority p{font-size:.8rem;line-height:1.7;color:#526581}.l34-dashboard .dashboard-priority small{font-size:.69rem}.l34-dashboard .dashboard-rank{width:25px;height:25px;display:grid;place-items:center;background:#f0f1ff;color:#6366f1;border-radius:7px;flex-shrink:0;font-size:.72rem}.l34-dashboard .dashboard-priority>strong{font-size:.9rem;flex-shrink:0}.l34-dashboard .dashboard-recommendation{display:flex;gap:12px;padding:16px 0;border-bottom:1px solid #edf0f5}.l34-dashboard .dashboard-recommendation:first-child{padding-top:0}.l34-dashboard .dashboard-recommendation>span{width:34px;height:34px;display:grid;place-items:center;border-radius:9px;flex-shrink:0;font-size:1.1rem}.l34-dashboard .dashboard-recommendation h6{font-size:.83rem}.l34-dashboard .dashboard-recommendation p{font-size:.77rem;line-height:1.8}.l34-dashboard .dashboard-method>summary{display:flex;justify-content:space-between;align-items:center;gap:12px;cursor:pointer;list-style:none}.l34-dashboard .dashboard-method>summary::-webkit-details-marker{display:none}.l34-dashboard .dashboard-method[open]>summary>.bx-chevron-down{transform:rotate(180deg)}.l34-dashboard .dashboard-method li{margin-bottom:8px;line-height:1.8}.l34-dashboard .dashboard-table-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:15px 22px;border-top:1px solid #edf0f5}.l34-dashboard .dashboard-empty{text-align:center;padding:50px 16px;color:#8b99ab;font-size:.8rem}.l34-dashboard .dashboard-empty>.bx{font-size:2rem;margin-bottom:12px}.l34-dashboard .badge{white-space:normal;line-height:1.4}.l34-dashboard [data-dashboard-row][hidden]{display:none!important}@media(max-width:767px){.l34-dashboard .dashboard-hero{padding:22px;flex-direction:column;gap:20px}.l34-dashboard .dashboard-hero-meta{align-self:stretch;flex-basis:auto;padding:12px 16px}.l34-dashboard .dashboard-stat{padding:14px}.l34-dashboard .dashboard-stat>strong{font-size:1.5rem}.l34-dashboard .dashboard-panel .card-header,.l34-dashboard .dashboard-panel .card-body{padding:18px}.l34-dashboard .chart-indicator{height:390px}.l34-dashboard .dashboard-table-footer{padding:14px 18px}}@media print{.no-print,.layout-navbar,.layout-menu,.content-footer{display:none!important}.layout-page{padding:0!important}.l34-dashboard{padding:0!important}.l34-dashboard .dashboard-panel{box-shadow:none;break-inside:avoid}.l34-dashboard .dashboard-table-scroll{max-height:none;overflow:visible}.l34-dashboard [data-dashboard-row][hidden]{display:table-row!important}.l34-dashboard .dashboard-hero{background:#eef3ff!important;color:#233752!important;print-color-adjust:exact}.l34-dashboard .dashboard-chart{height:310px!important}}
</style>
@endpush
@push('js')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}?v={{ filemtime(public_path('assets/vendor/libs/apex-charts/apexcharts.js')) }}"></script>
<script>
(() => {
    const palette = ['#6366f1', '#16a9bb', '#e5aa3c'];
    const number = value => Number(value).toLocaleString('id-ID', { maximumFractionDigits: 1 });
    function wrap(text, width = 34) {
        const words = String(text).split(/\s+/), lines = [];
        let line = '';
        words.forEach(word => {
            if ((line + ' ' + word).trim().length > width && line) { lines.push(line); line = word; }
            else line = (line + ' ' + word).trim();
        });
        if (line) lines.push(line);
        return lines.slice(0, 3);
    }
    const escape = value => String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    const chartJobs = [];
    function renderChart(id, options) {
        const el = document.getElementById(id);
        if (!el) return;
        if (typeof ApexCharts === 'undefined') {
            el.innerHTML = '<p class="small text-muted text-center py-4">Grafik belum dapat dimuat. Rincian angka tetap tersedia pada tabel.</p>';
            return;
        }
        try {
            const chart = new ApexCharts(el, options);
            chartJobs.push(chart.render().catch(() => { el.innerHTML = '<p class="small text-muted text-center py-4">Grafik belum dapat ditampilkan. Lihat rincian angka pada tabel.</p>'; }));
        } catch (error) {
            el.innerHTML = '<p class="small text-muted text-center py-4">Grafik belum dapat ditampilkan. Lihat rincian angka pada tabel.</p>';
        }
    }
    function barOptions(labels, series, colors, legend = false) {
        return {
            chart: { type: 'bar', height: '100%', parentHeightOffset: 0, toolbar: { show: false }, animations: { enabled: false }, fontFamily: "'Public Sans', sans-serif" },
            series, colors,
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: legend ? '58%' : '46%' } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#edf1f6' },
            xaxis: { categories: labels, min: 0, max: 100, tickAmount: 4, labels: { formatter: value => number(value), style: { fontSize: '10px' } }, axisTicks: { show: false }, axisBorder: { show: false } },
            yaxis: { labels: { maxWidth: 220, style: { fontSize: '10px' }, formatter: value => { const text = String(value ?? ''); return text.length > 48 ? text.slice(0, 46) + '?' : text; } } },
            legend: { show: legend, position: 'bottom', fontSize: '11px' },
            tooltip: { custom: function ({series, seriesIndex, dataPointIndex, w}) { const value = series[seriesIndex][dataPointIndex]; return '<div style="padding:12px;max-width:330px;white-space:normal;font-size:12px"><strong>'+escape(labels[dataPointIndex] ?? '')+'</strong><br>'+escape(w.config.series[seriesIndex].name)+': '+(value === null ? '-' : escape(number(value)))+'/100</div>'; } },
            noData: { text: 'Belum ada nilai yang dapat dihitung.' }
        };
    }
    const labels = @json($chartSections->values());
    const series = @json($roleChartSeries);
    renderChart('roleChart', barOptions(labels, series.map(item => ({ name: item.label, data: item.data })), palette, true));
    function indicatorChart(id, items, color) {
        const valid = items.filter(item => item.average !== null).sort((a, b) => a.average - b.average).slice(0, 6);
        const options = barOptions(valid.map(item => item.label), [{ name: 'Rerata', data: valid.map(item => item.average) }], [color]);
        options.annotations = { xaxis: [{ x: 80, borderColor: '#c5a35a', strokeDashArray: 4, label: { text: 'Acuan 80', style: { fontSize: '9px', color: '#8a702e', background: '#fff5da' } } }] };
        renderChart(id, options);
    }
    indicatorChart('l3Chart', @json($l3Indicators->values()), '#6366f1');
    indicatorChart('l4Chart', @json($l4Indicators->values()), '#16a9bb');
    const impactValues = @json($impactDistribution->values());
    renderChart('impactChart', {
        chart: { type: 'donut', height: '100%', parentHeightOffset: 0, animations: { enabled: false }, fontFamily: "'Public Sans', sans-serif" },
        series: impactValues.some(value => value > 0) ? impactValues : [],
        labels: @json($impactDistribution->keys()),
        colors: ['#22a380','#16a9bb','#e5aa3c','#e87575','#c5cedb'],
        plotOptions: { pie: { donut: { size: '74%' } } },
        dataLabels: { enabled: false }, legend: { show: false },
        tooltip: { y: { formatter: value => number(value) + ' alumni' } },
        noData: { text: 'Belum ada alumni sasaran.' }
    });
    document.querySelectorAll('.l34-dashboard [data-dashboard-search]').forEach(panel => {
        const rows = [...panel.querySelectorAll('[data-dashboard-row]')];
        const query = panel.querySelector('[data-dashboard-query]'), filter = panel.querySelector('[data-dashboard-filter]');
        const empty = panel.querySelector('[data-dashboard-empty]'), paged = panel.hasAttribute('data-dashboard-paged');
        const info = panel.querySelector('[data-dashboard-page-info]'), prev = panel.querySelector('[data-dashboard-prev]'), next = panel.querySelector('[data-dashboard-next]');
        let page = 1;
        const size = 10;
        function render() {
            const term = query.value.trim().toLocaleLowerCase('id-ID'), status = filter?.value || '';
            const matches = rows.filter(row => (!term || row.textContent.toLocaleLowerCase('id-ID').includes(term)) && (!status || row.dataset.status === status));
            const pages = Math.max(1, Math.ceil(matches.length / size));
            page = Math.min(page, pages);
            rows.forEach(row => row.hidden = true);
            (paged ? matches.slice((page - 1) * size, page * size) : matches).forEach(row => row.hidden = false);
            empty.hidden = matches.length > 0;
            if (paged) {
                info.textContent = matches.length ? ((page - 1) * size + 1) + '-' + Math.min(page * size, matches.length) + ' dari ' + matches.length + ' alumni / Halaman ' + page + '/' + pages : 'Tidak ada alumni yang cocok';
                prev.disabled = page <= 1; next.disabled = page >= pages;
            }
        }
        query.addEventListener('input', () => { page = 1; render(); });
        filter?.addEventListener('change', () => { page = 1; render(); });
        if (paged) { prev.addEventListener('click', () => { page--; render(); }); next.addEventListener('click', () => { page++; render(); }); }
        render();
    });
    let closedDetails = [];
    window.addEventListener('beforeprint', () => { closedDetails = [...document.querySelectorAll('.l34-dashboard details:not([open])')]; closedDetails.forEach(el => el.open = true); });
    window.addEventListener('afterprint', () => { closedDetails.forEach(el => el.open = false); closedDetails = []; });
})();
</script>
@endpush
