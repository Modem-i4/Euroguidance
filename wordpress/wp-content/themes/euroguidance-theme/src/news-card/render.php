<?php
// render.php для my/card-block (без register_block_type)

// Стало (контекст Query Loop або поточний глобальний пост з твого WP_Query):
$post_id = isset( $block->context['postId'] ) && $block->context['postId']
  ? (int) $block->context['postId']
  : (int) get_the_ID();

if ( ! $post_id ) return;

// 2) Атрибути
$fallback_image = ! empty( $attributes['fallbackImage'] ) ? esc_url( $attributes['fallbackImage'] ) : '';
$excerpt_len    = isset( $attributes['excerptLength'] ) ? max( 0, (int) $attributes['excerptLength'] ) : 200;

// 3) Дані поста
$title      = get_the_title( $post_id );
$permalink  = get_permalink( $post_id );
$excerpt    = wp_trim_words( get_the_excerpt( $post_id ), $excerpt_len, ' […]' );
$title_attr = the_title_attribute( [ 'echo' => false, 'post' => $post_id ] );

// "N часу тому" (локальний час WP)
$date_diff = human_time_diff(
	get_post_time( 'U', false, $post_id ),
	current_time( 'timestamp' )
) . ' тому';
?>

<div class="news-latest-card swiper-slide">
	<a class="card-wrapper" href="<?php echo esc_url( $permalink ); ?>">
		<div class="card-image-wrapper">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<?php echo get_the_post_thumbnail( $post_id, 'medium', [ 'class' => 'card-image' ] ); ?>
			<?php elseif ( $fallback_image ) : ?>
				<img src="<?php echo $fallback_image; ?>" alt="<?php echo esc_attr( $title_attr ); ?>" class="card-image" />
			<?php else : ?>
				<div class="card-image-placeholder"></div>
			<?php endif; ?>
		</div>

		<div class="card-gradient-bar"></div>

		<div class="card-content">
			<h3 class="card-title"><?php echo esc_html( $title ); ?></h3>
            <div class="card-excerp"><?php echo esc_html( $excerpt ); ?></div>
			<div class="card-meta">
				<div class="card-date"><?php echo esc_html( $date_diff ); ?></div>
			</div>
		</div>
	</a>
</div>
