import Swiper from 'swiper';
import { Navigation, Mousewheel } from 'swiper/modules';

export function initOurProjects() {
    const swiperEl = document.querySelector('.our-projects-swiper');
    if (!swiperEl) return;

    new Swiper('.our-projects-swiper', {
        modules: [Navigation, Mousewheel],
        slidesPerView: 'auto',
        spaceBetween: 20,
        breakpoints: {
            0: {
                spaceBetween: 8,
            },
            481: {
                spaceBetween: 20,
            },
        },
        navigation: {
            nextEl: '.our-projects-next',
            prevEl: '.our-projects-prev',
        },
        mousewheel: {
            forceToAxis: true,
        },
    });
}
