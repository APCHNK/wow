<?php
/**
 * Single template for project_catalog posts — the former taxonomy archive view,
 * now driven by a CPT post. Sections come from the post's category_sections
 * Flexible Content; the breadcrumb is inserted automatically right after the
 * first archive_hero block.
 *
 * We iterate the flexible array manually and push each row through ACF via
 * have_rows(/the_row() with the specific section index, because ACF's
 * implicit state can loop forever when a catalog_grid row happens to carry
 * no saved sub-field values (seen on catalogs without cards).
 */

get_header();

while (have_posts()) : the_post();
    $current = get_post();
    $parent  = $current->post_parent ? get_post($current->post_parent) : null;

    $slug_label = static function ($post): string {
        return ucwords(str_replace('-', ' ', $post->post_name));
    };

    $sections = get_field('category_sections');
    if (!is_array($sections)) $sections = [];
?>
<main class="wedding-projects-archive">
    <?php
    $hero_done = false;
    foreach ($sections as $index => $row) :
        if (!is_array($row)) continue;
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === '') continue;

        // Expose the current row to the block via a query var so block files
        // can pull fields from $row directly if they want; have_rows is
        // avoided here on purpose.
        set_query_var('wow_current_section', $row);
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
                    <span><?php echo esc_html($slug_label($current)); ?></span>
                <?php else : ?>
                    <span><?php echo esc_html($current->post_title); ?></span>
                <?php endif; ?>
            </section>
            <?php
        endif;
    endforeach;
    ?>
</main>
<?php
endwhile;

get_footer();
