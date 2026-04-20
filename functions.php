<?php
/**
 * Theme functions
 */

@ini_set('upload_max_filesize', '256M');
@ini_set('post_max_size', '256M');
add_filter('upload_size_limit', function() { return 256 * 1024 * 1024; });

require_once get_template_directory() . '/inc/acf-flexible-content.php';

// Allow SVG uploads
function wow_allow_svg($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'wow_allow_svg');

// Enqueue scripts and styles
function wow_enqueue_assets() {
    $css_ver = file_exists(get_template_directory() . '/assets/css/main.css') ? filemtime(get_template_directory() . '/assets/css/main.css') : time();
    $js_ver = file_exists(get_template_directory() . '/assets/js/main.js') ? filemtime(get_template_directory() . '/assets/js/main.js') : time();

    wp_enqueue_style('wow-style', get_stylesheet_uri(), [], $css_ver);
    wp_enqueue_style('wow-main', get_template_directory_uri() . '/assets/css/main.css', [], $css_ver);
    wp_enqueue_script('wow-main', get_template_directory_uri() . '/assets/js/main.js', [], $js_ver, true);
}
add_action('wp_enqueue_scripts', 'wow_enqueue_assets');

// Add Mux Player as module script
function wow_add_mux_player() {
    echo '<script type="module" src="https://cdn.jsdelivr.net/npm/@mux/mux-player@3/dist/mux-player.mjs"></script>' . "\n";
}
add_action('wp_head', 'wow_add_mux_player');

// Theme setup
function wow_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);

    register_nav_menus([
        'primary' => 'Primary Menu',
        'footer' => 'Footer Menu',
    ]);
}
add_action('after_setup_theme', 'wow_setup');

// Get Instagram photos using Social Feed Gallery plugin token
function wow_get_instagram_photos($count = 12) {
    // Get token from Social Feed Gallery plugin
    $accounts = get_option('insta_gallery_accounts', []);

    if (empty($accounts)) {
        return [];
    }

    // Get first account's token
    $account = reset($accounts);
    $token = $account['access_token'] ?? '';

    if (empty($token)) {
        return [];
    }

    $transient_key = 'wow_instagram_photos';
    $photos = get_transient($transient_key);

    if ($photos !== false) {
        return array_slice($photos, 0, $count);
    }

    $url = 'https://graph.instagram.com/me/media?fields=id,media_type,media_url,permalink,thumbnail_url&access_token=' . $token;
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return [];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data['data'])) {
        return [];
    }

    $photos = [];
    foreach ($data['data'] as $item) {
        if ($item['media_type'] === 'IMAGE' || $item['media_type'] === 'CAROUSEL_ALBUM') {
            $photos[] = [
                'url' => $item['media_url'],
                'link' => $item['permalink'],
            ];
        } elseif ($item['media_type'] === 'VIDEO' && !empty($item['thumbnail_url'])) {
            $photos[] = [
                'url' => $item['thumbnail_url'],
                'link' => $item['permalink'],
            ];
        }
    }

    set_transient($transient_key, $photos, 5 * MINUTE_IN_SECONDS);

    return array_slice($photos, 0, $count);
}

// Clear Instagram cache
function wow_clear_instagram_cache() {
    delete_transient('wow_instagram_photos');
}

// Add admin bar button to clear Instagram cache
function wow_admin_bar_instagram_cache($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['clear_instagram_cache'])) {
        wow_clear_instagram_cache();
    }

    $wp_admin_bar->add_node([
        'id' => 'clear-instagram-cache',
        'title' => 'Clear Instagram Cache',
        'href' => add_query_arg('clear_instagram_cache', '1'),
    ]);
}
add_action('admin_bar_menu', 'wow_admin_bar_instagram_cache', 100);

// Register Wedding Projects custom post type
function wow_register_wedding_projects() {
    $labels = [
        'name'                  => 'Projects',
        'singular_name'         => 'Project',
        'menu_name'             => 'Projects',
        'name_admin_bar'        => 'Project',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New Project',
        'new_item'              => 'New Project',
        'edit_item'             => 'Edit Project',
        'view_item'             => 'View Project',
        'all_items'             => 'All Projects',
        'search_items'          => 'Search Projects',
        'not_found'             => 'No projects found.',
        'not_found_in_trash'    => 'No projects found in Trash.',
        'featured_image'        => 'Featured Image',
        'set_featured_image'    => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image'    => 'Use as featured image',
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'wedding-projects'],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-heart',
        'supports'           => ['title', 'editor', 'thumbnail', 'excerpt'],
        'show_in_rest'       => true,
    ];

    register_post_type('wedding_project', $args);
}
add_action('init', 'wow_register_wedding_projects');

// Register Project Catalog CPT — replaces the project_category taxonomy in the admin UI
// so editing a catalog feels like editing a page (title + page-attributes + sidebar).
// Uses a temporary rewrite slug ('catalog') during the migration to avoid colliding
// with the taxonomy's still-active 'project-category' rewrite. Will be switched to
// 'project-category' in the final cleanup step.
function wow_register_project_catalog() {
    $labels = [
        'name'                  => 'Catalogs',
        'singular_name'         => 'Catalog',
        'menu_name'             => 'Catalogs',
        'name_admin_bar'        => 'Catalog',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New Catalog',
        'new_item'              => 'New Catalog',
        'edit_item'             => 'Edit Catalog',
        'view_item'             => 'View Catalog',
        'all_items'             => 'All Catalogs',
        'search_items'          => 'Search Catalogs',
        'parent_item_colon'     => 'Parent Catalog:',
        'not_found'             => 'No catalogs found.',
        'not_found_in_trash'    => 'No catalogs found in Trash.',
    ];

    register_post_type('project_catalog', [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'project-category', 'with_front' => false],
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => true,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-book-alt',
        'supports'           => ['title', 'page-attributes', 'thumbnail'],
        'show_in_rest'       => true,
    ]);
}
add_action('init', 'wow_register_project_catalog');

// project_category taxonomy — kept as an internal link between wedding_project
// posts and project_catalog CPT posts (so tax_query still works on archive
// pages). No public URL, no top-level menu: the CPT owns the UI now.
function wow_register_project_category() {
    register_taxonomy('project_category', 'wedding_project', [
        'labels'            => [
            'name'          => 'Catalogs',
            'singular_name' => 'Catalog',
            'menu_name'     => 'Catalogs',
        ],
        'hierarchical'      => true,
        'public'            => false,
        'publicly_queryable'=> false,
        'show_ui'           => true,  // still show the metabox on Project edit
        'show_in_menu'      => false,
        'show_in_rest'      => false,
        'show_admin_column' => true,
        'rewrite'           => false,
    ]);
}
add_action('init', 'wow_register_project_category');

// Keep the project_category term hierarchy synchronised with project_catalog
// posts, so tax_query on a Project still resolves to the right catalog.
add_action('save_post_project_catalog', function ($post_id, $post) {
    if (wp_is_post_revision($post_id)) return;
    if ($post->post_status !== 'publish') return;
    $slug = $post->post_name ?: sanitize_title($post->post_title);
    $parent_term_id = 0;
    if ($post->post_parent) {
        $parent_post = get_post($post->post_parent);
        if ($parent_post) {
            $parent_term = get_term_by('slug', $parent_post->post_name, 'project_category');
            if ($parent_term) $parent_term_id = $parent_term->term_id;
        }
    }
    $existing = get_term_by('slug', $slug, 'project_category');
    if ($existing) {
        wp_update_term($existing->term_id, 'project_category', [
            'name'   => $post->post_title,
            'parent' => $parent_term_id,
        ]);
    } else {
        wp_insert_term($post->post_title, 'project_category', [
            'slug'   => $slug,
            'parent' => $parent_term_id,
        ]);
    }
}, 10, 2);

add_action('before_delete_post', function ($post_id) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'project_catalog') return;
    $term = get_term_by('slug', $post->post_name, 'project_category');
    if ($term && !is_wp_error($term)) {
        wp_delete_term($term->term_id, 'project_category');
    }
});

// Bump a stored version so the rewrite rules get flushed whenever we change
// CPT/taxonomy registration. Cheap on every request (simple option check).
add_action('init', function () {
    $v = '2';
    if (get_option('wow_rewrite_version') !== $v) {
        flush_rewrite_rules(false);
        update_option('wow_rewrite_version', $v);
    }
}, 99);

// Disable admin bar on frontend
add_filter('show_admin_bar', '__return_false');

// ---------------------------------------------------------------------------
// Duplicate-post row action (pages, projects, catalogs).
// ---------------------------------------------------------------------------
// Post types we add the Duplicate link to.
function wow_duplicate_post_types() {
    return ['page', 'wedding_project', 'project_catalog'];
}

// Add "Duplicate" link to the row actions on edit.php listings.
add_filter('post_row_actions', 'wow_duplicate_row_action', 10, 2);
add_filter('page_row_actions', 'wow_duplicate_row_action', 10, 2);
function wow_duplicate_row_action($actions, $post) {
    if (!current_user_can('edit_posts')) return $actions;
    if (!in_array($post->post_type, wow_duplicate_post_types(), true)) return $actions;
    $url = wp_nonce_url(
        admin_url('admin.php?action=wow_duplicate_post&post=' . $post->ID),
        'wow_duplicate_' . $post->ID
    );
    $actions['wow-duplicate'] = '<a href="' . esc_url($url) . '" title="Duplicate this item">Duplicate</a>';
    return $actions;
}

// "Duplicate" button on the post-edit screen toolbar (near Publish/Update).
add_action('post_submitbox_misc_actions', function () {
    global $post;
    if (!$post || !in_array($post->post_type, wow_duplicate_post_types(), true)) return;
    if (!current_user_can('edit_posts')) return;
    $url = wp_nonce_url(
        admin_url('admin.php?action=wow_duplicate_post&post=' . $post->ID),
        'wow_duplicate_' . $post->ID
    );
    ?>
    <div class="misc-pub-section" style="padding-top:10px;">
        <a href="<?php echo esc_url($url); ?>" class="button">Duplicate</a>
    </div>
    <?php
});

// Handler: deep-copies the post (postmeta + taxonomy terms + featured image)
// and redirects the editor to the new draft.
add_action('admin_action_wow_duplicate_post', function () {
    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
    if (!$post_id) wp_die('No post id supplied.');
    check_admin_referer('wow_duplicate_' . $post_id);
    if (!current_user_can('edit_posts')) wp_die('Insufficient permissions.');

    $post = get_post($post_id);
    if (!$post) wp_die('Post not found.');
    if (!in_array($post->post_type, wow_duplicate_post_types(), true)) wp_die('This post type cannot be duplicated.');

    $new_id = wp_insert_post([
        'post_type'      => $post->post_type,
        'post_title'     => $post->post_title . ' (Copy)',
        'post_content'   => $post->post_content,
        'post_excerpt'   => $post->post_excerpt,
        'post_status'    => 'draft',
        'post_author'    => get_current_user_id(),
        'post_parent'    => $post->post_parent,
        'menu_order'     => $post->menu_order,
        'comment_status' => $post->comment_status,
        'ping_status'    => $post->ping_status,
    ], true);
    if (is_wp_error($new_id)) wp_die('Duplicate failed: ' . $new_id->get_error_message());

    // Copy every postmeta row verbatim (ACF flexible content, thumbnail, etc).
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d",
        $post_id
    ));
    foreach ($rows as $r) {
        if ($r->meta_key === '_edit_lock' || $r->meta_key === '_edit_last') continue;
        $wpdb->insert($wpdb->postmeta, [
            'post_id'    => $new_id,
            'meta_key'   => $r->meta_key,
            'meta_value' => $r->meta_value,
        ]);
    }

    // Copy taxonomy terms.
    foreach (get_object_taxonomies($post->post_type) as $tax) {
        $terms = wp_get_object_terms($post_id, $tax, ['fields' => 'ids']);
        if (!is_wp_error($terms) && !empty($terms)) {
            wp_set_object_terms($new_id, $terms, $tax);
        }
    }

    wp_safe_redirect(admin_url('post.php?action=edit&post=' . $new_id));
    exit;
});

// Register ACF Options pages
function wow_register_acf_options() {
    if (!function_exists('acf_add_options_page')) return;

    acf_add_options_page([
        'page_title' => 'Header Settings',
        'menu_title' => 'Header',
        'menu_slug' => 'header-settings',
        'capability' => 'edit_posts',
        'icon_url' => 'dashicons-arrow-up-alt',
        'position' => 60,
    ]);

    acf_add_options_page([
        'page_title' => 'Footer Settings',
        'menu_title' => 'Footer',
        'menu_slug' => 'footer-settings',
        'capability' => 'edit_posts',
        'icon_url' => 'dashicons-arrow-down-alt',
        'position' => 61,
    ]);
}
add_action('acf/init', 'wow_register_acf_options');

// Register ACF fields
function wow_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;

    // Header settings
    acf_add_local_field_group([
        'key' => 'group_header',
        'title' => 'Header',
        'fields' => [
            [
                'key' => 'field_header_logo',
                'label' => 'Logo',
                'name' => 'header_logo',
                'type' => 'image',
                'return_format' => 'url',
                'preview_size' => 'medium',
            ],
            [
                'key' => 'field_header_nav_items',
                'label' => 'Navigation Items',
                'name' => 'header_nav_items',
                'type' => 'repeater',
                'layout' => 'block',
                'button_label' => 'Add Nav Item',
                'sub_fields' => [
                    [
                        'key' => 'field_header_nav_title',
                        'label' => 'Title',
                        'name' => 'title',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'field_header_nav_desc',
                        'label' => 'Description',
                        'name' => 'description',
                        'type' => 'textarea',
                        'rows' => 2,
                    ],
                    [
                        'key' => 'field_header_nav_link',
                        'label' => 'Link',
                        'name' => 'link',
                        'type' => 'text',
                    ],
                ],
            ],
            [
                'key' => 'field_header_phone',
                'label' => 'Phone',
                'name' => 'header_phone',
                'type' => 'text',
                'default_value' => '+48571286783',
            ],
            [
                'key' => 'field_header_email',
                'label' => 'Email',
                'name' => 'header_email',
                'type' => 'text',
                'default_value' => 'event@golden5here.com',
            ],
            [
                'key' => 'field_header_instagram',
                'label' => 'Instagram URL',
                'name' => 'header_instagram',
                'type' => 'url',
            ],
            [
                'key' => 'field_header_facebook',
                'label' => 'Facebook URL',
                'name' => 'header_facebook',
                'type' => 'url',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'header-settings',
                ],
            ],
        ],
    ]);

    // Footer settings
    acf_add_local_field_group([
        'key' => 'group_footer',
        'title' => 'Footer',
        'fields' => [
            [
                'key' => 'field_footer_title',
                'label' => 'Title',
                'name' => 'footer_title',
                'type' => 'text',
                'default_value' => "Let's turn your idea into something real",
            ],
            [
                'key' => 'field_footer_btn_text',
                'label' => 'Button Text',
                'name' => 'footer_btn_text',
                'type' => 'text',
                'default_value' => 'CONTACT US',
            ],
            [
                'key' => 'field_footer_btn_link',
                'label' => 'Button Link',
                'name' => 'footer_btn_link',
                'type' => 'url',
            ],
            [
                'key' => 'field_footer_phone',
                'label' => 'Phone',
                'name' => 'footer_phone',
                'type' => 'text',
                'default_value' => '+48571286783',
            ],
            [
                'key' => 'field_footer_email',
                'label' => 'Email',
                'name' => 'footer_email',
                'type' => 'text',
                'default_value' => 'event@golden5here.com',
            ],
            [
                'key' => 'field_footer_instagram',
                'label' => 'Instagram URL',
                'name' => 'footer_instagram',
                'type' => 'url',
            ],
            [
                'key' => 'field_footer_facebook',
                'label' => 'Facebook URL',
                'name' => 'footer_facebook',
                'type' => 'url',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'footer-settings',
                ],
            ],
        ],
    ]);

}
add_action('acf/init', 'wow_register_acf_fields');

// WOW diamond SVG shortcode
function wow_diamond_svg($atts) {
    $atts = shortcode_atts(['width' => '165', 'height' => '173'], $atts);
    $w = esc_attr($atts['width']);
    $h = esc_attr($atts['height']);
    return '<svg width="' . $w . '" height="' . $h . '" viewBox="0 0 165 173" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g filter="url(#filter0_d_413_18229)">
        <path d="M158.445 37.6865C161.063 40.3042 161.063 44.5484 158.445 47.1662L48.3007 157.31C45.683 159.928 41.4388 159.928 38.8211 157.31L6.43227 124.922C3.81456 122.304 3.81454 118.06 6.43227 115.442L116.577 5.29771C119.194 2.67999 123.438 2.68 126.056 5.29771L158.445 37.6865ZM18.9724 112.208C17.5042 110.739 15.1234 110.739 13.6553 112.208C12.1874 113.676 12.1879 116.056 13.6559 117.524C15.1241 118.991 17.5037 118.992 18.9717 117.524C20.4396 116.056 20.4399 113.676 18.9724 112.208ZM26.9473 120.183C25.4792 118.714 23.0991 118.714 21.6309 120.182C20.1628 121.65 20.1628 124.031 21.6309 125.499C23.0991 126.967 25.48 126.967 26.948 125.499C28.416 124.031 28.4151 121.651 26.9473 120.183ZM34.9223 128.158C33.4543 126.689 31.0741 126.689 29.6059 128.157C28.1378 129.625 28.1378 132.006 29.6059 133.474C31.0741 134.942 33.4543 134.941 34.9223 133.473C36.39 132.005 36.3899 129.626 34.9223 128.158ZM42.8973 136.132C41.4292 134.664 39.0483 134.664 37.5802 136.132C36.1124 137.601 36.1128 139.981 37.5809 141.449C39.049 142.916 41.4286 142.917 42.8966 141.449C44.3645 139.981 44.3648 137.601 42.8973 136.132ZM50.8723 144.107C49.4042 142.639 47.024 142.639 45.5559 144.107C44.0877 145.575 44.0877 147.956 45.5559 149.424C47.024 150.891 49.4043 150.891 50.8723 149.423C52.34 147.955 52.3398 145.576 50.8723 144.107ZM126.632 20.495C125.164 19.0273 122.784 19.0277 121.316 20.4957C119.848 21.9637 119.847 24.3441 121.315 25.8121C122.783 27.2801 125.164 27.2798 126.632 25.8121C128.1 24.3439 128.1 21.9631 126.632 20.495ZM118.658 12.5207C117.19 11.0527 114.809 11.0526 113.341 12.5207C111.873 13.9888 111.874 16.3691 113.341 17.8371C114.809 19.3048 117.189 19.3047 118.657 17.8371C120.125 16.3691 120.126 13.9889 118.658 12.5207ZM134.608 28.4706C133.14 27.0028 130.759 27.0021 129.291 28.47C127.824 29.9381 127.824 32.319 129.291 33.7871C130.759 35.255 133.14 35.2541 134.608 33.7864C136.076 32.3183 136.076 29.9387 134.608 28.4706ZM142.582 36.4449C141.114 34.9773 138.734 34.9776 137.266 36.4456C135.798 37.9137 135.798 40.2934 137.266 41.7614C138.734 43.2293 141.114 43.2296 142.582 41.762C144.05 40.2939 144.05 37.9131 142.582 36.4449ZM150.557 44.4199C149.089 42.9522 146.709 42.9526 145.241 44.4206C143.773 45.8887 143.773 48.2683 145.241 49.7363C146.709 51.2043 149.089 51.2046 150.557 49.737C152.025 48.2689 152.025 45.8881 150.557 44.4199Z" fill="#D090FF"/>
        <path d="M62.9399 79.2177L79.9065 105.906L76.9412 108.871L59.6688 98.0797C59.3631 97.8556 59.0065 97.6212 58.5988 97.3766C58.2116 97.1117 57.804 96.8263 57.376 96.5206C56.948 96.2149 56.4997 95.8888 56.0309 95.5424C55.5622 95.1959 55.1036 94.8596 54.6552 94.5336C54.8998 94.8189 55.1647 95.1653 55.4501 95.573C55.7354 95.9398 56.0411 96.3474 56.3672 96.7958C56.6729 97.2238 56.9786 97.6721 57.2843 98.1409C57.59 98.6096 57.8957 99.0784 58.2014 99.5471L69.0846 116.728L66.1498 119.663L39.4005 102.757L42.0907 100.067L58.3237 110.553C58.9555 110.981 59.5669 111.409 60.1579 111.837C60.749 112.224 61.3196 112.611 61.8699 112.998C62.3998 113.365 62.8991 113.722 63.3679 114.068C63.8366 114.415 64.2544 114.751 64.6212 115.077C64.234 114.567 63.8468 114.058 63.4596 113.548C63.0927 113.019 62.7157 112.478 62.3285 111.928C61.9616 111.358 61.5948 110.807 61.2279 110.277C60.8814 109.727 60.535 109.197 60.1885 108.688L49.8862 92.2713L52.4236 89.734L68.8706 100.006C69.4004 100.332 69.9303 100.678 70.4602 101.045C70.9697 101.392 71.4894 101.748 72.0193 102.115C72.5492 102.482 73.0791 102.869 73.609 103.277C74.1389 103.644 74.679 104.041 75.2292 104.469C74.679 103.756 74.1389 103.053 73.609 102.36C73.0995 101.646 72.6002 100.943 72.111 100.25C71.6219 99.5166 71.1634 98.8134 70.7354 98.1409L60.2802 81.8773L62.9399 79.2177ZM91.1379 72.7859C92.7479 74.396 94.0828 76.0366 95.1426 77.7078C96.2024 79.379 96.9157 81.0502 97.2826 82.7214C97.6698 84.3722 97.6596 85.972 97.252 87.5209C96.824 89.0495 95.9273 90.4965 94.5618 91.862C93.1555 93.2682 91.6678 94.1853 90.0985 94.6133C88.5496 95.0209 86.9395 95.0413 85.2683 94.6745C83.6175 94.2872 81.9565 93.5637 80.2853 92.504C78.6141 91.4034 76.9837 90.0583 75.394 88.4686C72.9891 86.0637 71.2466 83.7506 70.1665 81.5291C69.0863 79.2669 68.6991 77.1269 69.0048 75.1093C69.3309 73.0713 70.3703 71.1759 72.123 69.4232C73.9368 67.6093 75.9035 66.6209 78.0231 66.4578C80.163 66.2744 82.3539 66.7533 84.5958 67.8946C86.8172 69.0156 88.9979 70.646 91.1379 72.7859ZM78.176 85.7479C80.0917 87.6636 81.8954 89.1004 83.5869 90.0583C85.2785 90.9754 86.8478 91.4034 88.2948 91.3423C89.7418 91.2811 91.036 90.6799 92.1773 89.5386C93.3186 88.3973 93.9198 87.1235 93.9809 85.7173C94.0625 84.2907 93.6243 82.7316 92.6664 81.04C91.7289 79.328 90.2921 77.504 88.356 75.5678C85.4619 72.6738 82.8533 70.9007 80.5299 70.2486C78.2065 69.5556 76.1889 70.0651 74.4769 71.7771C73.3152 72.9388 72.6834 74.2431 72.5815 75.6901C72.5 77.1168 72.9382 78.6759 73.8961 80.3674C74.8336 82.0386 76.2602 83.8321 78.176 85.7479ZM105.452 36.7055L122.419 63.3936L119.453 66.3589L102.181 55.5675C101.875 55.3433 101.519 55.109 101.111 54.8644C100.724 54.5995 100.316 54.3141 99.8882 54.0084C99.4602 53.7027 99.0119 53.3766 98.5431 53.0302C98.0744 52.6837 97.6158 52.3474 97.1674 52.0213C97.412 52.3067 97.677 52.6531 97.9623 53.0607C98.2476 53.4276 98.5533 53.8352 98.8794 54.2836C99.1851 54.7115 99.4908 55.1599 99.7965 55.6287C100.102 56.0974 100.408 56.5662 100.714 57.0349L111.597 74.2156L108.662 77.1504L81.9127 60.2448L84.6029 57.5546L100.836 68.0403C101.468 68.4683 102.079 68.8963 102.67 69.3243C103.261 69.7115 103.832 70.0987 104.382 70.486C104.912 70.8528 105.411 71.2095 105.88 71.5559C106.349 71.9024 106.767 72.2387 107.133 72.5648C106.746 72.0553 106.359 71.5457 105.972 71.0362C105.605 70.5063 105.228 69.9663 104.841 69.416C104.474 68.8453 104.107 68.2951 103.74 67.7652C103.394 67.2149 103.047 66.685 102.701 66.1755L92.3984 49.7591L94.9358 47.2218L111.383 57.4935C111.913 57.8196 112.443 58.166 112.972 58.5329C113.482 58.8793 114.002 59.236 114.532 59.6028C115.061 59.9697 115.591 60.3569 116.121 60.7645C116.651 61.1314 117.191 61.5288 117.741 61.9568C117.191 61.2435 116.651 60.5403 116.121 59.8474C115.612 59.1341 115.112 58.431 114.623 57.738C114.134 57.0043 113.676 56.3012 113.248 55.6287L102.792 39.3651L105.452 36.7055Z" fill="black"/>
        </g>
        <defs>
        <filter id="filter0_d_413_18229" x="4.76837e-06" y="3.33442" width="164.877" height="169.346" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
        <feOffset dy="8.93749"/>
        <feGaussianBlur stdDeviation="2.23437"/>
        <feComposite in2="hardAlpha" operator="out"/>
        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_413_18229"/>
        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_413_18229" result="shape"/>
        </filter>
        </defs>
    </svg>';
}
add_shortcode('wow_diamond', 'wow_diamond_svg');
