<?php
/**
 * Archive template for Wedding Projects.
 *
 * On a project_category term, everything is driven by the `category_sections`
 * Flexible Content field. The template only prints the automatic breadcrumb —
 * hero and catalog-grid are ordinary blocks inside category_sections.
 *
 * On the bare post-type archive (/wedding-projects/) we fall back to a
 * hardcoded hero + catalog_grid so the page still renders.
 */

get_header();

$is_taxonomy = is_tax('project_category');
$term = $is_taxonomy ? get_queried_object() : null;
$parent_term = ($term && $term->parent) ? get_term($term->parent, 'project_category') : null;
?>

<main class="wedding-projects-archive">
    <?php
    $term_ref = $term ? ('project_category_' . $term->term_id) : null;
    $rendered_via_flex = false;

    if ($term_ref && have_rows('category_sections', $term_ref)) :
        $rendered_via_flex = true;
        $hero_done = false;
        while (have_rows('category_sections', $term_ref)) : the_row();
            $layout = get_row_layout();
            get_template_part('template-parts/sections/block', $layout);

            // Drop the breadcrumb right after the first archive_hero block,
            // so the page keeps the old visual order: hero → breadcrumb → catalog.
            if (!$hero_done && $layout === 'archive_hero') :
                $hero_done = true;
                ?>
                <section class="wedding-projects-breadcrumb">
                    <a href="<?php echo home_url(); ?>">Main</a>
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 10 16" fill="none">
                        <path d="M0.707031 0.707092L7.70703 7.70709L0.707031 14.7071" stroke="black" stroke-width="2"/>
                    </svg>
                    <?php if ($parent_term) : ?>
                        <a href="<?php echo esc_url(get_term_link($parent_term)); ?>"><?php echo esc_html($parent_term->name); ?></a>
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 10 16" fill="none">
                            <path d="M0.707031 0.707092L7.70703 7.70709L0.707031 14.7071" stroke="black" stroke-width="2"/>
                        </svg>
                    <?php endif; ?>
                    <span><?php echo esc_html($term->name); ?></span>
                </section>
                <?php
            endif;
        endwhile;
    endif;

    // Fallback: either an empty term (category_sections not set yet) or the
    // bare /wedding-projects/ post-type archive. Render a minimal default.
    if (!$rendered_via_flex) :
        $fallback_title = $term ? ('Our [wow_diamond] ' . $term->name) : 'Our [wow_diamond] Wedding Projects';
        $fallback_subtitle = 'Please take a look at our catalog of Weddings projects';
        $fallback_image = get_template_directory_uri() . '/assets/images/wp.jpg';
    ?>
        <section class="wedding-projects-hero">
            <div class="wedding-projects-hero-title">
                <?php
                $parts = preg_split('/(\[wow_diamond\])/', $fallback_title, -1, PREG_SPLIT_DELIM_CAPTURE);
                foreach ($parts as $part) {
                    if ($part === '[wow_diamond]') echo do_shortcode($part);
                    elseif (trim($part) !== '') echo '<span>' . esc_html(trim($part)) . '</span>';
                }
                ?>
            </div>
            <div class="wedding-projects-hero-subtitle"><?php echo esc_html($fallback_subtitle); ?></div>
            <div class="wedding-projects-hero-img"><img src="<?php echo esc_url($fallback_image); ?>" alt=""></div>
        </section>

        <section class="wedding-projects-breadcrumb">
            <a href="<?php echo home_url(); ?>">Main</a>
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 10 16" fill="none">
                <path d="M0.707031 0.707092L7.70703 7.70709L0.707031 14.7071" stroke="black" stroke-width="2"/>
            </svg>
            <?php if ($term) : ?>
                <span><?php echo esc_html($term->name); ?></span>
            <?php else : ?>
                <span>All Projects</span>
            <?php endif; ?>
        </section>

        <?php get_template_part('template-parts/sections/block', 'catalog_grid'); ?>
    <?php endif; ?>
</main>

<?php
get_footer();
