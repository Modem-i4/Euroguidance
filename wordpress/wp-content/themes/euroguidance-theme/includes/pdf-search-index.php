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