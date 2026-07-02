<?php
/**
 * Tools → Normalize Terminology
 *
 * One-off cleanup of already-translated RU content where the AI translator
 * spelled the same term differently across pages (e.g. "митцва" vs "мицва",
 * "бар мицва" vs "бар-мицва", mixed casing). Future translations stay
 * consistent via the glossary + translation memory in polylang-translate.php;
 * this tool fixes what was translated BEFORE that.
 *
 * Rule (confirmed with the client): mirror the English source. English writes
 * "Bar Mitzvah" capitalized everywhere, so the RU term is normalised to the
 * capitalized, hyphenated, no-extra-т form — "Бар-Мицва" / "Бат-Мицва" — with
 * grammatical endings preserved.
 *
 * Always DRY-RUN first: the page shows every old→new change before you apply.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Normalize the Bar/Bat Mitzvah term inside a RU text string.
 * Preserves grammatical endings (мицва/мицвы/мицву/мицве/...) and surrounding text.
 */
function wow_normalize_terms_text($text) {
    // "цв" catches both "мицв" and the misspelled "митцв" (и-т-ц-в); the
    // regexes below are specific, so a broad guard is just a cheap fast-path.
    if (!is_string($text) || $text === '' || mb_stripos($text, 'цв') === false) {
        return $text;
    }

    // 1) Spelling: drop the extra т — "митцв" → "мицв" (both cases).
    $text = preg_replace('/([Мм])итцв/u', '$1ицв', $text);

    // 2) Compound form "бар[- и ]бат[-]мицв<end>" (any hyphen/space/"и"/"and"
    //    between) → full canonical "Бар-Мицв<end> и Бат-Мицв<end>", declining
    //    both parts with the shared ending. Do this BEFORE the singular pass.
    $text = preg_replace_callback(
        '/\b[Бб]ар[\s\-]*(?:и|and)?[\s\-]*[Бб]ат[\s\-]*[Мм]ицв([а-яёА-ЯЁ]*)/u',
        static function ($m) {
            $end = mb_strtolower($m[1]);
            return 'Бар-Мицв' . $end . ' и Бат-Мицв' . $end;
        },
        $text
    );

    // 3) Singular "бар|бат [-| ]мицв<end>" → "Бар-Мицв<end>" / "Бат-Мицв<end>"
    //    (capital Б + capital М, hyphenated — mirrors the always-capitalized EN).
    $text = preg_replace_callback(
        '/\b([Бб]а[рт])[\s\-]*([Мм])ицв([а-яёА-ЯЁ]*)/u',
        static function ($m) {
            $which = mb_strtoupper(mb_substr($m[1], 0, 1)) . mb_strtolower(mb_substr($m[1], 1)); // Бар / Бат
            $end   = mb_strtolower($m[3]);
            return $which . '-Мицв' . $end;
        },
        $text
    );

    return $text;
}

/**
 * Scan all content and return the list of pending changes.
 * Each row: [post_id, title, where, old, new].
 */
function wow_normalize_terms_scan($limit = 0) {
    global $wpdb;
    $changes = [];

    // post_title + post_content. Match BOTH spellings: "мицв" (и-ц-в) and the
    // misspelled "митцв" (и-т-ц-в) — the latter does NOT contain "ицв".
    $posts = $wpdb->get_results(
        "SELECT ID, post_title, post_content FROM {$wpdb->posts}
         WHERE post_status IN ('publish','draft')
           AND (post_title LIKE '%ицв%' OR post_title LIKE '%итцв%'
                OR post_content LIKE '%ицв%' OR post_content LIKE '%итцв%')"
    );
    foreach ($posts as $p) {
        foreach (['post_title' => $p->post_title, 'post_content' => $p->post_content] as $field => $val) {
            $new = wow_normalize_terms_text($val);
            if ($new !== $val) {
                $changes[] = [
                    'post_id' => (int) $p->ID, 'title' => $p->post_title,
                    'where' => $field, 'old' => $val, 'new' => $new,
                ];
            }
        }
    }

    // postmeta: ACF text + Yoast. Skip field-key rows (_-prefixed) and serialized blobs.
    $metas = $wpdb->get_results(
        "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta}
         WHERE meta_value LIKE '%ицв%' OR meta_value LIKE '%итцв%'"
    );
    foreach ($metas as $m) {
        if ($m->meta_key !== '' && $m->meta_key[0] === '_' && strpos($m->meta_key, '_yoast_wpseo_') !== 0) {
            continue; // ACF field-reference keys etc.
        }
        $val = $m->meta_value;
        if (is_serialized($val)) {
            continue; // don't touch serialized structures
        }
        $new = wow_normalize_terms_text($val);
        if ($new !== $val) {
            $changes[] = [
                'post_id' => (int) $m->post_id, 'title' => get_the_title($m->post_id),
                'where' => 'meta:' . $m->meta_key, 'old' => $val, 'new' => $new,
                'meta_id' => (int) $m->meta_id,
            ];
        }
        if ($limit && count($changes) >= $limit) {
            break;
        }
    }

    // term names + taxonomy descriptions (nav/category terms carry copy too)
    $terms = $wpdb->get_results(
        "SELECT t.term_id, t.name, tt.term_taxonomy_id, tt.description
         FROM {$wpdb->terms} t
         JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id
         WHERE t.name LIKE '%ицв%' OR t.name LIKE '%итцв%'
            OR tt.description LIKE '%ицв%' OR tt.description LIKE '%итцв%'"
    );
    foreach ($terms as $t) {
        $nn = wow_normalize_terms_text($t->name);
        if ($nn !== $t->name) {
            $changes[] = ['post_id' => 0, 'title' => 'term: ' . $t->name, 'where' => 'term_name',
                'old' => $t->name, 'new' => $nn, 'term_id' => (int) $t->term_id];
        }
        $nd = wow_normalize_terms_text((string) $t->description);
        if ($nd !== (string) $t->description) {
            $changes[] = ['post_id' => 0, 'title' => 'term: ' . $t->name, 'where' => 'term_desc',
                'old' => $t->description, 'new' => $nd, 'tt_id' => (int) $t->term_taxonomy_id];
        }
    }

    // Polylang string translations — hardcoded theme UI strings whose RU value
    // lives serialized in the language term's "_pll_strings_translations" meta
    // as [source, translation] pairs. Normalize each translation side.
    $plls = $wpdb->get_results(
        "SELECT meta_id, meta_value FROM {$wpdb->termmeta} WHERE meta_key = '_pll_strings_translations'"
    );
    foreach ($plls as $row) {
        $arr = maybe_unserialize($row->meta_value);
        if (!is_array($arr)) {
            continue;
        }
        $changed = false;
        $sample_old = $sample_new = '';
        foreach ($arr as $i => $pair) {
            if (is_array($pair) && isset($pair[1]) && is_string($pair[1])) {
                $nn = wow_normalize_terms_text($pair[1]);
                if ($nn !== $pair[1]) {
                    if ($sample_old === '') {
                        $sample_old = $pair[1];
                        $sample_new = $nn;
                    }
                    $arr[$i][1] = $nn;
                    $changed = true;
                }
            }
        }
        if ($changed) {
            $changes[] = ['post_id' => 0, 'title' => 'Polylang UI strings', 'where' => 'pll_strings',
                'old' => $sample_old, 'new' => $sample_new,
                'pll_meta_id' => (int) $row->meta_id, 'pll_value' => maybe_serialize($arr)];
        }
    }

    // termmeta (ACF fields on terms, e.g. a nav description)
    $tmetas = $wpdb->get_results(
        "SELECT meta_id, term_id, meta_key, meta_value FROM {$wpdb->termmeta}
         WHERE meta_value LIKE '%ицв%' OR meta_value LIKE '%итцв%'"
    );
    foreach ($tmetas as $m) {
        if ($m->meta_key !== '' && $m->meta_key[0] === '_') {
            continue;
        }
        if (is_serialized($m->meta_value)) {
            continue;
        }
        $new = wow_normalize_terms_text($m->meta_value);
        if ($new !== $m->meta_value) {
            $changes[] = ['post_id' => 0, 'title' => 'term #' . $m->term_id, 'where' => 'termmeta:' . $m->meta_key,
                'old' => $m->meta_value, 'new' => $new, 'term_meta_id' => (int) $m->meta_id];
        }
    }

    return $changes;
}

/** Apply the changes returned by the scan. */
function wow_normalize_terms_apply(array $changes) {
    global $wpdb;
    $n = 0;
    foreach ($changes as $c) {
        if ($c['where'] === 'post_title') {
            $wpdb->update($wpdb->posts, ['post_title' => $c['new']], ['ID' => $c['post_id']]);
        } elseif ($c['where'] === 'post_content') {
            $wpdb->update($wpdb->posts, ['post_content' => $c['new']], ['ID' => $c['post_id']]);
        } elseif (!empty($c['meta_id'])) {
            $wpdb->update($wpdb->postmeta, ['meta_value' => $c['new']], ['meta_id' => $c['meta_id']]);
        } elseif ($c['where'] === 'term_name' && !empty($c['term_id'])) {
            $wpdb->update($wpdb->terms, ['name' => $c['new']], ['term_id' => $c['term_id']]);
            clean_term_cache($c['term_id']);
        } elseif ($c['where'] === 'term_desc' && !empty($c['tt_id'])) {
            $wpdb->update($wpdb->term_taxonomy, ['description' => $c['new']], ['term_taxonomy_id' => $c['tt_id']]);
        } elseif (!empty($c['term_meta_id'])) {
            $wpdb->update($wpdb->termmeta, ['meta_value' => $c['new']], ['meta_id' => $c['term_meta_id']]);
        } elseif ($c['where'] === 'pll_strings' && !empty($c['pll_meta_id'])) {
            $wpdb->update($wpdb->termmeta, ['meta_value' => $c['pll_value']], ['meta_id' => $c['pll_meta_id']]);
        } else {
            continue;
        }
        if (!empty($c['post_id'])) {
            clean_post_cache($c['post_id']);
        }
        $n++;
    }
    return $n;
}

add_action('admin_menu', static function () {
    add_management_page('Normalize Terminology', 'Normalize Terminology', 'manage_options', 'wow-normalize-terms', 'wow_normalize_terms_page');
});

function wow_normalize_terms_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Nope.');
    }
    echo '<div class="wrap"><h1>Normalize Terminology</h1>';
    echo '<p>Normalizes the Bar/Bat Mitzvah term across all content to the canonical form <code>Бар-Мицва</code> / <code>Бат-Мицва</code> (matching the English source: capitalized, hyphenated, no extra “т”), keeping grammatical endings. Review the preview, then apply.</p>';

    if (isset($_POST['wow_norm_apply']) && check_admin_referer('wow_norm')) {
        $changes = wow_normalize_terms_scan();
        $n = wow_normalize_terms_apply($changes);
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        echo '<div class="notice notice-success"><p>Applied <strong>' . (int) $n . '</strong> changes.</p></div>';
    }

    $changes = wow_normalize_terms_scan();
    if (!$changes) {
        echo '<p>✅ Nothing to change — the term is already consistent.</p></div>';
        return;
    }

    echo '<p><strong>' . count($changes) . '</strong> change(s) pending:</p>';
    echo '<table class="widefat striped"><thead><tr><th>Page</th><th>Field</th><th>Before → After</th></tr></thead><tbody>';
    foreach (array_slice($changes, 0, 300) as $c) {
        // show only the changed fragment context for readability
        $old = wow_norm_snippet($c['old'], $c['new']);
        echo '<tr><td><a href="' . esc_url(get_edit_post_link($c['post_id'])) . '">' . esc_html($c['title'] ?: ('#' . $c['post_id'])) . '</a></td>'
            . '<td><code>' . esc_html($c['where']) . '</code></td>'
            . '<td>' . $old . '</td></tr>';
    }
    echo '</tbody></table>';
    if (count($changes) > 300) {
        echo '<p><em>… and ' . (count($changes) - 300) . ' more (all will be applied).</em></p>';
    }

    echo '<form method="post" style="margin-top:16px">';
    wp_nonce_field('wow_norm');
    submit_button('Apply all changes', 'primary', 'wow_norm_apply');
    echo '</form></div>';
}

/** Highlight the differing fragment (old vs new) for the preview table. */
function wow_norm_snippet($old, $new) {
    // find first divergence
    $i = 0;
    $lo = mb_strlen($old);
    $ln = mb_strlen($new);
    while ($i < $lo && $i < $ln && mb_substr($old, $i, 1) === mb_substr($new, $i, 1)) {
        $i++;
    }
    $start = max(0, $i - 25);
    $oldFrag = ($start > 0 ? '…' : '') . mb_substr($old, $start, 70);
    $newFrag = ($start > 0 ? '…' : '') . mb_substr($new, $start, 70);
    return '<span style="color:#a00">' . esc_html($oldFrag) . '</span><br>→ <span style="color:#093">' . esc_html($newFrag) . '</span>';
}
