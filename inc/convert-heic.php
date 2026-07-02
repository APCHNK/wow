<?php
/**
 * HEIC → JPG
 *
 * Photos uploaded straight from an iPhone are often .heic, which web browsers
 * cannot display — the hero image just shows blank ("no photo"). This:
 *   1. auto-converts any HEIC to JPG on upload (so it never happens again), and
 *   2. Tools → Convert HEIC Images: converts the HEIC files already in the
 *      library and repoints every reference to the new JPG.
 *
 * Requires the server's Imagick/GD to read HEIC (libheif). The tool reports
 * clearly if the server can't.
 */

if (!defined('ABSPATH')) {
    exit;
}

/** True if wp_get_image_editor can actually open a HEIC file. */
function wow_heic_supported() {
    if (class_exists('Imagick')) {
        $fmts = @Imagick::queryFormats('HEIC');
        if (!empty($fmts)) {
            return true;
        }
    }
    return function_exists('imagecreatefromheic');
}

/* -------------------------------------------------------------------------
 * 1) Auto-convert on upload — future-proofing.
 * ---------------------------------------------------------------------- */
add_filter('wp_handle_upload', function ($upload) {
    if (empty($upload['file']) || !preg_match('/\.heic$/i', $upload['file'])) {
        return $upload;
    }
    if (!wow_heic_supported()) {
        return $upload;
    }
    $jpg = preg_replace('/\.heic$/i', '.jpg', $upload['file']);
    $editor = wp_get_image_editor($upload['file']);
    if (is_wp_error($editor)) {
        return $upload;
    }
    $saved = $editor->save($jpg, 'image/jpeg');
    if (is_wp_error($saved)) {
        return $upload;
    }
    @unlink($upload['file']);
    $upload['file'] = $saved['path'];
    $upload['url']  = preg_replace('/\.heic$/i', '.jpg', $upload['url']);
    $upload['type'] = 'image/jpeg';
    return $upload;
}, 20);

/* -------------------------------------------------------------------------
 * 2) Convert the HEIC files already in the media library.
 * ---------------------------------------------------------------------- */
function wow_convert_heic_library() {
    global $wpdb;
    $report = ['converted' => [], 'failed' => [], 'refs' => 0, 'supported' => wow_heic_supported()];
    if (!$report['supported']) {
        return $report;
    }
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $ids = $wpdb->get_col(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment'
         AND (guid LIKE '%.heic' OR post_mime_type = 'image/heic')"
    );
    foreach ($ids as $id) {
        $id   = (int) $id;
        $file = get_attached_file($id);
        if (!$file || !preg_match('/\.heic$/i', $file) || !file_exists($file)) {
            $report['failed'][] = "#$id (file missing)";
            continue;
        }
        $jpg    = preg_replace('/\.heic$/i', '.jpg', $file);
        $editor = wp_get_image_editor($file);
        if (is_wp_error($editor)) {
            $report['failed'][] = "#$id ({$editor->get_error_message()})";
            continue;
        }
        $saved = $editor->save($jpg, 'image/jpeg');
        if (is_wp_error($saved)) {
            $report['failed'][] = "#$id (save: {$saved->get_error_message()})";
            continue;
        }

        $base_old = basename($file);
        $base_new = basename($saved['path']);

        // Repoint the attachment itself to the JPG.
        update_attached_file($id, $saved['path']);
        wp_update_post(['ID' => $id, 'post_mime_type' => 'image/jpeg']);
        $wpdb->update($wpdb->posts, ['guid' => str_ireplace('.heic', '.jpg', get_the_guid($id))], ['ID' => $id]);
        wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $saved['path']));

        // Backstop: rewrite any hardcoded .heic URLs in content/meta (ACF stores
        // images by ID so those fix themselves, but content may hardcode a URL).
        $report['refs'] += wow_heic_replace_refs($base_old, $base_new);

        @unlink($file);
        $report['converted'][] = "#$id  $base_old → $base_new";
    }
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    return $report;
}

/** Replace a .heic basename with its .jpg in post content + non-serialized meta. */
function wow_heic_replace_refs($base_old, $base_new) {
    global $wpdb;
    $n = 0;
    $like = '%' . $wpdb->esc_like($base_old) . '%';

    foreach ($wpdb->get_results($wpdb->prepare(
        "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_content LIKE %s", $like
    )) as $p) {
        $new = str_replace($base_old, $base_new, $p->post_content);
        if ($new !== $p->post_content) {
            $wpdb->update($wpdb->posts, ['post_content' => $new], ['ID' => $p->ID]);
            clean_post_cache($p->ID);
            $n++;
        }
    }
    foreach ($wpdb->get_results($wpdb->prepare(
        "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE %s", $like
    )) as $m) {
        if (is_serialized($m->meta_value)) {
            continue;
        }
        $new = str_replace($base_old, $base_new, $m->meta_value);
        if ($new !== $m->meta_value) {
            $wpdb->update($wpdb->postmeta, ['meta_value' => $new], ['meta_id' => $m->meta_id]);
            $n++;
        }
    }
    return $n;
}

add_action('admin_menu', function () {
    add_management_page('Convert HEIC Images', 'Convert HEIC Images', 'manage_options', 'wow-convert-heic', 'wow_convert_heic_page');
});

function wow_convert_heic_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Nope.');
    }
    global $wpdb;
    echo '<div class="wrap"><h1>Convert HEIC Images</h1>';
    echo '<p>iPhone “.heic” photos don’t display in browsers. This converts every HEIC in the media library to JPG and repoints all references. New HEIC uploads are converted automatically from now on.</p>';

    if (!wow_heic_supported()) {
        echo '<div class="notice notice-error"><p>This server’s image library can’t read HEIC (missing libheif). Ask the host to enable HEIC in ImageMagick, or re-upload the photos as JPG.</p></div></div>';
        return;
    }

    if (isset($_POST['wow_heic_go']) && check_admin_referer('wow_heic')) {
        $r = wow_convert_heic_library();
        echo '<div class="notice notice-success"><p>Converted <strong>' . count($r['converted']) . '</strong> image(s), updated <strong>' . (int) $r['refs'] . '</strong> reference(s).</p>';
        if ($r['converted']) {
            echo '<ul style="margin-left:18px;list-style:disc">';
            foreach ($r['converted'] as $c) {
                echo '<li>' . esc_html($c) . '</li>';
            }
            echo '</ul>';
        }
        if ($r['failed']) {
            echo '<p style="color:#a00">Failed: ' . esc_html(implode(', ', $r['failed'])) . '</p>';
        }
        echo '</div>';
    }

    $pending = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='attachment' AND (guid LIKE '%.heic' OR post_mime_type='image/heic')");
    if (!$pending) {
        echo '<p>✅ No HEIC images in the library.</p></div>';
        return;
    }
    echo '<p><strong>' . $pending . '</strong> HEIC image(s) to convert.</p>';
    echo '<form method="post">';
    wp_nonce_field('wow_heic');
    submit_button('Convert all HEIC to JPG', 'primary', 'wow_heic_go');
    echo '</form></div>';
}
