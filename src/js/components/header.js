export function initHeader() {
    const menuToggle = document.getElementById('menu-toggle');
    const mainNav = document.getElementById('main-nav');
    const body = document.body;

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', () => {
            const isOpen = mainNav.classList.contains('is-open');

            if (isOpen) {
                menuToggle.classList.remove('is-active');
                mainNav.classList.remove('is-open');
                body.classList.remove('menu-open');
                body.style.overflow = '';
            } else {
                menuToggle.classList.add('is-active');
                mainNav.classList.add('is-open');
                body.classList.add('menu-open');
                body.style.overflow = 'hidden';
            }
        });

        mainNav.querySelectorAll('.nav-item a').forEach(link => {
            link.addEventListener('click', () => {
                menuToggle.classList.remove('is-active');
                mainNav.classList.remove('is-open');
                body.classList.remove('menu-open');
                body.style.overflow = '';
            });
        });
    }
}
