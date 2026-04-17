<?php
$dream_bg1 = get_sub_field('dream_bg_line1') ?: 'DREAM';
$dream_bg2 = get_sub_field('dream_bg_line2') ?: 'EVENT';
$dream_slides = get_sub_field('dream_slides');
?>
<section class="dream" id="dream">
    <div class="dream-bg">
        <span><?php echo esc_html($dream_bg1); ?></span>
        <span><?php echo esc_html($dream_bg2); ?></span>
    </div>
    <div class="dream-slider">
        <div class="swiper dream-swiper">
            <div class="swiper-wrapper">
                <?php if (!empty($dream_slides)) : ?>
                    <?php foreach ($dream_slides as $slide) : ?>
                <div class="swiper-slide">
                    <div class="dream-slide-content">
                        <h2 class="dream-title"><?php echo esc_html($slide['title']); ?></h2>
                        <p class="dream-desc"><?php echo esc_html($slide['description']); ?></p>
                    </div>
                </div>
                    <?php endforeach; ?>
                <?php else : ?>
                <div class="swiper-slide">
                    <div class="dream-slide-content">
                        <h2 class="dream-title">Turn Your Dream Event Into Reality – Book With Us Today!</h2>
                        <p class="dream-desc">Don't leave your special day to chance—trust the experts with over 19 years of experience in creating unforgettable weddings, private celebrations, corporate events, and star-studded parties. From concept to execution, we handle every detail with creativity, precision, and passion.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="dream-nav">
            <button class="dream-btn dream-prev" aria-label="Previous">
                <svg width="15" height="14" viewBox="0 0 15 14" fill="none">
                    <path d="M5.72439 13.7388L6.73332 12.5281L2.74055 7.72816L15 7.72816L15 6.01084L2.74055 6.01084L6.73331 1.21094L5.72439 0.000228921L-1.00088e-06 6.8695L5.72439 13.7388Z" fill="white"/>
                </svg>
            </button>
            <button class="dream-btn dream-next" aria-label="Next">
                <svg width="15" height="14" viewBox="0 0 15 14" fill="none">
                    <path d="M9.27561 13.7388L8.26668 12.5281L12.2595 7.72816L8.75774e-07 7.72816L1.126e-06 6.01084L12.2595 6.01084L8.26669 1.21094L9.27561 0.000228921L15 6.8695L9.27561 13.7388Z" fill="white"/>
                </svg>
            </button>
        </div>
    </div>
</section>
