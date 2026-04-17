<?php $video_section_id = wow_field('video_section_file'); ?>
<section class="video-section" id="video-section">
    <?php if ($video_section_id) : ?>
        <mux-player playback-id="<?php echo esc_attr($video_section_id); ?>" autoplay muted loop stream-type="on-demand" default-hidden-captions playback-rates="" no-hot-keys></mux-player>
    <?php endif; ?>
</section>
