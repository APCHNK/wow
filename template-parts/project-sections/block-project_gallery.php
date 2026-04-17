<?php
$gallery_title = get_sub_field('project_gallery_title');
$gallery_desc = get_sub_field('project_gallery_desc');
$gallery_btn_text = get_sub_field('project_gallery_btn_text') ?: 'CONTACT US';
$gallery_btn_link = get_sub_field('project_gallery_btn_link') ?: '#contact';
$gallery = get_sub_field('project_gallery');
?>
<?php if (!empty($gallery)) : ?>
<section class="wedding-project-gallery">
    <div class="wedding-project-gallery-top">
        <h2 class="wedding-project-gallery-title"><?php echo esc_html($gallery_title ?: 'Check out photos from ' . get_the_title()); ?></h2>
    </div>
    <div class="wedding-project-gallery-bottom">
    <div class="wedding-project-gallery-slider">
        <div class="swiper wedding-project-swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide slide-info">
                    <div class="wedding-project-gallery-info">
                        <?php if ($gallery_desc) : ?>
                        <div class="wedding-project-gallery-desc">
                            <?php echo esc_html($gallery_desc); ?>
                        </div>
                        <?php endif; ?>
                        <a href="<?php echo esc_url($gallery_btn_link); ?>" class="footer-btn">
                            <span class="footer-btn-icon">
                                <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M23.041 3.66051C24.5639 1.77659 27.4361 1.77659 28.959 3.66051L30.4696 5.52917C31.4158 6.69968 32.963 7.20242 34.4165 6.81161L36.7369 6.18771C39.0763 5.55872 41.4 7.24698 41.5247 9.66623L41.6484 12.0659C41.7259 13.569 42.6821 14.8852 44.0877 15.4234L46.3317 16.2825C48.594 17.1487 49.4816 19.8804 48.1605 21.9109L46.8501 23.925C46.0293 25.1866 46.0293 26.8134 46.8501 28.075L48.1605 30.0891C49.4816 32.1196 48.594 34.8513 46.3317 35.7175L44.0877 36.5766C42.6821 37.1148 41.7259 38.431 41.6484 39.9341L41.5247 42.3338C41.4 44.753 39.0763 46.4413 36.7369 45.8123L34.4165 45.1884C32.963 44.7976 31.4158 45.3003 30.4696 46.4708L28.959 48.3395C27.4361 50.2234 24.5639 50.2234 23.041 48.3395L21.5304 46.4708C20.5842 45.3003 19.037 44.7976 17.5835 45.1884L15.2631 45.8123C12.9237 46.4413 10.6 44.753 10.4753 42.3338L10.3516 39.9341C10.2741 38.431 9.31787 37.1148 7.91225 36.5766L5.66827 35.7175C3.40596 34.8513 2.51838 32.1196 3.8395 30.0891L5.14992 28.075C5.97075 26.8134 5.97075 25.1866 5.14992 23.925L3.8395 21.9109C2.51838 19.8804 3.40596 17.1487 5.66827 16.2825L7.91225 15.4234C9.31787 14.8852 10.2741 13.569 10.3516 12.0659L10.4753 9.66623C10.6 7.24698 12.9237 5.55872 15.2631 6.18771L17.5835 6.81161C19.037 7.20242 20.5842 6.69968 21.5304 5.52917L23.041 3.66051Z" fill="#D090FF"/>
                                    <path d="M34.1758 26.8691L27.749 19.1572L27.2949 19.7031L32.0156 25.377L32.8789 26.415L16.6338 26.415L16.6338 27.3232L32.8789 27.3232L32.0156 28.3623L27.2949 34.0361L27.749 34.582L34.1758 26.8691Z" fill="black" stroke="black" stroke-width="1.26667"/>
                                </svg>
                            </span>
                            <span><?php echo esc_html($gallery_btn_text); ?></span>
                        </a>
                    </div>
                </div>
                <?php foreach ($gallery as $image) : ?>
                <div class="swiper-slide">
                    <img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" loading="lazy" decoding="async">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="wedding-project-gallery-nav">
            <button class="wedding-project-gallery-prev" aria-label="Previous">
                <svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.72439 13.7388L6.73332 12.5281L2.74055 7.72816L15 7.72816L15 6.01084L2.74055 6.01084L6.73331 1.21094L5.72439 0.000228921L-1.00088e-06 6.8695L5.72439 13.7388Z" fill="black"/>
                </svg>
            </button>
            <button class="wedding-project-gallery-next" aria-label="Next">
                <svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.27561 13.7388L8.26668 12.5281L12.2595 7.72816L8.75774e-07 7.72816L1.126e-06 6.01084L12.2595 6.01084L8.26669 1.21094L9.27561 0.000228921L15 6.8695L9.27561 13.7388Z" fill="black"/>
                </svg>
            </button>
        </div>
        <div class="wedding-project-gallery-mobile-btn">
            <a href="<?php echo esc_url($gallery_btn_link); ?>" class="footer-btn">
                <span class="footer-btn-icon">
                    <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M23.041 3.66051C24.5639 1.77659 27.4361 1.77659 28.959 3.66051L30.4696 5.52917C31.4158 6.69968 32.963 7.20242 34.4165 6.81161L36.7369 6.18771C39.0763 5.55872 41.4 7.24698 41.5247 9.66623L41.6484 12.0659C41.7259 13.569 42.6821 14.8852 44.0877 15.4234L46.3317 16.2825C48.594 17.1487 49.4816 19.8804 48.1605 21.9109L46.8501 23.925C46.0293 25.1866 46.0293 26.8134 46.8501 28.075L48.1605 30.0891C49.4816 32.1196 48.594 34.8513 46.3317 35.7175L44.0877 36.5766C42.6821 37.1148 41.7259 38.431 41.6484 39.9341L41.5247 42.3338C41.4 44.753 39.0763 46.4413 36.7369 45.8123L34.4165 45.1884C32.963 44.7976 31.4158 45.3003 30.4696 46.4708L28.959 48.3395C27.4361 50.2234 24.5639 50.2234 23.041 48.3395L21.5304 46.4708C20.5842 45.3003 19.037 44.7976 17.5835 45.1884L15.2631 45.8123C12.9237 46.4413 10.6 44.753 10.4753 42.3338L10.3516 39.9341C10.2741 38.431 9.31787 37.1148 7.91225 36.5766L5.66827 35.7175C3.40596 34.8513 2.51838 32.1196 3.8395 30.0891L5.14992 28.075C5.97075 26.8134 5.97075 25.1866 5.14992 23.925L3.8395 21.9109C2.51838 19.8804 3.40596 17.1487 5.66827 16.2825L7.91225 15.4234C9.31787 14.8852 10.2741 13.569 10.3516 12.0659L10.4753 9.66623C10.6 7.24698 12.9237 5.55872 15.2631 6.18771L17.5835 6.81161C19.037 7.20242 20.5842 6.69968 21.5304 5.52917L23.041 3.66051Z" fill="#D090FF"/>
                        <path d="M34.1758 26.8691L27.749 19.1572L27.2949 19.7031L32.0156 25.377L32.8789 26.415L16.6338 26.415L16.6338 27.3232L32.8789 27.3232L32.0156 28.3623L27.2949 34.0361L27.749 34.582L34.1758 26.8691Z" fill="black" stroke="black" stroke-width="1.26667"/>
                    </svg>
                </span>
                <span><?php echo esc_html($gallery_btn_text); ?></span>
            </a>
        </div>
    </div>
    </div>

</section>
<?php endif; ?>
