<?php

/* === Meta Description === */
add_action('wp_head', function () {
    if ( ! is_singular() ) return;
    if ( defined('WPSEO_VERSION') || class_exists('RankMath') || defined('AIOSEO_VERSION') ) return;

    $desc = wp_strip_all_tags( get_the_excerpt(), true );
    if ($desc !== '') {
        if (function_exists('mb_substr')) $desc = mb_substr($desc, 0, 156);
        else                             $desc = substr($desc, 0, 156);
        echo '<meta name="description" content="'. esc_attr($desc) .'">' . "\n";
        echo '<meta name="og:description" content="'. esc_attr($desc) .'">' . "\n";
    }
}, 5);

/* === Meta Keywords у Quick Edit: AJAX-варіант === */
if (!defined('ABSPATH')) exit;

define('MKQE_KEY', 'meta_keywords');

/* 1) Реєстрація мета-поля для post/page */
add_action('init', function () {
    foreach (['post','page'] as $t) {
        register_post_meta($t, MKQE_KEY, [
            'show_in_rest'      => true,
            'single'            => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback'     => fn() => current_user_can('edit_posts'),
        ]);
    }
});

/* 2) Збереження при Quick Edit / звичайн. редагуванні */
add_action('save_post', function($post_id){
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST[MKQE_KEY])) {
        $val = sanitize_text_field($_POST[MKQE_KEY]);
        update_post_meta($post_id, MKQE_KEY, $val);
    }
});

/* 3) AJAX: беремо актуальне значення keywords */
add_action('wp_ajax_mkqe_get_keywords', function () {
    check_ajax_referer('mkqe_nonce');
    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_send_json_error(['message' => 'denied'], 403);
    }
    $val = (string) get_post_meta($post_id, MKQE_KEY, true);
    wp_send_json_success(['value' => $val]);
});

/* 4) Quick Edit: вставляємо інпут і підтягуємо значення через AJAX */
add_action('admin_print_footer_scripts-edit.php', function () {
    $nonce = wp_create_nonce('mkqe_nonce');
    ?>
<script>
jQuery(function($){
  const FIELD = '<?php echo esc_js(MKQE_KEY); ?>';
  const NONCE = '<?php echo esc_js($nonce); ?>';

  function ensureField($row){
    if ($row.find('input[name="'+FIELD+'"]').length) return;
    let $container = $row.find('.inline-edit-col-right .inline-edit-col');
    if (!$container.length) $container = $row.find('.inline-edit-col');
    const html =
    '<label class="inline-edit-group mkqe-field" style="display:block;margin-top:6px;">' +
        '<span class="title">Ключові слова</span>' +
        '<input type="text" name="'+FIELD+'" placeholder="Europass, навички, ..." />' +
    '</label>' +
    '<p class="description" style="margin:.25rem 0 0;line-height:1.4;">' +
        'Насправді ключові слова зараз не впливають на пошук (Google та інші ігнорують їх), ' +
        'але поле додано згідно ТЗ.' +
    '</p>';
    $container.append(html);
  }

  const orig = inlineEditPost.edit;
  inlineEditPost.edit = function(id){
    const r = orig.apply(this, arguments);
    const postId = (typeof id === 'object') ? this.getId(id) : id;
    const $edit  = $('#edit-' + postId);
    ensureField($edit);

    const $input = $edit.find('input[name="'+FIELD+'"]');
    $input.val('');

    $.ajax({
      url: ajaxurl,
      method: 'POST',
      dataType: 'json',
      data: { action: 'mkqe_get_keywords', _ajax_nonce: NONCE, post_id: postId }
    }).done(function(res){
      if (res && res.success) {
        $input.val(res.data.value || '');
      }
    });

    return r;
  };
});
</script>
<?php
});

/* 5) (Опційно) Виводимо <meta name="keywords"> у <head> */
add_action('wp_head', function(){
    if (is_singular()) {
        $v = get_post_meta(get_the_ID(), MKQE_KEY, true);
        if ($v) echo '<meta name="keywords" content="'.esc_attr($v).'">' . "\n";
    }
});
/* === /Meta Keywords === */
