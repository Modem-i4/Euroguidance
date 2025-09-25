<?php
// render.php для parts-blocks/reading-time (динамічний блок через block.json->render)

// 1) Пост (контекст Query Loop або поточний глобальний пост)
$post_id = ( isset( $block->context['postId'] ) && $block->context['postId'] )
  ? (int) $block->context['postId']
  : (int) get_the_ID();

if ( ! $post_id ) return '';

// 2) Атрибути
$wpm       = isset( $attributes['wpm'] ) ? (int) $attributes['wpm'] : 200;
$showLabel = isset( $attributes['showLabel'] ) ? (bool) $attributes['showLabel'] : true;
$label     = ( isset( $attributes['label'] ) && $attributes['label'] !== '' ) ? $attributes['label'] : 'хв читання';

// 3) Готуємо текст для підрахунку (враховуємо блоки/екстракти)
$content_raw = (string) get_post_field( 'post_content', $post_id );

// Якщо контент — блоки, рендеримо їх у HTML, інакше застосуємо the_content фільтри
if ( function_exists( 'has_blocks' ) && has_blocks( $content_raw ) ) {
	$content_html = do_blocks( $content_raw );
} else {
	$content_html = apply_filters( 'the_content', $content_raw );
}

// Чистимо HTML → текст
$content = wp_strip_all_tags( $content_html, true );

// Якщо порожньо після рендеру блоків — пробуємо excerpt
if ( $content === '' ) {
	$excerpt_html = get_the_excerpt( $post_id );
	$content = wp_strip_all_tags( (string) $excerpt_html, true );
}

// Замінюємо нерозривні пробіли на звичайні
if ( $content !== '' ) {
	$content = preg_replace( '/\x{00A0}|\xC2\xA0/u', ' ', $content );
}

// 4) Підрахунок слів (UTF-8/укр)
$word_count = 0;
if ( $content !== '' ) {
	if ( preg_match_all( "/\p{L}[\p{L}\p{Mn}\p{Pd}']*/u", $content, $m ) ) {
		$word_count = count( $m[0] );
	}
	// Якщо все ще 0, грубо оцінюємо з символів (fallback)
	if ( $word_count === 0 ) {
		$chars = preg_split( '//u', trim( $content ), -1, PREG_SPLIT_NO_EMPTY );
		$word_count = $chars ? (int) floor( count( $chars ) / 5 ) : 0;
	}
}

if ( $word_count <= 0 ) {
	// Якщо зовсім нема тексту — нічого не виводимо
	return '';
}

// 5) Обрахунок хвилин
$wpm     = max( 100, $wpm );
$minutes = max( 1, (int) ceil( $word_count / $wpm ) );

// 6) Вивід
$text = (string) $minutes . ( $showLabel && $label ? ' ' . $label : '' );
echo '<span class="wp-block-parts-blocks-reading-time">' . esc_html( $text ) . '</span>';
