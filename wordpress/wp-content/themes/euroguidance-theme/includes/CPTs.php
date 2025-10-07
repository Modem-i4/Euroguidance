<?php
if (!defined('ABSPATH')) exit;

add_action('init', function () {
  $people_types = [
    'ambassador' => ['label' => 'Амбасадори', 'icon' => 'dashicons-groups'],
    'expert'     => ['label' => 'Експерти',    'icon' => 'dashicons-welcome-learn-more'],
  ];

  $sanitize_string_array = function ($value) {
    if (is_string($value)) {
      $items = [];
      if (strpos($value, '<li') !== false && preg_match_all('~<li\b[^>]*>(.*?)</li>~is', $value, $m)) {
        foreach ($m[1] as $li) {
          $t = trim(wp_strip_all_tags($li));
          if ($t !== '') $items[] = $t;
        }
        return $items;
      }
      $t = trim(wp_strip_all_tags($value));
      return $t === '' ? [] : [$t];
    }

    if (!is_array($value)) return [];

    $to_string = function ($v) use (&$to_string) {
      if (is_string($v)) return trim(wp_strip_all_tags($v));
      if (is_array($v)) {
        if (array_key_exists('props', $v)) {
          $children = $v['props']['children'] ?? null;
          if (is_array($children)) {
            $joined = trim(implode('', array_map(fn($c) => is_string($c) ? $c : (is_scalar($c) ? (string)$c : ''), $children)));
            return trim(wp_strip_all_tags($joined));
          }
          if (is_string($children)) return trim(wp_strip_all_tags($children));
        }
        return trim(wp_strip_all_tags(implode(' ', array_map($to_string, $v))));
      }
      if (is_scalar($v)) return trim(wp_strip_all_tags((string)$v));
      return '';
    };

    $out = [];
    foreach ($value as $el) {
      $s = $to_string($el);
      if ($s !== '') $out[] = $s;
    }
    return array_values($out);
  };

  foreach ($people_types as $type => $cfg) {
    register_post_type($type, [
      'label'        => $cfg['label'],
      'public'       => true,
      'show_in_rest' => true,
      'supports'     => ['title','editor','thumbnail','custom-fields','page-attributes'],
      'menu_icon'    => $cfg['icon'],
      'template' => [ [ 'core/columns', [], [ [ 'core/column', [ 'width' => '350px' ], [ [ 'core/group', [ 'layout' => [ 'type' => 'constrained', 'justifyContent' => 'center' ] ], [ [ 'core/post-featured-image', [ 'aspectRatio' => '1', 'width'       => '', 'height'      => '', 'sizeSlug'    => 'full', 'align'       => 'wide', 'style'       => [ 'border' => [ 'radius' => '0px' ] ], ], ], [ 'core/post-title', [ 'textAlign' => 'center', 'className' => 'ambasadors__text-image', 'style'     => [ 'typography' => [ 'fontSize' => '26px' ], 'spacing'    => [ 'padding' => [ 'top' => '10px', 'bottom' => '10px' ] ], ], ], ], ], ], ], ], [ 'core/column', [ 'verticalAlignment' => 'center', 'style'             => [ 'spacing' => [ 'blockGap' => '0' ] ], ], [ [ 'core/post-title', [] ], [ 'core/separator', [ 'tagName'   => 'div', 'className' => 'wide-divider', 'style'     => [ 'spacing' => [ 'margin' => [ 'top' => 'var:preset|spacing|20', 'bottom' => 'var:preset|spacing|20' ] ], 'color'   => [ 'background' => '#7abe92' ], ], ], ], [ 'core/paragraph', [ 'className' => 'indent', 'content'   => 'Уведіть короткий опис', ], ], ], ], ], ], [ 'core/group', [ 'metadata'  => [ 'name' => 'Інфо' ], 'className' => 'amb-info', 'style'     => [ 'spacing' => [ 'padding'  => [ 'top' => 'var:preset|spacing|30', 'bottom' => 'var:preset|spacing|30' ], 'blockGap' => '0', ], ], 'layout' => [ 'type' => 'default' ], ], [ [ 'core/heading', [ 'textAlign' => 'center', 'level'     => 1, 'content'   => '<strong>Сертифікація та кваліфікація</strong>', ], ], [ 'parts-blocks/bullet-meta-list', [ 'className' => 'checks-list green-checks' ], ], [ 'core/heading', [ 'textAlign' => 'center', 'level' => 1, 'content' => 'Спеціалізації' ], ], [ 'parts-blocks/bullet-meta-list', [ 'metaKey' => 'spec', 'className' => 'checks-list blue-checks' ], ], [ 'core/heading', [ 'textAlign' => 'center', 'level' => 1, 'content' => 'Досвід' ], ], [ 'parts-blocks/bullet-meta-list', [ 'metaKey' => 'exp', 'className' => 'checks-list black-ckecks' ], ], [ 'core/heading', [ 'textAlign' => 'center', 'level' => 1, 'content' => 'Контакти' ], ], [ 'core/group', [ 'style'  => [ 'spacing' => [ 'blockGap' => '0', 'padding'  => [ 'right' => '0', 'left' => '0' ], ], ], 'layout' => [ 'type'           => 'flex', 'orientation'    => 'vertical', 'justifyContent' => 'center', ], ], [ [ 'ntd/social-links', [ 'showLabels' => true ] ], [ 'core/media-text', [ 'mediaId'           => 186, 'mediaLink'         => 'http://localhost:8095/mail-01/', 'mediaType'         => 'image', 'mediaUrl'          => 'http://localhost:8095/wp-content/uploads/2025/08/mail-01.svg', 'mediaWidth'        => 15, 'isStackedOnMobile' => false, 'style'             => [ 'layout' => [ 'selfStretch' => 'fit', 'flexSize' => null ] ], ], [ [ 'core/paragraph', [ 'placeholder' => 'Вміст…', 'metadata'    => [ 'bindings' => [ 'content' => [ 'source' => 'core/post-meta', 'args'   => [ 'key' => 'email' ], ], ], ], ], ], ], ], ], ], ], ], ],
    ]);

    $common = [
      'single'            => true,
      'type'              => 'array',
      'default'           => [],
      'auth_callback'     => function() { return current_user_can('edit_posts'); },
      'sanitize_callback' => $sanitize_string_array,
      'show_in_rest'      => [
        'schema' => [
          'type'    => 'array',
          'default' => [],
          'items'   => [ 'type' => 'string' ],
        ],
      ],
    ];

    register_post_meta($type, 'qual', $common);
    register_post_meta($type, 'spec', $common);
    register_post_meta($type, 'exp', $common);

    $meta_string = [
      'single'        => true,
      'type'          => 'string',
      'default'       => '',
      'auth_callback' => function () { return current_user_can('edit_posts'); },
      'show_in_rest'  => [
        'schema' => [
          'type'    => 'string',
          'default' => '',
        ],
      ],
    ];

    register_post_meta($type, 'phone', $meta_string);
    register_post_meta($type, 'email', $meta_string);
    register_post_meta($type, 'show_label', $meta_string);
  }
});


/*** 1) CPT «Матеріали» (прихований в адмінці, але доступний у фронті/REST) ***/
add_action('init', function () {
  register_post_type('resource', [
    'label'               => 'Матеріали',

    // приховати з адмін-UI
    'show_ui'             => false,
    'show_in_menu'        => false,
    'show_in_admin_bar'   => false,
    'show_in_nav_menus'   => false,

    // поведінка на фронті / в запитах
    'public'              => false,
    'publicly_queryable'  => true,
    'exclude_from_search' => false, // дозволимо потрапляти у пошук (все одно нижче явно задаємо post_type)
    'show_in_rest'        => true,

    // архів не потрібен; сінгл лишимо зі слугом /resource/...
    'has_archive'         => false,
    'rewrite'             => ['slug' => 'resource', 'with_front' => false],

    // мінімально потрібні поля
    'supports'            => ['title','excerpt','custom-fields'],
  ]);

  // meta для REST/синхронізації
  register_post_meta('resource','external_url',[
    'show_in_rest'=> true,'single'=> true,'type'=> 'string',
    'sanitize_callback'=>'esc_url_raw','auth_callback'=> fn()=> current_user_can('edit_posts')
  ]);
  register_post_meta('resource','file_id',[
    'show_in_rest'=> true,'single'=> true,'type'=> 'integer',
    'auth_callback'=> fn()=> current_user_can('edit_posts')
  ]);
  // технічні мета-ключі
  register_post_meta('resource','source_post',[ 'show_in_rest'=> false,'single'=> true,'type'=> 'integer' ]);
  register_post_meta('resource','block_uid',  [ 'show_in_rest'=> false,'single'=> true,'type'=> 'string'  ]);
});


/*** 2) Пермалінк ресурсу → файл або зовнішній URL ***/
add_filter('post_type_link', function($permalink, $post){
  if ($post->post_type !== 'resource') return $permalink;
  $file_id  = (int) get_post_meta($post->ID, 'file_id', true);
  $ext_url  = trim((string) get_post_meta($post->ID, 'external_url', true));
  if ($file_id) { $file = wp_get_attachment_url($file_id); if ($file) return $file; }
  if ($ext_url) return esc_url($ext_url);
  return $permalink;
}, 10, 2);


/*** 3) pre_get_posts: Пошук і сторінка /news ***/
add_action('pre_get_posts', function($q){
  if ( is_admin() || ! $q->is_main_query() ) return;

  // A) Сторінка /news → тільки пости (новини)
  if ( is_page('news') ) {
    $q->set('post_type', 'post');
    return;
  }

  // B) Пошук: без ?type → ВСІ (post,page,resource)
  if ( $q->is_search() ) {
    $map = [
      'posts'     => 'post',
      'pages'     => 'page',
      'resources' => 'resource',
    ];
    $type = isset($_GET['type']) ? sanitize_key($_GET['type']) : null;

    if ($type && isset($map[$type])) {
      $q->set('post_type', $map[$type]);
      // Додаткове звуження ресурсів за ?rtype=
      if ($map[$type]==='resource' && isset($_GET['rtype'])) {
        $rtype = sanitize_key($_GET['rtype']);
        if ($rtype === 'pdf')  $q->set('meta_query', [[ 'key'=>'file_id', 'compare'=>'EXISTS' ]]);
        if ($rtype === 'link') $q->set('meta_query', [[ 'key'=>'external_url', 'value'=>'', 'compare'=>'!=' ]]);
      }
  } else {
    $q->set('post_type', ['post','page','resource']);
    $q->set('orderby',   [ 'relevance' => 'DESC', 'date' => 'DESC' ]);

    add_filter('posts_clauses', function($clauses, $query) use ($q) {
      if ($query !== $q) return $clauses;
      global $wpdb;
      $case =
        "CASE {$wpdb->posts}.post_type " .
        $wpdb->prepare("WHEN %s THEN 0 ", 'page') .
        $wpdb->prepare("WHEN %s THEN 1 ", 'resource') .
        $wpdb->prepare("WHEN %s THEN 2 ", 'post') .
        "ELSE 999 END";

      $clauses['orderby'] = $case . " ASC, " . $clauses['orderby'];
      return $clauses;
    }, 20, 2);
  }
  }
}, 11);





/*** 4) СИНХРОНІЗАТОР: створення/оновлення + видалення відсутніх ресурсів ***/
add_action('save_post', function($post_id, $post){
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if ($post->post_type === 'revision' || $post->post_type === 'resource') return;
  if (!current_user_can('edit_post', $post_id)) return;

  $content = $post->post_content ?? '';
  $uids_present = [];   // UID-и карток, що реально є в контенті

  // дістаємо всі блоки materials-card (рекурсивно)
  if ($content) {
    $find_blocks = function($blocks, &$found) use (&$find_blocks){
      foreach ($blocks as $b) {
        if (!empty($b['blockName']) && $b['blockName']==='parts-blocks/materials-card') {
          $found[] = $b;
        }
        if (!empty($b['innerBlocks'])) $find_blocks($b['innerBlocks'], $found);
      }
    };
    $blocks = parse_blocks($content);
    $materials = [];
    $find_blocks($blocks, $materials);

    // створення/оновлення
    foreach ($materials as $b) {
      $a      = isset($b['attrs']) ? $b['attrs'] : [];
      $uid    = isset($a['uid']) ? sanitize_text_field($a['uid']) : '';
      $title  = isset($a['title']) ? wp_strip_all_tags($a['title']) : '';
      $file   = isset($a['file']) ? esc_url_raw(trim($a['file'])) : '';
      $fileId = isset($a['fileId']) ? (int)$a['fileId'] : 0;

      if (!$uid) continue;
      $uids_present[] = $uid;

      if (!$title && !$file && !$fileId) continue;

      // знайти існуючий ресурс за (source_post + block_uid)
      $existing = get_posts([
        'post_type'        => 'resource',
        'post_status'      => 'any',
        'meta_query'       => [
          ['key'=>'source_post','value'=>$post_id,'compare'=>'='],
          ['key'=>'block_uid','value'=>$uid,'compare'=>'='],
        ],
        'numberposts'      => 1,
        'fields'           => 'ids',
        'no_found_rows'    => true,
        'suppress_filters' => true,
      ]);
      $res_id = $existing ? $existing[0] : 0;

      // якщо є лише URL і немає fileId — спробуємо отримати ID вкладення
      if (!$fileId && $file) {
        $maybe_id = attachment_url_to_postid($file);
        if ($maybe_id) $fileId = $maybe_id;
      }

      $title_eff = $title ?: ($file ? wp_basename(parse_url($file, PHP_URL_PATH) ?: '') : 'Матеріал');
      $excerpt = '';
      if ($fileId) {
        $excerpt = 'Матеріал PDF на тему «' . $title_eff . '»';
      } elseif ($file) {
        $excerpt = 'Зовнішній матеріал на тему «' . $title_eff . '»';
      }

      $postarr = [
        'post_type'    => 'resource',
        'post_status'  => 'publish',
        'post_title'   => $title_eff,
        'post_excerpt' => $excerpt,
      ];

      if ($res_id) {
        $postarr['ID'] = $res_id;
        wp_update_post($postarr);
      } else {
        $res_id = wp_insert_post($postarr);
        if (is_wp_error($res_id) || !$res_id) continue;
        update_post_meta($res_id,'source_post',$post_id);
        update_post_meta($res_id,'block_uid',$uid);
      }

      // one-of: або file_id, або external_url
      if ($fileId) {
        update_post_meta($res_id,'file_id',$fileId);
        delete_post_meta($res_id,'external_url');
      } elseif ($file) {
        if (!preg_match('#^https?://#i',$file)) $file = 'https://' . ltrim($file,'/');
        update_post_meta($res_id,'external_url', esc_url_raw($file));
        delete_post_meta($res_id,'file_id');
      }
    }
  }

  // ВИДАЛЕННЯ ВІДСУТНІХ РЕСУРСІВ
  $meta_query_base = [
    ['key'=>'source_post','value'=>$post_id,'compare'=>'='],
  ];

  if (!empty($uids_present)) {
    // видаляємо всі, чиї block_uid НЕ в списку (або відсутній)
    $to_delete = get_posts([
      'post_type'        => 'resource',
      'post_status'      => 'any',
      'fields'           => 'ids',
      'numberposts'      => -1,
      'no_found_rows'    => true,
      'suppress_filters' => true,
      'meta_query'       => array_merge($meta_query_base, [[
        'relation' => 'OR',
        ['key'=>'block_uid','value'=>$uids_present,'compare'=>'NOT IN'],
        ['key'=>'block_uid','compare'=>'NOT EXISTS'],
      ]]),
    ]);
  } else {
    // карток немає — зносимо всі ресурси цього поста
    $to_delete = get_posts([
      'post_type'        => 'resource',
      'post_status'      => 'any',
      'fields'           => 'ids',
      'numberposts'      => -1,
      'no_found_rows'    => true,
      'suppress_filters' => true,
      'meta_query'       => $meta_query_base,
    ]);
  }

  if (!empty($to_delete)) {
    foreach ($to_delete as $rid) {
      // жорстке видалення з БД
      wp_delete_post((int)$rid, true);
    }
  }
}, 20, 2);
