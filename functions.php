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
