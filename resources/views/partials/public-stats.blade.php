@php
    $items = [
        ['key' => 'total_training', 'label' => 'Total Pelatihan', 'icon' => 'bx-collection', 'suffix' => ''],
        ['key' => 'active_training', 'label' => 'Pelatihan Aktif Hari Ini', 'icon' => 'bx-play-circle', 'suffix' => ''],
        ['key' => 'unique_alumni', 'label' => 'Alumni Unik', 'icon' => 'bx-user-check', 'suffix' => ''],
        ['key' => 'total_participations', 'label' => 'Total Keikutsertaan', 'icon' => 'bx-group', 'suffix' => ''],
        ['key' => 'satisfaction_rate', 'label' => 'Indeks Kepuasan L1', 'icon' => 'bx-smile', 'suffix' => '%'],
        ['key' => 'impact_score', 'label' => 'Skor Dampak L4', 'icon' => 'bx-trending-up', 'suffix' => ''],
        ['key' => 'upcoming_agendas', 'label' => 'Agenda Publik Mendatang', 'icon' => 'bx-calendar-event', 'suffix' => ''],
        ['key' => 'available_assets', 'label' => 'Aset Aktif', 'icon' => 'bx-building-house', 'suffix' => ''],
    ];
@endphp
<div class="text-center mb-4">
    <span class="badge rounded-pill bg-white text-primary px-3 py-2 mb-3">Data INTEGRAL Terkini</span>
    <h2 class="text-white fw-bold mb-2">Capaian dalam Angka</h2>
    <p class="text-white opacity-75 mb-0">Dihitung langsung dari data pelatihan, peserta, evaluasi, agenda, dan aset.</p>
</div>
<div class="row g-3 text-center">
    @foreach($items as $index => $item)
        <div class="col-6 col-md-4 col-xl-3 animate__animated animate__fadeInUp" style="animation-delay: {{ $index * 0.05 }}s">
            <div class="h-100 rounded-4 p-3 p-lg-4" style="background:rgba(255,255,255,.11);border:1px solid rgba(255,255,255,.18);backdrop-filter:blur(8px)">
                <div class="icon-box-white mx-auto mb-3"><i class="bx {{ $item['icon'] }}"></i></div>
                <h2 class="text-white fw-bold mb-1">
                    <span class="counter" data-target="{{ $stats[$item['key']] ?? 0 }}">0</span>@if($item['suffix'])<small class="fs-5">{{ $item['suffix'] }}</small>@endif
                </h2>
                <p class="text-white opacity-75 text-uppercase small fw-bold mb-0">{{ $item['label'] }}</p>
            </div>
        </div>
    @endforeach
</div>
<p class="text-center text-white opacity-50 small mt-4 mb-0"><i class="bx bx-refresh me-1"></i>Statistik diperbarui otomatis mengikuti data sistem.</p>
