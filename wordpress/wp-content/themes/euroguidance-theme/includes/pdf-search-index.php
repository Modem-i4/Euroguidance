<?php
// 1) Пости/сторінки — як було
add_filter('pre_get_posts', function ($q) {
    if (!$q->is_main_query() || !$q->is_search() || is_admin()) return;

    $q->set('post_type', ['post', 'page']);
    $q->set('post_status', ['publish']);
    $q->set('sentence', true); // 1-символьні запити
});

// 2) Добір PDF: при звичайному запиті — по s; при "пробіл" — всі PDF
add_filter('the_posts', function ($posts, $q) {
    if (!$q->is_main_query() || !$q->is_search() || is_admin()) return $posts;

    $s_raw = (string) $q->get('s');
    $s_trim = trim($s_raw);
    $space_only = ($s_trim === '' && $s_raw !== '');

    // Лише на першій сторінці, щоб не ламати пагінацію
    $paged = max(1, (int) $q->get('paged'));
    if ($paged > 1) return $posts;

    // Скільки місця лишилося у цій сторінці
    $need = max(0, (int) $q->get('posts_per_page') - count($posts));
    if ($need === 0) return $posts;

    // Базові аргументи для PDF
    $args = [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'application/pdf',
        'posts_per_page' => $need,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ];

    // Якщо це НЕ "пробіл", шукаємо PDF по запиту; якщо "пробіл" — беремо всі PDF (без s)
    if (!$space_only) {
        if ($s_trim === '') return $posts; // порожній по-справжньому — нічого не добираємо
        $args['s'] = $s_trim;
    }

    $pdf_q = new WP_Query($args);

    if (!empty($pdf_q->posts)) {
        $have_ids = wp_list_pluck($posts, 'ID');
        $add_ids  = array_values(array_diff($pdf_q->posts, $have_ids));

        foreach ($add_ids as $id) {
            if ($p = get_post($id)) $posts[] = $p;
        }
        // Лічильник found_posts не чіпаємо, бо ми додаємо тільки на 1-й сторінці рівно до ліміту.
    }

    return $posts;
}, 10, 2);












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
 * 2) У блоці core/search на сторінці /news/:
 *    - підкладаємо hidden поле cat (якщо є)
 *    - форсимо action="/news/"
 *    (post_type більше НЕ додаємо)
 */
add_filter('render_block', function ($content, $block) {
    if (($block['blockName'] ?? '') !== 'core/search') return $content;
    if (!ntd_is_news_search_context())                 return $content;

    $cat = (int) ( get_query_var('cat') ?: ($_GET['cat'] ?? 0) );

    if ($cat) {
        $content = preg_replace('~</form>~', '<input type="hidden" name="cat" value="'.(int)$cat.'"></form>', $content, 1);
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
}, 10, 2);
