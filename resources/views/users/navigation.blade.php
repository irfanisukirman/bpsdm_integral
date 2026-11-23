<nav class="nav gap-2 mb-4 users-navigation" aria-label="Kelola Pengguna">
    <a class="btn users-tab-list" href="{{ route('users.index') }}" @unless($recapActive) aria-current="page" @endunless>
        <i class="bx bx-group me-1" aria-hidden="true"></i> Daftar User
    </a>
    <a class="btn users-tab-recap" href="{{ route('users.index', ['tab' => 'rekap-peserta']) }}" @if($recapActive) aria-current="page" @endif>
        <i class="bx bx-list-check me-1" aria-hidden="true"></i> Rekap Peserta
    </a>
</nav>

@push('css')
<style>
    .users-navigation .btn { font-weight: 600; border-color: var(--users-tab-border); color: var(--users-tab-ink); background: var(--users-tab-soft); }
    .users-navigation .btn:is([aria-current="page"], :hover) { color: var(--users-tab-on-fill); background: var(--users-tab-fill); border-color: var(--users-tab-fill); }
    .users-navigation .btn[aria-current="page"] { box-shadow: inset 0 -3px 0 rgb(0 0 0 / 18%); }
    .users-navigation .btn:focus-visible { outline: 2px solid var(--users-tab-border); outline-offset: 2px; }
    .users-tab-list {
        --users-tab-soft: var(--integral-brand-soft, #e6f6ec);
        --users-tab-ink: var(--integral-brand-ink, #006b37);
        --users-tab-border: var(--integral-brand-accent, #16a75c);
        --users-tab-fill: #008444;
        --users-tab-on-fill: #fff;
    }
    .users-tab-recap {
        --users-tab-soft: var(--integral-orange-soft, #ffedd9);
        --users-tab-ink: var(--integral-orange-ink, #371900);
        --users-tab-border: var(--integral-orange-line, #b85b0e);
        --users-tab-fill: var(--integral-orange, #ff8a26);
        --users-tab-on-fill: var(--integral-orange-on-fill, #371900);
    }
</style>
@endpush
