<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme shadow-sm">
    <!-- Logo & Brand -->
    <div class="app-brand demo" style="height: 75px;">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                {{-- LOGO INTEGRAL --}}
                <img src="https://res.cloudinary.com/dnwyqw6gn/image/upload/v1786770700/Integral_1_ykmzxx.png" 
                     alt="Integral Logo" 
                     style="width: 32px; filter: drop-shadow(0px 2px 4px rgba(105, 108, 255, 0.3));">
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2 text-uppercase" style="letter-spacing: 1px; font-size: 1.25rem;">Integral</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-3">
        <li class="menu-header small text-uppercase mt-0"><span class="menu-header-text">Menu Utama</span></li>
        @if(Auth::user()->role === 'admin_aset')
            <li class="menu-item {{ request()->routeIs('assets.dashboard') ? 'active' : '' }}"><a href="{{ route('assets.dashboard') }}" class="menu-link"><i class="menu-icon bx bx-grid-alt"></i><div class="fw-bold">Dashboard Aset</div></a></li>
        @elseif(in_array(Auth::user()->role, ['superadmin', 'admin_bidang']))
            <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><a href="{{ route('dashboard') }}" class="menu-link"><i class="menu-icon bx bx-home-circle"></i><div class="fw-bold">Dashboard Admin</div></a></li>
        @elseif(Auth::user()->role === 'participant')
            <li class="menu-item {{ request()->routeIs('participant.dashboard') ? 'active' : '' }}"><a href="{{ route('participant.dashboard') }}" class="menu-link"><i class="menu-icon bx bx-home-alt"></i><div class="fw-bold">Dashboard Saya</div></a></li>
        @elseif(Auth::user()->role === 'pengajar')
            <li class="menu-item {{ request()->routeIs('pengajar.index') || request()->routeIs('pengajar.manage') ? 'active' : '' }}">
                <a href="{{ route('pengajar.index') }}" class="menu-link"><i class="menu-icon bx bx-home-alt"></i><div class="fw-bold">Dashboard Narasumber</div></a>
            </li>
        @elseif(Auth::user()->role === 'mitra')
            <li class="menu-item {{ request()->routeIs('mitra.dashboard') ? 'active' : '' }}"><a href="{{ route('mitra.dashboard') }}" class="menu-link"><i class="menu-icon bx bx-handshake"></i><div class="fw-bold">Pengajuan Mitra</div></a></li>
        @else
            <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><a href="{{ route('dashboard') }}" class="menu-link"><i class="menu-icon bx bx-home-circle"></i><div class="fw-bold">Dashboard</div></a></li>
        @endif
        @if(in_array(Auth::user()->role, ['superadmin', 'admin_bidang']))
            <li class="menu-item {{ request()->is('trainings*') && !request()->is('*attendance*') && !request()->is('*monitoring*') && !request()->is('*evaluasi*') ? 'active' : '' }}">
                <a href="{{ route('trainings.index') }}" class="menu-link"><i class="menu-icon bx bx-collection"></i><div>Daftar Pelatihan</div></a>
            </li>
        @elseif(Auth::user()->role === 'participant')
            <li class="menu-item {{ request()->routeIs('participant.trainings') ? 'active' : '' }}">
                <a href="{{ route('participant.trainings') }}" class="menu-link"><i class="menu-icon bx bx-list-ul"></i><div>Daftar Pelatihan</div></a>
            </li>
        @endif

        @if(Auth::user()->role === 'superadmin' || (Auth::user()->role === 'admin_bidang' && Auth::user()->bidang === 'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan'))
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Sertifikasi SKPK</span></li>
            <li class="menu-item {{ request()->routeIs('certifications.*') ? 'active' : '' }}">
                <a href="{{ route('certifications.index') }}" class="menu-link"><i class="menu-icon bx bx-certification"></i><div>Kelola Sertifikasi</div></a>
            </li>
        @endif

        @if(in_array(Auth::user()->role, ['admin_aset', 'superadmin']))
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Manajemen Aset & Agenda</span></li>
            @if(Auth::user()->role === 'superadmin')
                <li class="menu-item {{ request()->routeIs('assets.dashboard') ? 'active' : '' }}"><a href="{{ route('assets.dashboard') }}" class="menu-link"><i class="menu-icon bx bx-grid-alt"></i><div>Dashboard Aset</div></a></li>
            @endif
            <li class="menu-item {{ request()->routeIs('assets.index') ? 'active' : '' }}"><a href="{{ route('assets.index') }}" class="menu-link"><i class="menu-icon bx bx-cube"></i><div>Kelola Aset</div></a></li>
            <li class="menu-item {{ request()->routeIs('assets.monitoring') ? 'active' : '' }}"><a href="{{ route('assets.monitoring') }}" class="menu-link"><i class="menu-icon bx bx-bar-chart-alt-2"></i><div>Monitoring Aset</div></a></li>
            <li class="menu-item {{ request()->routeIs('agendas.*') ? 'active' : '' }}"><a href="{{ route('agendas.index') }}" class="menu-link"><i class="menu-icon bx bx-calendar-event"></i><div>Kelola Agenda</div></a></li>
        @endif
        @if(Auth::user()->role === 'admin_bidang')
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Agenda Kegiatan</span></li>
            <li class="menu-item {{ request()->routeIs('agendas.*') ? 'active' : '' }}"><a href="{{ route('agendas.index') }}" class="menu-link"><i class="menu-icon bx bx-calendar-event"></i><div>Kelola Agenda</div></a></li>
        @endif
        
        <!-- ========================================================= -->
        <!-- 1. MENU KHUSUS ADMIN BIDANG & SUPERADMIN                  -->
        <!-- ========================================================= -->
        @if(Auth::user()->role === 'superadmin' || Auth::user()->role === 'admin_bidang')
            @if(Auth::user()->role === 'superadmin' || in_array(Auth::user()->bidang, [
                'Bidang Pengembangan Kompetensi Teknis Inti',
                'Bidang Pengembangan Kompetensi Teknis Umum',
                'Bidang Pengembangan Kompetensi Manajerial',
                'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan'
            ], true))
                <li class="menu-header small text-uppercase"><span class="menu-header-text">Kemitraan</span></li>
                <li class="menu-item {{ request()->routeIs('mitra.admin.*') || (request()->routeIs('mitra.submissions.show') && Auth::user()->role !== 'mitra') ? 'active' : '' }}">
                    <a href="{{ route('mitra.admin.index') }}" class="menu-link"><i class="menu-icon bx bx-handshake"></i><div>Pengajuan Mitra</div></a>
                </li>
            @endif
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Monitoring & Tindak Lanjut</span></li>
            <li class="menu-item {{ request()->routeIs('followup.*') ? 'active' : '' }}">
                <a href="{{ route('followup.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-task"></i>
                    <div>Rekomendasi Monitoring</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Kelola Instrumen</span>
            </li>

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

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Kelola Dokumen</span>
            </li>

            <li class="menu-item {{ request()->is('documents*') ? 'active' : '' }}">
                <a href="{{ route('documents.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-folder"></i>
                    <div>Manajemen Dokumen</div>
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Manajemen Alumni</span>
            </li>

            <li class="menu-item {{ request()->routeIs('alumni.index') ? 'active' : '' }}">
                <a href="{{ route('alumni.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user-pin"></i>
                    <div>Kelola Alumni</div>
                </a>
            </li>

            @if(Auth::user()->role === 'superadmin')
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
        @endif

        @if(Auth::user()->role === 'participant')
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Portal Peserta</span>
            </li>
            <li class="menu-item {{ request()->routeIs('participant.history') ? 'active' : '' }}">
                <a href="{{ route('participant.history') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div>Riwayat Pelatihan</div>
                </a>
            </li>
        @endif

        @if(Auth::user()->canAccessNarasumberPortal())
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Portal Narasumber</span>
            </li>
            <li class="menu-item {{ request()->routeIs('pengajar.schedule') ? 'active' : '' }}">
                <a href="{{ route('pengajar.schedule') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar"></i>
                    <div>Jadwal Mengajar</div>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('pengajar.history') ? 'active' : '' }}">
                <a href="{{ route('pengajar.history') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div>Riwayat Mengajar</div>
                </a>
            </li>
        @endif
        <li class="menu-spacer" aria-hidden="true"></li>

    </ul>
</aside>

{{-- CSS CUSTOM UNTUK INTERAKTIVITAS --}}
@push('css')
<style>
    /* Transisi menu */
    .menu-vertical .menu-item .menu-link {
        transition: all 0.2s ease-in-out;
        border-radius: 0.375rem;
        margin: 0.15rem 1rem;
        padding-left: 1rem;
    }

    /* Efek Hover */
    .menu-vertical .menu-item:not(.active):not(.open) .menu-link:hover {
        background-color: rgba(105, 108, 255, 0.08) !important;
        transform: translateX(5px);
        color: #696cff !important;
    }

    .menu-vertical .menu-item:not(.active):not(.open) .menu-link:hover i {
        color: #696cff !important;
        transform: scale(1.1);
    }

    /* Mempercantik Menu Header */
    .menu-header {
        margin: 1.5rem 0 0.5rem 0 !important;
        padding-left: 1.5rem;
    }

    .menu-header-text {
        color: #a1acb8 !important;
        font-weight: 700 !important;
        letter-spacing: 1px;
    }

    /* Badge Active Glow */
    .menu-vertical .menu-item.active > .menu-link {
        box-shadow: 0px 4px 8px rgba(105, 108, 255, 0.25);
        background: linear-gradient(72.47deg, #696cff 22.16%, rgba(105, 108, 255, 0.7) 76.47%) !important;
        color: #fff !important;
    }

    .menu-vertical .menu-item.active > .menu-link i {
        color: #fff !important;
    }

    /* Animasi Dropdown */
    .menu-sub {
        transition: all 0.3s ease-in-out;
    }

    /* Ruang aman agar menu terakhir tidak menempel ke bawah layar. */
    .menu-inner {
        padding-bottom: 3.5rem !important;
    }

    .menu-spacer {
        display: block;
        min-height: 2rem;
        flex: 0 0 2rem;
    }
</style>
@endpush
