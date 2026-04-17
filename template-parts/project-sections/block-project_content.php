<?php $read_more_text = get_sub_field('project_read_more_text'); ?>
<?php if ($read_more_text) : ?>
<section id="full-content" class="wedding-project-single-content">
    <?php echo wp_kses_post(nl2br(esc_html($read_more_text))); ?>
    <a href="#full-content" class="wedding-project-single-readmore less">
        Less
        <svg style="transform: rotate(-90deg);" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
            <path d="M6.01875 16.5L4.6875 15.1688L10.8563 9L4.6875 2.83125L6.01875 1.5L13.5188 9L6.01875 16.5Z" fill="black"/>
        </svg>
    </a>
</section>
<?php endif; ?>
