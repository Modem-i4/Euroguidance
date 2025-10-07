<?php

require_once get_template_directory() . '/includes/create-categories.php';

function init_custom_blocks() {
    wp_register_block_types_from_metadata_collection(
        __DIR__ . '/build',
        __DIR__ . '/build/blocks-manifest.php'
    );
}
add_action( 'init', 'init_custom_blocks' );

function mytheme_assets_cfg(): array {
    return [
        'required' => [
            'mytheme-style' => 'css/style.css',
            'mytheme-header'   => 'css/header.css',
            'mytheme-hero'     => 'css/hero.css',
            'mytheme-lang'     => 'css/lang-switcher.css',
            'mytheme-buttons'  => 'css/buttons__main-page.css',
        ],
        'deferred' => [
            'mytheme-search'     => 'css/search.css',
            'mytheme-checks'     => 'css/checks.css',
            'mytheme-form'       => 'css/contact-form.css',
            'mytheme-footer'     => 'css/footer.css',
            'mytheme-links'      => 'css/useful-links.css',
            'mytheme-centers'    => 'css/national-centers.css',
            'mytheme-cover'      => 'css/cover-letter.css',
            'mytheme-about'      => 'css/about-page.css',
            'mytheme-intermob'   => 'css/inter-mobility-page.css',
            'mytheme-ambassadors'=> 'css/ambasadors.css',
            'mytheme-services'=> 'css/services.css',
        ],
    ];
}
function mytheme_ver($rel) {
    $p = get_template_directory() . '/' . ltrim($rel, '/');
    return file_exists($p) ? filemtime($p) : null;
}

add_action('wp_enqueue_scripts', function () {
    $cfg = mytheme_assets_cfg();
    $url = get_template_directory_uri();

    foreach ($cfg['required'] as $h => $rel) {
        wp_enqueue_style($h, $url . '/' . $rel, [], mytheme_ver($rel));
    }

    $deps = array_keys($cfg['required']);
    foreach ($cfg['deferred'] as $h => $rel) {
        wp_enqueue_style($h, $url . '/' . $rel, $deps, mytheme_ver($rel));
    }

    $dir_js = get_template_directory() . '/js';
    if (is_dir($dir_js)) {
        foreach (glob($dir_js . '/*.js') as $f) {
            $h = 'mytheme-' . basename($f, '.js');
            wp_enqueue_script($h, $url . '/js/' . basename($f), [], filemtime($f), true);
            wp_script_add_data($h, 'defer', true);
        }
    }

    $GLOBALS['MYTHEME_DEFER_STYLES'] = array_keys($cfg['deferred']);
}, 20);

add_filter('style_loader_tag', function ($html, $handle, $href, $media) {
    if (is_admin() || is_user_logged_in() && is_admin_bar_showing()) {
        return $html;
    }
    if (in_array($GLOBALS['MYTHEME_DEFER_STYLES'] ?? [], [null, []], true)) {
        return $html;
    }
    if (in_array($handle, $GLOBALS['MYTHEME_DEFER_STYLES'], true)) {
        return
            "<link rel='preload' as='style' href='{$href}' />\n" .
            "<link rel='stylesheet' href='{$href}' media='print' onload=\"this.media='all'\" />\n" .
            "<noscript><link rel='stylesheet' href='{$href}' /></noscript>\n";
    }
    return $html;
}, 10, 4);

add_action('after_setup_theme', function () {
    add_theme_support('editor-styles');
    $cfg = mytheme_assets_cfg();
    add_editor_style(array_merge(
        array_values($cfg['required']),
        array_values($cfg['deferred'])
    ));
});

wp_set_script_translations('trp-block-controls', 'translatepress-multilingual', plugin_dir_path(__FILE__) . 'languages/plugins');

require_once get_template_directory() . '/includes/pdf-search-index.php';
require_once get_template_directory() . '/includes/permalinks.php';
require_once get_template_directory() . '/includes/create-content.php';
require_once get_template_directory() . '/includes/adjust-menus.php';
require_once get_template_directory() . '/includes/tp-cleanup.php';
require_once get_template_directory() . '/includes/lang.php';
require_once get_template_directory() . '/includes/CPTs.php';
require_once get_template_directory() . '/includes/meta-tags.php';
require_once get_template_directory() . '/includes/ambassador-socials.php';
