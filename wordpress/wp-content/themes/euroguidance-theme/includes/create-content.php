<?php
add_action('after_switch_theme', function () {
    
    
    return; // ! Зупинка автозаповнення !


    // ==== ВХІДНІ ДАНІ ========================================================
    $categories = array(
        array(
            'slug'       => 'euroguidance',
            'title'      => 'Про Euroguidance',
            'menu_order' => 2,
            'url'        => '#', // або home_url('/euroguidance/') якщо потрібна реальна сторінка
        ),
        array(
            'slug'       => 'tools',
            'title'      => 'Інструменти Euroguidance',
            'menu_order' => 3,
            'url'        => '#', // або home_url('/tools/')
        ),
    );

    $pages = array(
        array('slug'=>'news','title'=>'Новини','status'=>'publish','template'=>'index','menu_order'=>19),
        array('slug'=>'search','title'=>'Пошук','status'=>'publish','template'=>'page','menu_order'=>20),
        array('slug'=>'home','title'=>'Головна','menu_order'=>1),
        array('slug'=>'about','title'=>'Про Euroguidance','category'=>'euroguidance','menu_order'=>4),
        array('slug'=>'library','title'=>'Бібліотека матеріалів','category'=>'euroguidance','menu_order'=>5),
        array('slug'=>'useful-links','title'=>'Корисні посилання','category'=>'euroguidance','menu_order'=>6),
        array('slug'=>'profile-euroguidance','title'=>'Профіль Euroguidance','category'=>'tools','menu_order'=>7),
        array('slug'=>'resume','title'=>'Резюме','category'=>'euroguidance','menu_order'=>8),
        array('slug'=>'cover-letter','title'=>'Супровідний лист','category'=>'tools','menu_order'=>9),
        array('slug'=>'diploma-supplement','title'=>'Додаток до диплому','category'=>'tools','menu_order'=>10),
        array('slug'=>'certificate-supplement','title'=>'Додаток до сертифіката','category'=>'tools','menu_order'=>11),
        array('slug'=>'national-centres','title'=>'Національні центри Euroguidance','category'=>'tools','menu_order'=>12),
        array('slug'=>'mobility-passport','title'=>'Паспорт мобільності','category'=>'tools','menu_order'=>13),
    );

    // ==== 1) СТОРІНКИ ========================================================
    $page_ids = []; // slug => post_id

    foreach ($pages as $p) {
        $slug      = !empty($p['slug']) ? sanitize_title($p['slug']) : '';
        $title     = isset($p['title']) ? $p['title'] : '';
        $status    = isset($p['status']) ? $p['status'] : 'publish';
        $template  = isset($p['template']) ? $p['template'] : '';
        $order     = isset($p['menu_order']) ? intval($p['menu_order']) : 0;

        if (!$slug || !$title) continue;

        $existing = get_page_by_path($slug, OBJECT, 'page');
        if (!$existing) {
            $content_path = trailingslashit(get_stylesheet_directory()) . 'pages/' . $slug . '.html';
            $content = file_exists($content_path) ? file_get_contents($content_path) : '';

            $post_id = wp_insert_post(array(
                'post_type'      => 'page',
                'post_status'    => $status,
                'post_title'     => $title,
                'post_name'      => $slug,
                'menu_order'     => $order,
                'post_content'   => $content,
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
            ));
            if ($post_id && $template !== '') {
                update_post_meta($post_id, '_wp_page_template', $template);
            } elseif ($post_id) {
                delete_post_meta($post_id, '_wp_page_template');
            }
        } else {
            $post_id = (int) $existing->ID;
            $update  = array('ID' => $post_id);
            $do_update = false;

            if ($existing->post_status !== $status) { $update['post_status'] = $status; $do_update = true; }
            if ((int)$existing->menu_order !== $order){ $update['menu_order'] = $order;   $do_update = true; }
            if ($existing->post_title !== $title)    { $update['post_title']  = $title;   $do_update = true; }

            if ($do_update) wp_update_post($update);

            if ($template !== '') {
                update_post_meta($post_id, '_wp_page_template', $template);
            } else {
                delete_post_meta($post_id, '_wp_page_template');
            }
        }

        if (!empty($post_id)) {
            $page_ids[$slug] = $post_id;
        }
    }
    // ==== 2) МЕНЮ (wp_navigation) ============================================
    // Групуємо: категорії -> діти
    $cats_by_slug = [];
    foreach ($categories as $c) {
        $cs = sanitize_title($c['slug']);
        $cats_by_slug[$cs] = [
            'slug'       => $cs,
            'title'      => $c['title'],
            'menu_order' => isset($c['menu_order']) ? (int)$c['menu_order'] : 999,
            'url'        => isset($c['url']) ? esc_url_raw($c['url']) : '#',
            'children'   => [],
        ];
    }

    $top_level_pages = []; // сторінки без category
    foreach ($pages as $p) {
        $slug = sanitize_title($p['slug']);
        if (empty($page_ids[$slug])) continue;

        $entry = [
            'id'         => $page_ids[$slug],
            'title'      => $p['title'],
            'url'        => get_permalink($page_ids[$slug]),
            'menu_order' => isset($p['menu_order']) ? (int)$p['menu_order'] : 999,
        ];

        if (!empty($p['category'])) {
            $cat = sanitize_title($p['category']);
            if (!isset($cats_by_slug[$cat])) {
                $cats_by_slug[$cat] = [
                    'slug'=>$cat,'title'=>ucfirst($cat),'menu_order'=>999,'url'=>'#','children'=>[]
                ];
            }
            $cats_by_slug[$cat]['children'][] = $entry;
        } else {
            $top_level_pages[] = $entry;
        }
    }

    // Сортуємо дітей всередині категорій
    foreach ($cats_by_slug as &$cat) {
        usort($cat['children'], fn($a,$b)=>$a['menu_order']<=>$b['menu_order']);
    }
    unset($cat);

    // ЄДИННИЙ список топ-рівня (сторінки + категорії)
    $top_level = [];

    // 1) сторінки без категорії
    foreach ($top_level_pages as $p) {
        $top_level[] = [
            'kind'       => 'page',
            'menu_order' => $p['menu_order'],
            'data'       => $p,
        ];
    }

    // 2) категорії
    foreach ($cats_by_slug as $cat) {
        $top_level[] = [
            'kind'       => 'category',
            'menu_order' => $cat['menu_order'],
            'data'       => $cat,
        ];
    }

    // Сортуємо весь топ-рівень за menu_order (це й відтворює потрібний порядок)
    usort($top_level, fn($a,$b)=>$a['menu_order']<=>$b['menu_order']);

    // Будуємо контент меню
    $menu_content = '';
    foreach ($top_level as $item) {
        if ($item['kind'] === 'page') {
            $p = $item['data'];
            $attrs = ['type'=>'page','id'=>$p['id'],'url'=>$p['url'],'label'=>$p['title']];
            $menu_content .= sprintf("<!-- wp:navigation-link %s /-->\n",
                wp_json_encode($attrs, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
            );
        } else {
            $cat = $item['data'];
            $parent_attrs = ['label'=>$cat['title'],'url'=>$cat['url'] ?: '#'];
            $menu_content .= sprintf("<!-- wp:navigation-link %s -->\n",
                wp_json_encode($parent_attrs, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
            );
            foreach ($cat['children'] as $child) {
                $child_attrs = ['type'=>'page','id'=>$child['id'],'url'=>$child['url'],'label'=>$child['title']];
                $menu_content .= sprintf("  <!-- wp:navigation-link %s /-->\n",
                    wp_json_encode($child_attrs, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
                );
            }
            $menu_content .= "<!-- /wp:navigation-link -->\n";
        }
    }

    // Створюємо/оновлюємо wp_navigation 'main-menu'
    $menu_post = get_page_by_path('main-menu', OBJECT, 'wp_navigation');
    $postarr = [
        'post_type'   => 'wp_navigation',
        'post_status' => 'publish',
        'post_title'  => 'Головне меню',
        'post_name'   => 'main-menu',
        'post_content'=> $menu_content,
    ];
    $menu_post ? wp_update_post($postarr + ['ID'=>(int)$menu_post->ID]) : wp_insert_post($postarr);


    // Перемалюємо пермалінки сторінок
    flush_rewrite_rules(false);
});
