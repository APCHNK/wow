<?php
$title = wow_field('archive_hero_title', 'Our [wow_diamond] Wedding Projects');
$subtitle = wow_field('archive_hero_subtitle', 'Please take a look at our catalog of Weddings projects');
$image_raw = wow_field('archive_hero_image');
if (is_array($image_raw)) {
    $image = (string) ($image_raw['url'] ?? '');
} elseif (is_numeric($image_raw)) {
    $image = (string) wp_get_attachment_image_url((int) $image_raw, 'full');
} else {
    $image = (string) $image_raw;
}
if (!$image) $image = get_template_directory_uri() . '/assets/images/wp.jpg';
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
