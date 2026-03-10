import gsap from 'gsap';
import Swiper from 'swiper';
import { Navigation, Mousewheel } from 'swiper/modules';
import 'swiper/css';

export function initAbout() {
    initMarquee();
    initSlider();
    initTitleEffect();
}

function initTitleEffect() {
    const aboutTitle = document.querySelector('.about-title');
    if (!aboutTitle) return;

    // Fade in on scroll
    const fadeObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    aboutTitle.classList.add('animate');
                    fadeObserver.unobserve(aboutTitle);
                }
            });
        },
        { threshold: 0.3 }
    );
    fadeObserver.observe(aboutTitle);

    const baseFontSize1 = 215;
    const baseFontSize2 = 126;
    const baseMargin = -22;

    let progress = 0;
    let targetProgress = 0;

    function updateTitle() {
        const rect = aboutTitle.getBoundingClientRect();
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

        aboutTitle.style.setProperty('--title-size-1', `${fontSize1}px`);
        aboutTitle.style.setProperty('--title-size-2', `${fontSize2}px`);
        aboutTitle.style.setProperty('--title-margin', `${margin}px`);

        requestAnimationFrame(updateTitle);
    }

    requestAnimationFrame(updateTitle);
}

function initMarquee() {
    const marqueeTracks = document.querySelectorAll('.marquee-track');

    if (marqueeTracks.length === 0) return;

    const baseSpeed = 1;
    const scrollBoost = 0.4;
    let lastScrollY = window.scrollY;
    let scrollDelta = 0;

    // Track scroll velocity
    window.addEventListener('scroll', () => {
        const currentScrollY = window.scrollY;
        scrollDelta = currentScrollY - lastScrollY;
        lastScrollY = currentScrollY;
    }, { passive: true });

    marqueeTracks.forEach((track) => {
        const content = track.querySelector('.marquee-content');
        if (!content) return;

        const isReverse = track.classList.contains('marquee-track--reverse');

        // Clone content multiple times for seamless loop
        for (let i = 0; i < 3; i++) {
            const clone = content.cloneNode(true);
            track.appendChild(clone);
        }

        const contentWidth = content.offsetWidth;

        let xPos = 0;
        let currentSpeed = 0;
        let targetSpeed = 0;

        gsap.ticker.add(() => {
            // Calculate target speed based on scroll
            const direction = isReverse ? -1 : 1;
            targetSpeed = (baseSpeed + (scrollDelta * scrollBoost)) * direction;

            // Smooth speed transition
            currentSpeed += (targetSpeed - currentSpeed) * 0.1;

            // Update position
            xPos += currentSpeed;

            // Wrap position for infinite loop
            let wrappedX = ((xPos % contentWidth) + contentWidth) % contentWidth;
            wrappedX = -wrappedX;

            // Apply transform
            gsap.set(track, { x: wrappedX });

            // Decay target speed back to base
            targetSpeed += ((baseSpeed * direction) - targetSpeed) * 0.05;
        });
    });

    // Reset scroll delta after processing
    gsap.ticker.add(() => {
        scrollDelta *= 0.9;
    });
}

function initSlider() {
    const swiperEl = document.querySelector('.about-swiper');
    if (!swiperEl) return;

    const swiper = new Swiper('.about-swiper', {
        modules: [Navigation, Mousewheel],
        slidesPerView: 'auto',
        spaceBetween: 20,
        navigation: {
            nextEl: '.slider-next',
            prevEl: '.slider-prev',
        },
        mousewheel: {
            forceToAxis: true,
        },
        breakpoints: {
            0: {
                spaceBetween: 8,
            },
            481: {
                spaceBetween: 12,
            },
            769: {
                spaceBetween: 16,
            },
            1025: {
                spaceBetween: 20,
            },
        },
    });
}
