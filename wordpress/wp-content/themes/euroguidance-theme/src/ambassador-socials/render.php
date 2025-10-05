<?php
/**
 * Server render for ntd/social-links.
 */
if ( ! function_exists( 'ntd_social_links_render' ) ) :
function ntd_social_links_render( $attributes = [], $content = '', $block = null ) {
	$post_id = 0;
	if ( is_object( $block ) && ! empty( $block->context['postId'] ) ) {
		$post_id = (int) $block->context['postId'];
	}
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}
	if ( ! $post_id ) {
		return '';
	}

	$raw = get_post_meta( $post_id, 'ntd_social_links', true );
	if ( is_string( $raw ) ) {
		$decoded = json_decode( $raw, true );
		$links   = is_array( $decoded ) ? $decoded : [];
	} elseif ( is_array( $raw ) ) {
		$links = $raw;
	} else {
		$links = [];
	}

	$icons = [
		'linkedin' => '<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M19.7,3H4.3C3.582,3,3,3.582,3,4.3v15.4C3,20.418,3.582,21,4.3,21h15.4c0.718,0,1.3-0.582,1.3-1.3V4.3 C21,3.582,20.418,3,19.7,3z M8.339,18.338H5.667v-8.59h2.672V18.338z M7.004,8.574c-0.857,0-1.549-0.694-1.549-1.548 c0-0.855,0.691-1.548,1.549-1.548c0.854,0,1.547,0.694,1.547,1.548C8.551,7.881,7.858,8.574,7.004,8.574z M18.339,18.338h-2.669 v-4.177c0-0.996-0.017-2.278-1.387-2.278c-1.389,0-1.601,1.086-1.601,2.206v4.249h-2.667v-8.59h2.559v1.174h0.037 c0.356-0.675,1.227-1.387,2.526-1.387c2.703,0,3.203,1.779,3.203,4.092V18.338z"></path></svg>',
		'instagram'=> '<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12,4.622c2.403,0,2.688,0.009,3.637,0.052c0.877,0.04,1.354,0.187,1.671,0.31c0.42,0.163,0.72,0.358,1.035,0.673 c0.315,0.315,0.51,0.615,0.673,1.035c0.123,0.317,0.27,0.794,0.31,1.671c0.043,0.949,0.052,1.234,0.052,3.637 s-0.009,2.688-0.052,3.637c-0.04,0.877-0.187,1.354-0.31,1.671c-0.163,0.42-0.358,0.72-0.673,1.035 c-0.315,0.315-0.615,0.51-1.035,0.673c-0.317,0.123-0.794,0.27-1.671,0.31c-0.949,0.043-1.233,0.052-3.637,0.052 s-2.688-0.009-3.637-0.052c-0.877-0.04-1.354-0.187-1.671-0.31c-0.42-0.163-0.72-0.358-1.035-0.673 c-0.315-0.315-0.51-0.615-0.673-1.035c-0.123-0.317-0.27-0.794-0.31-1.671C4.631,14.688,4.622,14.403,4.622,12 s0.009-2.688,0.052-3.637c0-0.877,0.187-1.354,0.31-1.671c0.163-0.42,0.358-0.72,0.673-1.035 c0.315-0.315,0.615-0.51,1.035-0.673c0.317-0.123,0.794-0.27,1.671-0.31C9.312,4.631,9.597,4.622,12,4.622 M12,3 C9.556,3,9.249,3.01,8.289,3.054C7.331,3.098,6.677,3.25,6.105,3.472C5.513,3.702,5.011,4.01,4.511,4.511 c-.5.5-.808,1.002-1.038,1.594C3.25,6.677,3.098,7.331,3.054,8.289C3.01,9.249,3,9.556,3,12c0,2.444,0.01,2.751,0.054,3.711 c0.044,0.958,0.196,1.612,0.418,2.185c0.23,0.592,0.538,1.094,1.038,1.594c0.5,0.5,1.002,0.808,1.594,1.038 c0.572,0.222,1.227,0.375,2.185,0.418C9.249,20.99,9.556,21,12,21s2.751-0.01,3.711-0.054c0.958-0.044,1.612-0.196,2.185-0.418 c0.592-0.23,1.094-0.538,1.594-1.038c0.5-0.5,0.808-1.002,1.038-1.594c0.222-0.572,0.375-1.227,0.418-2.185 C20.99,14.751,21,14.444,21,12s-0.01-2.751-0.054-3.711c-0.044-.958-.196-1.612-.418-2.185-.23-.592-.538-1.094-1.038-1.594 c-.5-.5-1.002-.808-1.594-1.038-.572-.222-1.227-.375-2.185-.418C14.751,3.01,14.444,3,12,3L12,3z M12,7.378 c-2.552,0-4.622,2.069-4.622,4.622S9.448,16.622,12,16.622s4.622-2.069,4.622-4.622S14.552,7.378,12,7.378z M12,15 c-1.657,0-3-1.343-3-3s1.343-3,3-3s3,1.343,3,3S13.657,15,12,15z M16.804,6.116c-0.596,0-1.08,0.484-1.08,1.08 s0.484,1.08,1.08,1.08c.596,0,1.08-0.484,1.08-1.08S17.401,6.116,16.804,6.116z"></path></svg>',
		'facebook' => '<svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12c0 5 3.7 9.1 8.4 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7C18.3 21.1 22 17 22 12c0-5.5-4.5-10-10-10z"></path></svg>',
		'website'  => '<svg width="24" height="24" viewBox="0 0 420 420" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="26" d="M209,15a195,195 0 1,0 2,0z"/><path fill="none" stroke="currentColor" stroke-width="18" d="m210,15v390m195-195H15M59,90a260,260 0 0,0 302,0 m0,240 a260,260 0 0,0-302,0M195,20a250,250 0 0,0 0,382 m30,0 a250,250 0 0,0 0-382"/></svg>',
	];

	$labels = [
		'website'  => __( 'Вебсайт', 'ntd' ),
		'linkedin' => 'LinkedIn',
		'facebook' => 'Facebook',
		'instagram'=> 'Instagram',
	];

	$show_labels_global = ! empty( $attributes['showLabels'] );

	$out = [];
	foreach ( $links as $item ) {
		if ( empty( $item['type'] ) || empty( $icons[ $item['type'] ] ) ) {
			continue;
		}
		$url = isset( $item['url'] ) ? esc_url( $item['url'] ) : '';
		if ( ! $url ) {
			continue;
		}
		$type        = sanitize_key( $item['type'] );
		$title_text  = $labels[ $type ] ?? ucfirst( $type );
		$title_attr  = esc_attr( $title_text );
		$per_item_on = ! isset( $item['showLabel'] ) ? true : (bool) $item['showLabel'];
		$show_this   = $show_labels_global && $per_item_on;

		$label_html  = $show_this ? sprintf(
			'<span class="ntd-social-links__label">%s</span>',
			esc_html( $title_text )
		) : '';

		$classes_btn = 'ntd-social-links__btn' . ( $show_this ? ' has-label' : '' );

		$out[] = sprintf(
			'<a class="%1$s" href="%2$s" aria-label="%3$s" target="_blank" rel="noopener nofollow">%4$s%5$s</a>',
			esc_attr( $classes_btn ),
			$url,
			$title_attr,
			$icons[ $type ],
			$label_html
		);
	}

	if ( ! $out ) {
		return '';
	}
    $mod = ! empty( $attributes['showLabels'] ) ? ' ntd-social-links--labels' : '';
    return '<div class="wp-block-ntd-social-links"><div class="ntd-social-links' . $mod . '"><div class="ntd-social-links__row">'
		. implode( '', $out )
		. '</div></div></div>';
}
endif;

echo ntd_social_links_render( $attributes ?? [], $content ?? '', $block ?? null );
