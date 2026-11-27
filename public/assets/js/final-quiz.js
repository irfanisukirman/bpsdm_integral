const finalQuiz = document.querySelector('[data-final-quiz]');

if (finalQuiz) {
    const questions = Array.from(finalQuiz.querySelectorAll('[data-quiz-question]'));
    const numbers = Array.from(finalQuiz.querySelectorAll('[data-quiz-number]'));
    const controls = finalQuiz.querySelector('[data-quiz-controls]');
    const mobileNavigation = finalQuiz.querySelector('[data-quiz-mobile-navigation]');
    const navigatorPanel = finalQuiz.querySelector('[data-quiz-navigator]');
    const previous = finalQuiz.querySelector('[data-quiz-prev]');
    const next = finalQuiz.querySelector('[data-quiz-next]');
    const message = finalQuiz.querySelector('[data-quiz-message]');
    const progress = finalQuiz.querySelector('[data-quiz-progress]');
    const progressText = finalQuiz.querySelector('[data-quiz-progress-text]');
    const reviewButton = finalQuiz.querySelector('[data-quiz-review-button]');
    const review = finalQuiz.querySelector('[data-quiz-review]');
    const cancelReview = finalQuiz.querySelector('[data-quiz-review-cancel]');
    const confirmReview = finalQuiz.querySelector('[data-quiz-review-confirm]');
    let current = 0;

    const hasAnswer = question => Boolean(question.querySelector('input:checked'));

    function updateProgress() {
        const answered = questions.filter(hasAnswer).length;
        progress.value = answered;
        progressText.textContent = answered + ' dari ' + questions.length + ' terjawab';
        numbers.forEach((button, index) => {
            const isAnswered = hasAnswer(questions[index]);
            button.classList.toggle('is-answered', isAnswered);
            button.setAttribute('aria-label', 'Soal ' + (index + 1) + (isAnswered ? ', terjawab' : ', belum dijawab'));
        });
    }

    function showQuestion(index, moveFocus = true) {
        current = index;
        questions.forEach((question, position) => {
            question.hidden = position !== index;
            if (position === index) {
                numbers[position].setAttribute('aria-current', 'step');
            } else {
                numbers[position].removeAttribute('aria-current');
            }
        });
        previous.disabled = current === 0;
        const lastQuestion = current === questions.length - 1;
        next.hidden = lastQuestion;
        reviewButton.hidden = !lastQuestion;

        if (moveFocus) {
            questions[index].querySelector('legend').focus({preventScroll: true});
            questions[index].scrollIntoView({block: 'nearest'});
        }
    }

    if (questions.length) {
        numbers.forEach((button, index) => button.addEventListener('click', () => showQuestion(index)));
        previous.addEventListener('click', () => showQuestion(current - 1));
        next.addEventListener('click', () => showQuestion(current + 1));
        cancelReview.addEventListener('click', () => review.close());
        review.addEventListener('close', () => reviewButton.focus());
        finalQuiz.addEventListener('change', () => {
            message.hidden = true;
            updateProgress();
        });
        finalQuiz.addEventListener('submit', event => {
            const unanswered = questions.findIndex(question => !hasAnswer(question));
            if (unanswered !== -1) {
                event.preventDefault();
                if (review.open) review.close();
                showQuestion(unanswered);
                message.textContent = 'Soal ' + (unanswered + 1) + ' belum dijawab. Lengkapi semua soal sebelum mengirim.';
                message.hidden = false;
                return;
            }

            if (event.submitter === confirmReview && review.open) {
                return;
            }

            event.preventDefault();
            message.hidden = true;
            if (!review.open) review.showModal();
        });

        finalQuiz.noValidate = true;
        finalQuiz.classList.add('quiz-ready');
        next.after(reviewButton);
        controls.hidden = false;
        mobileNavigation.hidden = false;
        navigatorPanel.hidden = false;
        updateProgress();
        showQuestion(0, false);
    }
}
