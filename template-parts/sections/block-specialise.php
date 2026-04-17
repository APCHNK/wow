<?php
$specialise_title_1 = wow_field('specialise_title_1') ?: 'What';
$specialise_title_2 = wow_field('specialise_title_2') ?: 'We Specialise in';
$specialise_desc = wow_field('specialise_desc') ?: 'From concept to execution — we deliver experiences without compromise.';
?>
<section class="specialise" id="specialise">
    <div class="specialise-header">
        <h2 class="specialise-title">
            <span><?php echo esc_html($specialise_title_1); ?></span>
            <span><?php echo esc_html($specialise_title_2); ?></span>
        </h2>
        <p class="specialise-desc"><?php echo esc_html($specialise_desc); ?></p>
    </div>

    <div class="specialise-slider">
        <div class="swiper specialise-swiper">
            <div class="swiper-wrapper">
                <?php
                $cards = wow_field('specialise_cards');
                if (!empty($cards)) :
                    foreach ($cards as $card) :
                        $link = wow_resolve_link($card['link'] ?? '');
                        if (!$link) continue;
                        $image = (string) ($card['image'] ?? '');
                        $title = (string) ($card['title'] ?? '');
                        $btn = (string) ($card['button_text'] ?? '');
                        if ($btn === '') $btn = 'Show more';
                ?>
                <div class="swiper-slide">
                    <div class="specialise-card">
                        <?php if ($image) : ?>
                            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>">
                        <?php endif; ?>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo esc_html($title); ?></h3>
                            <a href="<?php echo esc_url($link); ?>" class="card-btn"><?php echo esc_html($btn); ?></a>
                        </div>
                    </div>
                </div>
                <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>
        <div class="specialise-nav">
            <button class="specialise-btn specialise-prev" aria-label="Previous">
                <svg width="15" height="14" viewBox="0 0 15 14" fill="none">
                    <path d="M5.72439 13.7388L6.73332 12.5281L2.74055 7.72816L15 7.72816L15 6.01084L2.74055 6.01084L6.73331 1.21094L5.72439 0.000228921L-1.00088e-06 6.8695L5.72439 13.7388Z" fill="black"/>
                </svg>
            </button>
            <button class="specialise-btn specialise-next" aria-label="Next">
                <svg width="15" height="14" viewBox="0 0 15 14" fill="none">
                    <path d="M9.27561 13.7388L8.26668 12.5281L12.2595 7.72816L8.75774e-07 7.72816L1.126e-06 6.01084L12.2595 6.01084L8.26669 1.21094L9.27561 0.000228921L15 6.8695L9.27561 13.7388Z" fill="black"/>
                </svg>
            </button>
        </div>
    </div>
</section>
