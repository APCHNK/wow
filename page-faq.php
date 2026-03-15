<?php get_header(); ?>
<style>
    .site-logo{
        display: none;
    }
</style>
<main class="faq-page">
    <section class="faq-hero">
        <h1 class="faq-hero-title">FREQUENTLY ASKED QUESTIONS</h1>
        <div class="wedding-projects-breadcrumb">
            <a href="<?php echo home_url(); ?>">MAIN</a>
            <svg xmlns="http://www.w3.org/2000/svg" width="8" height="12" viewBox="0 0 8 12" fill="none">
                <path d="M1.5 1L6.5 6L1.5 11" stroke="black" stroke-width="1.5"/>
            </svg>
            <span>FREQUENTLY ASKED QUESTIONS</span>
        </div>
    </section>

    <section class="faq-list">
        <?php
        $faqs = [
            [
                'question' => 'HOW DO YOU HANDLE SECURITY AND PRIVACY?',
                'answer' => 'Our Audio-Visual Production service offers a complete package for events, ensuring a memorable experience for both guests and hosts. We provide expert sound systems tailored to your venue\'s size and requirements, high-quality lighting setups that set the right mood, and projection services for stunning visual effects.'
            ],
            [
                'question' => 'IS THERE A LIMIT TO THE NUMBER OF MESSAGES I CAN SEND?',
                'answer' => 'Our Audio-Visual Production service offers a complete package for events, ensuring a memorable experience for both guests and hosts. We provide expert sound systems tailored to your venue\'s size and requirements, high-quality lighting setups that set the right mood, and projection services for stunning visual effects. Whether it\'s a corporate event, wedding, concert, or conference, we use cutting-edge technology and professional expertise to deliver seamless execution, enhancing your event\'s atmosphere and ensuring flawless presentation from start to finish. Our Audio-Visual Production service offers a complete package for events, ensuring a memorable experience for both guests and hosts. We provide expert sound systems tailored to your venue\'s size and requirements, high-quality lighting setups that set the right mood, and projection services for stunning visual effects. Whether it\'s a corporate event, wedding, concert, or conference, we use cutting-edge technology and professional expertise to deliver seamless execution, enhancing your event\'s atmosphere and ensuring flawless presentation from start to finish...'
            ],
            [
                'question' => 'CAN I SEND ATTACHMENTS?',
                'answer' => 'Our Audio-Visual Production service offers a complete package for events, ensuring a memorable experience for both guests and hosts. We provide expert sound systems tailored to your venue\'s size and requirements, high-quality lighting setups that set the right mood, and projection services for stunning visual effects.'
            ],
            [
                'question' => 'HOW DOES YOUR PRICING WORK?',
                'answer' => 'Our Audio-Visual Production service offers a complete package for events, ensuring a memorable experience for both guests and hosts. We provide expert sound systems tailored to your venue\'s size and requirements, high-quality lighting setups that set the right mood, and projection services for stunning visual effects.'
            ],
        ];
        ?>

        <?php foreach ($faqs as $faq) : ?>
            <div class="faq-item">
                <button class="faq-question">
                    <span><?php echo $faq['question']; ?></span>
                    <svg class="faq-arrow" width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#clip0_689_157)">
<path d="M24.1904 13.1582L15.8408 21.5078L15.0361 20.7031L21.0205 14.708L21.9951 13.7314L1.57226 13.7314L1.57226 12.5869L21.9951 12.5869L21.0205 11.6094L15.0361 5.61426L15.8408 4.8086L24.1904 13.1582Z" fill="black" stroke="black" stroke-width="1.14488"/>
</g>
<defs>
<clipPath id="clip0_689_157">
<rect width="26" height="26" fill="white"/>
</clipPath>
</defs>
</svg>

                </button>
                <div class="faq-answer">
                    <p><?php echo $faq['answer']; ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<?php get_footer(); ?>
