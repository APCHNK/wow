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
                ['key' => 'field_' . $p . '_specialise_categories', 'label' => 'Slider Categories', 'name' => 'specialise_categories', 'type' => 'taxonomy', 'taxonomy' => 'project_category', 'field_type' => 'multi_select', 'allow_null' => 1, 'return_format' => 'id', 'instructions' => 'Select and order categories for the slider. Leave empty to show all top-level categories.'],
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
}
add_action('acf/init', 'wow_register_flexible_content_groups');
