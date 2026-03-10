import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';

export function initDream() {
    const swiperEl = document.querySelector('.dream-swiper');
    if (!swiperEl) return;

    new Swiper(swiperEl, {
        modules: [Navigation],
        slidesPerView: 1,
        spaceBetween: 40,
        speed: 500,
        navigation: {
            nextEl: '.dream-next',
            prevEl: '.dream-prev',
        },
    });

    // Animate dream-bg falling effect
    const dreamSection = document.querySelector('.dream');
    const dreamBg = document.querySelector('.dream-bg');
    if (dreamSection && dreamBg) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        dreamBg.classList.add('animate');
                        observer.unobserve(dreamSection);
                    }
                });
            },
            { threshold: 0.5 }
        );
        observer.observe(dreamSection);
    }
}
