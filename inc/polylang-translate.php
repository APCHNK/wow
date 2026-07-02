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

/**
 * Yoast SEO meta keys that get translated too, so the RU search snippet and
 * social cards are in Russian instead of inheriting the English source.
 * Yoast template variables like %%sitename%% are preserved by the engine.
 */
function wow_i18n_yoast_meta_keys() {
    return apply_filters('wow_i18n_yoast_meta_keys', [
        '_yoast_wpseo_title',
        '_yoast_wpseo_metadesc',
        '_yoast_wpseo_opengraph-title',
        '_yoast_wpseo_opengraph-description',
        '_yoast_wpseo_twitter-title',
        '_yoast_wpseo_twitter-description',
    ]);
}

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

/**
 * Canonical EN => target-language term map, so the translator renders the
 * agency's service terms IDENTICALLY everywhere (no more "мицва" vs "митцва",
 * hyphen/casing drift). Derived from the site's OWN top-level project_catalog
 * titles (the authoritative names) + a few sub-terms that are not full category
 * titles + any admin-added pairs.
 */
function wow_i18n_glossary($target_slug) {
    $pairs = [];

    // 1) Authoritative service names = the site's top-level catalog titles.
    if (function_exists('pll_get_post') && function_exists('pll_get_post_language')) {
        $default = function_exists('pll_default_language') ? pll_default_language() : 'en';
        // Only TOP-LEVEL catalogs — these are the authoritative service names.
        // Leaf catalogs (city/country combos) carry the very inconsistencies we
        // are fixing, so they must not seed the glossary.
        $cats = get_posts([
            'post_type'        => 'project_catalog',
            'post_parent'      => 0,
            'posts_per_page'   => -1,
            'post_status'      => 'publish',
            'suppress_filters' => true,
        ]);
        foreach ($cats as $c) {
            if (pll_get_post_language($c->ID) !== $default) {
                continue;
            }
            $tw = pll_get_post($c->ID, $target_slug);
            if ($tw && ($t = get_post($tw)) && trim((string) $t->post_title) !== '') {
                $pairs[trim($c->post_title)] = trim($t->post_title);
            }
        }
    }

    // 2) Sub-terms that appear inside copy but are not standalone categories.
    $manual = [
        'ru' => [
            'Bar and Bat Mitzvah' => 'Бар и Бат Митцва',
            'Bar Mitzvah'         => 'Бар Митцва',
            'Bat Mitzvah'         => 'Бат Митцва',
        ],
    ];
    if (!empty($manual[$target_slug])) {
        // Category titles win over these defaults where the key is identical.
        $pairs = array_merge($manual[$target_slug], $pairs);
    }

    // 3) Admin-editable extras: option "wow_translate_glossary_<lang>", one
    //    "English = Перевод" pair per line. These override everything above.
    $extra = trim((string) get_option('wow_translate_glossary_' . $target_slug, ''));
    if ($extra !== '') {
        foreach (preg_split('/\r?\n/', $extra) as $line) {
            if (strpos($line, '=') === false) {
                continue;
            }
            [$en, $ru] = array_map('trim', explode('=', $line, 2));
            if ($en !== '' && $ru !== '') {
                $pairs[$en] = $ru;
            }
        }
    }

    return $pairs;
}

/**
 * Translation memory: EN title => target title pairs from posts ALREADY
 * translated in the SAME service branch (top-level project_catalog). Feeding
 * these to the model as reference keeps a new page's wording consistent with
 * its siblings ("Corporate Events in X" all render the same way in RU).
 */
function wow_i18n_category_memory($en_id, $target_slug, $max_pairs = 30) {
    if (!function_exists('pll_get_post') || !function_exists('pll_get_post_language')) {
        return [];
    }
    $post = get_post($en_id);
    if (!$post) {
        return [];
    }
    $default = function_exists('pll_default_language') ? (pll_default_language() ?: 'en') : 'en';

    // Resolve the top-level ancestor of a project_catalog id.
    $top_of = function ($cat_id) {
        $cat_id = (int) $cat_id;
        $guard = 0;
        while ($cat_id && ($p = get_post($cat_id)) && $p->post_parent && $guard++ < 10) {
            $cat_id = (int) $p->post_parent;
        }
        return $cat_id;
    };

    // Find sibling EN post ids in the same branch that are already translated.
    $siblings = [];
    if ($post->post_type === 'wedding_project') {
        $my_top = $top_of(get_post_meta($en_id, 'project_catalog', true));
        if (!$my_top) {
            return [];
        }
        $ids = get_posts([
            'post_type' => 'wedding_project', 'posts_per_page' => -1,
            'post_status' => 'publish', 'suppress_filters' => true, 'fields' => 'ids',
        ]);
        foreach ($ids as $pid) {
            if ($pid == $en_id || pll_get_post_language($pid) !== $default) {
                continue;
            }
            if ($top_of(get_post_meta($pid, 'project_catalog', true)) === $my_top && pll_get_post($pid, $target_slug)) {
                $siblings[] = $pid;
            }
        }
    } elseif ($post->post_type === 'project_catalog') {
        $ids = get_posts([
            'post_type' => 'project_catalog', 'post_parent' => $post->post_parent,
            'posts_per_page' => -1, 'post_status' => 'publish', 'suppress_filters' => true, 'fields' => 'ids',
        ]);
        foreach ($ids as $pid) {
            if ($pid == $en_id || pll_get_post_language($pid) !== $default) {
                continue;
            }
            if (pll_get_post($pid, $target_slug)) {
                $siblings[] = $pid;
            }
        }
    } else {
        return [];
    }

    // EN title => RU title, for siblings whose RU title was actually translated.
    $pairs = [];
    foreach ($siblings as $sid) {
        $rid = (int) pll_get_post($sid, $target_slug);
        if (!$rid) {
            continue;
        }
        $en_t = trim((string) get_post($sid)->post_title);
        $ru_t = trim((string) get_post($rid)->post_title);
        if ($en_t !== '' && $ru_t !== '' && $en_t !== $ru_t) {
            $pairs[$en_t] = $ru_t;
        }
        if (count($pairs) >= $max_pairs) {
            break;
        }
    }
    return $pairs;
}

function wow_i18n_llm_system($target_name = 'Russian', $target_slug = 'ru', array $memory = []) {
    // Tone is editable in Settings → AI Translate ("Style instructions").
    // Default deliberately asks for plain, human wording — the earlier
    // "elegant upscale brand voice" default produced overly pompous copy.
    $style = trim((string) get_option('wow_translate_style', ''));
    if ($style === '') {
        $style = "Produce natural, fluent {$target_name} the way a native copywriter would write for a website: simple, warm and human. Avoid pompous, flowery or overly formal phrasing.";
    }

    $glossary_rule = '';
    $gloss = wow_i18n_glossary($target_slug);
    if ($gloss) {
        $lines = [];
        foreach ($gloss as $en => $ru) {
            $lines[] = "\"{$en}\" = \"{$ru}\"";
        }
        $glossary_rule = "- GLOSSARY — translate these terms EXACTLY and CONSISTENTLY as given: keep the given spelling and hyphens, adjust ONLY the grammatical case/ending for {$target_name}, and match the capitalization of the ENGLISH source at each spot (capitalize when the English term is capitalized, lowercase when it is lowercase). Never invent spelling variants: " . implode('; ', $lines) . ".\n";
    }

    $memory_rule = '';
    if ($memory) {
        $lines = [];
        foreach ($memory as $en => $ru) {
            $lines[] = "\"{$en}\" = \"{$ru}\"";
        }
        $memory_rule = "- TRANSLATION MEMORY — existing {$target_name} translations of sibling pages in the SAME category. Match their wording, terminology and style so the new page is consistent with them (adapt for the specific place/name in the current text): " . implode('; ', $lines) . ".\n";
    }

    return "You are a professional English to {$target_name} translator for the website of a wedding and events agency (brand: Golden5Event). "
        . $style . "\n"
        . "Rules:\n"
        . "- Keep ALL HTML tags and attributes exactly as-is; translate only the human-visible text between them.\n"
        . "- NEVER translate or alter: the literal token [wow_diamond]; Yoast SEO variables wrapped in double percent signs such as %%sitename%%, %%title%%, %%page%% or %%sep%%; brand/product names (Golden5Event, Mux, Instagram, Facebook); URLs; email addresses; phone numbers.\n"
        . "- Translate well-known place names to their standard {$target_name} forms.\n"
        . $glossary_rule
        . $memory_rule
        . "- Preserve meaning exactly. Do NOT add, drop, summarize or reorder content.\n"
        . "- The input is a JSON object {\"id\": \"english text\"}. Return ONLY a JSON object {\"id\": \"{$target_name} text\"} with the SAME ids and no surrounding prose or code fences.";
}

/** Translate id=>text via an LLM. Returns id=>translation. */
function wow_i18n_llm(array $items, $key, $provider, $model, $target_name = 'Russian', $target_slug = 'ru', array $memory = []) {
    if (empty($items)) {
        return [];
    }
    $system = wow_i18n_llm_system($target_name, $target_slug, $memory);
    $user   = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($model === '' || $model === null) {
        $model = wow_i18n_default_model($provider);
    }

    if ($provider === 'gemini') {
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent';
        $resp = wp_remote_post($endpoint, [
            'timeout' => 180,
            'headers' => ['content-type' => 'application/json', 'x-goog-api-key' => $key],
            'body'    => json_encode([
                'system_instruction' => ['parts' => [['text' => $system]]],
                'contents'           => [['role' => 'user', 'parts' => [['text' => $user]]]],
                'generationConfig'   => ['temperature' => 0.2, 'responseMimeType' => 'application/json'],
            ]),
        ]);
        if (is_wp_error($resp)) { error_log('wow-i18n LLM: ' . $resp->get_error_message()); return []; }
        $b = json_decode(wp_remote_retrieve_body($resp), true);
        if (isset($b['error'])) { error_log('wow-i18n LLM API: ' . ($b['error']['message'] ?? 'unknown')); return []; }
        $text = $b['candidates'][0]['content']['parts'][0]['text'] ?? '';
    } elseif ($provider === 'anthropic') {
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
    // DeepL deprecated the legacy `auth_key` form-body method — the key must now
    // be sent in the Authorization header.
    $body = [
        'text' => array_values($texts), 'target_lang' => $target, 'source_lang' => 'EN',
        'tag_handling' => 'html', 'preserve_formatting' => '1',
    ];
    // Optional formality (Settings → AI Translate). prefer_* variants are safe:
    // languages that do not support formality simply ignore them.
    $formality = (string) get_option('wow_translate_formality', '');
    if ($formality === 'prefer_more' || $formality === 'prefer_less') {
        $body['formality'] = $formality;
    }
    $resp = wp_remote_post($endpoint, [
        'timeout' => 60,
        'headers' => ['Authorization' => 'DeepL-Auth-Key ' . $key],
        'body'    => $body,
    ]);
    if (is_wp_error($resp)) { error_log('wow-i18n DeepL: ' . $resp->get_error_message()); return []; }
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    if (empty($data['translations'])) {
        if (isset($data['message'])) { error_log('wow-i18n DeepL API: ' . $data['message']); }
        return [];
    }
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
            foreach (wow_i18n_llm($batch, $engine['key'], $engine['provider'], $engine['model'], $engine['target_name'] ?? 'Russian', $engine['target_slug'] ?? 'ru', $engine['memory'] ?? []) as $bid => $t) {
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
    // Each engine has its OWN key now. The selected engine decides which key and
    // provider are used; 'auto' picks the first one that has a key configured.
    $choice = $opts['engine'] ?? (string) get_option('wow_translate_engine', 'auto');

    $deepl_key = $opts['deepl'] ?? wow_i18n_deepl_key();
    $anthropic = wow_i18n_api_key();
    $openai    = wow_i18n_openai_key();
    $gemini    = wow_i18n_gemini_key();

    $deepl = '';
    $key = '';
    $provider = '';
    switch ($choice) {
        case 'deepl':     $deepl = $deepl_key; break;
        case 'anthropic': $key = $anthropic; $provider = 'anthropic'; break;
        case 'openai':    $key = $openai;    $provider = 'openai';    break;
        case 'gemini':    $key = $gemini;    $provider = 'gemini';    break;
        default: // auto — first configured key wins.
            if ($deepl_key)     { $deepl = $deepl_key; }
            elseif ($anthropic) { $key = $anthropic; $provider = 'anthropic'; }
            elseif ($openai)    { $key = $openai;    $provider = 'openai'; }
            elseif ($gemini)    { $key = $gemini;    $provider = 'gemini'; }
    }

    // Explicit opts win over the resolved values (used by callers/tests).
    if (array_key_exists('key', $opts))      { $key = $opts['key']; }
    if (array_key_exists('provider', $opts)) { $provider = $opts['provider']; }
    if ($provider === '') {
        $provider = (strpos((string) $key, 'sk-ant') === 0) ? 'anthropic' : 'openai';
    }

    $model = $opts['model'] ?? (string) get_option('wow_translate_model', '');
    if ($model === '') {
        $model = wow_i18n_default_model($provider);
    }
    $name = $target_slug;
    if (function_exists('PLL') && PLL()->model && ($lang = PLL()->model->get_language($target_slug))) {
        $name = $lang->name ?: $target_slug;
    }
    return [
        'key' => $key, 'deepl' => $deepl, 'provider' => $provider, 'model' => $model,
        'target_code' => strtoupper($target_slug), 'target_name' => $name, 'target_slug' => $target_slug,
    ];
}

/** Read the Anthropic (Claude) key: wp-config constant first, then the option. */
function wow_i18n_api_key() {
    if (defined('WOW_ANTHROPIC_KEY') && WOW_ANTHROPIC_KEY) {
        return (string) WOW_ANTHROPIC_KEY;
    }
    return (string) get_option('wow_translate_key', '');
}

/** Read the OpenAI (GPT) key: wp-config constant first, then the option. */
function wow_i18n_openai_key() {
    if (defined('WOW_OPENAI_KEY') && WOW_OPENAI_KEY) {
        return (string) WOW_OPENAI_KEY;
    }
    return (string) get_option('wow_translate_openai_key', '');
}

/** Read the DeepL key: wp-config constant first, then the saved option. */
function wow_i18n_deepl_key() {
    if (defined('WOW_DEEPL_KEY') && WOW_DEEPL_KEY) {
        return (string) WOW_DEEPL_KEY;
    }
    return (string) get_option('wow_translate_deepl_key', '');
}

/** Read the Gemini key: wp-config constant first, then the saved option. */
function wow_i18n_gemini_key() {
    if (defined('WOW_GEMINI_KEY') && WOW_GEMINI_KEY) {
        return (string) WOW_GEMINI_KEY;
    }
    return (string) get_option('wow_translate_gemini_key', '');
}

/** Default model id for an LLM provider when none is set explicitly. */
function wow_i18n_default_model($provider) {
    if ($provider === 'anthropic') { return 'claude-sonnet-4-6'; }
    if ($provider === 'gemini')    { return 'gemini-2.0-flash'; }
    return 'gpt-4o';
}

/** Selectable translation engines: value => human label. */
function wow_i18n_engines() {
    return [
        'auto'      => 'Auto (first key that is set: DeepL → Claude → OpenAI → Gemini)',
        'deepl'     => 'DeepL',
        'anthropic' => 'Claude (Anthropic)',
        'openai'    => 'OpenAI (GPT)',
        'gemini'    => 'Gemini (Google)',
    ];
}

/** Keep only a known engine value; fall back to 'auto'. */
function wow_i18n_sanitize_engine($value) {
    $value = is_string($value) ? trim($value) : '';
    return array_key_exists($value, wow_i18n_engines()) ? $value : 'auto';
}

/**
 * Resolve which engine will actually run, given the saved choice and the keys
 * present. Returns ['engine'=>'deepl|anthropic|openai|none', 'ready'=>bool].
 */
function wow_i18n_active_engine() {
    $choice    = wow_i18n_sanitize_engine(get_option('wow_translate_engine', 'auto'));
    $deepl     = (bool) wow_i18n_deepl_key();
    $anthropic = (bool) wow_i18n_api_key();
    $openai    = (bool) wow_i18n_openai_key();
    $gemini    = (bool) wow_i18n_gemini_key();
    if ($choice === 'deepl')     { return ['engine' => 'deepl',     'ready' => $deepl]; }
    if ($choice === 'anthropic') { return ['engine' => 'anthropic', 'ready' => $anthropic]; }
    if ($choice === 'openai')    { return ['engine' => 'openai',    'ready' => $openai]; }
    if ($choice === 'gemini')    { return ['engine' => 'gemini',    'ready' => $gemini]; }
    // auto: first configured key wins, in this order.
    if ($deepl)     { return ['engine' => 'deepl',     'ready' => true]; }
    if ($anthropic) { return ['engine' => 'anthropic', 'ready' => true]; }
    if ($openai)    { return ['engine' => 'openai',    'ready' => true]; }
    if ($gemini)    { return ['engine' => 'gemini',    'ready' => true]; }
    return ['engine' => 'none', 'ready' => false];
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
    $engine_label = !empty($engine['deepl']) ? 'DeepL' : ucfirst((string) $engine['provider']);
    if (empty($engine['deepl']) && empty($engine['key'])) {
        return ['error' => "No API key for the selected engine ({$engine_label}). Set its key in Settings → AI Translate."];
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
    // Yoast SEO title/description/social — a single-element path is written back
    // verbatim as the meta key by step 5 (implode('_', [$key]) === $key).
    foreach (wow_i18n_yoast_meta_keys() as $mk) {
        $val = get_post_meta($en_id, $mk, true);
        if (is_string($val) && trim($val) !== '') {
            $items[] = ['path' => [$mk], 'en' => $val];
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
    $engine['memory'] = wow_i18n_category_memory($en_id, $target_slug);
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

    // Loud failure instead of a silent English duplicate: if there was text to
    // translate but NOTHING came back, the engine call failed (wrong/blocked key
    // for this provider, rate limit, …). Tell the user rather than leaving a copy.
    if (count($items) > 0 && $wrote === 0) {
        return [
            'ru_id'      => $ru_id,
            'created'    => $created,
            'translated' => 0,
            'total'      => count($items),
            'error'      => "{$engine_label} returned no translations — its API key is likely missing or invalid for this provider. The draft was left in the source language; fix the key in Settings → AI Translate and re-run.",
        ];
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
        register_setting('wow_ai_translate', 'wow_translate_engine', ['sanitize_callback' => 'wow_i18n_sanitize_engine']);
        register_setting('wow_ai_translate', 'wow_translate_key', ['sanitize_callback' => 'trim']);
        register_setting('wow_ai_translate', 'wow_translate_openai_key', ['sanitize_callback' => 'trim']);
        register_setting('wow_ai_translate', 'wow_translate_deepl_key', ['sanitize_callback' => 'trim']);
        register_setting('wow_ai_translate', 'wow_translate_gemini_key', ['sanitize_callback' => 'trim']);
        register_setting('wow_ai_translate', 'wow_translate_model', ['sanitize_callback' => 'trim']);
        register_setting('wow_ai_translate', 'wow_translate_style', ['sanitize_callback' => 'sanitize_textarea_field']);
        register_setting('wow_ai_translate', 'wow_translate_formality', ['sanitize_callback' => function ($v) {
            return in_array($v, ['prefer_more', 'prefer_less'], true) ? $v : '';
        }]);
    });

    function wow_ai_translate_settings_page() {
        $key_const    = defined('WOW_ANTHROPIC_KEY') && WOW_ANTHROPIC_KEY;
        $openai_const = defined('WOW_OPENAI_KEY') && WOW_OPENAI_KEY;
        $deepl_const  = defined('WOW_DEEPL_KEY') && WOW_DEEPL_KEY;
        $gemini_const = defined('WOW_GEMINI_KEY') && WOW_GEMINI_KEY;
        $engine       = wow_i18n_sanitize_engine(get_option('wow_translate_engine', 'auto'));
        $engines      = wow_i18n_engines();
        $active       = wow_i18n_active_engine();
        $labels       = ['deepl' => 'DeepL', 'anthropic' => 'Claude (Anthropic)', 'openai' => 'OpenAI (GPT)', 'gemini' => 'Gemini (Google)', 'none' => 'none'];
        ?>
        <div class="wrap">
            <h1>AI Translate</h1>
            <p>Translate pages/projects into the other Polylang languages with AI. Hover a post in the list and click <strong>&ldquo;AI &rarr; RU&rdquo;</strong>.</p>
            <?php if ($active['engine'] !== 'none' && $active['ready']) : ?>
                <p><span class="dashicons dashicons-yes" style="color:#46b450"></span> <em>Active engine: <strong><?php echo esc_html($labels[$active['engine']]); ?></strong>.</em></p>
            <?php else : ?>
                <p><span class="dashicons dashicons-warning" style="color:#dba617"></span> <em>No usable key for the selected engine — fill the matching field below.</em></p>
            <?php endif; ?>
            <form method="post" action="options.php">
                <?php settings_fields('wow_ai_translate'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="wow_translate_engine">Translation engine</label></th>
                        <td>
                            <select id="wow_translate_engine" name="wow_translate_engine">
                                <?php foreach ($engines as $val => $label) : ?>
                                    <option value="<?php echo esc_attr($val); ?>" <?php selected($engine, $val); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Which AI translates. <strong>DeepL</strong> = cheapest/fastest. <strong>Claude</strong> = best brand voice. <strong>Auto</strong> = use DeepL when its key is set. The engine uses its matching key below.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wow_translate_deepl_key">DeepL API key</label></th>
                        <td>
                            <input type="password" id="wow_translate_deepl_key" name="wow_translate_deepl_key" class="regular-text"
                                   value="<?php echo esc_attr(get_option('wow_translate_deepl_key', '')); ?>" autocomplete="off" placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx:fx" <?php disabled($deepl_const); ?>>
                            <p class="description">
                                Free plan key ends with <code>:fx</code> (500,000 chars/month, no Pro needed) — the free endpoint is detected automatically.
                                Leave empty to translate with Anthropic / OpenAI instead.
                                <?php if ($deepl_const) : ?><br><em>Defined in <code>wp-config.php</code> (<code>WOW_DEEPL_KEY</code>) — this field is ignored.</em><?php endif; ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wow_translate_key">Claude (Anthropic) API key</label></th>
                        <td>
                            <input type="password" id="wow_translate_key" name="wow_translate_key" class="regular-text"
                                   value="<?php echo esc_attr(get_option('wow_translate_key', '')); ?>" autocomplete="off" placeholder="sk-ant-…" <?php disabled($key_const); ?>>
                            <p class="description">
                                Used when the engine is <strong>Claude</strong>. Best brand voice. No free tier.
                                <?php if ($key_const) : ?><br><em>Defined in <code>wp-config.php</code> (<code>WOW_ANTHROPIC_KEY</code>) — this field is ignored.</em><?php endif; ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wow_translate_openai_key">OpenAI (GPT) API key</label></th>
                        <td>
                            <input type="password" id="wow_translate_openai_key" name="wow_translate_openai_key" class="regular-text"
                                   value="<?php echo esc_attr(get_option('wow_translate_openai_key', '')); ?>" autocomplete="off" placeholder="sk-…" <?php disabled($openai_const); ?>>
                            <p class="description">
                                Used when the engine is <strong>OpenAI</strong>. No free tier (small trial credit only).
                                <?php if ($openai_const) : ?><br><em>Defined in <code>wp-config.php</code> (<code>WOW_OPENAI_KEY</code>) — this field is ignored.</em><?php endif; ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wow_translate_gemini_key">Gemini API key</label></th>
                        <td>
                            <input type="password" id="wow_translate_gemini_key" name="wow_translate_gemini_key" class="regular-text"
                                   value="<?php echo esc_attr(get_option('wow_translate_gemini_key', '')); ?>" autocomplete="off" placeholder="AIza…" <?php disabled($gemini_const); ?>>
                            <p class="description">
                                Google AI Studio key (free tier available). Used when the engine above is set to <strong>Gemini</strong>.
                                <?php if ($gemini_const) : ?><br><em>Defined in <code>wp-config.php</code> (<code>WOW_GEMINI_KEY</code>) — this field is ignored.</em><?php endif; ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wow_translate_model">Model (optional)</label></th>
                        <td>
                            <input type="text" id="wow_translate_model" name="wow_translate_model" class="regular-text"
                                   value="<?php echo esc_attr(get_option('wow_translate_model', '')); ?>" placeholder="claude-sonnet-4-6">
                            <p class="description">Leave blank for each engine's default (claude-sonnet-4-6 / gpt-4o / gemini-2.0-flash). Cheaper Claude: <code>claude-haiku-4-5-20251001</code>.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wow_translate_style">Style instructions<br><small>(Claude / OpenAI / Gemini)</small></label></th>
                        <td>
                            <textarea id="wow_translate_style" name="wow_translate_style" class="large-text" rows="3"
                                      placeholder="e.g. Переводи просто и по-человечески, без пафоса и канцелярита. Короткие живые фразы, обращение на «вы»."><?php echo esc_textarea(get_option('wow_translate_style', '')); ?></textarea>
                            <p class="description">
                                Your own tone brief for the LLM engines — written in any language. Leave empty for the default
                                (plain, human wording without pomp). <strong>Does not affect DeepL</strong> — DeepL cannot take prompts.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="wow_translate_formality">DeepL formality</label></th>
                        <td>
                            <?php $formality = (string) get_option('wow_translate_formality', ''); ?>
                            <select id="wow_translate_formality" name="wow_translate_formality">
                                <option value="" <?php selected($formality, ''); ?>>Default</option>
                                <option value="prefer_more" <?php selected($formality, 'prefer_more'); ?>>More formal (вы)</option>
                                <option value="prefer_less" <?php selected($formality, 'prefer_less'); ?>>Less formal (ты)</option>
                            </select>
                            <p class="description">The only tone control DeepL offers. For prompt-level style control use a Claude / OpenAI / Gemini engine with the field above.</p>
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
        if (!wow_i18n_api_key() && !wow_i18n_openai_key() && !wow_i18n_deepl_key() && !wow_i18n_gemini_key()) {
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
