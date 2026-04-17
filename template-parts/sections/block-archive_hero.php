<?php
// Sub-fields come either from an outer ACF have_rows() loop (get_sub_field)
// or from the $row array set via set_query_var('wow_current_section', $row)
// when the parent template iterates the flexible array manually.
$row = get_query_var('wow_current_section');
$get = function ($name) use ($row) {
    if (is_array($row) && array_key_exists($name, $row)) return $row[$name];
    if (function_exists('get_sub_field')) return get_sub_field($name);
    return null;
};

$title = $get('archive_hero_title') ?: 'Our [wow_diamond] Wedding Projects';
$subtitle = $get('archive_hero_subtitle') ?: 'Please take a look at our catalog of Weddings projects';
$image_raw = $get('archive_hero_image');
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
