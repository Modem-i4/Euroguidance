<?php
$posts_to_show   = $attributes['postsToShow']   ?? 6;
$slides_per_view = $attributes['slidesPerView'] ?? 2;
$slides_gap_px   = $attributes['slidesGapPx']   ?? 25;
$fallback_image  = ! empty( $attributes['fallbackImage'] ) ? esc_url( $attributes['fallbackImage'] ) : '';

$q = new WP_Query([
  'post_type'      => 'post',
  'posts_per_page' => $posts_to_show,
  'post_status'    => 'publish',
]);

if ( $q->have_posts() ) : ?>
  <div class="swiper-container">
    <div class="news-slider swiper"
         data-slides-per-view="<?php echo esc_attr( $slides_per_view ); ?>"
         data-slides-gap-px="<?php echo esc_attr( $slides_gap_px ); ?>">
      <div class="swiper-wrapper">
        <?php
        while ( $q->have_posts() ) : $q->the_post();

          // Рендеримо нашу картку як блок (передамо fallbackImage атрибутом)
          $card_block = sprintf(
            '<!-- wp:parts-blocks/news-card {"fallbackImage":"%s"} /-->',
            esc_js( $fallback_image )
          );
          echo do_blocks( $card_block );

        endwhile; ?>
      </div>
    </div>

    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-pagination"></div>
  </div>
<?php
endif;
wp_reset_postdata();
