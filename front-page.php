<?php get_header(); ?>

<main class="site-main">
    <?php
        $hero_subtitle_top = get_field('hero_subtitle_top') ?: 'WE CREATE';
        $hero_title = get_field('hero_title') ?: 'WOW EVENT';
        $hero_subtitle_bottom = get_field('hero_subtitle_bottom') ?: 'IN THE WORLD';
        $hero_video = get_field('hero_video');
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

    <?php
        $specialise_title_1 = get_field('specialise_title_1') ?: 'What';
        $specialise_title_2 = get_field('specialise_title_2') ?: 'We Specialise in';
        $specialise_desc = get_field('specialise_desc') ?: 'From concept to execution — we deliver experiences without compromise.';
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
                    $selected_cat_ids = get_field('specialise_categories');
                    if (!empty($selected_cat_ids)) {
                        // Use manually selected & ordered categories
                        $categories = [];
                        foreach ($selected_cat_ids as $cat_id) {
                            $term = get_term($cat_id, 'project_category');
                            if ($term && !is_wp_error($term)) {
                                $categories[] = $term;
                            }
                        }
                    } else {
                        // Fallback: all top-level categories
                        $categories = get_terms([
                            'taxonomy' => 'project_category',
                            'hide_empty' => false,
                            'parent' => 0,
                        ]);
                    }

                    if (!empty($categories) && !is_wp_error($categories)) :
                        foreach ($categories as $cat) :
                            $cat_image = get_field('category_image', 'project_category_' . $cat->term_id);
                            $cat_link = get_term_link($cat);
                    ?>
                    <div class="swiper-slide">
                        <div class="specialise-card">
                            <?php if ($cat_image) : ?>
                                <img src="<?php echo esc_url($cat_image); ?>" alt="<?php echo esc_attr($cat->name); ?>">
                            <?php endif; ?>
                            <div class="card-content">
                                <h3 class="card-title"><?php echo esc_html($cat->name); ?></h3>
                                <a href="<?php echo esc_url($cat_link); ?>" class="card-btn">SHOW MORE</a>
                            </div>
                        </div>
                    </div>
                    <?php
                        endforeach;
                    else :
                    ?>
                    <!-- Fallback if no categories -->
                    <div class="swiper-slide">
                        <div class="specialise-card">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/w1.jpg" alt="Weddings">
                            <div class="card-content">
                                <h3 class="card-title">WEDDINGS & LOVE STORIES</h3>
                                <a href="#" class="card-btn">SHOW MORE</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
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

    <?php
        $about_title_1 = get_field('about_title_1') ?: 'Who';
        $about_title_2 = get_field('about_title_2') ?: 'We Are';
        $about_marquee_top = get_field('about_marquee_top') ?: 'We provide a full range of services';
        $about_marquee_bottom = get_field('about_marquee_bottom') ?: 'Weddings Birthdays Bar & Bat Mitzvahs Corporate';
        $about_text = get_field('about_text') ?: '<p>We create captivating and unique realities for those who seek more, who want to bring their dreams to life.<br>I realize the luxury of self-expression.<br>I believe that celebrations are more than just the sum of their parts. It\'s a living organism that I approach comprehensively.<br>Getting to know my clients, I sense their energy, surround them with care, and give them all my attention.<br>As a result, I know how to give each celebration a unique individuality that reflects the personality.<br>This enables me to conduct unique, vibrant events at the highest level, constantly innovating and leveraging broad know-how acquired over more than 15 years!<br>I invite you to the world of celebrations!</p>';
        $about_gallery = get_field('about_gallery');
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

    <?php $video_section_id = get_field('video_section_file'); ?>
    <section class="video-section" id="video-section">
        <?php if ($video_section_id) : ?>
            <mux-player playback-id="<?php echo esc_attr($video_section_id); ?>" autoplay muted loop stream-type="on-demand" default-hidden-captions playback-rates="" no-hot-keys></mux-player>
        <?php endif; ?>
    </section>

    <?php
        $happen_title = get_field('happen_title') ?: 'Make it Happen!';
        $happen_desc = get_field('happen_desc') ?: 'One of the key advantages of working with us is our direct partnership with an international artist booking agency. This means we can provide our clients with exclusive access to world-renowned artists, celebrities, musicians, and performers for weddings, private parties, corporate events, and special occasions.';
        $happen_slides = get_field('happen_slides');
    ?>
    <section class="happen" id="happen">
        <div class="happen-header">
            <h2 class="happen-title">
                <span class="happen-title-text"><?php echo esc_html($happen_title); ?></span>
                <svg class="happen-title-border" width="422" height="201" viewBox="0 0 422 201" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M344.745 138.752C349.881 135.83 354.601 133.128 360.442 129.457C360.445 129.455 360.448 129.453 360.451 129.451C361.291 128.921 362.149 128.373 363.024 127.805C370.053 123.135 379.281 117.12 390.874 105.591C403.612 92.881 410.122 80.3432 412.036 72.4347C412.394 70.9665 412.84 69.4093 413.014 65.4985C413.069 63.5264 413.017 61.0435 412.377 57.8018C412.377 57.8018 412.377 57.8018 412.377 57.8018C412.176 56.7993 411.916 55.7344 411.571 54.6044C410.795 52.0624 409.599 49.1801 407.636 46.0334C401.566 35.2543 383.712 23.7914 365.494 18.0883C354.465 14.4103 343.572 12.1698 335.273 10.7695C323.088 8.729 311.214 7.50935 298.212 6.87552C286.304 6.31398 273.47 6.26814 258.641 7.07669C247.273 7.70343 236.159 8.49377 224.971 9.66321C223.532 9.81425 222.1 9.9707 220.674 10.1326C211.108 11.224 201.417 12.6113 190.984 14.5779C190.602 14.6501 190.219 14.7231 189.835 14.7967C170.514 18.5477 155.37 22.711 138.847 28.041C132.359 30.1527 125.713 32.4597 118.557 35.1445C107.227 39.379 91.1428 45.9921 75.6596 54.1052C74.3344 54.7972 73.0216 55.4948 71.7265 56.1946C71.72 56.1982 71.7134 56.2017 71.7069 56.2053C57.6081 63.7795 44.6041 72.4807 35.6272 79.7767C21.5418 91.3416 17.3944 97.1256 13.8368 101.5C12.4934 103.246 11.3449 104.83 10.0305 106.808C5.34202 113.837 -1.01959 125.936 0.195181 139.286C0.199481 139.336 0.203889 139.386 0.208406 139.436C0.399742 141.607 0.769022 143.342 0.764365 143.332C0.766812 143.321 0.384577 141.572 0.179101 139.401C-0.508276 132.439 0.867865 125.627 2.99619 119.942C3.07096 119.64 3.59892 118.118 4.41441 116.302C6.32995 111.847 13.3719 98.0384 31.3242 82.445C30.0766 83.5045 29.0471 84.4075 28.1658 85.1937C27.1256 86.1228 26.3748 86.8127 25.6877 87.4539C24.2703 88.783 23.3488 89.6712 21.7961 91.2668C18.1414 95.059 12.6621 101.241 8.19929 108.683C7.08753 110.527 6.21714 112.101 6.17224 112.063C6.12779 112.03 6.91725 110.39 8.0215 108.532C8.04331 108.495 8.06513 108.458 8.087 108.421C12.4835 101.005 18.1141 94.5616 21.9318 90.6004C22.886 89.6074 23.7377 88.7554 24.3865 88.1173C24.9022 87.6092 25.5718 86.9641 25.9302 86.6283C25.9777 86.5837 26.0685 86.4987 26.1775 86.3978C26.1793 86.3961 26.1812 86.3944 26.183 86.3928C26.2439 86.3363 26.5552 86.0482 26.897 85.7476C26.966 85.6869 27.1163 85.5551 27.2941 85.4047C27.3384 85.3644 27.3829 85.324 27.4274 85.2836C28.0475 84.7207 28.6891 84.1476 29.3942 83.5281C32.1677 81.1444 36.4192 77.2949 46.2078 70.3717C55.2065 64.0156 71.0478 54.0415 92.0875 44.294C90.6475 44.9457 89.2175 45.6052 87.8024 46.2702C84.2896 47.9198 80.6169 49.7291 77.1532 51.5196C75.7829 52.2278 74.4454 52.9317 73.0977 53.6542C70.9331 54.8148 68.7871 55.9993 66.6184 57.2345C65.1635 58.0632 63.7253 58.8997 62.3091 59.7406C62.3059 59.7426 62.3027 59.7445 62.2995 59.7464C60.2825 60.9444 58.2873 62.1649 56.3269 63.4014C55.6414 63.8334 54.9663 64.2636 54.2759 64.7085C51.5366 66.4769 49.015 68.1642 45.99 70.2868C45.6274 70.5399 45.235 70.8144 44.9432 71.0191C44.6294 71.2392 44.3287 71.4511 44.0454 71.6513C43.7954 71.8279 43.5577 71.9962 43.3307 72.1579C43.3003 72.1796 43.2699 72.2013 43.2394 72.223C42.9856 72.4039 42.744 72.5774 42.5126 72.7443C42.2839 72.9091 42.0544 73.0756 41.829 73.2405C41.8269 73.242 41.8249 73.2435 41.8228 73.2451C41.5878 73.4172 41.4099 73.548 41.1629 73.7314C40.8529 73.9616 40.5878 74.1601 40.2688 74.4014C39.9602 74.6349 39.6402 74.879 39.3191 75.1262C38.9981 75.3734 38.6731 75.6257 38.3452 75.8823C38.0171 76.139 37.6856 76.4004 37.3535 76.6644C37.0218 76.9281 36.6877 77.1959 36.3538 77.4654C36.0097 77.7431 35.6596 78.0277 35.3125 78.3123C34.512 78.9681 33.7438 79.5812 33.1702 80.0145C32.5967 80.4476 32.2615 80.6659 32.2275 80.619C32.1943 80.572 32.4649 80.2623 32.9888 79.7567C33.5129 79.251 34.2507 78.5906 35.047 77.9243C35.2637 77.7428 35.4824 77.5606 35.7029 77.3778C35.8328 77.2701 35.9695 77.1574 36.0957 77.0532C36.4382 76.7707 36.7864 76.4853 37.1391 76.1989C37.4916 75.9126 37.8497 75.6243 38.2142 75.3331C38.5797 75.0412 38.9526 74.7459 39.3336 74.4471C39.7136 74.149 40.1048 73.845 40.5051 73.5373C40.8957 73.2369 41.3446 72.8955 41.7495 72.5913C42.0446 72.3694 42.3921 72.1109 42.6906 71.8905C43.0005 71.6618 43.3073 71.4373 43.6045 71.2214C43.9023 71.0051 44.1919 70.7964 44.4694 70.5975C44.4991 70.5762 44.5287 70.5549 44.5584 70.5336C44.804 70.3577 45.042 70.1882 45.2675 70.0279C45.5195 69.8487 45.7589 69.679 45.9867 69.5177C46.2378 69.34 46.3905 69.2317 46.5781 69.0987C49.0752 67.3166 52.0637 65.2567 55.0866 63.2802C55.4547 63.0392 55.8225 62.7998 56.1877 62.5633C58.1239 61.3089 60.1198 60.054 62.1133 58.8355C63.5367 57.9649 64.9824 57.0985 66.4502 56.2368C68.6311 54.9569 70.7703 53.7377 72.9573 52.5262C74.3122 51.7757 75.6752 51.0347 77.0396 50.3054C80.5456 48.4294 84.2521 46.5389 87.8455 44.7865C91.4999 43.0044 95.1832 41.2893 98.922 39.6287C102.692 37.9547 106.49 36.3484 110.335 34.8078C113.091 33.7033 115.962 32.6131 118.654 31.6299C120.109 31.0981 121.483 30.6069 122.876 30.1172C122.881 30.1155 122.885 30.1139 122.89 30.1123C124.107 29.6844 125.316 29.2663 126.521 28.8542C129.126 27.9635 131.615 27.1363 134.161 26.3072C136.697 25.4815 139.249 24.6693 141.779 23.8805C144.299 23.0945 146.907 22.2984 149.461 21.5354C152.017 20.7722 154.544 20.0327 157.221 19.2686C160.736 18.2654 164.183 17.3126 167.798 16.355C171.366 15.4099 174.938 14.5016 178.583 13.6175C179.865 13.3068 181.11 13.01 182.382 12.7115C182.387 12.7103 182.392 12.7092 182.397 12.7081C184.743 12.1577 187.108 11.6202 189.488 11.0974C189.734 11.0434 189.981 10.9895 190.227 10.9358C193.662 10.187 197.094 9.47584 200.525 8.80387C204.236 8.07726 207.975 7.39129 211.706 6.75667C215.496 6.11298 219.341 5.51021 223.235 4.96847C226.737 4.48064 230.218 4.04124 233.655 3.6461C234.568 3.54106 235.48 3.43893 236.39 3.3397C240.697 2.86971 244.868 2.46908 249.008 2.10681C253.099 1.74925 257.296 1.42077 261.436 1.13996C265.589 0.858158 269.827 0.616323 274.068 0.430491C278.388 0.242856 282.537 0.11389 286.898 0.0477176C291.15 -0.0180919 295.509 -0.0172395 299.833 0.0577754C302.221 0.0982524 304.869 0.180525 307.283 0.285557C309.712 0.390257 312.19 0.529811 314.603 0.695219C315.582 0.762221 316.552 0.833671 317.511 0.908397C318.893 1.01582 320.322 1.13814 321.703 1.26464C324.045 1.47966 326.254 1.70504 328.472 1.95169C330.695 2.19937 332.826 2.45592 334.862 2.71219C336.973 2.97862 338.787 3.21979 340.874 3.50549C345.938 4.25129 349.151 4.89506 352.624 5.64222C353.32 5.79374 354.028 5.95091 354.761 6.11783C358.898 7.06613 363.107 8.19271 367.142 9.45278C371.256 10.7373 375.282 12.1817 379.209 13.8191C383.206 15.4862 387.111 17.3592 390.846 19.4638C394.67 21.6209 398.291 23.9927 401.746 26.7309C405.2 29.4698 408.473 32.592 411.288 36.0503C412.873 37.9879 414.474 40.2787 415.734 42.4661C416.664 44.0713 417.466 45.6738 418.174 47.325C418.462 47.9972 418.73 48.6715 418.98 49.3449C419.808 51.5805 420.428 53.825 420.863 56.0386C420.863 56.0386 420.863 56.0386 420.863 56.0386C420.879 56.1227 420.895 56.2067 420.911 56.2906C421.348 58.5845 421.585 60.823 421.665 63.0145C421.744 65.217 421.666 67.3565 421.455 69.4867C421.223 71.7238 420.941 73.4774 420.432 75.7899C420.097 77.2824 419.72 78.6805 419.277 80.1061C418.602 82.2617 417.867 84.2195 416.977 86.2787C416.119 88.2611 415.169 90.1945 414.132 92.1002C413.808 92.6963 413.456 93.3253 413.111 93.9224C412.039 95.7768 410.907 97.5693 409.745 99.2893C408.566 101.034 407.351 102.71 406.114 104.328C404.855 105.973 403.572 107.558 402.257 109.111C399.963 111.815 397.706 114.164 395.584 116.242C393.481 118.299 391.464 120.138 389.425 121.916C389.055 122.239 388.681 122.561 388.305 122.883C386.694 124.264 385.012 125.649 383.405 126.933C381.439 128.505 379.403 130.072 377.392 131.57C375.422 133.038 373.353 134.528 371.313 135.955C369.294 137.369 367.318 138.713 365.14 140.154C363.042 141.541 360.889 142.94 358.459 144.461C356.091 145.944 353.604 147.45 350.991 148.963C348.434 150.445 345.817 151.9 343.137 153.327C340.508 154.727 337.843 156.088 335.147 157.41C332.485 158.716 329.825 159.968 327.113 161.198C324.434 162.413 321.687 163.609 319.044 164.72C314.816 166.496 310.542 168.198 306.284 169.811C302.042 171.417 297.726 172.97 293.431 174.438C289.083 175.925 284.841 177.3 280.458 178.652C276.156 179.977 271.75 181.264 267.415 182.463C266.454 182.728 265.49 182.992 264.523 183.253C261.109 184.175 257.703 185.054 254.303 185.894C254.28 185.899 254.256 185.905 254.233 185.911C249.827 186.999 245.545 187.994 241.158 188.957C239.546 189.311 237.999 189.643 236.44 189.971C234.875 190.3 233.334 190.617 231.773 190.932C230.441 191.201 229.111 191.464 227.793 191.721C227.566 191.765 227.339 191.809 227.113 191.853C225.559 192.154 224.048 192.441 222.525 192.726C221.129 192.986 219.746 193.242 218.237 193.512C218.1 193.537 217.962 193.562 217.824 193.586C216.177 193.881 214.263 194.209 212.506 194.493C207.857 195.242 203.292 195.918 198.661 196.541C194.059 197.16 189.351 197.729 184.708 198.221C182.864 198.416 181.019 198.602 179.18 198.774C176.326 199.043 173.454 199.286 170.552 199.503C165.818 199.856 161.016 200.136 156.24 200.328C151.448 200.521 146.664 200.628 141.837 200.645C137.044 200.662 132.196 200.587 127.446 200.418C122.12 200.229 116.896 199.931 111.557 199.502C106.328 199.08 101.017 198.528 95.7682 197.839C90.5333 197.151 85.2935 196.32 80.1064 195.33C74.9065 194.337 69.7506 193.184 64.6326 191.831C61.5513 191.016 58.5497 190.146 55.5062 189.177C53.9041 188.684 52.4252 188.204 50.9222 187.693C40.1379 183.927 32.504 180.492 23.4448 174.456C22.5639 173.855 21.8627 173.361 21.0298 172.751C20.8711 172.634 20.7036 172.51 20.5338 172.383C19.4778 171.591 18.2138 170.616 16.5866 169.203C13.3921 166.369 8.42769 161.739 4.4842 154.157C3.51169 152.249 3.03673 150.966 3.22251 151.389C3.36166 151.688 3.81761 152.721 4.45894 153.951C3.2907 151.688 2.32094 149.286 1.61263 146.851C1.32773 145.876 1.10473 144.949 0.96064 144.286C0.827199 143.671 0.765057 143.332 0.764374 143.332C0.764374 143.332 0.764776 143.334 0.765584 143.338C0.77754 143.396 0.862404 143.851 1.0355 144.594C1.20825 145.337 1.46293 146.303 1.76729 147.276C2.57029 149.854 3.65148 152.35 4.9578 154.709C6.27259 157.085 7.81181 159.32 9.50124 161.398C11.2178 163.506 13.0566 165.43 15.0893 167.293C17.039 169.083 19.2247 170.837 21.3775 172.384C23.6229 173.996 25.8214 175.408 28.2104 176.803C29.6451 177.638 31.0316 178.399 32.4898 179.157C33.0294 179.437 33.5618 179.707 34.0999 179.975C34.5394 180.194 34.9535 180.397 35.3993 180.612C40.1024 182.881 45.1011 184.878 49.9851 186.554C55.0021 188.276 60.0232 189.722 65.1392 190.984C70.2428 192.242 75.39 193.299 80.5835 194.195C85.7488 195.086 90.9715 195.817 96.1881 196.403C101.405 196.989 106.687 197.436 111.878 197.753C115.97 198.002 119.929 198.17 123.997 198.275C125.614 198.3 127.147 198.313 128.696 198.318C133.215 198.331 137.716 198.241 142.177 198.073C134.262 198.266 127.926 198.243 121.834 198.094C121.02 198.073 120.214 198.05 119.413 198.024C111.198 197.741 103.299 197.272 92.6835 195.941C62.5364 192.023 45.8669 185.387 35.976 180.755C26.1412 175.974 22.3262 172.956 18.8895 170.315C13.831 166.666 5.81095 157.534 3.59441 151.856C2.66516 149.868 2.11952 148.195 2.12701 148.203C2.14127 148.209 2.71887 149.887 3.66384 151.866C6.01069 157.842 14.8971 167.588 21.0343 171.663C23.7294 173.537 26.7105 175.963 36.4279 180.6C46.1914 185.06 62.6806 191.54 92.836 195.3C103.062 196.526 110.627 196.958 118.752 197.216C119.542 197.239 120.34 197.26 121.148 197.278C121.153 197.278 121.158 197.278 121.163 197.279C128.645 197.437 136.704 197.427 147.642 196.988C172.853 196.253 206.383 190.836 214.211 189.1C215.772 188.79 217.253 188.484 218.821 188.152C221.113 187.668 223.59 187.13 226.694 186.433C231.743 185.291 238.388 183.784 248.42 181.161C252.004 180.22 256.097 179.115 260.637 177.819C267.353 175.726 273.834 173.535 279.424 171.577C284.28 169.878 288.654 168.172 292.895 166.421C294.109 165.919 295.322 165.408 296.529 164.893C299.493 163.632 302.411 162.346 305.22 161.097C307.5 160.086 309.866 158.997 312.321 157.831C321.328 153.537 331.996 148.158 345.448 140.623C359.788 132.55 371.282 124.678 380.588 117.043C373.762 122.579 368.04 126.436 363.3 129.497C362.399 130.076 361.526 130.629 360.677 131.16C354.753 134.846 350.336 137.343 345.535 140.068C322.657 152.667 313.382 156.118 303.265 160.742C300.611 161.908 297.592 163.218 294.403 164.554C287.996 167.238 280.518 170.164 272.423 172.892C264.819 175.455 256.351 178.144 247.182 180.727C247.178 180.728 247.175 180.729 247.171 180.73C245.398 181.23 243.595 181.727 241.767 182.219C236.796 183.556 231.783 184.82 226.609 186.023C220.126 187.531 213.596 188.897 207.127 190.085C205.894 190.312 204.395 190.585 202.674 190.889C192.824 192.625 175.288 195.4 153.812 196.408C149.038 196.633 144.181 196.761 138.545 196.795C136.835 196.804 135.105 196.803 133.221 196.791C125.246 196.72 115.496 196.516 102.629 195.438C92 194.532 79.1083 192.919 65.9535 189.87C59.4922 188.374 52.9301 186.514 46.7046 184.285C28.0182 177.486 10.5518 167.424 2.96763 150.228C2.96616 150.224 2.96466 150.221 2.96319 150.217C2.10744 148.212 1.63201 146.572 1.65572 146.645C1.68618 146.717 2.23114 148.461 3.11708 150.45C10.9286 167.515 28.3684 177.323 46.9792 183.961C53.1152 186.111 59.6127 187.915 66.0416 189.367C79.2434 192.354 92.2946 193.914 103.003 194.762C116.074 195.776 125.952 195.919 134.049 195.937C136.01 195.937 137.803 195.925 139.547 195.904C145.13 195.831 149.645 195.682 154.176 195.446C174.911 194.361 191.739 191.676 201.607 189.919C203.362 189.607 204.934 189.318 206.227 189.077C212.881 187.839 219.638 186.407 226.359 184.818C231.117 183.693 235.723 182.521 240.356 181.272C242.161 180.785 243.947 180.291 245.71 179.793C245.714 179.791 245.718 179.79 245.721 179.789C254.849 177.212 263.472 174.473 271.177 171.867C278.945 169.238 285.648 166.621 291.865 164.041C295.158 162.671 298.38 161.285 301.496 159.92C312.196 155.055 321.926 151.37 344.745 138.752ZM6.44002 157.391C9.31062 161.873 12.2939 164.963 14.6457 167.192C12.6421 165.297 10.8067 163.311 9.12531 161.171C8.19346 159.985 7.25888 158.677 6.44002 157.391ZM261.618 178.921C260.803 179.174 259.979 179.427 259.15 179.679C261.452 179.031 263.738 178.37 266.003 177.697C268.762 176.877 271.597 176.007 274.36 175.131C269.93 176.512 265.642 177.779 261.618 178.921ZM346.226 141.945C333.457 149.067 323.646 154.059 314.68 158.368C312.126 159.59 309.615 160.754 307.087 161.879C306.031 162.349 305.008 162.805 303.963 163.266C310.189 160.827 315.823 158.442 320.425 156.398C334.09 150.29 341.682 146.286 348.247 142.754C350.173 141.707 351.992 140.691 353.815 139.643C355.28 138.714 356.865 137.694 358.398 136.689C360.539 135.285 362.872 133.715 365.055 132.196C367.22 130.691 369.445 129.097 371.577 127.515C373.695 125.942 375.843 124.296 377.893 122.659C379.792 121.144 381.756 119.519 383.545 117.972C383.648 117.883 383.751 117.794 383.853 117.705C385.703 116.1 387.612 114.364 389.199 112.835C390.811 111.285 392.211 109.853 393.33 108.63C394.718 107.115 396.202 105.43 397.545 103.811C398.869 102.218 400.246 100.477 401.476 98.8116C402.692 97.1676 403.949 95.3594 405.044 93.6472C405.397 93.0953 405.778 92.486 406.129 91.9076C406.291 91.6414 406.45 91.3755 406.606 91.1106C404.435 94.5828 401.935 97.9671 399.079 101.348C394.991 106.182 390.139 111.012 384.39 115.934C374.649 124.253 362.338 132.899 346.226 141.945ZM402.028 36.4036C403.265 37.5981 405.098 39.439 407.071 41.993C407.548 42.6114 408.031 43.2701 408.51 43.9686C408.499 43.952 408.488 43.935 408.477 43.9183C407.316 42.145 406.143 40.6052 404.744 38.9917C402.258 36.139 399.543 33.6466 396.524 31.3022C393.575 29.0158 390.387 26.9324 387.036 25.0342C383.752 23.1733 380.299 21.4856 376.748 19.9605C373.261 18.4622 369.644 17.1072 366.006 15.906C364.831 15.5178 363.638 15.141 362.486 14.7916C365.054 15.6208 367.837 16.581 370.583 17.6313C372.883 18.5101 375.12 19.428 377.544 20.5194C378.47 20.9372 379.575 21.4538 380.828 22.0723C385.819 24.6155 393.754 28.6014 402.028 36.4036ZM332.39 8.52694C331.507 8.40255 330.625 8.28183 329.749 8.1647C327.228 7.82796 324.839 7.53527 322.454 7.26897C320.789 7.08293 319.077 6.90548 317.473 6.75266C316.791 6.68761 316.107 6.62491 315.427 6.56478C314.288 6.464 313.15 6.37048 312.014 6.28331C319.872 6.96566 327.662 7.91585 335.562 9.23707C335.668 9.25487 335.775 9.27236 335.882 9.29041C334.657 9.01572 333.533 8.7682 332.39 8.52694Z" fill="#262525"/>
</svg>

            </h2>
            <p class="happen-desc"><?php echo esc_html($happen_desc); ?></p>
        </div>

        <div class="happen-slider">
            <div class="swiper happen-swiper">
                <div class="swiper-wrapper">
                    <?php if (!empty($happen_slides)) : ?>
                        <?php foreach ($happen_slides as $slide) : ?>
                    <div class="swiper-slide">
                        <div class="happen-card">
                            <img src="<?php echo esc_url($slide['image']['url']); ?>" alt="<?php echo esc_attr($slide['title']); ?>" loading="lazy" decoding="async">
                            <div class="happen-card-content">
                                <span class="happen-card-country"><?php echo esc_html($slide['country']); ?></span>
                                <h3 class="happen-card-title"><?php echo esc_html($slide['title']); ?></h3>
                            </div>
                        </div>
                    </div>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <?php
                        $fallback_happen = [
                            ['img' => 'm1.png', 'country' => 'SPAIN', 'title' => 'MONATIK CONCERT'],
                            ['img' => 'm2.png', 'country' => 'POLAND', 'title' => 'TILL LINDEMANN'],
                            ['img' => 'm3.png', 'country' => 'POLAND', 'title' => 'BRUNO MARS'],
                        ];
                        for ($i = 0; $i < 3; $i++) :
                            foreach ($fallback_happen as $item) : ?>
                    <div class="swiper-slide">
                        <div class="happen-card">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/<?php echo $item['img']; ?>" alt="<?php echo $item['title']; ?>" loading="lazy" decoding="async">
                            <div class="happen-card-content">
                                <span class="happen-card-country"><?php echo $item['country']; ?></span>
                                <h3 class="happen-card-title"><?php echo $item['title']; ?></h3>
                            </div>
                        </div>
                    </div>
                            <?php endforeach;
                        endfor; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="happen-nav">
                <button class="happen-btn happen-prev" aria-label="Previous">
                    <svg width="15" height="14" viewBox="0 0 15 14" fill="none">
                        <path d="M5.72439 13.7388L6.73332 12.5281L2.74055 7.72816L15 7.72816L15 6.01084L2.74055 6.01084L6.73331 1.21094L5.72439 0.000228921L-1.00088e-06 6.8695L5.72439 13.7388Z" fill="black"/>
                    </svg>
                </button>
                <button class="happen-btn happen-next" aria-label="Next">
                    <svg width="15" height="14" viewBox="0 0 15 14" fill="none">
                        <path d="M9.27561 13.7388L8.26668 12.5281L12.2595 7.72816L8.75774e-07 7.72816L1.126e-06 6.01084L12.2595 6.01084L8.26669 1.21094L9.27561 0.000228921L15 6.8695L9.27561 13.7388Z" fill="black"/>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Instagram Section -->
    <?php
        $instagram_title = get_field('instagram_title') ?: 'Follow us on Instagram';
        $instagram_desc = get_field('instagram_desc') ?: 'One of the key advantages of working with us is our direct partnership with an international artist booking agency.';
        $instagram_link = get_field('instagram_link') ?: '#';
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
                        // Fallback images when no token
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

    <?php
        $dream_bg1 = get_field('dream_bg_line1') ?: 'DREAM';
        $dream_bg2 = get_field('dream_bg_line2') ?: 'EVENT';
        $dream_slides = get_field('dream_slides');
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
</main>

<?php get_footer(); ?>
