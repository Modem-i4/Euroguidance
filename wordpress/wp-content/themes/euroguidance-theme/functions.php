<?php

require_once get_template_directory() . '/includes/create-categories.php';

function init_custom_blocks() {
    wp_register_block_types_from_metadata_collection(
        __DIR__ . '/build',
        __DIR__ . '/build/blocks-manifest.php'
    );
}
add_action( 'init', 'init_custom_blocks' );

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'mytheme-style',
        get_template_directory_uri() . '/css/style.css',
        [],
        filemtime( get_template_directory() . '/css/style.css' )
    );

    foreach ( glob( get_template_directory() . '/js/*.js' ) as $file ) {
        $handle = 'mytheme-' . basename( $file, '.js' );
        wp_enqueue_script(
            $handle,
            get_template_directory_uri() . '/js/' . basename( $file ),
            [],
            filemtime( $file ),
            true
        );
    }
});

add_action( 'after_setup_theme', function () {
    add_theme_support( 'editor-styles' );
    add_editor_style( 'css/style.css' );
});
wp_set_script_translations('trp-block-controls', 'translatepress-multilingual', plugin_dir_path(__FILE__) . 'languages/plugins');

require_once get_template_directory() . '/includes/pdf-search-index.php';
require_once get_template_directory() . '/includes/permalinks.php';
require_once get_template_directory() . '/includes/create-content.php';
require_once get_template_directory() . '/includes/adjust-menus.php';
require_once get_template_directory() . '/includes/tp-cleanup.php';
require_once get_template_directory() . '/includes/lang.php';