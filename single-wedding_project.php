<?php
/**
 * Single template for Wedding Project
 */

get_header();
?>
<style>
    .site-logo{
        display: none;
    }
</style>
<main class="wedding-project-single">
    <?php while (have_posts()) : the_post();
        $catalog_id = (int) get_post_meta(get_the_ID(), 'project_catalog', true);
        $catalog_post = $catalog_id ? get_post($catalog_id) : null;
        $back_url = $catalog_post
            ? get_permalink($catalog_post)
            : get_post_type_archive_link('wedding_project');
    ?>

    <?php if (have_rows('project_sections')) : ?>
        <?php while (have_rows('project_sections')) : the_row(); ?>
            <?php
            $layout = get_row_layout();
            // Project-specific blocks live in /project-sections/, common blocks in /sections/
            $project_layouts = ['project_hero', 'project_content', 'project_gallery'];
            $path = in_array($layout, $project_layouts, true)
                ? 'template-parts/project-sections/block'
                : 'template-parts/sections/block';
            get_template_part($path, $layout);
            ?>
        <?php endwhile; ?>
    <?php endif; ?>

    <!-- Our Projects Section -->
    <?php
    $related_args = [
        'post_type' => 'wedding_project',
        'posts_per_page' => 7,
        'post__not_in' => [get_the_ID()],
        'orderby' => 'date',
        'order' => 'DESC',
    ];
    if ($catalog_id) {
        $related_args['meta_query'] = [[
            'key' => 'project_catalog',
            'value' => $catalog_id,
        ]];
    }
    $related = new WP_Query($related_args);
    if ($related->have_posts()) :
    ?>
    <section class="our-projects">
        <h2 class="our-projects-title">Our Projects</h2>
        <div class="our-projects-slider">
            <div class="swiper our-projects-swiper">
                <div class="swiper-wrapper">
                    <?php while ($related->have_posts()) : $related->the_post();
                        $hero_img = null;
                        if (have_rows('project_sections')) {
                            while (have_rows('project_sections')) { the_row();
                                if (get_row_layout() === 'project_hero') {
                                    $img = get_sub_field('project_hero_image');
                                    if (!empty($img['url'])) {
                                        $hero_img = $img;
                                    }
                                    break;
                                }
                            }
                        }
                    ?>
                    <div class="swiper-slide">
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="our-projects-card">
                            <?php if ($hero_img) : ?>
                                <img src="<?php echo esc_url($hero_img['url']); ?>" alt="<?php echo esc_attr($hero_img['alt'] ?: get_the_title()); ?>" loading="lazy" decoding="async">
                            <?php elseif (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('large'); ?>
                            <?php else : ?>
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/a1.png" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
                            <?php endif; ?>
                            <div class="our-projects-card-content">
                                <h3 class="our-projects-card-title"><?php the_title(); ?></h3>
                            </div>
                        </a>
                    </div>
                    <?php endwhile; ?>
                    <div class="swiper-slide">
                        <a href="<?php echo esc_url($back_url); ?>" class="our-projects-card our-projects-card--last">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/aa.jpg" alt="Show more" loading="lazy" decoding="async">
                            <div class="our-projects-card-hover">
                                <span class="our-projects-card-btn">SHOW MORE</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <div class="our-projects-nav">
                <button class="our-projects-btn our-projects-prev" aria-label="Previous">
                    <svg width="15" height="14" viewBox="0 0 15 14" fill="none">
                        <path d="M5.72439 13.7388L6.73332 12.5281L2.74055 7.72816L15 7.72816L15 6.01084L2.74055 6.01084L6.73331 1.21094L5.72439 0.000228921L-1.00088e-06 6.8695L5.72439 13.7388Z" fill="black"/>
                    </svg>
                </button>
                <button class="our-projects-btn our-projects-next" aria-label="Next">
                    <svg width="15" height="14" viewBox="0 0 15 14" fill="none">
                        <path d="M9.27561 13.7388L8.26668 12.5281L12.2595 7.72816L8.75774e-07 7.72816L1.126e-06 6.01084L12.2595 6.01084L8.26669 1.21094L9.27561 0.000228921L15 6.8695L9.27561 13.7388Z" fill="black"/>
                    </svg>
                </button>
            </div>
        </div>
    </section>
    <?php wp_reset_postdata(); endif; ?>

    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
