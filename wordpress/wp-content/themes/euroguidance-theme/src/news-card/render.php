<?php
// SSR рендер для parts-blocks/news-card

$post_id = isset( $block->context['postId'] ) && $block->context['postId']
  ? (int) $block->context['postId']
  : (int) get_the_ID();

if ( ! $post_id ) {
  return;
}

// Атрибути
$fallback_image = ! empty( $attributes['fallbackImage'] ) ? esc_url( $attributes['fallbackImage'] ) : '';
$excerpt_len    = isset( $attributes['excerptLength'] ) ? max( 0, (int) $attributes['excerptLength'] ) : 200;

// Дані поста
$title      = get_the_title( $post_id );
$permalink  = get_permalink( $post_id );
$excerpt    = wp_trim_words( get_the_excerpt( $post_id ), $excerpt_len, ' […]' );
$title_attr = the_title_attribute( [ 'echo' => false, 'post' => $post_id ] );

// Категорія (перша у списку)
$cat_name = '';
$terms = get_the_terms( $post_id, 'category' );
if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
  $first = array_shift( $terms );
  $cat_name = $first ? $first->name : '';
}

// Дата публікації у форматі DD/MM/YYYY
$pub_date = get_the_date( 'd/m/Y', $post_id );
?>
<div class="news-latest-card swiper-slide">
  <a class="card-wrapper" href="<?php echo esc_url( $permalink ); ?>">
    <div class="card-image-wrapper">
      <?php if ( has_post_thumbnail( $post_id ) ) : ?>
        <?php echo get_the_post_thumbnail( $post_id, 'large', [ 'class' => 'card-image' ] ); ?>
      <?php elseif ( $fallback_image ) : ?>
        <img src="<?php echo $fallback_image; ?>" alt="<?php echo esc_attr( $title_attr ); ?>" class="card-image" />
      <?php else : ?>
        <div class="card-image-placeholder"></div>
      <?php endif; ?>
    </div>

    <!-- лінія між фото та текстом видалена -->

    <div class="card-content">
      <div class="card-meta-top">
        <div class="card-date"><?php echo esc_html( $pub_date ); ?></div>
        <?php if ( $cat_name ) : ?>
          <span class="badge-category"><?php echo esc_html( $cat_name ); ?></span>
        <?php else : ?>
          <span class="badge-category is-empty">&nbsp;</span>
        <?php endif; ?>
      </div>

      <div class="card-title"><?php echo esc_html( $title ); ?></div>

      <div class="card-excerpt"><?php echo esc_html( $excerpt ); ?></div>
    </div>
  </a>
</div>
