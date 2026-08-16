<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <!-- Logo & Brand -->
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                {{-- LOGO INTEGRAL BARU --}}
                <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" alt="Integral Logo" style="width: 35px;">
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2 text-uppercase">Integral</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @if(Auth::user()->role !== 'participant')
            <li class="menu-item {{ request()->is('dashboard*') || request()->is('/') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div>Dashboard Admin</div>
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
            <li class="menu-item {{ request()->is('documents*') ? 'active' : '' }}">
                <a href="{{ route('documents.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder"></i>
                    <div>Manajemen Dokumen</div>
                </a>
            </li>

            @if(Auth::user()->role !== 'participant')
            <li class="menu-item {{ request()->routeIs('alumni.index') ? 'active' : '' }}">
                <a href="{{ route('alumni.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-pin"></i>
                    <div>Kelola Alumni Pelatihan</div>
                </a>
            </li>
            @endif


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

            @if(Auth::user()->role == 'superadmin')
                <li class="menu-header small text-uppercase"><span class="menu-header-text">Monitoring Sistem</span></li>
                <li class="menu-item {{ request()->is('activity-logs*') ? 'active' : '' }}">
                    <a href="#" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-history"></i>
                        <div>Log Aktivitas Admin</div>
                    </a>
                </li>
            @endif
        @endif

        @if(Auth::user()->role === 'participant')
            {{-- Dashboard Peserta --}}
            <li class="menu-item {{ request()->routeIs('participant.dashboard') ? 'active' : '' }}">
                <a href="{{ route('participant.dashboard') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-alt"></i>
                    <div data-i18n="Dashboard">Dashboard Saya</div>
                </a>
            </li>

            {{-- Header Menu --}}
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Portal Peserta</span>
            </li>

            {{-- Daftar Pelatihan --}}
            <li class="menu-item {{ request()->routeIs('participant.trainings') ? 'active' : '' }}">
                <a href="{{ route('participant.trainings') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-list-ul"></i>
                    <div data-i18n="Daftar Pelatihan">Daftar Pelatihan</div>
                </a>
            </li>

            {{-- Riwayat Pelatihan --}}
            <li class="menu-item {{ request()->routeIs('participant.history') ? 'active' : '' }}">
                <a href="{{ route('participant.history') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div data-i18n="Riwayat">Riwayat Pelatihan</div>
                </a>
            </li>
        @endif
    </ul>
</aside>