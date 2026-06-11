<?php
/**
 * Tools → Fix URL Slugs
 *
 * Polylang Free appends "-2" to a translation's slug because WordPress
 * enforces unique slugs across languages. The shared-slug setup on these
 * sites expects translations to use the SAME slug as the default-language
 * page (/ru/<slug>/). This tool finds the suffixed translations and fixes
 * them with a direct DB update (bypassing wp_unique_post_slug).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', function () {
    add_management_page(
        'Fix URL Slugs',
        'Fix URL Slugs',
        'manage_options',
        'fix-url-slugs',
        'bnm_fix_url_slugs_page'
    );
} );

/**
 * Find translations whose slug is "<twin-slug>-N" while their
 * default-language twin owns "<twin-slug>".
 *
 * @return array[] [post_id, current_slug, target_slug, lang, title]
 */
function bnm_find_suffixed_translations(): array {
    if ( ! function_exists( 'pll_get_post' ) || ! function_exists( 'pll_default_language' ) ) {
        return [];
    }
    $default = pll_default_language();
    $found   = [];

    $posts = get_posts( [
        'post_type'        => [ 'page', 'post' ],
        'post_status'      => 'any',
        'numberposts'      => -1,
        'suppress_filters' => false,
        'lang'             => '', // all languages
    ] );

    foreach ( $posts as $p ) {
        $lang = pll_get_post_language( $p->ID );
        if ( ! $lang || $lang === $default ) continue;

        $twin_id = pll_get_post( $p->ID, $default );
        if ( ! $twin_id || (int) $twin_id === (int) $p->ID ) continue;

        $twin = get_post( $twin_id );
        if ( ! $twin ) continue;

        // slug is exactly "<twin-slug>-<digits>"
        if ( $p->post_name !== $twin->post_name
            && preg_match( '/^' . preg_quote( $twin->post_name, '/' ) . '-\d+$/', $p->post_name ) ) {
            $found[] = [
                'post_id'      => $p->ID,
                'current_slug' => $p->post_name,
                'target_slug'  => $twin->post_name,
                'lang'         => $lang,
                'title'        => get_the_title( $p ),
            ];
        }
    }
    return $found;
}

function bnm_fix_url_slugs_page() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Nope.' );

    echo '<div class="wrap"><h1>Fix URL Slugs</h1>';

    if ( ! function_exists( 'pll_get_post' ) ) {
        echo '<p>Polylang не активний — інструмент не потрібен на цьому сайті.</p></div>';
        return;
    }

    // Run the fix
    if ( isset( $_POST['bnm_fix_slugs'] ) && check_admin_referer( 'bnm_fix_slugs' ) ) {
        global $wpdb;
        $items = bnm_find_suffixed_translations();
        $done  = 0;
        foreach ( $items as $it ) {
            // remember the old slug so WP redirects old URLs to the new one
            add_post_meta( $it['post_id'], '_wp_old_slug', $it['current_slug'] );
            $wpdb->update( $wpdb->posts, [ 'post_name' => $it['target_slug'] ], [ 'ID' => $it['post_id'] ] );
            clean_post_cache( $it['post_id'] );
            $done ++;
        }
        if ( function_exists( 'pll_languages_list' ) && method_exists( PLL()->model ?? null, 'clean_languages_cache' ) ) {
            PLL()->model->clean_languages_cache();
        }
        flush_rewrite_rules();
        if ( function_exists( 'wp_cache_flush' ) ) wp_cache_flush();
        echo '<div class="notice notice-success"><p>Виправлено слагів: <strong>' . (int) $done . '</strong>. Старі адреси редіректяться на нові.</p></div>';
    }

    $items = bnm_find_suffixed_translations();

    if ( ! $items ) {
        echo '<p>✅ Все чисто — слагів із суфіксом "-N" у перекладів не знайдено.</p></div>';
        return;
    }

    echo '<p>Знайдено перекладів зі "зайвим" суфіксом у слагу (WP додає його, бо слаг зайнятий сторінкою основної мови):</p>';
    echo '<table class="widefat striped" style="max-width:900px"><thead><tr><th>Сторінка</th><th>Мова</th><th>Зараз</th><th>Стане</th></tr></thead><tbody>';
    foreach ( $items as $it ) {
        printf(
            '<tr><td><a href="%s">%s</a></td><td>%s</td><td><code>%s</code></td><td><code>%s</code></td></tr>',
            esc_url( get_edit_post_link( $it['post_id'] ) ),
            esc_html( $it['title'] ),
            esc_html( $it['lang'] ),
            esc_html( $it['current_slug'] ),
            esc_html( $it['target_slug'] )
        );
    }
    echo '</tbody></table>';

    echo '<form method="post" style="margin-top:16px">';
    wp_nonce_field( 'bnm_fix_slugs' );
    submit_button( 'Виправити слаги', 'primary', 'bnm_fix_slugs' );
    echo '</form></div>';
}

// 404 fallback: /ru/foo-2/ → 301 → /ru/foo/ when the suffixless page exists
// in that language (covers links indexed before the slugs were fixed).
add_action( 'template_redirect', function () {
    if ( ! is_404() || ! function_exists( 'pll_get_post_language' ) ) return;
    $path = wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
    if ( ! is_string( $path ) || ! preg_match( '#^(.*/)([a-z0-9-]+)-\d+/?$#', $path, $m ) ) return;
    $base = $m[2];
    $posts = get_posts( [
        'post_type'        => [ 'page', 'post' ],
        'post_status'      => 'publish',
        'name'             => $base,
        'numberposts'      => -1,
        'fields'           => 'ids',
        'suppress_filters' => false,
        'lang'             => '',
    ] );
    if ( ! $posts ) return;
    wp_safe_redirect( home_url( untrailingslashit( $m[1] ) . '/' . $base . '/' ), 301 );
    exit;
} );
