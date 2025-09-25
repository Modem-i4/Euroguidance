<?php
add_filter('render_block', function ($html, $block) {
    if (($block['blockName'] ?? '') !== 'core/navigation-link') return $html;
    $className = $block['attrs']['className'] ?? '';
    if (strpos(' ' . $className . ' ', ' pb-lang-switch ') === false) return $html;

    $lang = determine_locale();
    $isEng = (strpos($lang, 'uk') === 0);
    $label = $isEng ? 'in English' : 'Українською';
    $flag = 'flag-'.($isEng?'en':'uk');

    $html = preg_replace(
        '/(<a[^>]*>)(.*?)(<\/a>)/',
        '$1<span class="wp-block-navigation-item__label '.$flag.'">'.$label.'</span>$3',
        $html
    );
    return $html;
}, 10, 2);
