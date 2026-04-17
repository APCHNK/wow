<?php
$hero_subtitle_top = get_sub_field('hero_subtitle_top') ?: 'WE CREATE';
$hero_title = get_sub_field('hero_title') ?: 'WOW EVENT';
$hero_subtitle_bottom = get_sub_field('hero_subtitle_bottom') ?: 'IN THE WORLD';
$hero_video = get_sub_field('hero_video');
?>
<section class="hero" id="hero">
    <div class="hero-content">
        <span class="hero-subtitle"><?php echo esc_html($hero_subtitle_top); ?></span>
        <h1 class="hero-title"><?php echo esc_html($hero_title); ?></h1>
        <span class="hero-subtitle"><?php echo esc_html($hero_subtitle_bottom); ?></span>
    </div>
    <div class="hero-video">
        <?php if ($hero_video) : ?>
            <mux-player playback-id="<?php echo esc_attr($hero_video); ?>" autoplay muted loop stream-type="on-demand" default-hidden-captions playback-rates="" no-hot-keys preload="auto" prefer-playback="mse" min-resolution="720p" max-resolution="1080p"></mux-player>
        <?php endif; ?>
    </div>
</section>
