(async () => {
    const root = document.documentElement;
    const preference = window.matchMedia('(prefers-reduced-motion: reduce)');
    const running = new Set();
    let observer;
    const reduced = () => preference.matches;
    function reveal(element, delay = 0) {
        if (reduced() || typeof element.animate !== 'function') return;
        const animation = element.animate([
            {transform: 'translateY(24px)'},
            {transform: 'translateY(0)'}
        ], {duration: 1700, delay, easing: 'cubic-bezier(.22,1,.36,1)', fill: 'backwards'});
        running.add(animation);
        animation.finished.catch(() => {}).finally(() => running.delete(animation));
    }
    if (window.integralWelcomeReady) await window.integralWelcomeReady;
    if (!reduced()) {
        root.classList.add('integral-motion-ready');
        const elements = [...document.querySelectorAll('.welcome-intro h2, .welcome-intro p, .hero-copy, .hero-illustration-wrap, .feature-card, .integration-card, .public-asset-item, #stats .col-6, section > .container > .text-center, .login-panel')];
        if ('IntersectionObserver' in window) {
            observer = new IntersectionObserver(entries => {
                let order = 0;
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;
                    reveal(entry.target, Math.min(order++ * 90, 360));
                    observer.unobserve(entry.target);
                });
            }, {threshold: 0.08, rootMargin: '0px 0px -16px 0px'});
            elements.forEach(element => observer.observe(element));
        }
    }
    preference.addEventListener('change', () => {
        if (!reduced()) return;
        running.forEach(animation => animation.cancel());
        observer?.disconnect();
        root.classList.remove('integral-motion-ready');
    });
})();
