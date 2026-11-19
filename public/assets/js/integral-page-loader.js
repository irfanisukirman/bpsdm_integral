(() => {
    const loader = document.getElementById('integralWelcomeLoader');
    if (!loader) return;
    const preference = matchMedia('(prefers-reduced-motion: reduce)');
    let navigating = false;
    let navigationTimer;
    let finish;
    function show() {
        loader.classList.remove('is-complete');
        loader.hidden = false;
    }
    function hide() { loader.hidden = true; navigating = false; }
    if (!preference.matches) {
        show();
        window.integralWelcomeReady = new Promise(resolve => {
            let finished = false;
            finish = () => {
                if (finished) return;
                finished = true;
                if (navigating || preference.matches) { if (!navigating) hide(); resolve(); return; }
                loader.classList.add('is-complete');
                // Start the content entrance alongside the logo exit.
                resolve();
                setTimeout(() => { if (!navigating) hide(); }, 260);
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', finish, {once:true});
            } else {
                requestAnimationFrame(finish);
            }
            setTimeout(finish, 1500);
        });
    }
    document.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (preference.matches || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || !link) return;
        if (link.hasAttribute('download') || (link.target && link.target !== '_self') || link.hasAttribute('data-bs-toggle') || link.hasAttribute('onclick')) return;
        const target = new URL(link.href, location.href);
        if (!['http:', 'https:'].includes(target.protocol) || target.origin !== location.origin) return;
        if (target.pathname === location.pathname && target.search === location.search) return;
        // File exports keep their usual download flow.
        if (/export|download|unduh|\.(pdf|xlsx?|docx?|zip)$/i.test(target.pathname)) return;
        event.preventDefault();
        if (navigating) return;
        navigating = true;
        show();
        navigationTimer = setTimeout(() => location.assign(target.href), 0);
        // A cancelled or failed navigation must never leave the logo covering the page.
        setTimeout(() => { if (document.visibilityState === 'visible') hide(); }, 8000);
    });
    window.addEventListener('pageshow', event => { if (event.persisted) { clearTimeout(navigationTimer); hide(); } });
    preference.addEventListener('change', () => { if (preference.matches && !navigating) { hide(); finish?.(); } });
})();
