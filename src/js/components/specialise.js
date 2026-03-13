import Swiper from 'swiper';
import { Navigation, Mousewheel } from 'swiper/modules';

export function initSpecialise() {
    const swiperEl = document.querySelector('.specialise-swiper');
    if (!swiperEl) return;

    // Only init swiper on desktop (>768px)
    if (window.innerWidth > 768) {
        const containerWidth = swiperEl.offsetWidth;
        const slideWidth = 535;
        const offset = (containerWidth - slideWidth) / 2;

        new Swiper(swiperEl, {
            modules: [Navigation, Mousewheel],
            slidesPerView: 'auto',
            spaceBetween: 220,
            centeredSlides: true,
            slidesOffsetBefore: offset,
            slidesOffsetAfter: offset,
            speed: 500,
            navigation: {
                prevEl: '.specialise-prev',
                nextEl: '.specialise-next',
            },
            mousewheel: {
                forceToAxis: true,
            },
            breakpoints: {
                769: {
                    spaceBetween: 120,
                    centeredSlides: true,
                    slidesPerView: 'auto',
                    slidesOffsetBefore: 0,
                    slidesOffsetAfter: 0,
                },
                1025: {
                    spaceBetween: 220,
                    centeredSlides: true,
                    slidesOffsetBefore: offset,
                    slidesOffsetAfter: offset,
                },
                1281: {
                    spaceBetween: 220,
                    centeredSlides: true,
                    slidesOffsetBefore: offset,
                    slidesOffsetAfter: offset,
                },
            },
        });
    }

    // Specialise title shrink effect
    const specialiseTitle = document.querySelector('.specialise-title');
    if (!specialiseTitle) return;

    // Fade in on scroll
    const fadeObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    specialiseTitle.classList.add('animate');
                    fadeObserver.unobserve(specialiseTitle);
                }
            });
        },
        { threshold: 0.3 }
    );
    fadeObserver.observe(specialiseTitle);

    const baseFontSize1 = 215;
    const baseFontSize2 = 126;
    const baseMargin = -22;

    let progress = 0;
    let targetProgress = 0;

    function updateTitle() {
        const rect = specialiseTitle.getBoundingClientRect();
        const startPoint = window.innerHeight * 0.5;
        const endPoint = 50;

        if (rect.top >= startPoint) {
            targetProgress = 0;
        } else if (rect.top <= endPoint) {
            targetProgress = 1;
        } else {
            targetProgress = 1 - (rect.top - endPoint) / (startPoint - endPoint);
        }

        progress += (targetProgress - progress) * 0.15;

        const fontSize1 = baseFontSize1 * (1 - progress * 0.3);
        const fontSize2 = baseFontSize2 * (1 - progress * 0.3);
        const margin = baseMargin * (1 - progress * 0.3);

        specialiseTitle.style.setProperty('--title-size-1', `${fontSize1}px`);
        specialiseTitle.style.setProperty('--title-size-2', `${fontSize2}px`);
        specialiseTitle.style.setProperty('--title-margin', `${margin}px`);

        requestAnimationFrame(updateTitle);
    }

    requestAnimationFrame(updateTitle);
}
