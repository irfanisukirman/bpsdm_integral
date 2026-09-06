<section class="card border-0 shadow-sm mb-4 ai-executive-panel">
    <div class="card-header bg-transparent border-bottom p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div class="d-flex align-items-start gap-3">
                <span class="ai-executive-icon"><i class="bx bx-bot"></i></span>
                <div><h5 class="fw-bold mb-1">Analisis Eksekutif dengan AI</h5><p class="small text-muted mb-0">Mengolah statistik agregat menjadi bahan telaah pimpinan tanpa mengirim identitas peserta.</p></div>
            </div>
            <form class="no-print" method="POST" action="{{ $aiRoute }}" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').innerHTML='Menganalisis...';">
                @csrf
                <button class="btn btn-primary text-nowrap"><i class="bx bx-sparkles me-1"></i>{{ $analysis ? 'Perbarui Analisis' : 'Analisis dengan AI' }}</button>
            </form>
        </div>
    </div>
    @if($aiError)<div class="card-body"><div class="alert alert-danger mb-0"><i class="bx bx-error-circle me-1"></i>{{ $aiError }}</div></div>@endif
    @if($analysis)
        <div class="card-body p-4">
            <div class="row g-4">
                @foreach([
                    'executive_summary' => ['Ringkasan Eksekutif','bx-file-find','primary'],
                    'key_findings' => ['Temuan Utama','bx-search-alt','info'],
                    'priority_actions' => ['Prioritas Tindakan','bx-target-lock','warning'],
                    'data_caution' => ['Catatan Kehati-hatian','bx-shield-quarter','secondary'],
                ] as $field => [$label,$icon,$tone])
                    <div class="col-lg-6"><article class="ai-analysis-item h-100"><span class="bg-label-{{ $tone }}"><i class="bx {{ $icon }}"></i></span><div><h6 class="fw-bold mb-2">{{ $label }}</h6><p class="mb-0">{{ $analysis[$field] ?? '-' }}</p></div></article></div>
                @endforeach
            </div>
            <div class="alert alert-warning small mt-4 mb-0"><i class="bx bx-info-circle me-1"></i>Analisis ini merupakan draf berbantuan AI. Cocokkan kembali dengan grafik, cakupan responden, dan bukti pelaksanaan sebelum digunakan sebagai keputusan resmi.</div>
        </div>
    @endif
</section>
<style>
.ai-executive-panel{border:1px solid #e1e3ff!important}.ai-executive-icon{display:grid;place-items:center;flex:0 0 46px;width:46px;height:46px;border-radius:13px;background:#696cff;color:#fff;font-size:1.45rem}.ai-analysis-item{display:flex;align-items:flex-start;gap:.8rem;padding:1rem;border:1px solid #ececf2;border-radius:.8rem;background:#fcfcfd}.ai-analysis-item>span{display:grid;place-items:center;flex:0 0 38px;width:38px;height:38px;border-radius:10px;font-size:1.2rem}.ai-analysis-item p{white-space:pre-line;line-height:1.65;color:#566a7f}
</style>
