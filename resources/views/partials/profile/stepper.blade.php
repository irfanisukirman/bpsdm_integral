{{--
    Stepper horizontal + wizard section form.

    Hanya satu container section yang tampil pada satu waktu. Nomor urut berubah
    menjadi lingkaran hijau bercentang begitu seluruh field section terisi.

    Parameter:
      $steps  array<int, array{key: string, label: string, fields: array<int, string>}>
              key    = nilai atribut data-section pada kartu section
              fields = nama input yang wajib terisi agar section dianggap lengkap

    Navigasi (Sebelumnya / Berikutnya) diambil dari partial wizard-nav. Elemen
    navigasi tersebut dipindahkan ke dalam kartu section yang aktif sebagai
    card-footer, sehingga tidak terlihat sebagai container terpisah. Tombol simpan
    tetap memakai footer bawaan layout.
--}}
@php
    $stepperSteps = $steps ?? [];
    $stepperActive = $activeStep ?? 0;
    $stepperErrors = $errorCounts ?? [];
@endphp

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bx bx-list-check me-1"></i> Kelengkapan Data
            </h6>
            <span class="badge bg-label-success" id="stepperSummary">0 dari {{ count($stepperSteps) }} section lengkap</span>
        </div>

        <ol class="profile-stepper" id="profileStepper">
            @foreach($stepperSteps as $index => $step)
                @php($errorCount = $stepperErrors[$index] ?? 0)
                <li class="profile-stepper__item{{ $index === $stepperActive ? ' is-active' : '' }}{{ $errorCount > 0 ? ' has-error' : '' }}"
                    data-step="{{ $step['key'] }}"
                    data-fields='@json($step['fields'])'
                    data-errors="{{ $errorCount }}">
                    <span class="profile-stepper__badge">
                        <span class="profile-stepper__number">{{ $index + 1 }}</span>
                        <i class="bx bx-check profile-stepper__check"></i>
                    </span>
                    <span class="profile-stepper__label">{{ $step['label'] }}</span>
                    @if($errorCount > 0)
                        <span class="profile-stepper__error" title="{{ $errorCount }} isian belum benar">{{ $errorCount }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</div>

@push('form_css')
<style>
    .profile-stepper {
        display: flex;
        align-items: flex-start;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .profile-stepper__item {
        position: relative;
        flex: 1 1 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 0 .25rem;
        cursor: default;
    }
    .profile-stepper__badge {
        position: relative;
        z-index: 1;
        display: grid;
        place-items: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #eef0f6;
        color: #566a7f;
        font-weight: 700;
        font-size: .85rem;
        transition: background .25s ease, color .25s ease, box-shadow .25s ease;
    }
    /* garis penghubung antar lingkaran */
    .profile-stepper__item::after {
        content: '';
        position: absolute;
        z-index: 0;
        top: 17px;
        left: 50%;
        width: 100%;
        height: 2px;
        background: #e7e7f0;
    }
    .profile-stepper__item:last-child::after { display: none; }
    .profile-stepper__check {
        position: absolute;
        font-size: 1.2rem;
        opacity: 0;
        transform: scale(.5);
        transition: opacity .25s ease, transform .25s ease;
    }
    .profile-stepper__label {
        margin-top: .45rem;
        font-size: .76rem;
        font-weight: 600;
        color: #566a7f;
        line-height: 1.2;
    }
    .profile-stepper__item.is-active .profile-stepper__badge {
        background: #696cff;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(105, 108, 255, .18);
    }
    .profile-stepper__item.is-active .profile-stepper__label { color: #303f9f; font-weight: 700; }
    .profile-stepper__item.is-complete .profile-stepper__badge {
        background: #28a66a;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(40, 166, 106, .15);
    }
    .profile-stepper__item.is-complete .profile-stepper__label { color: #218657; }
    .profile-stepper__item.is-complete .profile-stepper__number { opacity: 0; }
    .profile-stepper__item.is-complete .profile-stepper__check { opacity: 1; transform: scale(1); }
    .profile-stepper__item.is-complete.is-active .profile-stepper__badge {
        background: #28a66a;
        box-shadow: 0 0 0 5px rgba(40, 166, 106, .22);
    }
    .profile-stepper__item.is-blocked .profile-stepper__badge {
        background: #ff3e1d;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(255, 62, 29, .18);
    }
    .profile-stepper__error {
        position: absolute;
        top: -2px;
        right: calc(50% - 22px);
        z-index: 2;
        min-width: 17px;
        height: 17px;
        padding: 0 4px;
        border-radius: 999px;
        background: #ff3e1d;
        color: #fff;
        font-size: .64rem;
        font-weight: 700;
        line-height: 17px;
        text-align: center;
        box-shadow: 0 0 0 2px #fff;
    }
    @keyframes wizardShake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    .is-shaking { animation: wizardShake .3s ease 2; }

    .wizard-nav {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .5rem;
        padding: .85rem 1.5rem;
        background-color: #fafaff;
        border-top: 1px solid #e7e7ff;
        border-radius: 0 0 .5rem .5rem;
    }
    .wizard-nav__hint { min-height: 1.1rem; }

    @media (max-width: 575.98px) {
        .profile-stepper__badge { width: 30px; height: 30px; font-size: .75rem; }
        .profile-stepper__item::after { top: 14px; }
        .profile-stepper__label { font-size: .62rem; }
        .profile-stepper__item:not(.is-active) .profile-stepper__label {
            max-height: 2.4em;
            overflow: hidden;
        }
    }
</style>
@endpush

@push('form_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stepper = document.getElementById('profileStepper');
    const form = document.getElementById('reusableForm');
    if (!stepper || !form) return;

    const summary = document.getElementById('stepperSummary');
    const hint = document.getElementById('wizardHint');
    const nav = document.getElementById('wizardNav');
    const prevButton = document.getElementById('wizardPrev');
    const nextButton = document.getElementById('wizardNext');
    const layoutFooter = form.querySelector('[data-form-footer]');
    const items = Array.from(stepper.querySelectorAll('.profile-stepper__item'));

    const sections = items.map(item => ({
        item,
        key: item.dataset.step,
        panel: form.querySelector('[data-section="' + item.dataset.step + '"]')
    })).filter(step => step.panel);

    if (!sections.length) return;

    let current = 0;

    const controlOf = name => form.querySelector('[name="' + name + '"]');

    const isFilled = field => {
        if (!field || field.disabled) return true;
        if (field.type === 'checkbox' || field.type === 'radio') return field.checked;
        return String(field.value ?? '').trim() !== '';
    };

    const isValid = field => {
        if (!field || field.disabled) return true;
        if (typeof field.checkValidity === 'function' && !field.checkValidity()) return false;
        return isFilled(field);
    };

    const fieldsOf = step => {
        try {
            return JSON.parse(step.item.dataset.fields || '[]');
        } catch (error) {
            return [];
        }
    };

    const isComplete = step => {
        const fields = fieldsOf(step);
        return fields.length > 0 && fields.every(name => isValid(controlOf(name)));
    };

    const firstInvalidControl = step => fieldsOf(step)
        .map(name => controlOf(name))
        .find(field => field && !isValid(field));

    const render = () => {
        sections.forEach((step, index) => {
            const active = index === current;
            const complete = isComplete(step);

            step.panel.classList.toggle('d-none', !active);
            step.item.classList.toggle('is-active', active);
            step.item.classList.toggle('is-complete', complete);

            // Lencana merah dari server hilang begitu section sudah terisi.
            if (complete) step.item.classList.remove('has-error');
        });

        if (prevButton) prevButton.classList.toggle('d-none', current === 0);
        if (nextButton) nextButton.classList.toggle('d-none', current === sections.length - 1);
        if (layoutFooter) layoutFooter.classList.toggle('d-none', current !== sections.length - 1);

        // Navigasi dipindahkan ke dalam kartu section aktif agar tombol Berikutnya
        // menyatu dengan container isian, bukan berdiri sebagai kartu terpisah.
        if (nav && nav.parentElement !== sections[current].panel) {
            sections[current].panel.appendChild(nav);
        }
        if (nav) nav.classList.remove('d-none');

        const done = sections.filter(isComplete).length;
        if (summary) {
            summary.textContent = done + ' dari ' + sections.length + ' section lengkap';
        }
    };

    const goTo = index => {
        current = Math.min(Math.max(index, 0), sections.length - 1);
        render();
        // Peta Leaflet dihitung saat section masih tersembunyi, jadi paksa
        // menghitung ulang ukuran begitu section-nya terlihat.
        window.dispatchEvent(new Event('resize'));
        sections[current].panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const requireComplete = index => {
        const step = sections[index];
        if (isComplete(step)) return true;

        sections[index].item.classList.add('is-blocked');
        setTimeout(() => step.item.classList.remove('is-blocked'), 1600);

        const control = firstInvalidControl(step);
        if (control) {
            control.classList.add('is-invalid');
            control.focus({ preventScroll: true });
            setTimeout(() => control.classList.remove('is-invalid'), 1800);
        }

        if (hint) {
            hint.textContent = 'Lengkapi bagian yang masih kosong sebelum melanjutkan.';
            hint.classList.add('text-danger', 'fw-semibold');
            setTimeout(() => hint.classList.remove('text-danger', 'fw-semibold'), 2200);
        }

        step.item.classList.add('is-shaking');
        setTimeout(() => step.item.classList.remove('is-shaking'), 700);

        return false;
    };

    nextButton && nextButton.addEventListener('click', function() {
        if (requireComplete(current)) goTo(current + 1);
    });

    prevButton && prevButton.addEventListener('click', function() {
        goTo(current - 1);
    });

    // Stepper bisa diklik untuk Mundur, tapi tidak boleh melewati section yang belum lengkap.
    items.forEach((item, index) => {
        item.addEventListener('click', function() {
            if (index <= current || sections.slice(0, index).every(isComplete)) goTo(index);
        });
    });

    form.addEventListener('input', render);
    form.addEventListener('change', render);
    form.addEventListener('reset', () => setTimeout(() => goTo(0), 0));

    // Cegah submit bila section yang belum lengkap masih tersisa.
    form.addEventListener('submit', function(event) {
        const blocking = sections.findIndex(step => !isComplete(step));
        if (blocking === -1) return;

        event.preventDefault();
        event.stopPropagation();
        goTo(blocking);
        requireComplete(blocking);
    }, true);

    render();
});
</script>
@endpush