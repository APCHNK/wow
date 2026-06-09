<?php
/**
 * wow-i18n.php — bulk-translate ACF content into Polylang translations.
 *
 * CLI only. Bootstraps WordPress, so MySQL/WAMP must be running.
 *
 * USAGE (run from anywhere with the WAMP PHP binary):
 *   php bin/wow-i18n.php export ru export-ru.json
 *       Read-only. Dumps every translatable English ACF text field (and post
 *       titles) into JSON (UTF-8), with empty "ru" slots to fill in.
 *       (Omit the filename to print to stdout instead.)
 *
 *   php bin/wow-i18n.php import ru export-ru.json [engine] [--dry-run] [--limit=N]
 *       Creates/links the Russian translations (as DRAFTS), copies the layout
 *       + media from the English post, then writes the translated text.
 *       --dry-run : show what would change, call no API, write nothing.
 *       --limit=N : only process the first N posts (cheap test run).
 *     engine (pick one):
 *       --deepl=KEY
 *       --llm=KEY [--provider=anthropic|openai] [--model=ID]
 *           Provider is auto-detected from the key (sk-ant... = anthropic).
 *           Default models: claude-sonnet-4-6 / gpt-4o. Tone + glossary +
 *           HTML/[wow_diamond]/brand-name preservation are built into the prompt.
 *       If no engine is given, only the "ru" values already in the JSON are used.
 *
 *   php bin/wow-i18n.php strings ru (--llm=KEY | --deepl=KEY) [--dry-run]
 *       Auto-translate the Polylang UI strings (theme + ACF options) and write
 *       them into the language's string store, without clobbering existing ones.
 *
 * The translation engine (whitelist, collect/LLM/DeepL helpers, per-post
 * translate) lives in the theme and is shared with the admin "AI -> <lang>"
 * row action: wp-content/themes/wow/inc/polylang-translate.php
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

// The exported tree for a large site can exceed the default 128M during
// json_encode / get_fields. Raise it for this CLI run only.
@ini_set('memory_limit', '1024M');

/* --- Locate and load WordPress --------------------------------------------- */
$dir = __DIR__;
$wp_load = '';
for ($i = 0; $i < 8; $i++) {
    if (file_exists($dir . '/wp-load.php')) {
        $wp_load = $dir . '/wp-load.php';
        break;
    }
    $dir = dirname($dir);
}
if (!$wp_load) {
    fwrite(STDERR, "Could not find wp-load.php above " . __DIR__ . "\n");
    exit(1);
}
require $wp_load;

if (!function_exists('pll_default_language') || !function_exists('get_fields')) {
    fwrite(STDERR, "Polylang and ACF must be active.\n");
    exit(1);
}

/* --- Translation engine (shared with the admin) ---------------------------- *
 * Defined in the theme so the CLI and the admin row action use one
 * implementation: inc/polylang-translate.php (loaded via functions.php).
 * Provides WOW_I18N_TEXT_FIELDS, WOW_I18N_POST_TYPES, wow_i18n_collect(),
 * wow_i18n_deepl(), wow_i18n_llm().
 * -------------------------------------------------------------------------- */
if (!function_exists('wow_i18n_collect') || !defined('WOW_I18N_POST_TYPES')) {
    fwrite(STDERR, "Theme translation engine (inc/polylang-translate.php) is not loaded.\n");
    exit(1);
}

/* --- Commands -------------------------------------------------------------- */

$cmd    = $argv[1] ?? '';
$target = $argv[2] ?? '';
$file   = $argv[3] ?? '';
$opts   = array_slice($argv, 1);
$dry      = in_array('--dry-run', $opts, true);
$deepl    = '';
$llm      = '';
$provider = '';
$model    = '';
$limit    = 0;
foreach ($opts as $o) {
    if (strpos($o, '--deepl=') === 0)         { $deepl = substr($o, 8); }
    elseif (strpos($o, '--llm=') === 0)       { $llm = substr($o, 6); }
    elseif (strpos($o, '--provider=') === 0)  { $provider = substr($o, 11); }
    elseif (strpos($o, '--model=') === 0)     { $model = substr($o, 8); }
    elseif (strpos($o, '--limit=') === 0)     { $limit = (int) substr($o, 8); }
}

// Fall back to the keys saved in Settings → AI Translate (or wp-config constants)
// when no explicit flag is passed, so the CLI and the admin share one key.
$deepl_flag = ($deepl !== '');
$llm_flag   = ($llm !== '');
if (!$deepl_flag && function_exists('wow_i18n_deepl_key')) { $deepl = wow_i18n_deepl_key(); }
if (!$llm_flag   && function_exists('wow_i18n_api_key'))   { $llm = wow_i18n_api_key(); }

// When neither engine was forced via a flag, honour the engine chosen in the
// admin (Settings → AI Translate) so the CLI and the UI behave identically.
if (!$deepl_flag && !$llm_flag && function_exists('wow_i18n_sanitize_engine')) {
    $choice = wow_i18n_sanitize_engine(get_option('wow_translate_engine', 'auto'));
    if ($choice === 'deepl')         { $llm = ''; }
    elseif ($choice === 'anthropic') { $deepl = ''; if ($provider === '') { $provider = 'anthropic'; } }
    elseif ($choice === 'openai')    { $deepl = ''; if ($provider === '') { $provider = 'openai'; } }
    elseif ($choice === 'gemini')    { $deepl = ''; $llm = wow_i18n_gemini_key(); if ($provider === '') { $provider = 'gemini'; } }
}

$default = pll_default_language() ?: 'en';

if ($cmd === 'export') {
    if (!$target) {
        fwrite(STDERR, "Usage: php bin/wow-i18n.php export <lang>\n");
        exit(1);
    }

    $posts = get_posts([
        'post_type'   => WOW_I18N_POST_TYPES,
        'post_status' => ['publish', 'draft', 'pending', 'private'],
        'numberposts' => -1,
        'lang'        => $default,
        'orderby'     => 'ID',
        'order'       => 'ASC',
    ]);

    $out = ['default_lang' => $default, 'target_lang' => $target, 'posts' => []];
    $total = 0;
    foreach ($posts as $p) {
        $items = [];
        // Post title.
        if (trim($p->post_title) !== '') {
            $items[] = ['path' => ['__post_title__'], 'en' => $p->post_title, 'ru' => ''];
        }
        // ACF text fields.
        $fields = get_fields($p->ID);
        if (is_array($fields)) {
            foreach ($fields as $name => $val) {
                wow_i18n_collect($val, [$name], $items);
            }
        }
        // Yoast SEO title/description/social (single-element path = exact meta key).
        if (function_exists('wow_i18n_yoast_meta_keys')) {
            foreach (wow_i18n_yoast_meta_keys() as $mk) {
                $val = get_post_meta($p->ID, $mk, true);
                if (is_string($val) && trim($val) !== '') {
                    $items[] = ['path' => [$mk], 'en' => $val, 'ru' => ''];
                }
            }
        }
        if (empty($items)) {
            continue;
        }
        $existing = function_exists('pll_get_post') ? pll_get_post($p->ID, $target) : 0;
        $out['posts'][] = [
            'en_id'        => $p->ID,
            'type'         => $p->post_type,
            'ref_title'    => $p->post_title,
            'ru_id_exists' => $existing ?: null,
            'items'        => $items,
        ];
        $total += count($items);
    }

    fwrite(STDERR, sprintf("Exported %d strings across %d posts (lang=%s -> %s).\n",
        $total, count($out['posts']), $default, $target));
    $json = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($file) {
        // Write UTF-8 directly so the file is shell-encoding-independent.
        file_put_contents($file, $json);
        fwrite(STDERR, "Wrote " . $file . "\n");
    } else {
        echo $json;
    }
    exit(0);
}

if ($cmd === 'import') {
    if (!$target || !$file || !file_exists($file)) {
        fwrite(STDERR, "Usage: php bin/wow-i18n.php import <lang> <file.json> [--dry-run] [--limit=N]\n");
        fwrite(STDERR, "        engine: --deepl=KEY  |  --llm=KEY [--provider=anthropic|openai] [--model=ID]\n");
        exit(1);
    }
    $data = json_decode(file_get_contents($file), true);
    if (empty($data['posts'])) {
        fwrite(STDERR, "Nothing to import.\n");
        exit(1);
    }
    if ($limit > 0) {
        $data['posts'] = array_slice($data['posts'], 0, $limit);
        fwrite(STDERR, "Limiting to first {$limit} post(s).\n");
    }

    // Optional: auto-fill empty "ru" via DeepL (skipped on --dry-run to avoid cost).
    if ($deepl && !$dry) {
        $pending = [];   // refs to items needing translation
        $texts   = [];
        foreach ($data['posts'] as $pi => $post) {
            foreach ($post['items'] as $ii => $item) {
                if (trim((string) ($item['ru'] ?? '')) === '' && trim((string) $item['en']) !== '') {
                    $pending[] = [$pi, $ii];
                    $texts[]   = $item['en'];
                }
            }
        }
        fwrite(STDERR, "DeepL: translating " . count($texts) . " strings...\n");
        // DeepL caps ~50 texts and ~128 KiB per request; keep chunks small.
        $offset = 0;
        foreach (array_chunk($texts, 25) as $chunk) {
            $translated = wow_i18n_deepl($chunk, $deepl, strtoupper($target));
            foreach ($translated as $j => $txt) {
                [$pi, $ii] = $pending[$offset + $j];
                $data['posts'][$pi]['items'][$ii]['ru'] = $txt;
            }
            $offset += count($chunk);
        }
    }

    // Optional: auto-fill empty "ru" via an LLM (Claude/GPT). Skipped on --dry-run.
    if ($llm && !$dry) {
        if (!$provider) {
            $provider = (strpos($llm, 'sk-ant') === 0) ? 'anthropic' : 'openai';
        }
        if (!$model) {
            $model = wow_i18n_default_model($provider);
        }
        // Gather pending items with stable numeric ids.
        $idMap = []; // id => [postIndex, itemIndex]
        $queue = []; // id => english text
        $id = 0;
        foreach ($data['posts'] as $pi => $post) {
            foreach ($post['items'] as $ii => $item) {
                if (trim((string) ($item['ru'] ?? '')) === '' && trim((string) $item['en']) !== '') {
                    $idMap[$id] = [$pi, $ii];
                    $queue[$id] = $item['en'];
                    $id++;
                }
            }
        }
        fwrite(STDERR, sprintf("LLM (%s / %s): translating %d strings...\n", $provider, $model, count($queue)));

        $done = 0;
        $batch = [];
        $batchChars = 0;
        $flush = function () use (&$batch, &$batchChars, &$data, $idMap, $llm, $provider, $model, &$done) {
            if (empty($batch)) {
                return;
            }
            $res = wow_i18n_llm($batch, $llm, $provider, $model);
            foreach ($res as $bid => $txt) {
                if (!isset($idMap[$bid])) {
                    continue;
                }
                [$pi, $ii] = $idMap[$bid];
                $data['posts'][$pi]['items'][$ii]['ru'] = $txt;
                $done++;
            }
            $batch = [];
            $batchChars = 0;
        };
        foreach ($queue as $qid => $txt) {
            $batch[$qid] = $txt;
            $batchChars += strlen($txt);
            if (count($batch) >= 20 || $batchChars >= 4000) {
                $flush();
                fwrite(STDERR, "  ...{$done}/" . count($queue) . "\n");
            }
        }
        $flush();
        fwrite(STDERR, "LLM done: {$done}/" . count($queue) . " translated.\n");
    }

    $created = 0; $updated = 0; $skipped = 0;
    foreach ($data['posts'] as $post) {
        $en_id = (int) $post['en_id'];
        $en    = get_post($en_id);
        if (!$en) { $skipped++; continue; }

        // 1. Ensure a linked translation exists.
        $ru_id = function_exists('pll_get_post') ? (int) pll_get_post($en_id, $target) : 0;
        if (!$ru_id) {
            if ($dry) {
                fwrite(STDERR, "[dry] would CREATE {$target} translation of #{$en_id} ({$en->post_title})\n");
            } else {
                $parent = $en->post_parent;
                $ru_parent = $parent ? (int) pll_get_post($parent, $target) : 0;
                $ru_id = wp_insert_post([
                    'post_type'    => $en->post_type,
                    'post_title'   => $en->post_title,
                    'post_name'    => $en->post_name, // keep the Latin slug, not a Cyrillic %-encoded one
                    'post_content' => $en->post_content,
                    'post_excerpt' => $en->post_excerpt,
                    'post_status'  => 'draft',
                    'post_parent'  => $ru_parent ?: $parent,
                    'menu_order'   => $en->menu_order,
                ], true);
                if (is_wp_error($ru_id)) {
                    fwrite(STDERR, "  insert failed for #{$en_id}: " . $ru_id->get_error_message() . "\n");
                    $skipped++; continue;
                }
                foreach (get_object_taxonomies($en->post_type) as $tax) {
                    $terms = wp_get_object_terms($en_id, $tax, ['fields' => 'ids']);
                    if (!is_wp_error($terms) && $terms) wp_set_object_terms($ru_id, $terms, $tax);
                }
                pll_set_post_language($ru_id, $target);
                $tr = pll_get_post_translations($en_id);
                $tr[$default] = $en_id;
                $tr[$target]  = $ru_id;
                pll_save_post_translations($tr);
                wow_i18n_force_slug($ru_id, $en->post_name); // exact same slug as the source
                $created++;
            }
        }

        if ($dry) {
            $n = count(array_filter($post['items'], fn($i) => trim((string) ($i['ru'] ?? '')) !== ''));
            fwrite(STDERR, "[dry] #{$en_id} -> {$target}: would seed structure + write {$n} translated field(s)\n");
            continue;
        }
        if (!$ru_id) { $skipped++; continue; }

        // 2. Seed any MISSING meta from the English post (layout, media,
        //    field-key references) WITHOUT clobbering values already present on
        //    the translation — so re-runs never overwrite existing Russian.
        global $wpdb;
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", $en_id));
        foreach ($rows as $r) {
            if ($r->meta_key === '_edit_lock' || $r->meta_key === '_edit_last') continue;
            if (metadata_exists('post', $ru_id, $r->meta_key)) continue;
            $wpdb->insert($wpdb->postmeta, ['post_id' => $ru_id, 'meta_key' => $r->meta_key, 'meta_value' => $r->meta_value]);
        }

        // 3. Write each translated text leaf straight to its ACF meta key.
        //    A field path joins to the exact meta key, e.g.
        //    ['project_sections',0,'project_hero_title'] -> project_sections_0_project_hero_title
        //    This is far more reliable than re-saving a formatted tree via update_field().
        $newTitle = null;
        foreach ($post['items'] as $item) {
            if (trim((string) ($item['ru'] ?? '')) === '') continue;
            if (($item['path'][0] ?? '') === '__post_title__') {
                $newTitle = $item['ru'];
                continue;
            }
            update_post_meta($ru_id, implode('_', $item['path']), wp_slash($item['ru']));
        }
        if ($newTitle !== null) {
            wp_update_post(['ID' => $ru_id, 'post_title' => wp_slash($newTitle)]);
        }
        $updated++;
    }

    fwrite(STDERR, sprintf("Done. created=%d, updated=%d, skipped=%d%s\n",
        $created, $updated, $skipped, $dry ? " (dry-run)" : ""));
    exit(0);
}

if ($cmd === 'strings') {
    if (!$target) {
        fwrite(STDERR, "Usage: php bin/wow-i18n.php strings <lang> (--llm=KEY | --deepl=KEY) [--dry-run]\n");
        exit(1);
    }
    if (!class_exists('PLL_MO') || !function_exists('PLL')) {
        fwrite(STDERR, "Polylang string store (PLL_MO) unavailable.\n");
        exit(1);
    }
    $lang = PLL()->model->get_language($target);
    if (!$lang) {
        fwrite(STDERR, "Unknown language '{$target}'. Add it in Polylang first.\n");
        exit(1);
    }
    $mo = new PLL_MO();
    $mo->import_from_db($lang);

    // Collect every UI string original (theme strings + ACF option text).
    $originals = [];
    if (function_exists('wow_polylang_strings')) {
        foreach (wow_polylang_strings() as $s) { $originals[$s] = true; }
    }
    foreach (['footer_title' => "Let's turn your idea into something real", 'footer_btn_text' => 'CONTACT US'] as $name => $fb) {
        $originals[$fb] = true;
        $v = function_exists('get_field') ? get_field($name, 'option') : '';
        if (is_string($v) && trim($v) !== '') { $originals[$v] = true; }
    }
    $nav = function_exists('get_field') ? get_field('header_nav_items', 'option') : null;
    if (is_array($nav)) {
        foreach ($nav as $item) {
            if (!empty($item['title']))       { $originals[$item['title']] = true; }
            if (!empty($item['description'])) { $originals[$item['description']] = true; }
        }
    }
    $originals = array_keys($originals);

    // Only translate strings that have no translation yet (never clobber yours).
    $todo = [];
    foreach ($originals as $o) {
        if (trim((string) $o) === '') continue;
        if ($mo->translate($o) === $o) { $todo[] = $o; }
    }
    fwrite(STDERR, sprintf("UI strings: %d total, %d untranslated.\n", count($originals), count($todo)));

    if ($dry) {
        foreach ($todo as $o) { fwrite(STDERR, "  [todo] " . mb_substr($o, 0, 70) . "\n"); }
        exit(0);
    }
    if (empty($todo)) { exit(0); }

    if ($llm && !$provider) { $provider = (strpos($llm, 'sk-ant') === 0) ? 'anthropic' : 'openai'; }
    if ($llm && !$model)    { $model = wow_i18n_default_model($provider); }

    $idMap   = $todo;          // id => original
    $results = [];             // id => translation
    $batch = []; $chars = 0;
    $flush = function () use (&$batch, &$chars, &$results, $deepl, $llm, $provider, $model, $target) {
        if (empty($batch)) return;
        if ($llm) {
            foreach (wow_i18n_llm($batch, $llm, $provider, $model) as $bid => $txt) { $results[$bid] = $txt; }
        } elseif ($deepl) {
            $keys = array_keys($batch);
            foreach (wow_i18n_deepl(array_values($batch), $deepl, strtoupper($target)) as $j => $txt) { $results[$keys[$j]] = $txt; }
        }
        $batch = []; $chars = 0;
    };
    foreach ($idMap as $id => $text) {
        $batch[$id] = $text;
        $chars += strlen($text);
        if (count($batch) >= 20 || $chars >= 4000) { $flush(); fwrite(STDERR, "  ..." . count($results) . "/" . count($idMap) . "\n"); }
    }
    $flush();

    $wrote = 0;
    foreach ($results as $id => $ru) {
        if (!isset($idMap[$id]) || !is_string($ru) || trim($ru) === '') continue;
        $orig = $idMap[$id];
        if (method_exists($mo, 'make_entry')) {
            $mo->add_entry_or_merge($mo->make_entry($orig, $ru));
        } else {
            $mo->add_entry_or_merge(new Translation_Entry(['singular' => $orig, 'translations' => [$ru]]));
        }
        $wrote++;
    }
    $mo->export_to_db($lang);
    fwrite(STDERR, "Wrote {$wrote} string translation(s) to '{$target}'.\n");
    exit(0);
}

fwrite(STDERR, "Unknown command. Use 'export', 'import' or 'strings'. See file header for usage.\n");
exit(1);
