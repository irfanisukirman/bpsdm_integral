{{--
    Bar navigasi wizard: Sebelumnya / Berikutnya.

    Elemen ini bukan kartu tersendiri; script pada partial stepper memindahkannya
    ke dalam kartu section yang sedang aktif sebagai card-footer, sehingga tombol
    Berikutnya menyatu dengan container isian di atasnya.

    Tombol simpan tetap memakai footer bawaan layout, yang otomatis ditampilkan
    oleh script stepper pada step terakhir.
--}}
<div id="wizardNav" class="card-footer wizard-nav d-none">
    <button type="button" id="wizardPrev" class="btn btn-outline-secondary d-none">
        <i class="bx bx-left-arrow-alt me-1"></i> Sebelumnya
    </button>

    <small id="wizardHint" class="wizard-nav__hint text-muted flex-grow-1 text-center"></small>

    <button type="button" id="wizardNext" class="btn btn-primary shadow-sm">
        Berikutnya <i class="bx bx-right-arrow-alt ms-1"></i>
    </button>
</div>