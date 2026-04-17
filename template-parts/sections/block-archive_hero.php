<?php
$title = get_sub_field('archive_hero_title') ?: 'Our [wow_diamond] Wedding Projects';
$subtitle = get_sub_field('archive_hero_subtitle') ?: 'Please take a look at our catalog of Weddings projects';
$image = get_sub_field('archive_hero_image') ?: (get_template_directory_uri() . '/assets/images/wp.jpg');
?>
<section class="wedding-projects-hero">
    <div class="wedding-projects-hero-title">
        <?php
        $parts = preg_split('/(\[wow_diamond\])/', $title, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $part) {
            if ($part === '[wow_diamond]') {
                echo do_shortcode($part);
            } elseif (trim($part) !== '') {
                echo '<span>' . esc_html(trim($part)) . '</span>';
            }
        }
        ?>
    </div>
    <div class="wedding-projects-hero-subtitle">
        <?php echo esc_html($subtitle); ?>
    </div>
    <div class="wedding-projects-hero-img">
        <img src="<?php echo esc_url($image); ?>" alt="">
    </div>
</section>
