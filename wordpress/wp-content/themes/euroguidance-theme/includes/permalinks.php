<?php
// 1) Рерайти тільки для пустого пошуку: /search і /search/page/N
add_action('init', function () {
    if (!get_option('permalink_structure')) return;

    add_rewrite_rule('^search/?$', 'index.php?s=', 'top');
    add_rewrite_rule('^search/page/([0-9]{1,})/?$', 'index.php?s=&paged=$matches[1]', 'top');
});

// 2) Формування посилань пошуку:
//    - пустий запит -> /search
//    - інший -> ?s=...
add_filter('search_link', function($link, $query){
    $q = trim((string) $query);

    if (!get_option('permalink_structure')) {
        return ($q === '') ? add_query_arg('s', '', home_url('/')) : $link;
    }
    return ($q === '')
        ? home_url(user_trailingslashit('search'))
        : add_query_arg('s', $q, home_url('/'));
}, 10, 2);

// 3) ВАЖЛИВО: прибираємо "пробіл-хак" повністю
//    (видали ПОВНІСТЮ попередній блок "add_filter('request', ... $vars['s']=' '; )")

// 4) Дозволяємо порожньому пошуку повертати записи (без підстановки пробілу у s)
add_filter('posts_search', function($search, $q){
    if ($q->is_main_query() && $q->is_search() && !is_admin()) {
        $s = $q->get('s');
        if ($s !== null && trim((string)$s) === '') {
            // Порожній пошук -> не додаємо умову пошуку (тобто показати все як архів)
            return '';
        }
    }
    return $search;
}, 10, 2);

// 5) Базовий пошук: пости+сторінки (звичайний сценарій)
add_filter('pre_get_posts', function($q){
    if (!$q->is_main_query() || !$q->is_search() || is_admin()) return;

    $q->set('post_type', ['post', 'page']);
    $q->set('post_status', ['publish']);
    $q->set('sentence', true); // щоб працювали 1-символьні запити
});
add_filter('render_block', function ($block_content, $block) {
    if (empty($block['blockName']) || $block['blockName'] !== 'core/navigation-link') {
        return $block_content;
    }

    // Працюємо на будь-якій сторінці пошуку
    if ( ! is_search() ) {
        return $block_content;
    }

    $link_url = $block['attrs']['url'] ?? '';
    if (!$link_url) return $block_content;

    // нормалізовані варіанти адрес
    $link_url   = untrailingslashit($link_url);
    $pretty_url = untrailingslashit( home_url( user_trailingslashit('search') ) ); // /search/
    $query_url  = untrailingslashit( add_query_arg('s', '', home_url('/')) );      // /?s=

    // якщо це посилання на "пошукову" сторінку — підсвічуємо <li>
    if ($link_url === $pretty_url || $link_url === $query_url) {
        if (preg_match('/^<li\b[^>]*class="/', $block_content)) {
            $block_content = preg_replace(
                '/^<li\b([^>]*)class="([^"]*)"/',
                '<li$1class="$2 current-menu-item current_page_item"',
                $block_content
            );
        } else {
            $block_content = preg_replace(
                '/^<li\b/',
                '<li class="current-menu-item current_page_item"',
                $block_content
            );
        }
    }

    return $block_content;
}, 10, 2);




add_filter('body_class', function($classes){
    if ( is_search() ) {
        if ( trim( (string) get_search_query() ) !== '' ) {
            $classes[] = 'has-search-query';
        } else {
            $classes[] = 'has-empty-search';
        }
    }
    return $classes;
});


// 7) Флаш рерайтів при активації теми
add_action('after_switch_theme', function(){ flush_rewrite_rules(); });


// 1) Прибрати required у Gutenberg Search (core/search)
add_filter('render_block', function ($content, $block) {
    if (!is_array($block) || empty($block['blockName'])) return $content;
    if ($block['blockName'] !== 'core/search') return $content;

    // знімаємо required / required="required"
    $content = preg_replace('/\srequired(="required")?/i', '', $content);
    return $content;
}, 10, 2);

// 2) Дозволити порожній пошук: якщо ?s= порожній — ставимо пробіл
add_filter('request', function ($vars) {
    if (isset($vars['s']) && $vars['s'] === '') {
        $vars['s'] = ' '; // робить is_search=true і не редиректить на головну
    }
    return $vars;
});

