import Swiper from 'swiper';
import { Navigation, Mousewheel } from 'swiper/modules';

export function initHappen() {
    // Title text fade in on scroll
    const titleText = document.querySelector('.happen-title-text');
    if (titleText) {
        const textObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    titleText.classList.add('animate');
                    textObserver.disconnect();
                }
            });
        }, { threshold: 0.7 });
        textObserver.observe(titleText);
    }

    // Description slide up on scroll
    const descEl = document.querySelector('.happen-desc');
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

    // Border draw animation on scroll
    const borderEl = document.querySelector('.happen-title-border');
    if (borderEl) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    borderEl.classList.add('animate');
                    observer.disconnect();
                }
            });
        }, { threshold: 0.5 });
        observer.observe(borderEl);
    }

    const swiperEl = document.querySelector('.happen-swiper');
    if (!swiperEl) return;

    const swiper = new Swiper(swiperEl, {
        modules: [Navigation, Mousewheel],
        slidesPerView: 'auto',
        spaceBetween: 32,
        speed: 600,
        watchSlidesProgress: true,
        navigation: {
            nextEl: '.happen-next',
            prevEl: '.happen-prev',
        },
        mousewheel: {
            forceToAxis: true,
        },
        breakpoints: {
            0: {
                spaceBetween: 10,
            },
            481: {
                spaceBetween: 14,
            },
            769: {
                spaceBetween: 20,
            },
            1025: {
                spaceBetween: 32,
            },
        },
    });

    // Parallax settings
    const parallaxAmountX = 30; // horizontal pixels (slider)
    const parallaxAmountY = 0.05; // vertical speed (scroll)

    let scrollOffsetY = 0;

    function setImagesTransition(duration) {
        swiper.slides.forEach(slide => {
            const img = slide.querySelector('img');
            if (img) {
                img.style.transitionDuration = `${duration}ms`;
            }
        });
    }

    function updateParallax() {
        swiper.slides.forEach(slide => {
            const img = slide.querySelector('img');
            if (img) {
                // Horizontal: based on slide progress
                const offsetX = slide.progress * parallaxAmountX;
                // Vertical: based on scroll position
                img.style.transform = `translate3d(${offsetX}px, ${scrollOffsetY}px, 0)`;
            }
        });
    }

    // Scroll parallax
    function updateScrollParallax() {
        const rect = swiperEl.getBoundingClientRect();
        const windowHeight = window.innerHeight;
        // Calculate how far the section is from center of viewport
        const sectionCenter = rect.top + rect.height / 2;
        const viewportCenter = windowHeight / 2;
        const distance = sectionCenter - viewportCenter;
        scrollOffsetY = distance * parallaxAmountY;
        updateParallax();
    }

    window.addEventListener('scroll', updateScrollParallax, { passive: true });
    updateScrollParallax();

    // Remove transition during drag for smooth following
    swiper.on('touchStart', () => {
        setImagesTransition(0);
    });

    // Restore transition when drag ends
    swiper.on('touchEnd', () => {
        setImagesTransition(swiper.params.speed);
    });

    // For navigation buttons - set transition before translate
    swiper.on('beforeTransitionStart', () => {
        setImagesTransition(swiper.params.speed);
    });

    swiper.on('setTranslate', updateParallax);
    updateParallax();
}
