import Swiper from 'swiper';
import { Navigation, Mousewheel } from 'swiper/modules';
import 'swiper/css';

export function initWeddingProjects() {
    // Animate hero title spans, svg, hero image and subtitle
    const heroTitle = document.querySelector('.wedding-projects-hero-title');
    const heroImg = document.querySelector('.wedding-projects-hero-img');
    const heroSubtitle = document.querySelector('.wedding-projects-hero-subtitle');

    if (heroTitle) {
        const spans = heroTitle.querySelectorAll('span');
        const svg = heroTitle.querySelector('svg');

        const heroObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (heroImg) {
                        heroImg.classList.add('animate');
                    }

                    setTimeout(() => {
                        spans.forEach(span => {
                            span.classList.add('animate');
                        });

                        if (svg) {
                            setTimeout(() => {
                                svg.classList.add('animate');
                            }, 100);
                        }

                        if (heroSubtitle) {
                            setTimeout(() => {
                                heroSubtitle.classList.add('animate');
                            }, 600);
                        }
                    }, 800);

                    heroObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        heroObserver.observe(heroTitle);
    }

    // Animate breadcrumb sequentially
    const breadcrumb = document.querySelector('.wedding-projects-breadcrumb');

    if (breadcrumb) {
        const breadcrumbItems = breadcrumb.querySelectorAll('a, span, svg');

        const breadcrumbObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    breadcrumbItems.forEach((item, index) => {
                        setTimeout(() => {
                            item.classList.add('animate');
                        }, index * 150);
                    });

                    breadcrumbObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        breadcrumbObserver.observe(breadcrumb);
    }

    // Animate project titles and borders on scroll
    const projects = document.querySelectorAll('.project');

    if (projects.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const title = entry.target.querySelector('.project-title');
                    const border = entry.target.querySelector('.happen-title-border');
                    const desc = entry.target.querySelector('.project-desc');
                    const link = entry.target.querySelector('.project-link');

                    if (title) {
                        title.classList.add('animate');

                        if (border) {
                            setTimeout(() => {
                                border.classList.add('animate');
                            }, 500);
                        }
                    }

                    if (desc) {
                        desc.classList.add('animate');
                    }

                    if (link) {
                        link.classList.add('animate');
                    }

                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });

        projects.forEach(project => {
            observer.observe(project);
        });
    }

    // Read More button - reveal content
    const readMoreBtn = document.querySelector('.wedding-project-single-readmore');
    const singleContent = document.querySelector('.wedding-project-single-content');

    if (readMoreBtn && singleContent) {
        const readLessBtn = singleContent.querySelector('.wedding-project-single-readmore.less');

        readMoreBtn.addEventListener('click', (e) => {
            e.preventDefault();
            singleContent.classList.add('active');
            readMoreBtn.style.display = 'none';

            setTimeout(() => {
                singleContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        });

        if (readLessBtn) {
            readLessBtn.addEventListener('click', (e) => {
                e.preventDefault();
                singleContent.classList.remove('active');
                readMoreBtn.style.display = '';

                setTimeout(() => {
                    readMoreBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 100);
            });
        }
    }

    // Gallery slider with Swiper
    initGallerySwiper();

    // Project description text sliders
    initDescSwipers();

    // Single page animations
    initSingleAnimations();
}

function initDescSwipers() {
    const descSwipers = document.querySelectorAll('.project-desc-swiper');
    if (!descSwipers.length) return;

    descSwipers.forEach(el => {
        new Swiper(el, {
            slidesPerView: 1,
            loop: true,
            speed: 800,
        });
    });
}

function initSingleAnimations() {
    // Hero section: image (left), title (right), desc (bottom)
    const heroSection = document.querySelector('.wedding-project-single-hero');
    if (heroSection) {
        const img = document.querySelector('.wedding-project-single-img');
        const title = document.querySelector('.wedding-project-single-title');
        const desc = document.querySelector('.wedding-project-single-desc');

        const heroObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (img) img.classList.add('animate');
                    setTimeout(() => {
                        if (title) title.classList.add('animate');
                    }, 300);
                    setTimeout(() => {
                        if (desc) desc.classList.add('animate');
                    }, 600);
                    heroObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        heroObserver.observe(heroSection);
    }

    // Gallery section: title (left), desc (fade), footer-btn (bottom)
    const gallerySection = document.querySelector('.wedding-project-gallery');
    if (gallerySection) {
        const galleryTitle = document.querySelector('.wedding-project-gallery-title');
        const galleryDesc = document.querySelector('.wedding-project-gallery-desc');
        const footerBtn = gallerySection.querySelector('.wedding-project-gallery-info .footer-btn');

        const galleryObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (galleryTitle) galleryTitle.classList.add('animate');
                    setTimeout(() => {
                        if (galleryDesc) galleryDesc.classList.add('animate');
                    }, 300);
                    setTimeout(() => {
                        if (footerBtn) footerBtn.classList.add('animate');
                    }, 600);
                    galleryObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        galleryObserver.observe(gallerySection);
    }

    // Gallery slides: bottom to top, staggered
    const gallerySlider = document.querySelector('.wedding-project-gallery-slider');
    if (gallerySlider) {
        const slides = gallerySlider.querySelectorAll('.swiper-slide');

        const slidesObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    slides.forEach((slide, index) => {
                        setTimeout(() => {
                            slide.classList.add('animate');
                        }, 100 * (index + 1));
                    });
                    slidesObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        slidesObserver.observe(gallerySlider);
    }

    // Our Projects section: title (left) + cards (bottom, staggered)
    const ourProjectsSection = document.querySelector('.our-projects');
    if (ourProjectsSection) {
        const opTitle = ourProjectsSection.querySelector('.our-projects-title');
        const opCards = ourProjectsSection.querySelectorAll('.our-projects-card');

        const opObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (opTitle) opTitle.classList.add('animate');
                    opCards.forEach((card, index) => {
                        setTimeout(() => {
                            card.classList.add('animate');
                        }, 100 * (index + 1));
                    });
                    opObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        opObserver.observe(ourProjectsSection);
    }
}

function initGallerySwiper() {
    const swiperEl = document.querySelector('.wedding-project-swiper');
    if (!swiperEl) return;

    new Swiper('.wedding-project-swiper', {
        modules: [Navigation, Mousewheel],
        slidesPerView: 'auto',
        spaceBetween: 20,
        navigation: {
            nextEl: '.wedding-project-gallery-next',
            prevEl: '.wedding-project-gallery-prev',
        },
        mousewheel: {
            forceToAxis: true,
        },
    });
}
