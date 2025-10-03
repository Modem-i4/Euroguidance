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
      'supports'     => ['title','editor','thumbnail','custom-fields'],
      'menu_icon'    => $cfg['icon'],
      'template'     => [
        [ [ 'core/columns', [], [ [ 'core/column', [ 'width' => '300px' ], [ [ 'core/post-featured-image', [ 'style' => [ 'border' => [ 'radius' => '0px' ] ] ] ], ]], [ 'core/column', [], [ [ 'core/post-title', [] ], [ 'core/separator', [] ], [ 'core/paragraph', [ 'content' => 'Ваш короткий опис', 'placeholder' => 'Ваш короткий опис' ] ], ]], ]], [ 'core/heading', [ 'textAlign' => 'center', 'level' => 1, 'content' => '<strong>Сертифікація та кваліфікація</strong>' ] ], [ 'parts-blocks/bullet-meta-list', [ 'metaKey' => 'qual', 'className' => 'checks-list green-checks' ] ], [ 'core/heading', [ 'textAlign' => 'center', 'level' => 1, 'content' => 'Спеціалізації' ] ], [ 'parts-blocks/bullet-meta-list', [ 'metaKey' => 'spec', 'className' => 'checks-list blue-checks' ] ], [ 'core/heading', [ 'textAlign' => 'center', 'level' => 1, 'content' => 'Досвід' ] ], [ 'parts-blocks/bullet-meta-list', [ 'metaKey' => 'exp' ] ], [ 'core/heading', [ 'textAlign' => 'center', 'level' => 1, 'content' => 'Контакти' ] ], [ 'core/group', [ 'style'  => [ 'spacing' => [ 'blockGap' => 'var:preset|spacing|50' ] ], 'layout' => [ 'type' => 'flex', 'flexWrap' => 'nowrap', 'justifyContent' => 'center' ], ], [ [ 'core/media-text', [ 'mediaWidth' => 15, 'isStackedOnMobile' => false, 'imageFill' => false, 'mediaType' => 'image', 'mediaUrl' => get_template_directory_uri() . '/assets/icons/email.svg', ], [ [ 'core/paragraph', [ 'content' => 'email', 'placeholder' => 'Вміст…', 'metadata' => [ 'bindings' => [ 'content' => [ 'source' => 'core/post-meta', 'args' => [ 'key' => 'email' ], ], ], ], ]], ] ], [ 'core/media-text', [ 'mediaWidth' => 15, 'isStackedOnMobile' => false, 'imageFill' => false, 'mediaType' => 'image', 'mediaUrl' => get_template_directory_uri() . '/assets/icons/phone.svg', ], [ [ 'core/paragraph', [ 'content' => 'телефон', 'placeholder' => 'Вміст…', 'metadata' => [ 'bindings' => [ 'content' => [ 'source' => 'core/post-meta', 'args' => [ 'key' => 'phone' ], ], ], ], ]], ] ], ] ], ],
      ],
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
  }
});
