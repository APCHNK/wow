<?php get_header(); ?>

<main class="site-main">
    <?php if (have_rows('page_sections')) : ?>
        <?php while (have_rows('page_sections')) : the_row(); ?>
            <?php get_template_part('template-parts/sections/block', get_row_layout()); ?>
        <?php endwhile; ?>
    <?php else : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article class="page-content">
                <h1><?php the_title(); ?></h1>
                <?php the_content(); ?>
            </article>
        <?php endwhile; ?>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
