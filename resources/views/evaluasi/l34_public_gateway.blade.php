<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Gateway pengisian evaluasi pasca pelatihan Level 3 dan Level 4">
    <title>Evaluasi Pasca Pelatihan - {{ $training->nama_pelatihan }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon/inte.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}">

    @php
        $startDate = filled($training->tgl_mulai) ? \Carbon\Carbon::parse($training->tgl_mulai)->locale('id') : null;
        $endDate = filled($training->tgl_selesai) ? \Carbon\Carbon::parse($training->tgl_selesai)->locale('id') : null;
        $dateLabel = null;
        if ($startDate && $endDate) {
            $dateLabel = $startDate->isSameDay($endDate)
                ? $startDate->translatedFormat('d F Y')
                : $startDate->translatedFormat('d M Y').' - '.$endDate->translatedFormat('d M Y');
        } elseif ($startDate) {
            $dateLabel = $startDate->translatedFormat('d F Y');
        }
    @endphp

    <style>
        :root {
            --primary: #635bff;
            --primary-dark: #4338ca;
            --ink: #182230;
            --muted: #667085;
            --line: #e8ebf2;
            --surface: #ffffff;
            --page: #f4f6fb;
        }

        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            background:
                radial-gradient(circle at 5% 5%, rgba(99, 91, 255, .12), transparent 28rem),
                radial-gradient(circle at 95% 95%, rgba(3, 195, 236, .1), transparent 25rem),
                var(--page);
            font-family: "Public Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .page-shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 28px 0;
        }

        .public-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 22px;
        }

        .brand { display: flex; align-items: center; gap: 11px; }
        .brand-logo {
            width: 42px;
            height: 42px;
            padding: 5px;
            object-fit: contain;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 6px 20px rgba(16, 24, 40, .08);
        }
        .brand-name { font-size: 1.05rem; font-weight: 800; letter-spacing: .04em; }
        .brand-caption { color: var(--muted); font-size: .76rem; }
        .secure-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #475467;
            font-size: .8rem;
            font-weight: 600;
            padding: 8px 12px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255,255,255,.8);
        }

        .gateway-card {
            display: grid;
            grid-template-columns: minmax(0, .88fr) minmax(430px, 1.12fr);
            min-height: 620px;
            overflow: hidden;
            background: var(--surface);
            border: 1px solid rgba(232, 235, 242, .9);
            border-radius: 28px;
            box-shadow: 0 24px 70px rgba(16, 24, 40, .1);
        }

        .intro-panel {
            position: relative;
            overflow: hidden;
            padding: 48px;
            color: #fff;
            background: linear-gradient(145deg, #26245d 0%, #4037a7 55%, #635bff 100%);
        }
        .intro-panel::before,
        .intro-panel::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,.07);
        }
        .intro-panel::before { width: 300px; height: 300px; right: -145px; top: -120px; }
        .intro-panel::after { width: 230px; height: 230px; left: -130px; bottom: -105px; }
        .intro-content { position: relative; z-index: 1; }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 28px;
            padding: 8px 12px;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 999px;
            color: rgba(255,255,255,.9);
            background: rgba(255,255,255,.1);
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .intro-panel h1 {
            max-width: 470px;
            margin: 0 0 12px;
            color: #fff;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.12;
            letter-spacing: -.035em;
        }
        .intro-lead { margin: 0 0 28px; color: rgba(255,255,255,.72); line-height: 1.7; }
        .training-box {
            padding: 18px;
            border: 1px solid rgba(255,255,255,.16);
            border-radius: 18px;
            background: rgba(255,255,255,.09);
            backdrop-filter: blur(8px);
        }
        .training-label { display: block; margin-bottom: 7px; color: rgba(255,255,255,.6); font-size: .74rem; text-transform: uppercase; letter-spacing: .07em; }
        .training-name { margin: 0; color: #fff; font-size: 1.08rem; line-height: 1.5; }
        .meta-list { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
        .meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 10px;
            color: rgba(255,255,255,.86);
            background: rgba(12, 10, 48, .18);
            font-size: .76rem;
        }
        .level-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 24px; }
        .level-item { padding: 15px; border-radius: 15px; background: rgba(255,255,255,.09); }
        .level-number { display: block; color: #fff; font-weight: 800; font-size: 1rem; }
        .level-text { color: rgba(255,255,255,.65); font-size: .76rem; }

        .role-panel { display: flex; flex-direction: column; padding: 48px; }
        .role-heading { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 25px; }
        .role-heading h2 { margin: 0 0 7px; color: var(--ink); font-size: 1.55rem; letter-spacing: -.02em; }
        .role-heading p { max-width: 480px; margin: 0; color: var(--muted); line-height: 1.55; }
        .available-badge {
            flex: 0 0 auto;
            padding: 7px 10px;
            color: var(--primary-dark);
            border-radius: 999px;
            background: #eeecff;
            font-size: .72rem;
            font-weight: 700;
        }
        .role-list { display: grid; gap: 13px; }
        .role-card {
            display: grid;
            grid-template-columns: 56px minmax(0, 1fr) 40px;
            align-items: center;
            gap: 16px;
            padding: 18px;
            color: inherit;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: #fff;
            text-decoration: none;
            transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        }
        .role-card:hover, .role-card:focus-visible {
            color: inherit;
            transform: translateY(-3px);
            border-color: #c9c5ff;
            box-shadow: 0 12px 28px rgba(99, 91, 255, .12);
            outline: none;
        }
        .role-icon {
            display: grid;
            place-items: center;
            width: 56px;
            height: 56px;
            border-radius: 16px;
            font-size: 1.65rem;
        }
        .card-mandiri .role-icon { color: #635bff; background: #eeecff; }
        .card-atasan .role-icon { color: #087ea4; background: #e5f8fc; }
        .card-rekan .role-icon { color: #348514; background: #ecf9e7; }
        .role-title { display: block; margin-bottom: 4px; color: #1d2939; font-weight: 800; font-size: .94rem; }
        .role-description { display: block; color: var(--muted); font-size: .8rem; line-height: 1.5; }
        .role-arrow {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            color: var(--primary);
            border-radius: 50%;
            background: #f3f2ff;
            font-size: 1.35rem;
            transition: transform .2s ease;
        }
        .role-card:hover .role-arrow { transform: translateX(3px); }
        .empty-state { padding: 34px 24px; text-align: center; border: 1px dashed #d0d5dd; border-radius: 18px; background: #fafbfc; }
        .empty-icon { display: grid; place-items: center; width: 58px; height: 58px; margin: 0 auto 14px; color: #b54708; border-radius: 50%; background: #fff3e0; font-size: 1.7rem; }
        .empty-state h3 { margin: 0 0 7px; font-size: 1.05rem; }
        .empty-state p { margin: 0; color: var(--muted); font-size: .85rem; }
        .helper-box {
            display: flex;
            gap: 11px;
            margin-top: auto;
            padding-top: 26px;
            color: #475467;
            font-size: .78rem;
            line-height: 1.55;
        }
        .helper-box i { flex: 0 0 auto; margin-top: 2px; color: var(--primary); font-size: 1.15rem; }
        .public-footer { padding-top: 18px; color: #7b8494; text-align: center; font-size: .74rem; }

        @media (max-width: 900px) {
            .gateway-card { grid-template-columns: 1fr; }
            .intro-panel, .role-panel { padding: 36px; }
            .intro-panel h1 { max-width: none; }
        }
        @media (max-width: 575px) {
            .page-shell { width: min(100% - 20px, 1180px); padding: 14px 0 22px; }
            .public-header { margin-bottom: 14px; }
            .secure-label { display: none; }
            .gateway-card { border-radius: 21px; }
            .intro-panel, .role-panel { padding: 27px 22px; }
            .intro-panel h1 { font-size: 2rem; }
            .level-grid { grid-template-columns: 1fr; }
            .role-heading { display: block; }
            .available-badge { display: inline-block; margin-top: 13px; }
            .role-card { grid-template-columns: 48px minmax(0, 1fr) 32px; gap: 12px; padding: 15px; }
            .role-icon { width: 48px; height: 48px; border-radius: 14px; font-size: 1.4rem; }
            .role-arrow { width: 32px; height: 32px; }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <header class="public-header">
            <div class="brand">
                <img src="{{ asset('assets/img/favicon/inte.png') }}" class="brand-logo" alt="Logo INTEGRAL">
                <div>
                    <div class="brand-name">INTEGRAL</div>
                    <div class="brand-caption">BPSDM Provinsi Jawa Barat</div>
                </div>
            </div>
            <div class="secure-label"><i class="bx bx-lock-alt"></i> Formulir evaluasi resmi</div>
        </header>

        <main class="gateway-card">
            <section class="intro-panel">
                <div class="intro-content">
                    <div class="eyebrow"><i class="bx bx-line-chart"></i> Evaluasi Pasca Pelatihan</div>
                    <h1>Perubahan yang terukur dimulai dari penilaian Anda.</h1>
                    <p class="intro-lead">Pilih peran yang sesuai untuk memberikan penilaian Level 3 dan Level 4 secara objektif.</p>

                    <div class="training-box">
                        <span class="training-label">Pelatihan yang dinilai</span>
                        <h2 class="training-name">{{ $training->nama_pelatihan }}</h2>
                        <div class="meta-list">
                            @if($dateLabel)
                                <span class="meta-item"><i class="bx bx-calendar"></i>{{ $dateLabel }}</span>
                            @endif
                            @if(filled($training->program_evaluasi))
                                <span class="meta-item"><i class="bx bx-category"></i>{{ $training->program_evaluasi }}</span>
                            @endif
                            @if(filled($training->metode))
                                <span class="meta-item"><i class="bx bx-laptop"></i>{{ ucwords($training->metode) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="level-grid">
                        <div class="level-item">
                            <span class="level-number">Level 3</span>
                            <span class="level-text">Perubahan perilaku setelah pelatihan</span>
                        </div>
                        <div class="level-item">
                            <span class="level-number">Level 4</span>
                            <span class="level-text">Dampak pelatihan terhadap kinerja</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="role-panel">
                <div class="role-heading">
                    <div>
                        <h2>Anda mengisi sebagai siapa?</h2>
                        <p>Pilih satu peran yang paling sesuai. Hanya instrumen yang tersedia untuk pelatihan ini yang ditampilkan.</p>
                    </div>
                    <span class="available-badge">{{ $roleOptions->count() }} pilihan tersedia</span>
                </div>

                @if($roleOptions->isNotEmpty())
                    <div class="role-list">
                        @foreach($roleOptions as $role => $option)
                            <a href="{{ route('public.l34.form', [$training->id, $role]) }}" class="role-card card-{{ $role }}">
                                <span class="role-icon"><i class="bx {{ $option['icon'] }}"></i></span>
                                <span>
                                    <span class="role-title">{{ $option['label'] }}</span>
                                    <span class="role-description">{{ $option['description'] }}</span>
                                </span>
                                <span class="role-arrow" aria-hidden="true"><i class="bx bx-right-arrow-alt"></i></span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <span class="empty-icon"><i class="bx bx-info-circle"></i></span>
                        <h3>Instrumen evaluasi belum tersedia</h3>
                        <p>Belum ada pertanyaan Mandiri, Atasan, maupun Rekan yang sesuai dengan pelatihan ini.</p>
                    </div>
                @endif

                <div class="helper-box">
                    <i class="bx bx-shield-quarter"></i>
                    <span>Jawaban digunakan untuk peningkatan mutu pelatihan. Pastikan peran yang dipilih sesuai agar hasil evaluasi 360° akurat.</span>
                </div>
            </section>
        </main>

        <footer class="public-footer">INTEGRAL &copy; {{ date('Y') }} &middot; Sistem Informasi Pengembangan Kompetensi Terintegrasi</footer>
    </div>
</body>
</html>