<?php
function register_custom_block_categories( $categories ) {
    $new_categories = array(
        array(
            'slug'  => 'design-blocks',
            'title' => __( 'Легкий дизайн', 'design-blocks' ),
            'icon'  => 'star-filled',
        ),
        array(
            'slug'  => 'page-blocks',
            'title' => __( 'Блоки сторінок', 'page-blocks' ),
            'icon'  => 'layout',
        ),
        array(
            'slug'  => 'parts-blocks',
            'title' => __( 'Складові блоки', 'parts-blocks' ),
            'icon'  => 'archive',
        ),
    );
    return array_merge( $new_categories, $categories );
}
add_filter( 'block_categories_all', 'register_custom_block_categories', 10, 1 );