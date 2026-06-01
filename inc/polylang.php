<?php
/**
 * Polylang integration for the Wow theme.
 *
 * Responsibilities:
 *  1. Safe fallbacks for Polylang's pll_* helpers so the theme keeps working
 *     even if Polylang is deactivated.
 *  2. Registration of the theme's hardcoded UI strings so they appear under
 *     Languages -> Translations ("Strings translations"). Registering by the
 *     English source means it scales to any number of languages automatically.
 *  3. A reusable language switcher: wow_language_switcher().
 *  4. Making the wedding_project / project_catalog custom post types
 *     translatable.
 *  5. Seeding a freshly created translation with the source post's ACF /
 *     post-meta (layout + media) so the editor only has to translate text.
 *
 * NOTE: Header/Footer ACF Options pages are intentionally left GLOBAL for all
 * languages for now (project decision). Only theme UI strings and
 * post/page/CPT content are made translatable here.
 *
 * Polylang function reference: pll__($string) and pll_e($string) take a SINGLE
 * argument (they are NOT WordPress __()/_e() and take no text domain).
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ------------------------------------------------------------------------- *
 * 1. Fallback shims — keep the theme alive when Polylang is inactive.
 * ------------------------------------------------------------------------- */
if (!function_exists('pll__')) {
    function pll__($string) {
        return $string;
    }
}

if (!function_exists('pll_e')) {
    function pll_e($string) {
        echo $string;
    }
}

if (!function_exists('pll_register_string')) {
    function pll_register_string($name, $string, $group = 'Theme', $multiline = false) {
        // No-op when Polylang is not active.
    }
}

if (!function_exists('pll_current_language')) {
    function pll_current_language($value = 'slug') {
        return false;
    }
}

/* ------------------------------------------------------------------------- *
 * 2. Theme UI strings.
 *
 *    pll__()/pll_e() look strings up by their ENGLISH content, so each unique
 *    string is registered once here and is then translatable everywhere it is
 *    used. Keep the English source below byte-identical to the templates.
 * ------------------------------------------------------------------------- */
function wow_polylang_strings() {
    return array(
        // admin label                => English source string
        'header_menu'          => 'MENU',
        'label_contacts'       => 'Contacts',
        'label_email'          => 'E-mail',
        'label_faq'            => 'FAQ',
        'breadcrumb_main'      => 'Main',
        'breadcrumb_main_upper'=> 'MAIN',
        'breadcrumb_all'       => 'All Projects',
        'breadcrumb_projects'  => 'Projects',
        'btn_read_more'        => 'READ MORE',
        'btn_less'             => 'Less',
        'btn_show_more_upper'  => 'SHOW MORE',
        'btn_show_more'        => 'Show more',
        'btn_contact_us'       => 'Contact us',
        'heading_our'          => 'Our',
        'archive_hero_title'   => 'Our [wow_diamond] Wedding Projects',
        'archive_hero_subtitle'=> 'Please take a look at our catalog of Weddings projects',
        'slider_prev'          => 'Previous',
        'slider_next'          => 'Next',
        'alt_wedding_project'  => 'Wedding project',
        'alt_wedding_projects' => 'Wedding projects',
        'alt_about'            => 'About',
    );
}

function wow_register_polylang_strings() {
    foreach (wow_polylang_strings() as $name => $string) {
        pll_register_string('wow_' . $name, $string, 'Wow Theme', strlen($string) > 80);
    }
}
add_action('init', 'wow_register_polylang_strings');

/* ------------------------------------------------------------------------- *
 * 2b. Translatable ACF Options TEXT (footer title/button, header menu items).
 *
 *     The Header/Footer Options pages store one global value for all languages.
 *     We expose their text values as Polylang strings so the editor can
 *     translate them under Languages -> Translations (group
 *     "Wow Theme — Options"), and the templates output them through pll__().
 *     Phone / email / socials / links stay global and are NOT registered.
 * ------------------------------------------------------------------------- */
function wow_register_acf_option_strings() {
    if (!function_exists('get_field')) {
        return;
    }
    $group = 'Wow Theme — Options';

    // Simple text options + their template fallbacks, so whichever value
    // actually renders is always translatable.
    $simple = array(
        'footer_title'    => "Let's turn your idea into something real",
        'footer_btn_text' => 'CONTACT US',
    );
    foreach ($simple as $name => $fallback) {
        pll_register_string('wow_opt_' . $name, $fallback, $group, strlen($fallback) > 80);
        $value = get_field($name, 'option');
        if (is_string($value) && trim($value) !== '' && $value !== $fallback) {
            pll_register_string('wow_opt_' . $name . '_value', $value, $group, strlen($value) > 80);
        }
    }

    // Header navigation repeater: title + description of each item.
    $nav = get_field('header_nav_items', 'option');
    if (is_array($nav)) {
        foreach ($nav as $i => $item) {
            if (!empty($item['title'])) {
                pll_register_string('wow_nav_' . $i . '_title', $item['title'], $group);
            }
            if (!empty($item['description'])) {
                pll_register_string('wow_nav_' . $i . '_desc', $item['description'], $group, true);
            }
        }
    }
}
add_action('init', 'wow_register_acf_option_strings', 20); // after ACF (acf/init) has registered the option fields

/* ------------------------------------------------------------------------- *
 * 3. Language switcher.
 *
 *    Usage in templates: <?php wow_language_switcher(); ?>
 *    Renders nothing when Polylang is inactive or only one language exists.
 * ------------------------------------------------------------------------- */
function wow_language_switcher($args = array()) {
    if (!function_exists('pll_the_languages')) {
        return;
    }

    $defaults = array(
        'show_flags'             => 0,
        'show_names'             => 1,
        'display_names_as'       => 'slug', // EN / RU codes; use 'name' for full names.
        'hide_if_no_translation' => 0,      // still show the language even if a page has no translation yet.
        'hide_current'           => 0,
    );

    $items = pll_the_languages(array_merge($defaults, $args, array('raw' => 0, 'echo' => 0)));

    if (empty($items)) {
        return;
    }

    echo '<ul class="wow-lang-switcher">' . $items . '</ul>';
}

/* ------------------------------------------------------------------------- *
 * 4. Make the project custom post types translatable.
 *
 *    Declaring them via the filter (rather than only ticking the Polylang
 *    settings checkboxes) keeps the configuration in version control and
 *    consistent across environments. They still appear, pre-checked, under
 *    Languages -> Settings -> Custom post types and Taxonomies.
 *
 *    The internal project_category taxonomy is deliberately NOT made
 *    translatable: it is non-public plumbing kept in sync with the
 *    project_catalog CPT (see wow_sync_catalog_to_term in functions.php).
 * ------------------------------------------------------------------------- */
function wow_pll_translatable_post_types() {
    return array('page', 'post', 'wedding_project', 'project_catalog');
}

add_filter('pll_get_post_types', function ($post_types, $is_settings) {
    $post_types['wedding_project'] = 'wedding_project';
    $post_types['project_catalog'] = 'project_catalog';
    return $post_types;
}, 10, 2);

/* ------------------------------------------------------------------------- *
 * Keep a translation's slug identical to its source.
 *
 * A post in a non-default language should reuse the SAME slug as its source
 * post — Polylang's /<lang>/ URL prefix already makes it unique per language.
 * Without this, wp_unique_post_slug (and the theme's catalog-aware slug filter)
 * append "-2" every time the translation is saved/published. Runs last (99) so
 * it has the final say over other wp_unique_post_slug filters.
 * ------------------------------------------------------------------------- */
add_filter('wp_unique_post_slug', function ($slug, $post_id, $post_status, $post_type, $post_parent, $original_slug) {
    if (!function_exists('pll_get_post_language') || !function_exists('pll_default_language')) {
        return $slug;
    }
    if (!in_array($post_type, wow_pll_translatable_post_types(), true)) {
        return $slug;
    }
    $lang = pll_get_post_language($post_id);
    if ($lang && $lang !== pll_default_language() && $original_slug !== '') {
        return $original_slug; // translations keep the exact requested slug, no -N suffix
    }
    return $slug;
}, 99, 6);

/* ------------------------------------------------------------------------- *
 * 5. Seed a new translation with the source post's content.
 *
 *    Polylang can't list ACF Flexible Content meta keys in wpml-config.xml
 *    because they are dynamic (e.g. project_sections_0_project_hero_title).
 *    Instead we hook pll_copy_post_metas and, on the INITIAL copy only
 *    ($sync === false), seed the new translation with every meta key from the
 *    source post. This mirrors the theme's own "Duplicate" routine
 *    (functions.php) so the layout, images and field-key references are
 *    preserved; the editor then only translates the visible text.
 *
 *    Because we copy only on creation (not on sync), translated text is free
 *    to diverge from the source afterwards.
 *
 *    Caveats handed to the editor after seeding:
 *      - The copied `project_catalog` link still points at the source-language
 *        catalog; repoint it to the translated catalog if one exists.
 *      - ACF link/select values stored as "post:N" / "term:N" point at
 *        source-language targets and may need re-selecting.
 * ------------------------------------------------------------------------- */
add_filter('pll_copy_post_metas', function ($keys, $sync, $from, $to, $lang) {
    // Only seed on the initial copy; never overwrite an existing translation.
    if ($sync) {
        return $keys;
    }

    $post = get_post($from);
    if (!$post || !in_array($post->post_type, wow_pll_translatable_post_types(), true)) {
        return $keys;
    }

    foreach (array_keys(get_post_meta($from)) as $meta_key) {
        // Skip editor-lock bookkeeping (matches the theme's Duplicate routine).
        if ($meta_key === '_edit_lock' || $meta_key === '_edit_last') {
            continue;
        }
        $keys[] = $meta_key;
    }

    return array_values(array_unique($keys));
}, 20, 5);
