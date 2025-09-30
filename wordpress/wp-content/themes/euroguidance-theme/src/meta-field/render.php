<?php
// Динамічний рендер на фронті
$post_id = $block->context['postId'] ?? get_the_ID();
$key     = isset($attributes['key']) ? sanitize_key($attributes['key']) : '';
$tag     = (!empty($attributes['as']) && preg_match('~^[a-z0-9-]+$~i', $attributes['as'])) ? $attributes['as'] : 'span';

if (!$post_id || !$key) return '';

$raw = get_post_meta($post_id, $key, true);
if ($raw === '' || $raw === null) return '';

// якщо у властивостях обрано тип URL — зробимо лінк
if ( !empty($attributes['fieldType']) && $attributes['fieldType'] === 'url' ) {
  $href = esc_url($raw);
  return '<a class="meta-'.esc_attr($key).'" href="'.$href.'" target="_blank" rel="noopener">'.$href.'</a>';
}

// текстовий вивід
return '<'.$tag.' class="meta-'.esc_attr($key).'">'.esc_html($raw).'</'.$tag.'>';
