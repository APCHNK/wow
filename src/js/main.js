import '../scss/main.scss';
import Lenis from 'lenis';

// Components
import { initHeader } from './components/header';
import { initHero } from './components/hero';
import { initSpecialise } from './components/specialise';
import { initAbout } from './components/about';
import { initHappen } from './components/happen';
import { initOurProjects } from './components/our-projects';
import { initInstagram } from './components/instagram';
import { initDream } from './components/dream';
import { initWeddingProjects } from './components/wedding-projects';
import { initFaq } from './components/faq';

// Smooth scroll
const lenis = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    orientation: 'vertical',
    smoothWheel: true,
});

function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
}
requestAnimationFrame(raf);

document.addEventListener('DOMContentLoaded', () => {
    initHeader();
    initHero();
    initSpecialise();
    initAbout();
    initHappen();
    initOurProjects();
    initInstagram();
    initDream();
    initWeddingProjects();
    initFaq();
});
