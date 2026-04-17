<?php
/**
 * Single template for project_catalog posts — the former
 * archive-wedding_project.php taxonomy view, now driven by a CPT post.
 *
 * Every visual section (hero, catalog grid, FAQ, …) lives inside the post's
 * category_sections Flexible Content. The only non-block element here is the
 * breadcrumb, which we drop in automatically right after the first
 * archive_hero block so the visual order (hero → breadcrumb → grid) is kept.
 */

get_header();

while (have_posts()) : the_post();
    $current = get_post();
    $parent  = $current->post_parent ? get_post($current->post_parent) : null;
?>
<main class="wedding-projects-archive">
    <?php if (have_rows('category_sections')) :
        $hero_done = false;
        while (have_rows('category_sections')) : the_row();
            $layout = get_row_layout();
            get_template_part('template-parts/sections/block', $layout);

            if (!$hero_done && $layout === 'archive_hero') :
                $hero_done = true;
                ?>
                <section class="wedding-projects-breadcrumb">
                    <a href="<?php echo home_url(); ?>">Main</a>
                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 10 16" fill="none">
                        <path d="M0.707031 0.707092L7.70703 7.70709L0.707031 14.7071" stroke="black" stroke-width="2"/>
                    </svg>
                    <?php if ($parent) : ?>
                        <a href="<?php echo esc_url(get_permalink($parent)); ?>"><?php echo esc_html($parent->post_title); ?></a>
                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="16" viewBox="0 0 10 16" fill="none">
                            <path d="M0.707031 0.707092L7.70703 7.70709L0.707031 14.7071" stroke="black" stroke-width="2"/>
                        </svg>
                    <?php endif; ?>
                    <span><?php echo esc_html($current->post_title); ?></span>
                </section>
                <?php
            endif;
        endwhile;
    endif; ?>
</main>
<?php
endwhile;

get_footer();
