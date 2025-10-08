// Глобальні пакети WP доступні як wp.*
if(typeof wp !== 'undefined') {
  const { registerPlugin } = wp.plugins;
  const { PluginDocumentSettingPanel } = wp.editPost;
  const { TextControl } = wp.components;
  const { useSelect } = wp.data;
  const { useEntityProp } = wp.coreData;
  const { createElement: h, Fragment } = wp.element;

  function AmbMetaPanel() {
    // Показуємо панель тільки для пост-тайпу ambassador
    const postType = useSelect( (sel) => sel('core/editor').getCurrentPostType(), [] );
    if (postType !== 'ambassador') return null;

    // meta ←→ setMeta працюють через REST; поля мають бути register_post_meta(... show_in_rest:true)
    const [ meta, setMeta ] = useEntityProp('postType', postType, 'meta');

    const upd = (key) => (value) => setMeta({ ...meta, [key]: value });

    return h(PluginDocumentSettingPanel, { title: '*Поля амбасадора*', name: 'ambassador-meta' },
      h(Fragment, null,
        h(TextControl, {
          label: 'Роль',
          value: meta?.role || '',
          onChange: upd('role'),
          placeholder: 'напр., Координаторка проєктів'
        }),
        h(TextControl, {
          label: 'Опис',
          value: meta?.descr || '',
          onChange: upd('descr'),
          placeholder: 'напр., NQA / Nova Traditio'
        })
      )
    );
  }

  registerPlugin('ambassador-meta-panel', { render: AmbMetaPanel });
}

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".people .wp-block-post, .people li.wp-block-post").forEach(function(card){
    var labelEl = card.querySelector(".spec-text strong") || card.querySelector(".spec-text");
    if (!labelEl) return;

    var specBlock = labelEl.closest(".spec-text");
    var sib = specBlock ? specBlock.nextElementSibling : null;
    var specList = null;
    while (sib) {
      if (sib.tagName === "UL") { specList = sib; break; }
      sib = sib.nextElementSibling;
    }
    if (!specList) specList = card.querySelector("ul.bullet-meta-list--spec");

    if (!specList) return;
    var count = specList.querySelectorAll("li").length;

    labelEl.textContent = (count === 1) ? "Спеціалізація:" : "Спеціалізації:";
  });
});
