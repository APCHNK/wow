import Swiper from 'swiper';

export function initInstagram() {
    // Title fade in on scroll
    const titleEl = document.querySelector('.instagram-title');
    if (titleEl) {
        const titleObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    titleEl.classList.add('animate');
                    titleObserver.disconnect();
                }
            });
        }, { threshold: 0.5 });
        titleObserver.observe(titleEl);
    }

    // Description slide up on scroll
    const descEl = document.querySelector('.instagram-desc');
    if (descEl) {
        const descObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    descEl.classList.add('animate');
                    descObserver.disconnect();
                }
            });
        }, { threshold: 0.5 });
        descObserver.observe(descEl);
    }

    const swiperEl = document.querySelector('.instagram-swiper');
    if (!swiperEl) return;

    new Swiper(swiperEl, {
        slidesPerView: 'auto',
        spaceBetween: 20,
        loop: true,
    });
}
