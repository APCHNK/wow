export function initFaq() {
    const faqItems = document.querySelectorAll('.faq-item');
    if (!faqItems.length) return;

    // Open first item by default
    const firstItem = faqItems[0];
    const firstAnswer = firstItem.querySelector('.faq-answer');
    firstItem.classList.add('active');
    firstAnswer.style.maxHeight = firstAnswer.scrollHeight + 'px';

    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');

        question.addEventListener('click', () => {
            const isActive = item.classList.contains('active');

            // Close all
            faqItems.forEach(i => {
                const a = i.querySelector('.faq-answer');
                i.classList.remove('active');
                a.style.maxHeight = null;
            });

            // Open clicked if it wasn't active
            if (!isActive) {
                item.classList.add('active');
                answer.style.maxHeight = answer.scrollHeight + 'px';
            }
        });
    });

    // Animate breadcrumb
    const breadcrumb = document.querySelector('.faq-hero .wedding-projects-breadcrumb');
    if (breadcrumb) {
        const items = breadcrumb.querySelectorAll('a, span, svg');
        items.forEach((item, index) => {
            setTimeout(() => {
                item.classList.add('animate');
            }, index * 150);
        });
    }
}
