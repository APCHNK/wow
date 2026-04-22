<?php
// Catalog grid. Active on:
//   - a single project_catalog post
//   - the /wedding-projects/ post-type archive
//   - (legacy) a project_category taxonomy archive during the migration window
//
// Behavior:
//   * cards repeater filled -> render those cards
//   * cards repeater empty  -> auto fallback:
//       - on a single catalog: its child catalogs, or projects tied to its slug
//       - on the CPT archive or taxonomy: posts from the current query
if (!is_singular('project_catalog')
    && !is_post_type_archive('wedding_project')
    && !is_tax('project_category')) return;

// Shared country decoration SVG.
$country_svg = '<svg class="happen-title-border" preserveAspectRatio="none" width="422" height="201" viewBox="0 0 422 201" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M344.745 138.752C349.881 135.83 354.601 133.128 360.442 129.457C360.445 129.455 360.448 129.453 360.451 129.451C361.291 128.921 362.149 128.373 363.024 127.805C370.053 123.135 379.281 117.12 390.874 105.591C403.612 92.881 410.122 80.3432 412.036 72.4347C412.394 70.9665 412.84 69.4093 413.014 65.4985C413.069 63.5264 413.017 61.0435 412.377 57.8018C412.176 56.7993 411.916 55.7344 411.571 54.6044C410.795 52.0624 409.599 49.1801 407.636 46.0334C401.566 35.2543 383.712 23.7914 365.494 18.0883C354.465 14.4103 343.572 12.1698 335.273 10.7695C323.088 8.729 311.214 7.50935 298.212 6.87552C286.304 6.31398 273.47 6.26814 258.641 7.07669C247.273 7.70343 236.159 8.49377 224.971 9.66321C223.532 9.81425 222.1 9.9707 220.674 10.1326C211.108 11.224 201.417 12.6113 190.984 14.5779C190.602 14.6501 190.219 14.7231 189.835 14.7967C170.514 18.5477 155.37 22.711 138.847 28.041C132.359 30.1527 125.713 32.4597 118.557 35.1445C107.227 39.379 91.1428 45.9921 75.6596 54.1052C74.3344 54.7972 73.0216 55.4948 71.7265 56.1946C57.6081 63.7795 44.6041 72.4807 35.6272 79.7767C21.5418 91.3416 17.3944 97.1256 13.8368 101.5C12.4934 103.246 11.3449 104.83 10.0305 106.808C5.34202 113.837 -1.01959 125.936 0.195181 139.286C0.399742 141.607 0.769022 143.342 0.764365 143.332C0.766812 143.321 0.384577 141.572 0.179101 139.401C-0.508276 132.439 0.867865 125.627 2.99619 119.942C3.07096 119.64 3.59892 118.118 4.41441 116.302C6.32995 111.847 13.3719 98.0384 31.3242 82.445C30.0766 83.5045 29.0471 84.4075 28.1658 85.1937C27.1256 86.1228 26.3748 86.8127 25.6877 87.4539C24.2703 88.783 23.3488 89.6712 21.7961 91.2668C18.1414 95.059 12.6621 101.241 8.19929 108.683C7.08753 110.527 6.21714 112.101 6.17224 112.063C6.12779 112.03 6.91725 110.39 8.0215 108.532C12.4835 101.005 18.1141 94.5616 21.9318 90.6004C22.886 89.6074 23.7377 88.7554 24.3865 88.1173C24.9022 87.6092 25.5718 86.9641 25.9302 86.6283C26.2439 86.3363 26.5552 86.0482 26.897 85.7476C26.966 85.6869 27.1163 85.5551 27.2941 85.4047C28.0475 84.7207 28.6891 84.1476 29.3942 83.5281C32.1677 81.1444 36.4192 77.2949 46.2078 70.3717C55.2065 64.0156 71.0478 54.0415 92.0875 44.294C90.6475 44.9457 89.2175 45.6052 87.8024 46.2702C84.2896 47.9198 80.6169 49.7291 77.1532 51.5196C75.7829 52.2278 74.4454 52.9317 73.0977 53.6542C70.9331 54.8148 68.7871 55.9993 66.6184 57.2345C65.1635 58.0632 63.7253 58.8997 62.3091 59.7406C60.2825 60.9444 58.2873 62.1649 56.3269 63.4014C55.6414 63.8334 54.9663 64.2636 54.2759 64.7085C51.5366 66.4769 49.015 68.1642 45.99 70.2868C45.6274 70.5399 45.235 70.8144 44.9432 71.0191C44.6294 71.2392 44.3287 71.4511 44.0454 71.6513C43.7954 71.8279 43.5577 71.9962 43.3307 72.1579C42.9856 72.4039 42.744 72.5774 42.5126 72.7443C42.2839 72.9091 42.0544 73.0756 41.829 73.2405C41.5878 73.4172 41.4099 73.548 41.1629 73.7314C40.8529 73.9616 40.5878 74.1601 40.2688 74.4014C39.9602 74.6349 39.6402 74.879 39.3191 75.1262C38.9981 75.3734 38.6731 75.6257 38.3452 75.8823C38.0171 76.139 37.6856 76.4004 37.3535 76.6644C37.0218 76.9281 36.6877 77.1959 36.3538 77.4654C36.0097 77.7431 35.6596 78.0277 35.3125 78.3123C34.512 78.9681 33.7438 79.5812 33.1702 80.0145C32.5967 80.4476 32.2615 80.6659 32.2275 80.619C32.1943 80.572 32.4649 80.2623 32.9888 79.7567C33.5129 79.251 34.2507 78.5906 35.047 77.9243C35.7029 77.3778 36.4382 76.7707 37.1391 76.1989C37.8497 75.6243 38.5797 75.0412 39.3336 74.4471C40.1048 73.845 40.8957 73.2369 41.7495 72.5913C42.6906 71.8905 43.6045 71.2214 44.5584 70.5336C45.2675 70.0279 45.9867 69.5177 46.5781 69.0987C49.0752 67.3166 52.0637 65.2567 55.0866 63.2802C55.8225 62.7998 56.5633 62.3224 57.3057 61.8543C60.1198 60.054 62.1133 58.8355 66.4502 56.2368C70.7703 53.7377 74.3122 51.7757 77.0396 50.3054C84.2521 46.5389 91.4999 43.0044 98.922 39.6287C106.49 36.3484 115.962 32.6131 122.876 30.1172C129.126 27.9635 134.161 26.3072 141.779 23.8805C149.461 21.5354 157.221 19.2686 167.798 16.355C174.938 14.5016 182.382 12.7115 189.488 11.0974C193.662 10.187 200.525 8.80387 207.975 7.39129C215.496 6.11298 223.235 4.96847 233.655 3.6461C240.697 2.86971 253.099 1.74925 265.589 0.858158C274.068 0.430491 286.898 0.0477176 299.833 0.0577754C307.283 0.285557 317.511 0.908397 328.472 1.95169C340.874 3.50549 354.761 6.11783 367.142 9.45278C379.209 13.8191 390.846 19.4638 401.746 26.7309C411.288 36.0503 415.734 42.4661 418.98 49.3449C420.863 56.0386 421.665 63.0145 421.455 69.4867C420.432 75.7899 419.277 80.1061 416.977 86.2787C414.132 92.1002 410.907 97.5693 406.114 104.328C402.257 109.111 397.706 114.164 391.464 120.138C385.012 125.649 377.392 131.57 365.14 140.154C353.604 147.45 337.843 156.088 319.044 164.72C302.042 171.417 289.083 175.925 271.75 181.264C254.303 185.894 234.875 190.3 217.824 193.586C198.661 196.541 176.326 199.043 156.24 200.328C141.837 200.645 122.12 200.229 95.7682 197.839C74.9065 194.337 52.9301 186.514 23.4448 174.456C16.5866 169.203 8.42769 161.739 0.764374 143.332C2.23114 148.461 14.8971 167.588 23.7294 173.537C46.1914 185.06 92.836 195.3 121.163 197.279C172.853 196.253 215.772 188.79 248.42 181.161C284.28 169.878 321.328 153.537 380.588 117.043C363.3 129.497 345.535 140.068 303.265 160.742C280.518 170.164 247.171 180.73 226.609 186.023C192.824 192.625 138.545 196.795 102.629 195.438C46.7046 184.285 2.96319 150.217 1.65572 146.645C28.3684 177.323 92.2946 193.914 154.176 195.446C206.227 189.077 245.721 179.789 291.865 164.041C312.196 155.055 344.745 138.752Z" fill="#262525"/></svg>';

/**
 * Render one card. $image_url can be empty — $thumb_html (post featured image)
 * is used as a fallback, then a placeholder.
 */
if (!function_exists('wow_catalog_card')) {
function wow_catalog_card($image_url, $thumb_html, $link, $title, $country, $desc_slides, $button_text, $country_svg, $alt = '') {
    if (!$link) return;
    ?>
    <div class="project">
        <div class="project-img">
            <?php if ($image_url) : ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($alt); ?>" loading="lazy" decoding="async">
            <?php elseif ($thumb_html) : echo $thumb_html;
            else : ?>
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/p1.jpg" alt="" loading="lazy" decoding="async">
            <?php endif; ?>
        </div>
        <div class="project-info">
            <div class="project-title">
                <span><?php echo esc_html($title); ?></span>
                <?php if ($country !== '') : ?>
                <span class="project-title-country"><?php echo esc_html($country); ?><?php echo $country_svg; ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($desc_slides)) : ?>
            <div class="project-desc">
                <div class="swiper project-desc-swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($desc_slides as $slide) : ?>
                            <div class="swiper-slide"><?php echo esc_html($slide['text'] ?? ''); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <a href="<?php echo esc_url($link); ?>" class="project-link"><?php echo esc_html($button_text !== '' ? $button_text : 'Show more'); ?></a>
        </div>
    </div>
    <?php
}
}
?>

<section class="wedding-projects-catalog">
    <div class="wedding-projects-catalog-list">
    <?php
    $cards = wow_field('cards');
    if (!empty($cards)) :
        foreach ($cards as $card) :
            $link = wow_resolve_link($card['link'] ?? '');
            if (!$link) continue;
            $image = (string) ($card['image'] ?? '');
            $title = (string) ($card['title_top'] ?? '');
            $country = (string) ($card['country'] ?? '');
            $desc = is_array($card['desc_slider'] ?? null) ? $card['desc_slider'] : [];
            $btn = (string) ($card['button_text'] ?? '');
            wow_catalog_card($image, '', $link, $title, $country, $desc, $btn, $country_svg, $title);
        endforeach;
    else :
        // Auto fallback. On a single catalog we prefer its child catalogs,
        // then the projects tied to the catalog (via the taxonomy kept in sync
        // with the CPT). On the CPT archive or legacy taxonomy archive we just
        // paginate through the current query.
        $rendered_auto = false;
        if (is_singular('project_catalog')) {
            $cur = get_queried_object();
            $children = get_children([
                'post_parent' => $cur->ID,
                'post_type' => 'project_catalog',
                'post_status' => 'publish',
                'orderby' => 'menu_order title',
                'order' => 'ASC',
                'numberposts' => -1,
            ]);
            if (!empty($children)) {
                foreach ($children as $child) {
                    $thumb = get_the_post_thumbnail($child->ID, 'large');
                    wow_catalog_card('', $thumb, get_permalink($child), $child->post_title, '', [], 'Show more', $country_svg, $child->post_title);
                }
                $rendered_auto = true;
            } else {
                $projects = get_posts([
                    'post_type' => 'wedding_project',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'tax_query' => [[
                        'taxonomy' => 'project_category',
                        'field' => 'slug',
                        'terms' => [$cur->post_name],
                    ]],
                ]);
                foreach ($projects as $pp) {
                    $thumb = get_the_post_thumbnail($pp->ID, 'large');
                    wow_catalog_card('', $thumb, get_permalink($pp), $pp->post_title, '', [], 'Show more', $country_svg, $pp->post_title);
                }
                $rendered_auto = !empty($projects);
            }
        }
        if (!$rendered_auto && is_post_type_archive('wedding_project')) {
            // Safe on the CPT archive: the main query is the list of projects.
            // We must NOT run this on a single-catalog page — the main query
            // there is a single post, and nesting have_posts()/the_post()
            // inside the parent's while(have_posts()) loop flips the query
            // pointer back and forth infinitely.
            if (have_posts()) : while (have_posts()) : the_post();
                $thumb = get_the_post_thumbnail(get_the_ID(), 'large');
                wow_catalog_card('', $thumb, get_permalink(), get_the_title(), '', [], 'Show more', $country_svg, get_the_title());
            endwhile; endif;
        }
    endif;
    ?>
    </div>
</section>
