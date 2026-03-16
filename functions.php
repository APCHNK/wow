<?php
/**
 * Theme functions
 */

// Enqueue scripts and styles
function wow_enqueue_assets() {
    $theme_version = wp_get_theme()->get('Version');

    wp_enqueue_style('wow-style', get_stylesheet_uri(), [], $theme_version);
    wp_enqueue_style('wow-main', get_template_directory_uri() . '/assets/css/main.css', [], $theme_version);
    wp_enqueue_script('wow-main', get_template_directory_uri() . '/assets/js/main.js', [], $theme_version, true);
}
add_action('wp_enqueue_scripts', 'wow_enqueue_assets');

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
        'name'                  => 'WEDDING PROJECTS',
        'singular_name'         => 'Wedding Project',
        'menu_name'             => 'WEDDING PROJECTS',
        'name_admin_bar'        => 'Wedding Project',
        'add_new'               => 'Add New',
        'add_new_item'          => 'Add New Wedding Project',
        'new_item'              => 'New Wedding Project',
        'edit_item'             => 'Edit Wedding Project',
        'view_item'             => 'View Wedding Project',
        'all_items'             => 'All Wedding Projects',
        'search_items'          => 'Search Wedding Projects',
        'not_found'             => 'No wedding projects found.',
        'not_found_in_trash'    => 'No wedding projects found in Trash.',
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

// Disable admin bar on frontend
add_filter('show_admin_bar', '__return_false');

// Register ACF fields
function wow_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;

    // Hero section
    acf_add_local_field_group([
        'key' => 'group_hero',
        'title' => 'Hero Section',
        'fields' => [
            [
                'key' => 'field_hero_subtitle_top',
                'label' => 'Subtitle Top',
                'name' => 'hero_subtitle_top',
                'type' => 'text',
                'default_value' => 'WE CREATE',
            ],
            [
                'key' => 'field_hero_title',
                'label' => 'Title',
                'name' => 'hero_title',
                'type' => 'text',
                'default_value' => 'WOW EVENT',
            ],
            [
                'key' => 'field_hero_subtitle_bottom',
                'label' => 'Subtitle Bottom',
                'name' => 'hero_subtitle_bottom',
                'type' => 'text',
                'default_value' => 'IN THE WORLD',
            ],
            [
                'key' => 'field_hero_video',
                'label' => 'Video',
                'name' => 'hero_video',
                'type' => 'file',
                'return_format' => 'url',
                'mime_types' => 'mp4,webm',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'page_type',
                    'operator' => '==',
                    'value' => 'front_page',
                ],
            ],
        ],
        'menu_order' => 0,
    ]);
}
add_action('acf/init', 'wow_register_acf_fields');
