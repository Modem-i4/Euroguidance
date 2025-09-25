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



// 6) Добираємо PDF-вкладення при порожньому пошуку (показати ВСІ PDF на 1-й сторінці)
add_filter('the_posts', function($posts, $q){
    if (!$q->is_main_query() || !$q->is_search() || is_admin()) return $posts;

    $s_raw  = (string) $q->get('s');
    $is_empty_search = ( $q->get('s') !== null && trim($s_raw) === '' );
    if (!$is_empty_search) return $posts;

    // Лише перша сторінка — не ламаємо пагінацію
    $paged = max(1, (int)$q->get('paged'));
    if ($paged > 1) return $posts;

    $need = max(0, (int)$q->get('posts_per_page') - count($posts));
    if ($need === 0) return $posts;

    $pdf_q = new WP_Query([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'application/pdf',
        'posts_per_page' => $need,     // добираємо рівно до ліміту
        'orderby'        => 'date',
        'order'          => 'DESC',
        'fields'         => 'ids',
        'no_found_rows'  => true,
    ]);

    if (!empty($pdf_q->posts)) {
        $have_ids = wp_list_pluck($posts, 'ID');
        $add_ids  = array_values(array_diff($pdf_q->posts, $have_ids));
        foreach ($add_ids as $id) {
            if ($p = get_post($id)) $posts[] = $p;
        }
        // found_posts не міняємо — ми лише заповнюємо поточну сторінку.
    }

    return $posts;
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
