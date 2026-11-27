const quizReceipt = document.querySelector('[data-quiz-receipt-url]');

if (quizReceipt) {
    const deadline = Date.now() + 10000;
    const seconds = quizReceipt.querySelector('[data-quiz-seconds]');
    const progress = quizReceipt.querySelector('progress');

    function returnToIdentity() {
        quizReceipt.hidden = true;
        window.location.replace(quizReceipt.dataset.quizReceiptUrl);
    }

    function updateCountdown() {
        const remaining = Math.max(0, deadline - Date.now());
        seconds.textContent = Math.ceil(remaining / 1000);
        progress.value = remaining / 1000;
        if (remaining === 0) {
            window.clearInterval(countdown);
            returnToIdentity();
        }
    }

    const countdown = window.setInterval(updateCountdown, 250);
    document.addEventListener('visibilitychange', updateCountdown);
    window.addEventListener('pagehide', () => { quizReceipt.hidden = true; });
    window.addEventListener('pageshow', event => {
        if (event.persisted) returnToIdentity();
    });
}
