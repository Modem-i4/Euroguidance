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

// URL для "Більше"
$blog_page_id = (int) get_option( 'page_for_posts' );
$more_url = $blog_page_id ? get_permalink( $blog_page_id ) : get_post_type_archive_link( 'post' );

if ( $q->have_posts() ) : ?>
  <div class="swiper-container">
    <div
      class="news-slider swiper"
      data-slides-per-view="<?php echo esc_attr( $slides_per_view ); ?>"
      data-slides-gap-px="<?php echo esc_attr( $slides_gap_px ); ?>"
    >
      <div class="swiper-wrapper">
        <?php
        while ( $q->have_posts() ) : $q->the_post();
          // Рендеримо картку як блок, прокидаючи fallback
          $card_block = sprintf(
            '<!-- wp:parts-blocks/news-card {"fallbackImage":"%s"} /-->',
            esc_js( $fallback_image )
          );
          echo do_blocks( $card_block );
        endwhile; ?>
      </div>
    </div>

    <!-- НОВІ нижні контролі без пагінації -->
    <div class="ntd-news-carousel__controls">
      <button class="ntd-news-carousel__nav is-prev" type="button" aria-label="Попередня сторінка"></button>

      <?php if ( $more_url ) : ?>
        <a class="ntd-news-carousel__more" href="<?php echo esc_url( $more_url ); ?>">
          Більше
        </a>
      <?php else : ?>
        <button class="ntd-news-carousel__more" type="button">Більше</button>
      <?php endif; ?>

      <button class="ntd-news-carousel__nav is-next" type="button" aria-label="Наступна сторінка"></button>
    </div>
  </div>
<?php
endif;
wp_reset_postdata();
