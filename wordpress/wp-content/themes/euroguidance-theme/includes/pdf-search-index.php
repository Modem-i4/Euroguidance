<?php
// Допоміжна: чи ми в контексті сторінки новин (/news/)
function ntd_is_news_search_context(): bool {
    // Базовий шлях для /news/ (враховує можливі префікси, сабдиректрії сайту тощо)
    $news_path = parse_url( home_url( '/news/' ), PHP_URL_PATH );
    $req_path  = isset($_SERVER['REQUEST_URI']) ? wp_parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';

    // Починається з /news/
    if ($news_path && $req_path && strpos(trailingslashit($req_path), trailingslashit($news_path)) === 0) {
        return true;
    }
    return false;
}

// виключення технічної сторінки пошуку
add_action('pre_get_posts', function (WP_Query $q) {
  if (is_admin() || !$q->is_main_query() || !$q->is_search()) return;
  if ($p = get_page_by_path('search-2', OBJECT, 'page')) {
    $q->set('post__not_in', array_unique(array_merge((array)$q->get('post__not_in'), [(int)$p->ID])));
  }
});


/**
 * 1) Перенаправляємо кліки на посилання категорій у пошуку новин
 *    Замість архіву категорії -> на /news/?s=…&cat=…
 */
add_filter('term_link', function ($termlink, $term, $taxonomy) {
    if ($taxonomy !== 'category') return $termlink;
    if (!is_search())             return $termlink;
    if (!ntd_is_news_search_context()) return $termlink; // лише в /news/

    $s = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

    // База — /news/
    $base = home_url('/news/');
    return add_query_arg([
        's'   => $s,
        'cat' => (int) $term->term_id,
    ], $base);
}, 10, 3);



/**
 * 1) Перенаправляємо кліки на посилання категорій у контексті /news/
 *    Замість архіву категорії -> на /news/?s=…&cat=…
 *    ВАЖЛИВО: без is_search(), з високим пріоритетом (99)
 */
add_filter('term_link', function ($termlink, $term, $taxonomy) {
    if ($taxonomy !== 'category') return $termlink;
    if (!ntd_is_news_search_context()) return $termlink; // лише коли ми на /news/

    // Збережемо поточний s (може бути й порожній — нам ок)
    $s = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';

    return add_query_arg([
        's'   => $s,
        'cat' => (int) $term->term_id,
    ], home_url('/news/'));
}, 99, 3); // <-- пріоритет 99, щоб перебити інші фільтри

/**
 * Розширення core/search:
 * - на /news/ додає hidden cat та action="/news/"
 * - на звичайному пошуку додає hidden type та action="/search/"
 * (post_type НЕ додаємо)
 */
if (!function_exists('ntd_is_default_search_context')) {
    function ntd_is_default_search_context(): bool {
        // "Звичайний" пошук: /search/ або /?s=...
        // і не контекст /news/
        if (function_exists('ntd_is_news_search_context') && ntd_is_news_search_context()) {
            return false;
        }
        // is_search() покриває /?s=.. ; /search/ може бути окремою сторінкою
        $is_search_page = is_search()
            || (is_page() && (get_query_var('pagename') === 'search' || get_the_ID() && get_post_field('post_name', get_the_ID()) === 'search'));

        return (bool) $is_search_page;
    }
}

add_filter('render_block', function ($content, $block) {
    if (($block['blockName'] ?? '') !== 'core/search') return $content;

    // ====== КОНТЕКСТ НОВИН (/news/) ======
    if (function_exists('ntd_is_news_search_context') && ntd_is_news_search_context()) {
        $cat = (int) ( get_query_var('cat') ?: ($_GET['cat'] ?? 0) );

        if ($cat && !preg_match('~name=("|\')cat\1~i', $content)) {
            $content = preg_replace(
                '~</form>~i',
                '<input type="hidden" name="cat" value="'.(int)$cat.'"></form>',
                $content,
                1
            );
        }

        // Примусово action="/news/"
        $content = preg_replace_callback('~<form\b([^>]*)>~i', function ($m) {
            $attrs  = $m[1];
            $target = ' action="'.esc_url(home_url('/news/')).'"';
            if (preg_match('~\saction=("|\')[^"\']*\1~i', $attrs)) {
                $attrs = preg_replace('~\saction=("|\')[^"\']*\1~i', $target, $attrs);
            } else {
                $attrs .= $target;
            }
            return '<form' . $attrs . '>';
        }, $content, 1);

        return $content;
    }

    // ====== ЗВИЧАЙНИЙ ПОШУК (/search/ або /?s=...) ======
    if (function_exists('ntd_is_default_search_context') && ntd_is_default_search_context()) {
        $type = sanitize_text_field( get_query_var('type') ?: ($_GET['type'] ?? '') );

        if ($type !== '' && !preg_match('~name=("|\')type\1~i', $content)) {
            $content = preg_replace(
                '~</form>~i',
                '<input type="hidden" name="type" value="'.esc_attr($type).'"></form>',
                $content,
                1
            );
        }

        // Примусово action="/search/"
        $content = preg_replace_callback('~<form\b([^>]*)>~i', function ($m) {
            $attrs  = $m[1];
            $target = ' action="'.esc_url(home_url('/search/')).'"';
            if (preg_match('~\saction=("|\')[^"\']*\1~i', $attrs)) {
                $attrs = preg_replace('~\saction=("|\')[^"\']*\1~i', $target, $attrs);
            } else {
                $attrs .= $target;
            }
            return '<form' . $attrs . '>';
        }, $content, 1);

        return $content;
    }

    return $content;
}, 10, 2);
