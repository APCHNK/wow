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
                            }, 300);
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
}
