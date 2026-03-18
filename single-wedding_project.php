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
        $project_title_top = get_field('project_title_top');
        $project_country = get_field('project_country');
        $hero_title = get_field('project_hero_title');
        $hero_desc = get_field('project_hero_desc');
        $read_more_text = get_field('project_read_more_text');
        $gallery_title = get_field('project_gallery_title');
        $gallery_desc = get_field('project_gallery_desc');
        $gallery_btn_text = get_field('project_gallery_btn_text') ?: 'CONTACT US';
        $gallery_btn_link = get_field('project_gallery_btn_link') ?: '#contact';
        $gallery = get_field('project_gallery');
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

    <?php if (!empty($gallery)) : ?>
    <section class="wedding-project-gallery">
        <div class="wedding-project-gallery-top">
            <h2 class="wedding-project-gallery-title"><?php echo esc_html($gallery_title ?: 'Check out photos from ' . get_the_title()); ?></h2>
        </div>
        <div class="wedding-project-gallery-bottom">
        <div class="wedding-project-gallery-info">
            <?php if ($gallery_desc) : ?>
            <div class="wedding-project-gallery-desc">
                <?php echo esc_html($gallery_desc); ?>
            </div>
            <?php endif; ?>
            <a href="<?php echo esc_url($gallery_btn_link); ?>" class="footer-btn">
                <span class="footer-btn-icon">
                    <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M23.041 3.66051C24.5639 1.77659 27.4361 1.77659 28.959 3.66051L30.4696 5.52917C31.4158 6.69968 32.963 7.20242 34.4165 6.81161L36.7369 6.18771C39.0763 5.55872 41.4 7.24698 41.5247 9.66623L41.6484 12.0659C41.7259 13.569 42.6821 14.8852 44.0877 15.4234L46.3317 16.2825C48.594 17.1487 49.4816 19.8804 48.1605 21.9109L46.8501 23.925C46.0293 25.1866 46.0293 26.8134 46.8501 28.075L48.1605 30.0891C49.4816 32.1196 48.594 34.8513 46.3317 35.7175L44.0877 36.5766C42.6821 37.1148 41.7259 38.431 41.6484 39.9341L41.5247 42.3338C41.4 44.753 39.0763 46.4413 36.7369 45.8123L34.4165 45.1884C32.963 44.7976 31.4158 45.3003 30.4696 46.4708L28.959 48.3395C27.4361 50.2234 24.5639 50.2234 23.041 48.3395L21.5304 46.4708C20.5842 45.3003 19.037 44.7976 17.5835 45.1884L15.2631 45.8123C12.9237 46.4413 10.6 44.753 10.4753 42.3338L10.3516 39.9341C10.2741 38.431 9.31787 37.1148 7.91225 36.5766L5.66827 35.7175C3.40596 34.8513 2.51838 32.1196 3.8395 30.0891L5.14992 28.075C5.97075 26.8134 5.97075 25.1866 5.14992 23.925L3.8395 21.9109C2.51838 19.8804 3.40596 17.1487 5.66827 16.2825L7.91225 15.4234C9.31787 14.8852 10.2741 13.569 10.3516 12.0659L10.4753 9.66623C10.6 7.24698 12.9237 5.55872 15.2631 6.18771L17.5835 6.81161C19.037 7.20242 20.5842 6.69968 21.5304 5.52917L23.041 3.66051Z" fill="#D090FF"/>
                        <path d="M34.1758 26.8691L27.749 19.1572L27.2949 19.7031L32.0156 25.377L32.8789 26.415L16.6338 26.415L16.6338 27.3232L32.8789 27.3232L32.0156 28.3623L27.2949 34.0361L27.749 34.582L34.1758 26.8691Z" fill="black" stroke="black" stroke-width="1.26667"/>
                    </svg>
                </span>
                <span><?php echo esc_html($gallery_btn_text); ?></span>
            </a>
        </div>
        <div class="wedding-project-gallery-slider">
            <div class="swiper wedding-project-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($gallery as $image) : ?>
                    <div class="swiper-slide">
                        <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" loading="lazy" decoding="async">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="wedding-project-gallery-nav">
                <button class="wedding-project-gallery-prev" aria-label="Previous">
                    <svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5.72439 13.7388L6.73332 12.5281L2.74055 7.72816L15 7.72816L15 6.01084L2.74055 6.01084L6.73331 1.21094L5.72439 0.000228921L-1.00088e-06 6.8695L5.72439 13.7388Z" fill="black"/>
                    </svg>
                </button>
                <button class="wedding-project-gallery-next" aria-label="Next">
                    <svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.27561 13.7388L8.26668 12.5281L12.2595 7.72816L8.75774e-07 7.72816L1.126e-06 6.01084L12.2595 6.01084L8.26669 1.21094L9.27561 0.000228921L15 6.8695L9.27561 13.7388Z" fill="black"/>
                    </svg>
                </button>
            </div>
            <div class="wedding-project-gallery-mobile-btn">
                <a href="<?php echo esc_url($gallery_btn_link); ?>" class="footer-btn">
                    <span class="footer-btn-icon">
                        <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M23.041 3.66051C24.5639 1.77659 27.4361 1.77659 28.959 3.66051L30.4696 5.52917C31.4158 6.69968 32.963 7.20242 34.4165 6.81161L36.7369 6.18771C39.0763 5.55872 41.4 7.24698 41.5247 9.66623L41.6484 12.0659C41.7259 13.569 42.6821 14.8852 44.0877 15.4234L46.3317 16.2825C48.594 17.1487 49.4816 19.8804 48.1605 21.9109L46.8501 23.925C46.0293 25.1866 46.0293 26.8134 46.8501 28.075L48.1605 30.0891C49.4816 32.1196 48.594 34.8513 46.3317 35.7175L44.0877 36.5766C42.6821 37.1148 41.7259 38.431 41.6484 39.9341L41.5247 42.3338C41.4 44.753 39.0763 46.4413 36.7369 45.8123L34.4165 45.1884C32.963 44.7976 31.4158 45.3003 30.4696 46.4708L28.959 48.3395C27.4361 50.2234 24.5639 50.2234 23.041 48.3395L21.5304 46.4708C20.5842 45.3003 19.037 44.7976 17.5835 45.1884L15.2631 45.8123C12.9237 46.4413 10.6 44.753 10.4753 42.3338L10.3516 39.9341C10.2741 38.431 9.31787 37.1148 7.91225 36.5766L5.66827 35.7175C3.40596 34.8513 2.51838 32.1196 3.8395 30.0891L5.14992 28.075C5.97075 26.8134 5.97075 25.1866 5.14992 23.925L3.8395 21.9109C2.51838 19.8804 3.40596 17.1487 5.66827 16.2825L7.91225 15.4234C9.31787 14.8852 10.2741 13.569 10.3516 12.0659L10.4753 9.66623C10.6 7.24698 12.9237 5.55872 15.2631 6.18771L17.5835 6.81161C19.037 7.20242 20.5842 6.69968 21.5304 5.52917L23.041 3.66051Z" fill="#D090FF"/>
                            <path d="M34.1758 26.8691L27.749 19.1572L27.2949 19.7031L32.0156 25.377L32.8789 26.415L16.6338 26.415L16.6338 27.3232L32.8789 27.3232L32.0156 28.3623L27.2949 34.0361L27.749 34.582L34.1758 26.8691Z" fill="black" stroke="black" stroke-width="1.26667"/>
                        </svg>
                    </span>
                    <span><?php echo esc_html($gallery_btn_text); ?></span>
                </a>
            </div>
        </div>
        </div>

    </section>
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
    if ($category) {
        $related_args['tax_query'] = [
            [
                'taxonomy' => 'project_category',
                'field' => 'term_id',
                'terms' => $category->term_id,
            ],
        ];
    }
    $related = new WP_Query($related_args);
    if ($related->have_posts()) :
    ?>
    <section class="our-projects">
        <h2 class="our-projects-title">Our Projects</h2>
        <div class="our-projects-slider">
            <div class="swiper our-projects-swiper">
                <div class="swiper-wrapper">
                    <?php while ($related->have_posts()) : $related->the_post(); ?>
                    <div class="swiper-slide">
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="our-projects-card">
                            <?php if (has_post_thumbnail()) : ?>
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
                        <a href="<?php echo esc_url(get_post_type_archive_link('wedding_project')); ?>" class="our-projects-card our-projects-card--last">
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
