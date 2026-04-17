<?php
$instagram_title = wow_field('instagram_title') ?: 'Follow us on Instagram';
$instagram_desc = wow_field('instagram_desc') ?: 'One of the key advantages of working with us is our direct partnership with an international artist booking agency.';
$instagram_link = wow_field('instagram_link') ?: '#';
?>
<section class="instagram" id="instagram">
    <div class="instagram-header">
        <h2 class="instagram-title"><?php echo esc_html($instagram_title); ?></h2>
        <p class="instagram-desc"><?php echo esc_html($instagram_desc); ?></p>
        <a href="<?php echo esc_url($instagram_link); ?>" class="instagram-btn" target="_blank" rel="noopener">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
<rect width="40" height="40" rx="12" fill="#D090FF"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M15.125 7H24.875C29.3616 7 33 10.6384 33 15.125V24.875C33 29.3616 29.3616 33 24.875 33H15.125C10.6384 33 7 29.3616 7 24.875V15.125C7 10.6384 10.6384 7 15.125 7ZM24.875 30.5625C28.0112 30.5625 30.5625 28.0113 30.5625 24.875V15.125C30.5625 11.9887 28.0112 9.4375 24.875 9.4375H15.125C11.9887 9.4375 9.4375 11.9887 9.4375 15.125V24.875C9.4375 28.0113 11.9887 30.5625 15.125 30.5625H24.875Z" fill="black"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M13.5 20C13.5 16.4104 16.4104 13.5 20 13.5C23.5896 13.5 26.5 16.4104 26.5 20C26.5 23.5896 23.5896 26.5 20 26.5C16.4104 26.5 13.5 23.5896 13.5 20ZM15.9375 20C15.9375 22.2393 17.7607 24.0625 20 24.0625C22.2393 24.0625 24.0625 22.2393 24.0625 20C24.0625 17.7591 22.2393 15.9375 20 15.9375C17.7607 15.9375 15.9375 17.7591 15.9375 20Z" fill="black"/>
<path d="M26.9872 13.8787C27.4656 13.8787 27.8533 13.491 27.8533 13.0126C27.8533 12.5343 27.4656 12.1465 26.9872 12.1465C26.5089 12.1465 26.1211 12.5343 26.1211 13.0126C26.1211 13.491 26.5089 13.8787 26.9872 13.8787Z" fill="black"/>
</svg>

            <span>INSTAGRAM</span>
        </a>
    </div>
    <div class="instagram-slider">
        <div class="swiper instagram-swiper">
            <div class="swiper-wrapper">
                <?php
                $instagram_photos = wow_get_instagram_photos(12);
                if (!empty($instagram_photos)) :
                    foreach ($instagram_photos as $photo) :
                ?>
                <div class="swiper-slide">
                    <a href="<?php echo esc_url($photo['link']); ?>" class="instagram-card" target="_blank" rel="noopener">
                        <img src="<?php echo esc_url($photo['url']); ?>" alt="Instagram" loading="lazy">
                    </a>
                </div>
                <?php
                    endforeach;
                else :
                    for ($i = 1; $i <= 6; $i++) :
                ?>
                <div class="swiper-slide">
                    <a href="#" class="instagram-card">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/i<?php echo $i; ?>.jpg" alt="Instagram" loading="lazy" decoding="async">
                    </a>
                </div>
                <?php
                    endfor;
                endif;
                ?>
            </div>
        </div>
    </div>
</section>
