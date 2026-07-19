document.addEventListener('DOMContentLoaded', () => {

    const slides = document.querySelectorAll('.review-slide');
    const btnPrev = document.getElementById('prev-review');
    const btnNext = document.getElementById('next-review');
    let current = 0;
    let timer;

    function showSlide(index) {
        slides.forEach(s => s.classList.remove('active'));
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');
    }

    function startAutoplay() {
        timer = setInterval(() => showSlide(current + 1), 5000);
    }

    function resetAutoplay() {
        clearInterval(timer);
        startAutoplay();
    }

    if (slides.length > 0) {
        showSlide(0);
        startAutoplay();
        btnPrev?.addEventListener('click', () => { showSlide(current - 1); resetAutoplay(); });
        btnNext?.addEventListener('click', () => { showSlide(current + 1); resetAutoplay(); });
    }

    const backToTop = document.getElementById('backToTop');
    window.addEventListener('scroll', () => {
        backToTop?.classList.toggle('visible', window.scrollY > 400);
    }, { passive: true });

});

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}