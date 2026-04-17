<?php
$hero_title = get_sub_field('project_hero_title');
$hero_desc = get_sub_field('project_hero_desc');
$categories = get_the_terms(get_the_ID(), 'project_category');
$category = !empty($categories) ? $categories[0] : null;
?>
<section class="wedding-project-single-hero">
    <div class="wedding-project-single-img">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('full'); ?>
        <?php else : ?>
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/wp.jpg" alt="<?php the_title(); ?>">
        <?php endif; ?>
    </div>
    <div class="wedding-project-single-info">
        <h1 class="wedding-project-single-title"><?php echo esc_html($hero_title ?: get_the_title()); ?></h1>

        <?php if ($hero_desc) : ?>
        <div class="wedding-project-single-desc">
            <?php echo esc_html($hero_desc); ?>
            <a href="#full-content" class="wedding-project-single-readmore">
                READ MORE
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                    <path d="M6.01875 16.5L4.6875 15.1688L10.8563 9L4.6875 2.83125L6.01875 1.5L13.5188 9L6.01875 16.5Z" fill="black"/>
                </svg>
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="wedding-projects-breadcrumb single">
    <a href="<?php echo home_url(); ?>">Main</a>
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 10 16" fill="none">
        <path d="M0.707031 0.707092L7.70703 7.70709L0.707031 14.7071" stroke="black" stroke-width="2"/>
    </svg>
    <?php if ($category) : ?>
        <a href="<?php echo esc_url(get_term_link($category)); ?>"><?php echo esc_html($category->name); ?></a>
    <?php else : ?>
        <a href="<?php echo esc_url(get_post_type_archive_link('wedding_project')); ?>">Projects</a>
    <?php endif; ?>
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 10 16" fill="none">
        <path d="M0.707031 0.707092L7.70703 7.70709L0.707031 14.7071" stroke="black" stroke-width="2"/>
    </svg>
    <span><?php the_title(); ?></span>
</section>
