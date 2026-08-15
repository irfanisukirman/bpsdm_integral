<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <!-- Logo & Brand -->
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <svg width="25" viewBox="0 0 25 42" version="1.1"><path d="M13,3.5c-6.903,0-12.5,5.597-12.5,12.5c0,3.911,1.799,7.401,4.603,9.702c0.2,0.165,0.41,0.321,0.627,0.468 c0.33,0.222,0.672,0.428,1.026,0.615c0.199,0.106,0.404,0.203,0.612,0.293C8.423,27.534,9.673,28,11,28c0.16,0,0.318-0.007,0.476-0.02 c0.16,0.013,0.316,0.02,0.476,0.02c1.327,0,2.577-0.466,3.632-1.252c0.208-0.09,0.413-0.187,0.612-0.293 c0.354-0.187,0.696-0.393,1.026-0.615c0.217-0.147,0.427-0.303,0.627-0.468C20.701,23.401,22.5,19.911,22.5,16 C22.5,9.097,16.903,3.5,13,3.5z" fill="#696cff" /></svg>
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2 text-uppercase">Sim-Pel</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- 1. DASHBOARD -->
        <li class="menu-item {{ request()->is('dashboard*') || request()->is('/') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard Utama</div>
            </a>
        </li>

        <!-- 2. MANAJEMEN PELATIHAN (DATA INDUK) -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Manajemen Pelatihan</span>
        </li>
        <li class="menu-item {{ request()->is('trainings*') && !request()->is('*attendance*') && !request()->is('*monitoring*') && !request()->is('*evaluasi*') ? 'active' : '' }}">
            <a href="{{ route('trainings.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-collection"></i>
                <div>Daftar Pelatihan</div>
            </a>
        </li>

        <!-- 3. BANK INSTRUMEN (SOAL & INDIKATOR) -->
        <li class="menu-item {{ request()->is('monitoring-indicators*') || request()->is('questions*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-book-content"></i>
                <div>Bank Instrumen</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('monitoring-indicators*') ? 'active' : '' }}">
                    <a href="{{ route('indicators.index') }}" class="menu-link">
                        <div>Indikator Monitoring</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('questions*') ? 'active' : '' }}">
                    <a href="{{ route('questions.index') }}" class="menu-link">
                        <div>Soal Evaluasi L1-L4</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- 4. PELAKSANAAN (OPERASIONAL) -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Pelaksanaan Pelatihan</span>
        </li>
        <li class="menu-item {{ request()->is('*attendance*') ? 'active' : '' }}">
            <a href="{{ route('attendance.all') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user-check"></i>
                <div>Kehadiran Peserta</div>
            </a>
        </li>
        <li class="menu-item {{ request()->is('*monitoring*') && !request()->is('*indicators*') ? 'active' : '' }}">
            <a href="{{ route('monitoring.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-desktop"></i>
                <div>Monitoring Tahapan</div>
            </a>
        </li>

        <!-- 5. EVALUASI & DAMPAK -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Evaluasi & Dampak</span>
        </li>
        
        <!-- Tindak Lanjut dengan Notifikasi -->
        @php
            $countFollowUp = \App\Models\MonitoringResult::where('answer', 'tidak')
                ->where('is_resolved', false)
                ->when(Auth::user()->role !== 'superadmin', function($q) {
                    return $q->where('follow_up_target', Auth::user()->bidang);
                })->count();
        @endphp
        <li class="menu-item {{ request()->is('follow-up*') ? 'active' : '' }}">
            <a href="{{ route('followup.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-error-alt"></i>
                <div class="d-flex justify-content-between w-100">
                    <div>Tindak Lanjut</div>
                    @if($countFollowUp > 0)
                        <span class="badge badge-center rounded-pill bg-danger animate__animated animate__heartBeat animate__infinite" style="width: 18px; height: 18px; font-size: 9px;">
                            {{ $countFollowUp }}
                        </span>
                    @endif
                </div>
            </a>
        </li>

        <!-- Dropdown Kirkpatrick -->
        <li class="menu-item {{ request()->is('*evaluasi/l1*') || request()->is('*evaluasi/l2*') || request()->is('*evaluasi/l34*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                <div>Evaluasi Kirkpatrick</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('*evaluasi/l1*') ? 'active' : '' }}">
                    <a href="{{ route('evaluasi.l1') }}" class="menu-link"><div>Level 1: Reaksi</div></a>
                </li>
                <li class="menu-item {{ request()->is('*evaluasi/l2*') ? 'active' : '' }}">
                    <a href="{{ route('evaluasi.l2') }}" class="menu-link"><div>Level 2: Learning</div></a>
                </li>
                <li class="menu-item {{ request()->is('*evaluasi/l34*') ? 'active' : '' }}">
                    <a href="{{ route('evaluasi.l34') }}" class="menu-link"><div>Level 3 & 4: Dampak</div></a>
                </li>
            </ul>
        </li>

        @php
            // Hitung berapa pelatihan yang sisa harinya <= 0 (Sudah harus sebar)
            $allTrainings = \App\Models\Training::all();
            if (Auth::user()->role !== 'superadmin') {
                $allTrainings = $allTrainings->where('bidang', Auth::user()->bidang);
            }

            $countReadyToDistribute = 0;
            foreach($allTrainings as $tr) {
                if($tr->sisa_hari_sebar <= 0) {
                    $countReadyToDistribute++;
                }
            }
        @endphp
        <li class="menu-item {{ request()->is('*control-l34*') ? 'active' : '' }}">
            <a href="{{ route('control_l34.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-stopwatch"></i>
                <div class="d-flex justify-content-between w-100">
                    <div>Kontrol Evaluasi Pasca</div>
                    @if($countReadyToDistribute > 0)
                        <span class="badge badge-center rounded-pill bg-danger animate__animated animate__heartBeat animate__infinite" style="width: 20px; height: 20px; font-size: 10px;">
                            {{ $countReadyToDistribute }}
                        </span>
                    @endif
                </div>
            </a>
        </li>

        <!-- 6. PENGATURAN (SUPERADMIN ONLY) -->
        @if(Auth::check() && Auth::user()->role == 'superadmin')
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Pengaturan Sistem</span>
        </li>
        <li class="menu-item {{ request()->is('users*') ? 'active' : '' }}">
            <a href="{{ route('users.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                <div>Kelola User PIC</div>
            </a>
        </li>
        @endif
    </ul>
</aside>