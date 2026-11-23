(() => {
    const loader = document.getElementById('integralWelcomeLoader');
    if (!loader) return;

    const preference = matchMedia('(prefers-reduced-motion: reduce)');
    if (preference.matches) return;

    // Show the loader only while this document is being prepared.
    loader.hidden = false;
    window.integralWelcomeReady = new Promise(resolve => {
        const finish = () => {
            clearTimeout(fallback);
            loader.hidden = true;
            resolve();
        };
        const fallback = setTimeout(finish, 1500);

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', finish, { once: true });
        } else {
            finish();
        }

        window.addEventListener('pageshow', event => {
            if (event.persisted) finish();
        });
        preference.addEventListener('change', () => {
            if (preference.matches) finish();
        });
    });
})();
