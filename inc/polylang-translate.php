<?php
/**
 * Shared AI-translation engine for Polylang, used by BOTH:
 *   - the CLI tool  bin/wow-i18n.php  (bulk export/import/strings)
 *   - the admin row action "AI → <lang>" on the pages/projects/catalogs lists
 *
 * Translates the TEXT sub-fields of ACF content (whitelist below) into a
 * Polylang translation (created as a draft), preserving layout, media,
 * [wow_diamond], brand names and HTML. Phone/email/links/images untouched.
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ACF text sub-field names that get translated (everything else is left as-is). */
const WOW_I18N_TEXT_FIELDS = [
    'hero_subtitle_top', 'hero_title', 'hero_subtitle_bottom',
    'specialise_title_1', 'specialise_title_2', 'specialise_desc',
    'about_title_1', 'about_title_2', 'about_marquee_top', 'about_marquee_bottom', 'about_text',
    'happen_title', 'happen_desc',
    'instagram_title', 'instagram_desc',
    'dream_bg_line1', 'dream_bg_line2',
    'faq_title',
    'archive_hero_title', 'archive_hero_subtitle',
    'project_hero_title', 'project_hero_desc', 'project_read_more_text',
    'project_gallery_title', 'project_gallery_desc', 'project_gallery_btn_text',
    // generic repeater leaves (cards / slides / faq items / desc slider)
    'title', 'title_top', 'button_text', 'country', 'description', 'question', 'answer', 'text',
];

const WOW_I18N_POST_TYPES = ['page', 'post', 'wedding_project', 'project_catalog'];

/* ------------------------------------------------------------------ *
 * Collection
 * ------------------------------------------------------------------ */

/** Recursively collect translatable {path, en} leaves from an ACF field tree. */
function wow_i18n_collect($value, array $path, array &$items) {
    if (is_array($value)) {
        // Skip ACF media/image/file/link arrays — their inner title/description
        // are attachment/link metadata, not page copy.
        if (isset($value['url']) || isset($value['sizes']) || isset($value['mime_type']) || isset($value['filename'])) {
            return;
        }
        foreach ($value as $k => $v) {
            if ($k === 'acf_fc_layout') {
                continue;
            }
            wow_i18n_collect($v, array_merge($path, [$k]), $items);
        }
        return;
    }
    $leaf = end($path);
    if (is_string($value) && trim($value) !== '' && in_array($leaf, WOW_I18N_TEXT_FIELDS, true)) {
        $items[] = ['path' => $path, 'en' => $value];
    }
}

/* ------------------------------------------------------------------ *
 * Translation engines (LLM + DeepL)
 * ------------------------------------------------------------------ */

function wow_i18n_llm_system($target_name = 'Russian') {
    return "You are a professional English to {$target_name} translator for the website of a luxury wedding and events agency (brand: Golden5Event). "
        . "Produce natural, fluent, elegant {$target_name} suitable for an upscale brand voice.\n"
        . "Rules:\n"
        . "- Keep ALL HTML tags and attributes exactly as-is; translate only the human-visible text between them.\n"
        . "- NEVER translate or alter: the literal token [wow_diamond]; brand/product names (Golden5Event, Mux, Instagram, Facebook); URLs; email addresses; phone numbers.\n"
        . "- Translate well-known place names to their standard {$target_name} forms.\n"
        . "- Preserve meaning exactly. Do NOT add, drop, summarize or reorder content.\n"
        . "- The input is a JSON object {\"id\": \"english text\"}. Return ONLY a JSON object {\"id\": \"{$target_name} text\"} with the SAME ids and no surrounding prose or code fences.";
}

/** Translate id=>text via an LLM. Returns id=>translation. */
function wow_i18n_llm(array $items, $key, $provider, $model, $target_name = 'Russian') {
    if (empty($items)) {
        return [];
    }
    $system = wow_i18n_llm_system($target_name);
    $user   = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($provider === 'anthropic') {
        $resp = wp_remote_post('https://api.anthropic.com/v1/messages', [
            'timeout' => 180,
            'headers' => ['x-api-key' => $key, 'anthropic-version' => '2023-06-01', 'content-type' => 'application/json'],
            'body'    => json_encode(['model' => $model, 'max_tokens' => 16000, 'system' => $system, 'messages' => [['role' => 'user', 'content' => $user]]]),
        ]);
        if (is_wp_error($resp)) { error_log('wow-i18n LLM: ' . $resp->get_error_message()); return []; }
        $b = json_decode(wp_remote_retrieve_body($resp), true);
        if (isset($b['error'])) { error_log('wow-i18n LLM API: ' . ($b['error']['message'] ?? 'unknown')); return []; }
        $text = $b['content'][0]['text'] ?? '';
    } else {
        $resp = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'timeout' => 180,
            'headers' => ['Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json'],
            'body'    => json_encode(['model' => $model, 'temperature' => 0.2, 'response_format' => ['type' => 'json_object'], 'messages' => [['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => $user]]]),
        ]);
        if (is_wp_error($resp)) { error_log('wow-i18n LLM: ' . $resp->get_error_message()); return []; }
        $b = json_decode(wp_remote_retrieve_body($resp), true);
        if (isset($b['error'])) { error_log('wow-i18n LLM API: ' . ($b['error']['message'] ?? 'unknown')); return []; }
        $text = $b['choices'][0]['message']['content'] ?? '';
    }

    $text = trim((string) $text);
    $text = preg_replace('~^```(?:json)?\s*|\s*```$~m', '', $text);
    $out  = json_decode($text, true);
    if (!is_array($out) && preg_match('~\{.*\}~s', $text, $m)) {
        $out = json_decode($m[0], true);
    }
    return is_array($out) ? $out : [];
}

/** Translate a list of strings via DeepL (HTML-aware). Returns values in order. */
function wow_i18n_deepl(array $texts, $key, $target = 'RU') {
    if (empty($texts)) {
        return [];
    }
    $endpoint = (strpos($key, ':fx') !== false) ? 'https://api-free.deepl.com/v2/translate' : 'https://api.deepl.com/v2/translate';
    $resp = wp_remote_post($endpoint, ['timeout' => 60, 'body' => [
        'auth_key' => $key, 'text' => array_values($texts), 'target_lang' => $target, 'source_lang' => 'EN',
        'tag_handling' => 'html', 'preserve_formatting' => '1',
    ]]);
    if (is_wp_error($resp)) { error_log('wow-i18n DeepL: ' . $resp->get_error_message()); return []; }
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    if (empty($data['translations'])) { return []; }
    return array_map(function ($t) { return $t['text']; }, $data['translations']);
}

/**
 * Translate an id=>text map in batches. $engine: key, provider, model, deepl,
 * target_code, target_name. Returns id=>translation.
 */
function wow_i18n_translate_texts(array $texts, array $engine) {
    if (empty($texts)) {
        return [];
    }
    $results = [];
    $batch = [];
    $chars = 0;
    $flush = function () use (&$batch, &$chars, &$results, $engine) {
        if (empty($batch)) {
            return;
        }
        if (!empty($engine['deepl'])) {
            $keys = array_keys($batch);
            foreach (wow_i18n_deepl(array_values($batch), $engine['deepl'], $engine['target_code'] ?? 'RU') as $j => $t) {
                $results[$keys[$j]] = $t;
            }
        } else {
            foreach (wow_i18n_llm($batch, $engine['key'], $engine['provider'], $engine['model'], $engine['target_name'] ?? 'Russian') as $bid => $t) {
                $results[$bid] = $t;
            }
        }
        $batch = [];
        $chars = 0;
    };
    foreach ($texts as $id => $t) {
        $batch[$id] = $t;
        $chars += strlen($t);
        if (count($batch) >= 20 || $chars >= 4000) {
            $flush();
        }
    }
    $flush();
    return $results;
}

/* ------------------------------------------------------------------ *
 * Per-post translation (create/link/seed/translate/write)
 * ------------------------------------------------------------------ */

/** Resolve the engine settings for a target language slug. */
function wow_i18n_engine($target_slug, array $opts = []) {
    $key   = $opts['key']   ?? wow_i18n_api_key();
    $deepl = $opts['deepl'] ?? (defined('WOW_DEEPL_KEY') ? WOW_DEEPL_KEY : '');
    $provider = $opts['provider'] ?? ((strpos((string) $key, 'sk-ant') === 0) ? 'anthropic' : 'openai');
    $model = $opts['model'] ?? (string) get_option('wow_translate_model', '');
    if ($model === '') {
        $model = ($provider === 'anthropic') ? 'claude-sonnet-4-6' : 'gpt-4o';
    }
    $name = $target_slug;
    if (function_exists('PLL') && PLL()->model && ($lang = PLL()->model->get_language($target_slug))) {
        $name = $lang->name ?: $target_slug;
    }
    return [
        'key' => $key, 'deepl' => $deepl, 'provider' => $provider, 'model' => $model,
        'target_code' => strtoupper($target_slug), 'target_name' => $name,
    ];
}

/** Read the API key: wp-config constant first, then the saved option. */
function wow_i18n_api_key() {
    if (defined('WOW_ANTHROPIC_KEY') && WOW_ANTHROPIC_KEY) {
        return (string) WOW_ANTHROPIC_KEY;
    }
    return (string) get_option('wow_translate_key', '');
}

/**
 * Force a post's slug to exactly $slug, bypassing wp_unique_post_slug (which
 * would append -2 etc.). A translation must keep the SAME slug as its source —
 * Polylang's /<lang>/ prefix already makes the URL unique per language.
 */
function wow_i18n_force_slug($post_id, $slug) {
    $slug = (string) $slug;
    if ($slug === '' || get_post_field('post_name', $post_id) === $slug) {
        return;
    }
    global $wpdb;
    $wpdb->update($wpdb->posts, ['post_name' => $slug], ['ID' => (int) $post_id]);
    clean_post_cache($post_id);
}

/**
 * Translate one source post into $target_slug. Creates the translation as a
 * DRAFT if missing, seeds layout/media, then writes the translated text leaves.
 * Returns ['ru_id'=>int, 'created'=>bool, 'translated'=>int, 'total'=>int] or ['error'=>string].
 */
function wow_i18n_translate_post($en_id, $target_slug, array $opts = []) {
    if (!function_exists('pll_get_post') || !function_exists('get_fields')) {
        return ['error' => 'Polylang and ACF must be active.'];
    }
    $en = get_post($en_id);
    if (!$en) {
        return ['error' => 'Source post not found.'];
    }
    $engine = wow_i18n_engine($target_slug, $opts);
    if (empty($engine['deepl']) && empty($engine['key'])) {
        return ['error' => 'No API key configured.'];
    }

    $default = pll_default_language() ?: 'en';
    global $wpdb;

    // 1. Ensure a linked translation (draft).
    $created = false;
    $ru_id = (int) pll_get_post($en_id, $target_slug);
    if (!$ru_id) {
        $parent    = $en->post_parent;
        $ru_parent = $parent ? (int) pll_get_post($parent, $target_slug) : 0;
        $ru_id = wp_insert_post([
            'post_type'    => $en->post_type,
            'post_title'   => $en->post_title,
            'post_name'    => $en->post_name, // keep the Latin slug; otherwise WP makes a Cyrillic %-encoded URL from the translated title
            'post_content' => $en->post_content,
            'post_excerpt' => $en->post_excerpt,
            'post_status'  => 'draft',
            'post_parent'  => $ru_parent ?: $parent,
            'menu_order'   => $en->menu_order,
        ], true);
        if (is_wp_error($ru_id)) {
            return ['error' => 'Insert failed: ' . $ru_id->get_error_message()];
        }
        foreach (get_object_taxonomies($en->post_type) as $tax) {
            $terms = wp_get_object_terms($en_id, $tax, ['fields' => 'ids']);
            if (!is_wp_error($terms) && $terms) {
                wp_set_object_terms($ru_id, $terms, $tax);
            }
        }
        pll_set_post_language($ru_id, $target_slug);
        $tr = pll_get_post_translations($en_id);
        $tr[$default]      = $en_id;
        $tr[$target_slug]  = $ru_id;
        pll_save_post_translations($tr);
        wow_i18n_force_slug($ru_id, $en->post_name); // exact same slug as the source
        $created = true;
    }

    // 2. Seed missing meta (layout/media/field-keys) without clobbering existing.
    $rows = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", $en_id));
    foreach ($rows as $r) {
        if ($r->meta_key === '_edit_lock' || $r->meta_key === '_edit_last') {
            continue;
        }
        if (metadata_exists('post', $ru_id, $r->meta_key)) {
            continue;
        }
        $wpdb->insert($wpdb->postmeta, ['post_id' => $ru_id, 'meta_key' => $r->meta_key, 'meta_value' => $r->meta_value]);
    }

    // 3. Collect translatable text (title + ACF leaves).
    $items = [];
    if (trim($en->post_title) !== '') {
        $items[] = ['path' => ['__post_title__'], 'en' => $en->post_title];
    }
    if (trim($en->post_content) !== '') {
        $items[] = ['path' => ['__post_content__'], 'en' => $en->post_content];
    }
    $fields = get_fields($en_id);
    if (is_array($fields)) {
        foreach ($fields as $name => $val) {
            wow_i18n_collect($val, [$name], $items);
        }
    }
    if (empty($items)) {
        return ['ru_id' => $ru_id, 'created' => $created, 'translated' => 0, 'total' => 0];
    }

    // 4. Translate.
    $texts = [];
    foreach ($items as $i => $it) {
        $texts[$i] = $it['en'];
    }
    $translations = wow_i18n_translate_texts($texts, $engine);

    // 5. Write (path joins to the exact ACF meta key).
    $wrote = 0;
    $newTitle = null;
    $newContent = null;
    foreach ($items as $i => $it) {
        $ru = isset($translations[$i]) ? trim((string) $translations[$i]) : '';
        if ($ru === '') {
            continue;
        }
        $p0 = $it['path'][0] ?? '';
        if ($p0 === '__post_title__') {
            $newTitle = $translations[$i];
            continue;
        }
        if ($p0 === '__post_content__') {
            $newContent = $translations[$i];
            continue;
        }
        update_post_meta($ru_id, implode('_', $it['path']), wp_slash($translations[$i]));
        $wrote++;
    }
    $postarr = ['ID' => $ru_id];
    if ($newTitle !== null) {
        $postarr['post_title'] = wp_slash($newTitle);
    }
    if ($newContent !== null) {
        $postarr['post_content'] = wp_slash($newContent);
        $wrote++;
    }
    if (count($postarr) > 1) {
        wp_update_post($postarr);
    }

    return ['ru_id' => $ru_id, 'created' => $created, 'translated' => $wrote, 'total' => count($items)];
}

/* ------------------------------------------------------------------ *
 * Admin UI: settings field + row action + handler
 * ------------------------------------------------------------------ */

if (is_admin()) {

    // Settings → AI Translate (API key + optional model).
    add_action('admin_menu', function () {
        add_options_page('AI Translate', 'AI Translate', 'manage_options', 'wow-ai-translate', 'wow_ai_translate_settings_page');
    });
    add_action('admin_init', function () {
        register_setting('wow_ai_translate', 'wow_translate_key');
        register_setting('wow_ai_translate', 'wow_translate_model');
    });

    function wow_ai_translate_settings_page() {
        $key_const = defined('WOW_ANTHROPIC_KEY') && WOW_ANTHROPIC_KEY;
        ?>
        <div class="wrap">
            <h1>AI Translate</h1>
            <p>Translate pages/projects into the other Polylang languages with AI. Hover a post in the list and click <strong>&ldquo;AI &rarr; RU&rdquo;</strong>.</p>
            <?php if ($key_const) : ?>
                <p><em>The key is defined in <code>wp-config.php</code> (<code>WOW_ANTHROPIC_KEY</code>) — the field below is ignored.</em></p>
            <?php endif; ?>
            <form method="post" action="options.php">
                <?php settings_fields('wow_ai_translate'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="wow_translate_key">Anthropic / OpenAI API key</label></th>
                        <td>
                            <input type="password" id="wow_translate_key" name="wow_translate_key" class="regular-text"
                                   value="<?php echo esc_attr(get_option('wow_translate_key', '')); ?>" autocomplete="off" <?php disabled($key_const); ?>>
                            <p class="description">A <code>sk-ant-…</code> key = Anthropic, otherwise OpenAI. More secure: define <code>WOW_ANTHROPIC_KEY</code> in <code>wp-config.php</code>.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wow_translate_model">Model (optional)</label></th>
                        <td>
                            <input type="text" id="wow_translate_model" name="wow_translate_model" class="regular-text"
                                   value="<?php echo esc_attr(get_option('wow_translate_model', '')); ?>" placeholder="claude-sonnet-4-6">
                            <p class="description">Leave blank for the default (claude-sonnet-4-6 / gpt-4o). Cheaper: <code>claude-haiku-4-5-20251001</code>.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    // Row action "AI → <LANG>" on translatable post lists (source-language posts only).
    $wow_row_actions = function ($actions, $post) {
        if (!current_user_can('edit_posts') || !function_exists('pll_default_language')) {
            return $actions;
        }
        if (!in_array($post->post_type, wow_pll_translatable_post_types(), true)) {
            return $actions;
        }
        $default = pll_default_language();
        if (pll_get_post_language($post->ID) !== $default) {
            return $actions; // only offer on the source-language post
        }
        foreach (pll_languages_list() as $slug) {
            if ($slug === $default) {
                continue;
            }
            $url = wp_nonce_url(
                admin_url('admin-post.php?action=wow_ai_translate&post=' . $post->ID . '&lang=' . $slug),
                'wow_ai_translate_' . $post->ID
            );
            $actions['wow_ai_' . $slug] = '<a href="' . esc_url($url) . '">' . esc_html('AI → ' . strtoupper($slug)) . '</a>';
        }
        return $actions;
    };
    add_filter('page_row_actions', $wow_row_actions, 10, 2);
    add_filter('post_row_actions', $wow_row_actions, 10, 2);

    // Handler. NOTE: admin-post.php fires `admin_post_{action}` (not admin_action_).
    add_action('admin_post_wow_ai_translate', function () {
        // Single-page translation makes several API calls — give it room so the
        // web request (mod_fcgid, often 128M / 30s) does not die with a blank page.
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        $post_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
        $lang    = isset($_GET['lang']) ? sanitize_key($_GET['lang']) : '';
        $uid     = get_current_user_id();
        $back    = wp_get_referer() ?: admin_url('edit.php');

        $fail = function ($msg) use ($uid, $back) {
            set_transient('wow_ai_notice_' . $uid, ['type' => 'error', 'msg' => $msg], 120);
            wp_safe_redirect($back);
            exit;
        };

        if (!$post_id || !$lang) {
            $fail('Bad request (missing post or language).');
        }
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'wow_ai_translate_' . $post_id)) {
            $fail('Security check failed — reload the list and try again.');
        }
        if (!current_user_can('edit_posts')) {
            $fail('Insufficient permissions.');
        }
        if (!wow_i18n_api_key() && !(defined('WOW_DEEPL_KEY') && WOW_DEEPL_KEY)) {
            $fail('No API key set. Go to Settings → AI Translate.');
        }

        // Turn a fatal (memory/timeout) into a readable notice instead of a blank page.
        register_shutdown_function(function () use ($uid) {
            $e = error_get_last();
            if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR, E_RECOVERABLE_ERROR], true)) {
                set_transient('wow_ai_notice_' . $uid, [
                    'type' => 'error',
                    'msg'  => 'Translation crashed: ' . $e['message'] . ' (' . basename($e['file']) . ':' . $e['line'] . ')',
                ], 120);
            }
        });

        try {
            $res = wow_i18n_translate_post($post_id, $lang, []);
        } catch (\Throwable $e) {
            error_log('wow_ai_translate: ' . $e->getMessage());
            $fail('Error: ' . $e->getMessage());
        }

        if (!empty($res['error'])) {
            $fail('Translation error: ' . $res['error']);
        }
        set_transient('wow_ai_notice_' . $uid, [
            'type' => 'success',
            'msg'  => sprintf('Translated %d/%d fields → %s. Draft #%d — review and publish.', $res['translated'], $res['total'], strtoupper($lang), $res['ru_id']),
        ], 60);
        wp_safe_redirect(admin_url('post.php?post=' . $res['ru_id'] . '&action=edit'));
        exit;
    });

    // Notice.
    add_action('admin_notices', function () {
        $uid = get_current_user_id();
        $n = get_transient('wow_ai_notice_' . $uid);
        if (!$n) {
            return;
        }
        delete_transient('wow_ai_notice_' . $uid);
        printf('<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
            $n['type'] === 'error' ? 'error' : 'success', esc_html($n['msg']));
    });
}
