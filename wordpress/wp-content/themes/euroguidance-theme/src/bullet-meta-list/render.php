<?php
// Вивід на фронті з meta як масиву рядків
if ( ! defined( 'ABSPATH' ) ) exit;

$post_id = $block->context['postId'] ?? get_the_ID();
$metaKey = isset($attributes['metaKey']) ? sanitize_key($attributes['metaKey']) : '';
$show    = isset($attributes['showOnFront']) ? (bool)$attributes['showOnFront'] : true;

if (! $show || ! $post_id || ! $metaKey) return '';

$raw = get_post_meta($post_id, $metaKey, true);

// Перетворення у масив рядків
$items = [];
if (is_array($raw)) {
  foreach ($raw as $t) {
    $t = trim(wp_strip_all_tags((string)$t));
    if ($t !== '') $items[] = $t;
  }
} elseif (is_string($raw) && $raw !== '') {
  if (strpos($raw, '<li') !== false && preg_match_all('~<li\b[^>]*>(.*?)</li>~is', $raw, $m)) {
    foreach ($m[1] as $li) {
      $t = trim(wp_strip_all_tags($li));
      if ($t !== '') $items[] = $t;
    }
  } else {
    $t = trim(wp_strip_all_tags($raw));
    if ($t !== '') $items[] = $t;
  }
}

if (empty($items)) return '';

$base_class = 'bullet-meta-list bullet-meta-list--' . sanitize_html_class($metaKey);

// Додаємо всі класи/стилі/якір з редактора до <ul>
$wrapper_attrs = get_block_wrapper_attributes([
  'class' => $base_class,
]);

$out  = '<ul ' . $wrapper_attrs . '>';
foreach ($items as $t) {
  $out .= '<li>' . esc_html($t) . '</li>';
}
$out .= '</ul>';

echo $out;
