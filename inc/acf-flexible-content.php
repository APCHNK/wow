<?php
/**
 * ACF Flexible Content groups for page builder.
 *
 * group_page_sections  (page)            — 8 common blocks
 * group_project_sections (wedding_project) — 3 project blocks + 8 common blocks
 */

if (!defined('ABSPATH')) exit;

// Build sub-field list for all 8 common blocks. Keys get a unique $prefix so
// the same block schema can live inside both page_sections and project_sections
// without ACF field_key collisions.
function wow_fc_common_layouts($prefix) {
    $p = $prefix;
    return [
        // 1. Hero
        [
            'key' => 'layout_' . $p . '_hero',
            'name' => 'hero',
            'label' => 'Hero',
            'display' => 'block',
            'sub_fields' => [
                ['key' => 'field_' . $p . '_hero_subtitle_top', 'label' => 'Subtitle Top', 'name' => 'hero_subtitle_top', 'type' => 'text', 'default_value' => 'WE CREATE'],
                ['key' => 'field_' . $p . '_hero_title', 'label' => 'Title', 'name' => 'hero_title', 'type' => 'text', 'default_value' => 'WOW EVENT'],
                ['key' => 'field_' . $p . '_hero_subtitle_bottom', 'label' => 'Subtitle Bottom', 'name' => 'hero_subtitle_bottom', 'type' => 'text', 'default_value' => 'IN THE WORLD'],
                ['key' => 'field_' . $p . '_hero_video', 'label' => 'Mux Playback ID', 'name' => 'hero_video', 'type' => 'text', 'instructions' => 'Paste Mux Playback ID from dashboard.mux.com'],
            ],
        ],
        // 2. Specialise
        [
            'key' => 'layout_' . $p . '_specialise',
            'name' => 'specialise',
            'label' => 'Specialise',
            'display' => 'block',
            'sub_fields' => [
                ['key' => 'field_' . $p . '_specialise_title_1', 'label' => 'Title Line 1', 'name' => 'specialise_title_1', 'type' => 'text', 'default_value' => 'What'],
                ['key' => 'field_' . $p . '_specialise_title_2', 'label' => 'Title Line 2', 'name' => 'specialise_title_2', 'type' => 'text', 'default_value' => 'We Specialise in'],
                ['key' => 'field_' . $p . '_specialise_desc', 'label' => 'Description', 'name' => 'specialise_desc', 'type' => 'textarea', 'rows' => 2],
                [
                    'key' => 'field_' . $p . '_specialise_cards',
                    'label' => 'Cards',
                    'name' => 'specialise_cards',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => 'Add Card',
                    'sub_fields' => [
                        ['key' => 'field_' . $p . '_scard_link', 'label' => 'Link', 'name' => 'link', 'type' => 'select', 'ui' => 1, 'allow_null' => 0, 'required' => 1, 'choices' => [], 'return_format' => 'value', 'instructions' => 'Pick a project category or project — the button will link there.'],
                        ['key' => 'field_' . $p . '_scard_image', 'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium'],
                        ['key' => 'field_' . $p . '_scard_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text'],
                        ['key' => 'field_' . $p . '_scard_button_text', 'label' => 'Button Text', 'name' => 'button_text', 'type' => 'text', 'default_value' => 'Show more'],
                    ],
                ],
            ],
        ],
        // 3. About
        [
            'key' => 'layout_' . $p . '_about',
            'name' => 'about',
            'label' => 'About',
            'display' => 'block',
            'sub_fields' => [
                ['key' => 'field_' . $p . '_about_title_1', 'label' => 'Title Line 1', 'name' => 'about_title_1', 'type' => 'text', 'default_value' => 'Who'],
                ['key' => 'field_' . $p . '_about_title_2', 'label' => 'Title Line 2', 'name' => 'about_title_2', 'type' => 'text', 'default_value' => 'We Are'],
                ['key' => 'field_' . $p . '_about_marquee_top', 'label' => 'Marquee Top Text', 'name' => 'about_marquee_top', 'type' => 'text'],
                ['key' => 'field_' . $p . '_about_marquee_bottom', 'label' => 'Marquee Bottom Text', 'name' => 'about_marquee_bottom', 'type' => 'text'],
                ['key' => 'field_' . $p . '_about_text', 'label' => 'About Text', 'name' => 'about_text', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 0],
                ['key' => 'field_' . $p . '_about_gallery', 'label' => 'Slider Images', 'name' => 'about_gallery', 'type' => 'gallery', 'return_format' => 'array', 'preview_size' => 'medium', 'min' => 0, 'max' => 20],
                ['key' => 'field_' . $p . '_about_bg', 'label' => 'Background Image', 'name' => 'about_bg', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium'],
            ],
        ],
        // 4. Video
        [
            'key' => 'layout_' . $p . '_video',
            'name' => 'video',
            'label' => 'Video Section',
            'display' => 'block',
            'sub_fields' => [
                ['key' => 'field_' . $p . '_video_section_file', 'label' => 'Mux Playback ID', 'name' => 'video_section_file', 'type' => 'text', 'instructions' => 'Paste Mux Playback ID from dashboard.mux.com'],
            ],
        ],
        // 5. Happen
        [
            'key' => 'layout_' . $p . '_happen',
            'name' => 'happen',
            'label' => 'Happen',
            'display' => 'block',
            'sub_fields' => [
                ['key' => 'field_' . $p . '_happen_title', 'label' => 'Title', 'name' => 'happen_title', 'type' => 'text', 'default_value' => 'Make it Happen!'],
                ['key' => 'field_' . $p . '_happen_desc', 'label' => 'Description', 'name' => 'happen_desc', 'type' => 'textarea', 'rows' => 3],
                [
                    'key' => 'field_' . $p . '_happen_slides',
                    'label' => 'Slides',
                    'name' => 'happen_slides',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => 'Add Slide',
                    'sub_fields' => [
                        ['key' => 'field_' . $p . '_happen_slide_image', 'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium'],
                        ['key' => 'field_' . $p . '_happen_slide_country', 'label' => 'Country', 'name' => 'country', 'type' => 'text'],
                        ['key' => 'field_' . $p . '_happen_slide_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text'],
                    ],
                ],
            ],
        ],
        // 6. Instagram
        [
            'key' => 'layout_' . $p . '_instagram',
            'name' => 'instagram',
            'label' => 'Instagram',
            'display' => 'block',
            'sub_fields' => [
                ['key' => 'field_' . $p . '_instagram_title', 'label' => 'Title', 'name' => 'instagram_title', 'type' => 'text', 'default_value' => 'Follow us on Instagram'],
                ['key' => 'field_' . $p . '_instagram_desc', 'label' => 'Description', 'name' => 'instagram_desc', 'type' => 'textarea', 'rows' => 2],
                ['key' => 'field_' . $p . '_instagram_link', 'label' => 'Instagram URL', 'name' => 'instagram_link', 'type' => 'url'],
            ],
        ],
        // 7. Dream
        [
            'key' => 'layout_' . $p . '_dream',
            'name' => 'dream',
            'label' => 'Dream',
            'display' => 'block',
            'sub_fields' => [
                ['key' => 'field_' . $p . '_dream_bg_line1', 'label' => 'Background Text Line 1', 'name' => 'dream_bg_line1', 'type' => 'text', 'default_value' => 'DREAM'],
                ['key' => 'field_' . $p . '_dream_bg_line2', 'label' => 'Background Text Line 2', 'name' => 'dream_bg_line2', 'type' => 'text', 'default_value' => 'EVENT'],
                [
                    'key' => 'field_' . $p . '_dream_slides',
                    'label' => 'Slides',
                    'name' => 'dream_slides',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => 'Add Slide',
                    'sub_fields' => [
                        ['key' => 'field_' . $p . '_dream_slide_title', 'label' => 'Title', 'name' => 'title', 'type' => 'text'],
                        ['key' => 'field_' . $p . '_dream_slide_desc', 'label' => 'Description', 'name' => 'description', 'type' => 'textarea', 'rows' => 4],
                    ],
                ],
            ],
        ],
        // 8. FAQ
        [
            'key' => 'layout_' . $p . '_faq',
            'name' => 'faq',
            'label' => 'FAQ',
            'display' => 'block',
            'sub_fields' => [
                ['key' => 'field_' . $p . '_faq_title', 'label' => 'Page Title', 'name' => 'faq_title', 'type' => 'text', 'default_value' => 'FREQUENTLY ASKED QUESTIONS'],
                [
                    'key' => 'field_' . $p . '_faq_items',
                    'label' => 'FAQ Items',
                    'name' => 'faq_items',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => 'Add Question',
                    'sub_fields' => [
                        ['key' => 'field_' . $p . '_faq_question', 'label' => 'Question', 'name' => 'question', 'type' => 'text'],
                        ['key' => 'field_' . $p . '_faq_answer', 'label' => 'Answer', 'name' => 'answer', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 0],
                    ],
                ],
            ],
        ],
        // 9. Archive Hero — image + title + subtitle (e.g. catalog page top).
        [
            'key' => 'layout_' . $p . '_archive_hero',
            'name' => 'archive_hero',
            'label' => 'Archive Hero (image + title + subtitle)',
            'display' => 'block',
            'sub_fields' => [
                ['key' => 'field_' . $p . '_archive_hero_title', 'label' => 'Title', 'name' => 'archive_hero_title', 'type' => 'text', 'instructions' => 'Use [wow_diamond] shortcode to insert decorative diamond'],
                ['key' => 'field_' . $p . '_archive_hero_subtitle', 'label' => 'Subtitle', 'name' => 'archive_hero_subtitle', 'type' => 'text'],
                ['key' => 'field_' . $p . '_archive_hero_image', 'label' => 'Image', 'name' => 'archive_hero_image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium'],
            ],
        ],
        // 10. Catalog Grid — auto-renders children categories or projects depending on context.
        [
            'key' => 'layout_' . $p . '_catalog_grid',
            'name' => 'catalog_grid',
            'label' => 'Catalog Grid (auto — children or projects)',
            'display' => 'block',
            'sub_fields' => [
                [
                    'key' => 'field_' . $p . '_catalog_grid_note',
                    'label' => '',
                    'name' => '',
                    'type' => 'message',
                    'message' => 'On a parent category page this renders subcategory cards automatically. On a leaf category page it renders project cards — leave "Project Cards" empty for an auto list of all projects, or fill it to pick projects manually and override their look.',
                ],
                [
                    'key' => 'field_' . $p . '_catalog_grid_cards',
                    'label' => 'Cards (optional)',
                    'name' => 'cards',
                    'type' => 'repeater',
                    'layout' => 'block',
                    'button_label' => 'Add Card',
                    'instructions' => 'Leave empty to auto-list children (on a parent category) or projects (on a leaf category). Fill to manually build cards with a free-form link.',
                    'sub_fields' => [
                        ['key' => 'field_' . $p . '_card_link', 'label' => 'Link', 'name' => 'link', 'type' => 'select', 'ui' => 1, 'allow_null' => 0, 'required' => 1, 'choices' => [], 'return_format' => 'value', 'instructions' => 'Pick a project category or project — the button will link there.'],
                        ['key' => 'field_' . $p . '_card_image', 'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'url', 'preview_size' => 'medium'],
                        ['key' => 'field_' . $p . '_card_title_top', 'label' => 'Title', 'name' => 'title_top', 'type' => 'text'],
                        ['key' => 'field_' . $p . '_card_country', 'label' => 'Country', 'name' => 'country', 'type' => 'text'],
                        [
                            'key' => 'field_' . $p . '_card_desc_slider',
                            'label' => 'Description Slides',
                            'name' => 'desc_slider',
                            'type' => 'repeater',
                            'layout' => 'block',
                            'button_label' => 'Add Slide',
                            'sub_fields' => [
                                ['key' => 'field_' . $p . '_card_desc_text', 'label' => 'Text', 'name' => 'text', 'type' => 'textarea', 'rows' => 2],
                            ],
                        ],
                        ['key' => 'field_' . $p . '_card_button_text', 'label' => 'Button Text', 'name' => 'button_text', 'type' => 'text', 'default_value' => 'Show more'],
                    ],
                ],
            ],
        ],
    ];
}

// Project-only block layouts (3) — used inside project_sections only.
function wow_fc_project_layouts() {
    return [
        [
            'key' => 'layout_pr_project_hero',
            'name' => 'project_hero',
            'label' => 'Project Hero',
            'display' => 'block',
            'sub_fields' => [
                ['key' => 'field_pr_project_hero_image', 'label' => 'Hero Image', 'name' => 'project_hero_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium'],
                ['key' => 'field_pr_project_hero_title', 'label' => 'Hero Title', 'name' => 'project_hero_title', 'type' => 'text'],
                ['key' => 'field_pr_project_hero_desc', 'label' => 'Hero Description', 'name' => 'project_hero_desc', 'type' => 'textarea', 'rows' => 3],
            ],
        ],
        [
            'key' => 'layout_pr_project_content',
            'name' => 'project_content',
            'label' => 'Project Content (Read More)',
            'display' => 'block',
            'sub_fields' => [
                ['key' => 'field_pr_project_read_more_text', 'label' => 'Read More Text', 'name' => 'project_read_more_text', 'type' => 'textarea', 'rows' => 5],
            ],
        ],
        [
            'key' => 'layout_pr_project_gallery',
            'name' => 'project_gallery',
            'label' => 'Project Gallery',
            'display' => 'block',
            'sub_fields' => [
                ['key' => 'field_pr_project_gallery_title', 'label' => 'Gallery Title', 'name' => 'project_gallery_title', 'type' => 'text'],
                ['key' => 'field_pr_project_gallery_desc', 'label' => 'Gallery Description', 'name' => 'project_gallery_desc', 'type' => 'textarea'],
                ['key' => 'field_pr_project_gallery_btn_text', 'label' => 'Gallery Button Text', 'name' => 'project_gallery_btn_text', 'type' => 'text', 'default_value' => 'CONTACT US'],
                ['key' => 'field_pr_project_gallery_btn_link', 'label' => 'Gallery Button Link', 'name' => 'project_gallery_btn_link', 'type' => 'url'],
                ['key' => 'field_pr_project_gallery', 'label' => 'Photos', 'name' => 'project_gallery', 'type' => 'gallery', 'return_format' => 'array', 'preview_size' => 'medium'],
            ],
        ],
    ];
}

function wow_register_flexible_content_groups() {
    if (!function_exists('acf_add_local_field_group')) return;

    // Page sections — for post_type = page.
    acf_add_local_field_group([
        'key' => 'group_page_sections',
        'title' => 'Page Sections',
        'fields' => [
            [
                'key' => 'field_page_sections',
                'label' => 'Page Sections',
                'name' => 'page_sections',
                'type' => 'flexible_content',
                'button_label' => 'Add Section',
                'layouts' => wow_fc_common_layouts('pg'),
            ],
        ],
        'location' => [
            [
                ['param' => 'post_type', 'operator' => '==', 'value' => 'page'],
            ],
        ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
    ]);

    // Project sections — for post_type = wedding_project.
    // 3 project-specific blocks + 8 common blocks.
    $project_layouts = array_merge(wow_fc_project_layouts(), wow_fc_common_layouts('pr'));

    acf_add_local_field_group([
        'key' => 'group_project_sections',
        'title' => 'Project Page Sections',
        'fields' => [
            [
                'key' => 'field_project_sections',
                'label' => 'Project Page Sections',
                'name' => 'project_sections',
                'type' => 'flexible_content',
                'button_label' => 'Add Section',
                'layouts' => $project_layouts,
            ],
        ],
        'location' => [
            [
                ['param' => 'post_type', 'operator' => '==', 'value' => 'wedding_project'],
            ],
        ],
        'menu_order' => 1,
        'position' => 'normal',
        'style' => 'default',
    ]);

    // Catalog picker on wedding_project edit screen. Primary source of truth
    // for "which catalog does this project belong to" — a save_post hook
    // mirrors the value back onto the project_category taxonomy so existing
    // tax_query-based code still works.
    acf_add_local_field_group([
        'key' => 'group_wedding_project_catalog',
        'title' => 'Catalog',
        'fields' => [[
            'key' => 'field_wp_project_catalog',
            'label' => 'Catalog',
            'name' => 'project_catalog',
            'type' => 'post_object',
            'post_type' => ['project_catalog'],
            'return_format' => 'id',
            'allow_null' => 1,
            'multiple' => 0,
            'instructions' => 'The project appears under this catalog on the site and its URL is /<catalog-path>/<project-slug>/.',
        ]],
        'location' => [[[ 'param' => 'post_type', 'operator' => '==', 'value' => 'wedding_project' ]]],
        'position' => 'side',
        'menu_order' => 0,
    ]);

    // Category sections — for the project_catalog CPT (formerly the project_category
    // taxonomy term edit screen). The field name stays 'category_sections' so the
    // migrated postmeta keys continue to match the ACF schema.
    acf_add_local_field_group([
        'key' => 'group_category_sections',
        'title' => 'Catalog Page Sections',
        'fields' => [
            [
                'key' => 'field_category_sections',
                'label' => 'Catalog Page Sections',
                'name' => 'category_sections',
                'type' => 'flexible_content',
                'button_label' => 'Add Section',
                'layouts' => wow_fc_common_layouts('cat'),
            ],
        ],
        'location' => [
            [
                ['param' => 'post_type', 'operator' => '==', 'value' => 'project_catalog'],
            ],
        ],
        'menu_order' => 2,
        'position' => 'normal',
        'style' => 'default',
    ]);
}
add_action('acf/init', 'wow_register_flexible_content_groups');

/**
 * Populate the card "Link" select with all project categories + wedding projects.
 * Stored value is "term:<id>" or "post:<id>"; wow_resolve_link() turns it back into a URL.
 *
 * Only the link sub-fields inside our card repeaters carry these choices — matched
 * by key suffix so the filter doesn't leak to unrelated fields named "link".
 */
function wow_fc_populate_link_choices($field) {
    $key = $field['key'] ?? '';
    $suffixes = ['_card_link', '_scard_link'];
    $match = false;
    foreach ($suffixes as $suf) {
        if (substr($key, -strlen($suf)) === $suf) { $match = true; break; }
    }
    if (!$match) return $field;

    $choices = [];

    $catalogs = get_posts([
        'post_type' => 'project_catalog',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    foreach ($catalogs as $c) {
        $choices['post:' . $c->ID] = 'Catalog — ' . $c->post_title;
    }

    $projects = get_posts([
        'post_type' => 'wedding_project',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    foreach ($projects as $p) {
        $choices['post:' . $p->ID] = 'Project — ' . $p->post_title;
    }

    $field['choices'] = $choices;
    return $field;
}
add_filter('acf/load_field/name=link', 'wow_fc_populate_link_choices');

/**
 * Flexible-content sub-field accessor that works in both contexts:
 *   - inside a have_rows()/the_row() loop (page.php, front-page.php) — delegates to get_sub_field()
 *   - when a parent template iterated the flexible array manually and passed the current
 *     row via set_query_var('wow_current_section', $row) — pulls the key straight from $row
 *
 * Block template files should use this instead of get_sub_field() so they work on every
 * surface (pages, projects, catalogs).
 */
function wow_field($name, $default = null) {
    $row = get_query_var('wow_current_section');
    if (is_array($row) && array_key_exists($name, $row)) {
        $v = $row[$name];
        return ($v === null || $v === false || $v === '') ? $default : $v;
    }
    if (function_exists('get_sub_field')) {
        $v = get_sub_field($name);
        if ($v !== null && $v !== false && $v !== '') return $v;
    }
    return $default;
}

/**
 * Turn a stored card-link value ("term:N" or "post:N") into a real URL.
 * Accepts legacy absolute URLs too — those pass through unchanged.
 */
function wow_resolve_link($value) {
    $value = (string) $value;
    if ($value === '') return '';
    if (strpos($value, 'term:') === 0) {
        $tid = (int) substr($value, 5);
        if ($tid) {
            $url = get_term_link($tid, 'project_category');
            return is_wp_error($url) ? '' : $url;
        }
        return '';
    }
    if (strpos($value, 'post:') === 0) {
        $pid = (int) substr($value, 5);
        return $pid ? (string) get_permalink($pid) : '';
    }
    return $value; // treat as plain URL (legacy)
}

/**
 * Normalize an image alt string.
 *
 * Card titles in ACF are often written as fragments like "Event Management in"
 * or "Hostess in" — the trailing preposition reads naturally in the layout but
 * produces a dangling alt ("Image: Event Management in") for screen readers
 * and text-only views. We trim trailing connector words and fall back to the
 * provided context when the result is empty.
 */
function wow_alt($value, ...$fallbacks) {
    $clean = static function ($s) {
        $s = trim(wp_strip_all_tags((string) $s));
        $s = preg_replace('/\s+(in|at|for|of|on|with|by|to|the|a|an|and|or)\s*$/i', '', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    };
    $out = $clean($value);
    if ($out !== '') return $out;
    foreach ($fallbacks as $fb) {
        $out = $clean($fb);
        if ($out !== '') return $out;
    }
    return '';
}
