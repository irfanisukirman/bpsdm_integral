(() => {
    const form = document.getElementById('reusableForm');
    const steps = [...form.querySelectorAll('.profile-step')];
    const links = [...document.querySelectorAll('[data-go-step]')];
    const previous = document.getElementById('profilePrevious');
    const next = document.getElementById('profileNext');
    const submit = document.getElementById('btnSubmitForm');
    const error = document.getElementById('profileStepError');
    let current = 0;
    function show(index, focus = true) {
        current = index;
        steps.forEach((step, i) => { step.hidden = i !== index; });
        links.forEach((link, i) => {
            link.classList.toggle('is-complete', i < index);
            if (i === index) link.setAttribute('aria-current', 'step');
            else link.removeAttribute('aria-current');
        });
        previous.hidden = index === 0;
        next.hidden = index === steps.length - 1;
        submit.hidden = index !== steps.length - 1;
        document.getElementById('profileStepStatus').textContent = `Langkah ${index + 1} dari ${steps.length}`;
        error.hidden = true;
        document.dispatchEvent(new CustomEvent('integral:profile-step'));
        if (focus) {
            const title = steps[index].querySelector('.form-section-title');
            title.setAttribute('tabindex', '-1');
            title.focus({preventScroll: true});
            steps[index].scrollIntoView({block: 'start', behavior: 'smooth'});
        }
    }
    function valid(index) {
        const fields = [...steps[index].querySelectorAll('input,select,textarea')];
        for (const field of fields) {
            field.classList.remove('is-invalid');
            const missing = field.required && !field.value.trim();
            if (missing || (!field.disabled && !field.checkValidity())) {
                show(index);
                field.classList.add('is-invalid');
                error.textContent = field.disabled ? 'Tunggu data wilayah selesai dimuat atau muat ulang halaman jika koneksi bermasalah.' : 'Lengkapi isian wajib dengan benar sebelum melanjutkan.';
                error.hidden = false;
                if (field.type !== 'hidden' && !field.disabled) {
                    if (window.jQuery && jQuery(field).hasClass('select2-hidden-accessible')) jQuery(field).select2('open');
                    else { field.focus(); field.reportValidity(); }
                }
                return false;
            }
        }
        const password = form.elements.password;
        if (index === 0 && password && password.value !== form.elements.password_confirmation.value) {
            error.textContent = 'Konfirmasi password belum sama dengan password baru.';
            error.hidden = false;
            form.elements.password_confirmation.focus();
            return false;
        }
        return true;
    }
    function go(index) {
        if (index > current) {
            for (let i = 0; i < index; i++) if (!valid(i)) return;
        }
        show(index);
    }
    previous.addEventListener('click', () => go(current - 1));
    next.addEventListener('click', () => go(current + 1));
    links.forEach(link => link.addEventListener('click', () => go(Number(link.dataset.goStep))));
    form.addEventListener('submit', event => {
        for (let i = 0; i < steps.length; i++) {
            if (!valid(i)) { event.preventDefault(); return; }
        }
        if (current !== steps.length - 1) { event.preventDefault(); go(current + 1); return; }
        submit.disabled = true;
        submit.textContent = 'Menyimpan?';
    });
    // Server validation returns to the section containing the first invalid field.
    const invalidNames = JSON.parse(form.dataset.errorFields || '[]');
    const invalidStep = steps.findIndex(step => [...step.querySelectorAll('[name]')].some(field => invalidNames.includes(field.name)));
    show(invalidStep >= 0 ? invalidStep : 0, false);
})();
