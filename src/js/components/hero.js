export function initHero() {
    const heroVideo = document.querySelector('.hero-video');
    if (!heroVideo) return;

    const hero = document.querySelector('.hero');
    if (!hero) return;

    const heroContent = document.querySelector('.hero-content');

    let progress = 0;
    let targetProgress = 0;

    function updateHeroVideo() {
        const heroRect = hero.getBoundingClientRect();
        const videoRect = heroVideo.getBoundingClientRect();

        // Calculate progress based on how far video top has moved from its initial position
        // Start: video top is at some positive value
        // End: video top reaches near top of viewport
        const startPoint = window.innerHeight * 0.6;
        const endPoint = 100;

        const currentTop = videoRect.top;

        // Map currentTop from startPoint->endPoint to 0->1
        if (currentTop >= startPoint) {
            targetProgress = 0;
        } else if (currentTop <= endPoint) {
            targetProgress = 1;
        } else {
            targetProgress = 1 - (currentTop - endPoint) / (startPoint - endPoint);
        }

        // Smooth interpolation
        progress += (targetProgress - progress) * 0.15;

        // Apply video styles
        const width = 65 + (progress * 50);
        const borderRadius = 40 - (progress * 30);

        heroVideo.style.width = `${width}%`;
        heroVideo.style.borderRadius = `${borderRadius}px`;

        // Apply text shrink
        if (heroContent) {
            const scale = 1 - (progress * 0.3); // 1 -> 0.7
            const opacity = 1 - (progress * 0.2); // 1 -> 0.8
            heroContent.style.transform = `scale(${scale})`;
            heroContent.style.opacity = opacity;
        }

        requestAnimationFrame(updateHeroVideo);
    }

    requestAnimationFrame(updateHeroVideo);
}
