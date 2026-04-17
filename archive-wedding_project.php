<?php
/**
 * Post-type archive /wedding-projects/.
 *
 * Taxonomy archives are no longer served from this template — every catalog
 * page lives on single-project_catalog.php. This file renders the bare
 * "all projects" listing with a hardcoded hero + catalog_grid block.
 */

get_header();
?>

<main class="wedding-projects-archive">
    <?php
    $fallback_title = 'Our [wow_diamond] Wedding Projects';
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
        <span>All Projects</span>
    </section>

    <?php get_template_part('template-parts/sections/block', 'catalog_grid'); ?>
</main>

<?php
get_footer();
