@php $menuNoticeCount = fn (string $key) => (int) ($menuNotificationCounts[$key] ?? 0); @endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme shadow-sm">
    <!-- Logo & Brand -->
    <div class="app-brand demo" style="height: 75px;">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                {{-- LOGO INTEGRAL --}}
                <img src="{{ asset('assets/img/favicon/inte.png') }}" 
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
        @if(Auth::user()->role === 'resepsionis')
            <li class="menu-item {{ request()->routeIs('guest-book.*') ? 'active' : '' }}"><a href="{{route('guest-book.index')}}" class="menu-link"><i class="menu-icon bx bx-book-reader"></i><div>Buku Tamu</div></a></li>
        @endif        @if(Auth::user()->role === 'pengelola_magang')
            <li class="menu-item {{ request()->routeIs('internships.*') ? 'active' : '' }}">
                <a href="{{ route('internships.index') }}" class="menu-link"><i class="menu-icon bx bx-briefcase-alt-2"></i><div>Presensi Magang/PKL</div></a>
            </li>
        @endif        @if(Auth::user()->role === 'penandatangan')
            <li class="menu-item {{ request()->routeIs('electronic-signatures.*') ? 'active' : '' }}">
                <a href="{{ route('electronic-signatures.index') }}" class="menu-link"><i class="menu-icon bx bx-pen"></i><div>Tanda Tangan Elektronik</div>@if($menuNoticeCount('electronic_signatures'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge">{{$menuNoticeCount('electronic_signatures')}}</span>@endif</a>
            </li>
        @endif
        @if(in_array(Auth::user()->role, ['superadmin', 'admin_bidang', 'admin_aset'], true))
            <li class="menu-item {{ request()->routeIs('ai-assistant.*') ? 'active' : '' }}">
                <a href="{{ route('ai-assistant.index') }}" class="menu-link"><i class="menu-icon bx bx-bot"></i><div>Asisten AI</div></a>
            </li>
        @endif
        @if(Auth::user()->role === 'admin_aset')
            <li class="menu-item {{ request()->routeIs('assets.dashboard') ? 'active' : '' }}"><a href="{{ route('assets.dashboard') }}" class="menu-link"><i class="menu-icon bx bx-grid-alt"></i><div>Dashboard Aset</div></a></li>
        @elseif(in_array(Auth::user()->role, ['superadmin', 'admin_bidang']))
            <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><a href="{{ route('dashboard') }}" class="menu-link"><i class="menu-icon bx bx-home-circle"></i><div>Dashboard Admin</div></a></li>
        @elseif(Auth::user()->role === 'resepsionis')
            {{-- Menu Buku Tamu sudah ditampilkan di atas. --}}
        @elseif(Auth::user()->role === 'pengelola_magang')
            {{-- Menu pengelola magang sudah ditampilkan di atas. --}}
        @elseif(Auth::user()->role === 'intern')
            <li class="menu-item {{ request()->routeIs('internships.dashboard') ? 'active' : '' }}"><a href="{{ route('internships.dashboard') }}" class="menu-link"><i class="menu-icon bx bx-briefcase-alt-2"></i><div>Dashboard Magang</div></a></li>
        @elseif(Auth::user()->role === 'participant')
            <li class="menu-item {{ request()->routeIs('participant.dashboard') ? 'active' : '' }}"><a href="{{ route('participant.dashboard') }}" class="menu-link"><i class="menu-icon bx bx-home-alt"></i><div>Dashboard Saya</div></a></li>
        @elseif(Auth::user()->role === 'pengajar')
            <li class="menu-item {{ request()->routeIs('pengajar.index') || request()->routeIs('pengajar.manage') ? 'active' : '' }}">
                <a href="{{ route('pengajar.index') }}" class="menu-link"><i class="menu-icon bx bx-home-alt"></i><div>Dashboard Narasumber</div></a>
            </li>
        @elseif(Auth::user()->role === 'mitra')
            <li class="menu-item {{ request()->routeIs('mitra.dashboard') ? 'active' : '' }}"><a href="{{ route('mitra.dashboard') }}" class="menu-link"><i class="menu-icon bx bx-link"></i><div>Pengajuan Mitra</div></a></li>
        @elseif(Auth::user()->role === 'penandatangan')
            {{-- Dashboard akun penandatangan adalah Pusat Tanda Tangan Elektronik di atas. --}}
        @else
            <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><a href="{{ route('dashboard') }}" class="menu-link"><i class="menu-icon bx bx-home-circle"></i><div>Dashboard</div></a></li>
        @endif
        @if(in_array(Auth::user()->role, ['superadmin', 'admin_bidang']))
            <li class="menu-item {{ request()->is('trainings*') && !request()->is('*attendance*') && !request()->is('*monitoring*') && !request()->is('*evaluasi*') ? 'active' : '' }}">
                <a href="{{ route('trainings.index') }}" class="menu-link"><i class="menu-icon bx bx-collection"></i><div>Daftar Pelatihan</div>@if($menuNoticeCount('trainings'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge" title="Notifikasi baru">{{$menuNoticeCount('trainings')>99?'99+':$menuNoticeCount('trainings')}}</span>@endif</a>
            </li>
            <li class="menu-item {{ request()->routeIs('teacher-monitoring.*') ? 'active' : '' }}">
                <a href="{{ route('teacher-monitoring.index') }}" class="menu-link"><i class="menu-icon bx bx-chalkboard"></i><div>Monitoring Pengajar</div></a>
            </li>
            <li class="menu-item {{ request()->routeIs('teacher-schedules.*') ? 'active' : '' }}">
                <a href="{{ route('teacher-schedules.index') }}" class="menu-link"><i class="menu-icon bx bx-calendar-week"></i><div>Jadwal Pengajar</div></a>
            </li>
        @elseif(Auth::user()->role === 'participant')
            <li class="menu-item {{ request()->routeIs('participant.trainings') ? 'active' : '' }}">
                <a href="{{ route('participant.trainings') }}" class="menu-link"><i class="menu-icon bx bx-list-ul"></i><div>Daftar Pelatihan</div>@if($menuNoticeCount('participant_trainings'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge" title="Notifikasi baru">{{$menuNoticeCount('participant_trainings')>99?'99+':$menuNoticeCount('participant_trainings')}}</span>@endif</a>
            </li>
        @endif

        @if(!in_array(Auth::user()->role, ['superadmin', 'admin_bidang'], true))
            <li class="menu-item {{ request()->routeIs('hotline.my') ? 'active' : '' }}">
                <a href="{{ route('hotline.my') }}" class="menu-link"><i class="menu-icon bx bx-support"></i><div>Aduan Saya</div></a>
            </li>
        @endif

        @if(in_array(Auth::user()->role, ['superadmin', 'admin_bidang', 'admin_aset'], true))
            <li class="menu-item {{ request()->routeIs('daily-schedule.*') ? 'active' : '' }}">
                <a href="{{ route('daily-schedule.index') }}" class="menu-link"><i class="menu-icon bx bx-calendar-check"></i><div>Monitoring Jadwal Harian</div></a>
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
            <li class="menu-item {{ request()->routeIs('asset-rentals.admin.*') ? 'active' : '' }}"><a href="{{ route('asset-rentals.admin.index') }}" class="menu-link"><i class="menu-icon bx bx-calendar-star"></i><div>Kelola Reservasi</div>@if($menuNoticeCount('asset_rentals'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge">{{$menuNoticeCount('asset_rentals')}}</span>@endif</a></li>
            <li class="menu-item {{ request()->routeIs('assets.monitoring') ? 'active' : '' }}"><a href="{{ route('assets.monitoring') }}" class="menu-link"><i class="menu-icon bx bx-bar-chart-alt-2"></i><div>Monitoring Aset</div>@if($menuNoticeCount('asset_monitoring'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge" title="Notifikasi baru">{{$menuNoticeCount('asset_monitoring')>99?'99+':$menuNoticeCount('asset_monitoring')}}</span>@endif</a></li>
            <li class="menu-item {{ request()->routeIs('asset-loans.*') ? 'active' : '' }}"><a href="{{ route('asset-loans.index') }}" class="menu-link"><i class="menu-icon bx bx-check-shield"></i><div>Persetujuan Peminjaman</div>@if($menuNoticeCount('asset_loans'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge" title="Notifikasi baru">{{$menuNoticeCount('asset_loans')>99?'99+':$menuNoticeCount('asset_loans')}}</span>@endif</a></li>
            <li class="menu-item {{ request()->routeIs('agendas.*') ? 'active' : '' }}"><a href="{{ route('agendas.index') }}" class="menu-link"><i class="menu-icon bx bx-calendar-event"></i><div>Kelola Agenda</div>@if($menuNoticeCount('agendas'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge" title="Notifikasi baru">{{$menuNoticeCount('agendas')>99?'99+':$menuNoticeCount('agendas')}}</span>@endif</a></li>
        @endif
        @if(Auth::user()->role === 'admin_bidang')
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Agenda Kegiatan</span></li>
            <li class="menu-item {{ request()->routeIs('agendas.*') ? 'active' : '' }}"><a href="{{ route('agendas.index') }}" class="menu-link"><i class="menu-icon bx bx-calendar-event"></i><div>Kelola Agenda</div>@if($menuNoticeCount('agendas'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge" title="Notifikasi baru">{{$menuNoticeCount('agendas')>99?'99+':$menuNoticeCount('agendas')}}</span>@endif</a></li>
        @endif
        
        @if(Auth::user()->role === 'superadmin' || Auth::user()->role === 'admin_bidang')
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Presensi & Form Publik</span></li>

            @if(Auth::user()->role === 'superadmin')
            <li class="menu-item {{ request()->routeIs('internships.*') ? 'active' : '' }}"><a href="{{ route('internships.index') }}" class="menu-link"><i class="menu-icon bx bx-briefcase-alt-2"></i><div>Presensi Magang/PKL</div></a></li>
            @endif
            @if(Auth::user()->role === 'superadmin')
            <li class="menu-item {{ request()->routeIs('guest-book.*') ? 'active' : '' }}"><a href="{{route('guest-book.index')}}" class="menu-link"><i class="menu-icon bx bx-book-reader"></i><div>Buku Tamu</div></a></li>
            @endif            <li class="menu-item {{ request()->routeIs('activity-attendance.*') ? 'active' : '' }}">
                <a href="{{ route('activity-attendance.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-clipboard"></i>
                    <div>Presensi Kegiatan</div>
                </a>
            </li>
            @if(Auth::user()->role === 'superadmin' || in_array(Auth::user()->bidang, [
                'Bidang Pengembangan Kompetensi Teknis Inti',
                'Bidang Pengembangan Kompetensi Teknis Umum',
                'Bidang Pengembangan Kompetensi Manajerial',
                'Bidang Sertifikasi Kompetensi & Pengelolaan Kelembagaan'
            ], true))
                <li class="menu-header small text-uppercase"><span class="menu-header-text">Kemitraan</span></li>
                <li class="menu-item {{ request()->routeIs('mitra.admin.*') || (request()->routeIs('mitra.submissions.show') && Auth::user()->role !== 'mitra') ? 'active' : '' }}">
                    <a href="{{ route('mitra.admin.index') }}" class="menu-link"><i class="menu-icon bx bx-link"></i><div>Pengajuan Mitra</div>@if($menuNoticeCount('partners'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge" title="Notifikasi baru">{{$menuNoticeCount('partners')>99?'99+':$menuNoticeCount('partners')}}</span>@endif</a>
                </li>
            @endif
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Monitoring & Tindak Lanjut</span></li>
            <li class="menu-item {{ request()->routeIs('followup.*') ? 'active' : '' }}">
                <a href="{{ route('followup.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-task"></i>
                    <div>Rekomendasi Monitoring</div>@if($menuNoticeCount('followup'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge" title="Notifikasi baru">{{$menuNoticeCount('followup')>99?'99+':$menuNoticeCount('followup')}}</span>@endif
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
                    <div>Manajemen Dokumen</div>@if($menuNoticeCount('documents'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge" title="Notifikasi baru">{{$menuNoticeCount('documents')>99?'99+':$menuNoticeCount('documents')}}</span>@endif
                </a>
            </li>

            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Persetujuan Elektronik</span>
            </li>
            <li class="menu-item {{ request()->routeIs('electronic-signatures.*') ? 'active' : '' }}">
                <a href="{{ route('electronic-signatures.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-pen"></i>
                    <div>Tanda Tangan Elektronik</div>@if($menuNoticeCount('electronic_signatures'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge" title="Dokumen menunggu tanda tangan">{{$menuNoticeCount('electronic_signatures')>99?'99+':$menuNoticeCount('electronic_signatures')}}</span>@endif
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
            <li class="menu-item {{ request()->routeIs('settings.login-help.*') ? 'active' : '' }}">
                <a href="{{ route('settings.login-help.edit') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-help-circle"></i>
                    <div>Bantuan Login</div>
                </a>
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
                    <div>Jadwal Mengajar</div>@if($menuNoticeCount('teacher_portal'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge" title="Notifikasi baru">{{$menuNoticeCount('teacher_portal')>99?'99+':$menuNoticeCount('teacher_portal')}}</span>@endif
                </a>
            </li>

            <li class="menu-item {{ request()->routeIs('pengajar.history') ? 'active' : '' }}">
                <a href="{{ route('pengajar.history') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-history"></i>
                    <div>Riwayat Mengajar</div>
                </a>
            </li>
        @endif

        @if(in_array(Auth::user()->role, ['superadmin', 'admin_bidang']))
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Kelola Layanan</span></li>
            <li class="menu-item {{ request()->routeIs('ticketing.*') && !request()->routeIs('ticketing.master.*') ? 'active' : '' }}">
                <a href="{{ route('ticketing.dashboard') }}" class="menu-link"><i class="menu-icon tf-icons bx bx-support"></i><div>Manajemen Layanan</div>@if($menuNoticeCount('ticketing'))<span class="badge bg-danger rounded-pill ms-auto menu-notification-badge" title="Tiket menunggu tindakan">{{$menuNoticeCount('ticketing')>99?'99+':$menuNoticeCount('ticketing')}}</span>@endif</a>
            </li>
            @if(Auth::user()->role === 'superadmin')
            <li class="menu-item {{ request()->routeIs('ticketing.master.*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-cog"></i>
                    <div>Master Data</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ request()->routeIs('ticketing.master.categories*') ? 'active' : '' }}">
                        <a href="{{ route('ticketing.master.categories') }}" class="menu-link"><div>Kategori Aduan</div></a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('ticketing.master.services*') ? 'active' : '' }}">
                        <a href="{{ route('ticketing.master.services') }}" class="menu-link"><div>Layanan</div></a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('ticketing.master.bidang*') ? 'active' : '' }}">
                        <a href="{{ route('ticketing.master.bidang') }}" class="menu-link"><div>Bidang</div></a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('ticketing.master.routing*') ? 'active' : '' }}">
                        <a href="{{ route('ticketing.master.routing') }}" class="menu-link"><div>Routing</div></a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('ticketing.master.sla*') ? 'active' : '' }}">
                        <a href="{{ route('ticketing.master.sla') }}" class="menu-link"><div>SLA</div></a>
                    </li>
                </ul>
            </li>
            @endif
        @endif
        <li class="menu-spacer" aria-hidden="true"></li>

    </ul>
</aside>

{{-- CSS CUSTOM UNTUK INTERAKTIVITAS --}}
<style>

    .menu-notification-badge {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 1.35rem; height: 1.35rem; margin-left: auto; padding: 0 .36rem;
        border: 2px solid #fff; border-radius: 50rem; background: #ff3e1d; color: #fff;
        font-size: .62rem; font-weight: 700; line-height: 1;
        box-shadow: 0 .15rem .45rem rgba(255, 62, 29, .3);
    }
    .menu-item.active > .menu-link .menu-notification-badge { border-color: rgba(255,255,255,.65); }
    /* Transisi menu (hanya menu level-1, biarkan sub-menu memakai indent bawaan template) */
    .menu-vertical > .menu-inner > .menu-item > .menu-link {
        transition: all 0.2s ease-in-out;
        border-radius: 0.375rem;
        margin: 0.15rem 1rem;
        padding-left: 1rem;
    }

    /* Efek Hover */
    .menu-vertical > .menu-inner > .menu-item:not(.active):not(.open) > .menu-link:hover {
        background-color: rgba(105, 108, 255, 0.08) !important;
        transform: translateX(5px);
        color: #696cff !important;
    }

    .menu-vertical > .menu-inner > .menu-item:not(.active):not(.open) > .menu-link:hover i {
        color: #696cff !important;
        transform: scale(1.1);
    }

    /* Mempercantik Menu Header */
    .menu-header {
        min-height: auto !important;
        margin: 1.35rem 1rem 0.55rem !important;
        padding: 0.52rem 0.75rem !important;
        border-left: 3px solid #8fa8ff;
        border-radius: 0.5rem;
    }

    .menu-header:first-child {
        margin-top: 0 !important;
    }

    .menu-header-text {
        color: #5d73c7 !important;
        font-weight: 800 !important;
        font-size: 0.67rem;
        line-height: 1.25;
        letter-spacing: 0.75px;
        white-space: normal;
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

    .menu-vertical .menu-item.active > .menu-link {
        font-weight: 700 !important;
    }

    /* Active sub-menu: ringan — bold ungu + aksen garis, tanpa gradien berat */
    .menu-vertical .menu-sub .menu-item.active > .menu-link {
        background: transparent !important;
        color: #696cff !important;
        font-weight: 700 !important;
        box-shadow: none !important;
        border-left: 3px solid #696cff;
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

