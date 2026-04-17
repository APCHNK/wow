<?php
$faq_title = get_sub_field('faq_title') ?: 'FREQUENTLY ASKED QUESTIONS';
$faq_items = get_sub_field('faq_items');
?>
<section class="faq-hero">
    <h1 class="faq-hero-title"><?php echo esc_html($faq_title); ?></h1>
    <div class="wedding-projects-breadcrumb">
        <a href="<?php echo home_url(); ?>">MAIN</a>
        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="12" viewBox="0 0 8 12" fill="none">
            <path d="M1.5 1L6.5 6L1.5 11" stroke="black" stroke-width="1.5"/>
        </svg>
        <span><?php echo esc_html($faq_title); ?></span>
    </div>
</section>

<section class="faq-list">
    <?php if (!empty($faq_items)) : ?>
        <?php foreach ($faq_items as $faq) : ?>
        <div class="faq-item">
            <button class="faq-question">
                <span><?php echo esc_html($faq['question']); ?></span>
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
                <?php echo $faq['answer']; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
