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
                spaceBetween: 8,
                slidesPerView: 2.2,
            },
            481: {
                spaceBetween: 14,
                slidesPerView: 2.5,
            },
            769: {
                spaceBetween: 20,
                slidesPerView: 'auto',
            },
            1025: {
                spaceBetween: 32,
                slidesPerView: 'auto',
            },
        },
    });

    // Parallax settings
    const parallaxAmountX = 30; // horizontal pixels (slider)
    const slideParallaxY = window.innerWidth <= 480 ? 0.03 : 0.06; // vertical speed for slides

    let scrollOffset = 0;

    function setParallaxTransition(duration) {
        swiper.slides.forEach(slide => {
            const img = slide.querySelector('img');
            if (img) {
                img.style.setProperty('--parallax-duration', `${duration}ms`);
            }
        });
    }

    function updateParallax() {
        swiper.slides.forEach((slide, index) => {
            const img = slide.querySelector('img');
            if (img) {
                // Horizontal parallax on images
                const offsetX = slide.progress * parallaxAmountX;
                img.style.transform = `translate3d(${offsetX}px, 0, 0)`;
            }
            // Vertical parallax on slides - odd/even move opposite
            const direction = index % 2 === 0 ? -1 : 1;
            const scrollOffsetY = scrollOffset * direction;
            slide.style.transform = `translateY(${scrollOffsetY}px)`;
        });
    }

    // Add hover listeners to cards - use separate scale property
    document.querySelectorAll('.happen-card').forEach(card => {
        const img = card.querySelector('img');
        card.addEventListener('mouseenter', () => {
            if (img) img.style.scale = '1.1';
        });
        card.addEventListener('mouseleave', () => {
            if (img) img.style.scale = '1';
        });
    });

    // Smooth transition when using navigation buttons
    swiper.on('beforeTransitionStart', () => {
        setParallaxTransition(swiper.params.speed);
    });

    swiper.on('transitionEnd', () => {
        setParallaxTransition(0);
    });

    // No transition during drag
    swiper.on('touchStart', () => {
        setParallaxTransition(0);
    });

    // Scroll parallax for slides
    function updateScrollParallax() {
        const rect = swiperEl.getBoundingClientRect();
        const windowHeight = window.innerHeight;
        const sectionCenter = rect.top + rect.height / 2;
        const viewportCenter = windowHeight / 2;
        const distance = sectionCenter - viewportCenter;
        scrollOffset = distance * slideParallaxY;
        updateParallax();
    }

    window.addEventListener('scroll', updateScrollParallax, { passive: true });
    updateScrollParallax();

    swiper.on('setTranslate', updateParallax);
    updateParallax();
}
