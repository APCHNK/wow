<?php
$about_title_1 = wow_field('about_title_1') ?: 'Who';
$about_title_2 = wow_field('about_title_2') ?: 'We Are';
$about_marquee_top = wow_field('about_marquee_top') ?: 'We provide a full range of services';
$about_marquee_bottom = wow_field('about_marquee_bottom') ?: 'Weddings Birthdays Bar & Bat Mitzvahs Corporate';
$about_text = wow_field('about_text') ?: '<p>We create captivating and unique realities for those who seek more, who want to bring their dreams to life.<br>I realize the luxury of self-expression.<br>I believe that celebrations are more than just the sum of their parts. It\'s a living organism that I approach comprehensively.<br>Getting to know my clients, I sense their energy, surround them with care, and give them all my attention.<br>As a result, I know how to give each celebration a unique individuality that reflects the personality.<br>This enables me to conduct unique, vibrant events at the highest level, constantly innovating and leveraging broad know-how acquired over more than 15 years!<br>I invite you to the world of celebrations!</p>';
$about_gallery = wow_field('about_gallery');
$separator = '<span class="marquee-separator"><i></i><i></i><i></i><i></i><i></i></span>';
?>
<section class="about" id="about">
    <h2 class="about-title">
        <span><?php echo esc_html($about_title_1); ?></span>
        <span><?php echo esc_html($about_title_2); ?></span>
    </h2>

    <div class="about-marquees">
        <div class="about-marquee about-marquee--top">
            <div class="marquee-track marquee-track--reverse">
                <div class="marquee-content">
                    <?php for ($i = 0; $i < 4; $i++) : ?>
                    <span class="marquee-text"><?php echo esc_html($about_marquee_top); ?><?php echo $separator; ?></span>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <div class="about-marquee about-marquee--bottom">
            <div class="marquee-track">
                <div class="marquee-content">
                    <?php for ($i = 0; $i < 5; $i++) : ?>
                    <span class="marquee-text"><?php echo esc_html($about_marquee_bottom); ?><?php echo $separator; ?></span>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="about-slider">
        <?php if ($about_text) : ?>
        <div class="about-text-mobile">
            <?php echo $about_text; ?>
        </div>
        <?php endif; ?>
        <div class="swiper about-swiper">
            <div class="swiper-wrapper">
                <?php if ($about_text) : ?>
                <div class="swiper-slide slide-text">
                    <div class="about-text">
                        <?php echo $about_text; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($about_gallery)) : ?>
                    <?php foreach ($about_gallery as $image) : ?>
                <div class="swiper-slide slide-image">
                    <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" loading="lazy" decoding="async">
                </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php
                    $fallback_images = ['a1.png', 'a2.png', 'a3.png', 'a1.png', 'a2.png', 'a3.png', 'a1.png', 'a2.png', 'a3.png'];
                    foreach ($fallback_images as $img) : ?>
                <div class="swiper-slide slide-image">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/<?php echo $img; ?>" alt="About" loading="lazy" decoding="async">
                </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="slider-nav">
            <button class="slider-btn slider-prev" aria-label="Previous">
                <svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.72439 13.7388L6.73332 12.5281L2.74055 7.72816L15 7.72816L15 6.01084L2.74055 6.01084L6.73331 1.21094L5.72439 0.000228921L-1.00088e-06 6.8695L5.72439 13.7388Z" fill="black" />
                </svg>
            </button>
            <button class="slider-btn slider-next" aria-label="Next">
                <svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.27561 13.7388L8.26668 12.5281L12.2595 7.72816L8.75774e-07 7.72816L1.126e-06 6.01084L12.2595 6.01084L8.26669 1.21094L9.27561 0.000228921L15 6.8695L9.27561 13.7388Z" fill="black" />
                </svg>
            </button>
        </div>
    </div>
</section>
