<?php
if (!defined('ABSPATH')) exit;

/**
 * Спільні налаштування для "Амбасадори" і "Експерти"
 */
add_action('init', function () {
  $people_types = [
    'ambassador' => ['label' => 'Амбасадори', 'icon' => 'dashicons-groups'],
    'expert'     => ['label' => 'Експерти',    'icon' => 'dashicons-welcome-learn-more'],
  ];

  foreach ($people_types as $type => $cfg) {
    register_post_type($type, [
      'label'        => $cfg['label'],
      'public'       => true,
      'show_in_rest' => true,
      'supports'     => ['title','editor','thumbnail','custom-fields'],
      'menu_icon'    => $cfg['icon'],
      'template'     => [
        [ 'parts-blocks/meta-field', [ 'key' => 'role',  'label' => 'Роль',  'placeholder' => 'напр., Координаторка проєктів' ] ],
        [ 'parts-blocks/meta-field', [ 'key' => 'descr', 'label' => 'Опис', 'placeholder' => 'Короткий опис (верхній текст)' ] ],
      ],
    ]);

    // meta під обидва ключі
    $m = ['single'=>true, 'show_in_rest'=>true, 'auth_callback'=>'__return_true'];
    register_post_meta($type, 'role',  ['type'=>'string'] + $m);
    register_post_meta($type, 'descr', ['type'=>'string'] + $m);
  }
});

/**
 * Скрипт панелі мета (один і той самий — для обох CPT)
 */
add_action('enqueue_block_editor_assets', function () {
  wp_enqueue_script(
    'people-meta-panel',
    get_stylesheet_directory_uri() . '/js/amb-meta-panel.js',
    ['wp-plugins','wp-edit-post','wp-element','wp-components','wp-data','wp-core-data'],
    null,
    true
  );
});

/**
 * Колонки в адмінці (для обох CPT)
 */
add_filter('manage_edit-ambassador_columns', 'people_add_cols');
add_filter('manage_edit-expert_columns',     'people_add_cols');
function people_add_cols($cols) {
  $ins = ['role' => 'Роль', 'descr' => 'Опис'];
  $new = [];
  foreach ($cols as $k=>$v) {
    $new[$k] = $v;
    if ($k === 'title') $new += $ins;
  }
  return $new;
}

add_action('manage_ambassador_posts_custom_column', 'people_fill_cols', 10, 2);
add_action('manage_expert_posts_custom_column',     'people_fill_cols', 10, 2);
function people_fill_cols($col, $post_id) {
  if ($col === 'role')  echo esc_html(get_post_meta($post_id, 'role',  true));
  if ($col === 'descr') echo esc_html(get_post_meta($post_id, 'descr', true));
}

add_filter('manage_edit-ambassador_sortable_columns', 'people_sortable_cols');
add_filter('manage_edit-expert_sortable_columns',     'people_sortable_cols');
function people_sortable_cols($cols) {
  $cols['role']  = 'role';
  $cols['descr'] = 'descr';
  return $cols;
}

/**
 * Сортування по meta (для обох CPT)
 */
add_action('pre_get_posts', function ($q) {
  if (!is_admin() || !$q->is_main_query()) return;
  if (!in_array($q->get('post_type'), ['ambassador','expert'], true)) return;

  $orderby = $q->get('orderby');
  if ($orderby === 'role' || $orderby === 'descr') {
    $q->set('meta_key', $orderby);
    $q->set('orderby', 'meta_value'); // meta_value_num для чисел
  }
});

/**
 * Quick Edit (поля + збереження) — для обох CPT
 */
add_action('quick_edit_custom_box', function($col, $post_type){
  if (!in_array($post_type, ['ambassador','expert'], true)) return;
  if (!in_array($col, ['role','descr'], true)) return; ?>
  <fieldset class="inline-edit-col-left">
    <div class="inline-edit-col">
      <?php if ($col==='role'): ?>
        <label><span class="title">Роль</span>
          <span class="input-text-wrap"><input type="text" name="role" value=""></span>
        </label>
      <?php else: ?>
        <label><span class="title">Опис</span>
          <span class="input-text-wrap"><input type="text" name="descr" value=""></span>
        </label>
      <?php endif; ?>
    </div>
  </fieldset>
<?php }, 10, 2);

add_action('save_post_ambassador', 'people_save_qe');
add_action('save_post_expert',     'people_save_qe');
function people_save_qe($post_id){
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;
  foreach (['role','descr'] as $k){
    if (isset($_POST[$k])) update_post_meta($post_id, $k, sanitize_text_field($_POST[$k]));
  }
}

/**
 * Підставити значення у форму Quick Edit (JS) — для обох CPT
 */
add_action('admin_print_footer_scripts-edit.php', function () {
  $screen = get_current_screen();
  if (!$screen || !in_array($screen->post_type, ['ambassador','expert'], true)) return; ?>
<script>
jQuery(function($){
  var $wp_inline_edit = inlineEditPost.edit;
  inlineEditPost.edit = function(id){
    $wp_inline_edit.apply(this, arguments);
    var postId = (typeof(id)==='object') ? this.getId(id) : id;
    if (!postId) return;

    var $row = $('#post-'+postId);
    var role = $('.column-role',  $row).text().trim();
    var descr = $('.column-descr', $row).text().trim();

    var $qe = $('#edit-'+postId);
    $('input[name="role"]',  $qe).val(role);
    $('input[name="descr"]', $qe).val(descr);
  };
});
</script>
<?php });
