export function initHeader() {
    const menuToggle = document.getElementById('menu-toggle');
    const mainNav = document.getElementById('main-nav');
    const body = document.body;
    const html = document.documentElement;

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', () => {
            const isOpen = mainNav.classList.contains('is-open');

            if (isOpen) {
                menuToggle.classList.remove('is-active');
                mainNav.classList.remove('is-open');
                body.classList.remove('menu-open');
                html.classList.remove('menu-open');
                if (window.lenis) window.lenis.start();
            } else {
                menuToggle.classList.add('is-active');
                mainNav.classList.add('is-open');
                body.classList.add('menu-open');
                html.classList.add('menu-open');
                if (window.lenis) window.lenis.stop();
            }
        });

        mainNav.querySelectorAll('.nav-item a').forEach(link => {
            link.addEventListener('click', () => {
                menuToggle.classList.remove('is-active');
                mainNav.classList.remove('is-open');
                body.classList.remove('menu-open');
                html.classList.remove('menu-open');
                if (window.lenis) window.lenis.start();
            });
        });
    }
}
