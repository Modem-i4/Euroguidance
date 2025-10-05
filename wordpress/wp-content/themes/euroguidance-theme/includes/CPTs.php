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
  }
});
