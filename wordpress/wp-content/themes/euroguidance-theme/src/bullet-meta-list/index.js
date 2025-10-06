import './style.scss';
(function (wp) {
  const { registerBlockType } = wp.blocks;
  const { useBlockProps, RichText, InspectorControls } = wp.blockEditor;
  const { PanelBody, TextControl, ToggleControl, Notice, Spinner } = wp.components;
  const { useEntityProp } = wp.coreData;
  const { useSelect } = wp.data;
  const { useEffect, useMemo, useState } = wp.element;
  const { __ } = wp.i18n;

  const esc = (s) => (s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  // Декодуємо ентіті → сирий текст (для збереження в meta)
  const decodeEntities = (s) => {
    if (typeof s !== 'string') return '';
    let out = s;
    out = out.replace(/&nbsp;/gi, ' ');
    out = out.replace(/&#x([0-9a-f]+);/gi, (_, h) => String.fromCharCode(parseInt(h, 16)));
    out = out.replace(/&#(\d+);/g, (_, d) => String.fromCharCode(parseInt(d, 10)));
    out = out
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>')
      .replace(/&quot;/g, '"')
      .replace(/&#039;/g, "'")
      .replace(/&apos;/g, "'")
      .replace(/&amp;/g, '&');
    return out;
  };

  const itemsToHtml = (items) => {
    if (!Array.isArray(items)) return '';
    return items
      .map((t) => {
        const raw = String(t ?? '');
        const safe = esc(raw).replace(/\r?\n/g, '<br>');
        return `<li>${safe}</li>`;
      })
      .join('');
  };

  const htmlToArray = (html) => {
    if (typeof html !== 'string') return [];
    const re = /<li\b[^>]*>([\s\S]*?)<\/li>/gi;
    const out = [];
    let m;
    while ((m = re.exec(html))) {
      const inner = m[1]
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/<\/?[^>]+>/g, '');
      const txt = decodeEntities(inner).trim();
      if (txt) out.push(txt);
    }
    return out;
  };

  registerBlockType('parts-blocks/bullet-meta-list', {
    edit: ({ attributes, setAttributes, context }) => {
      const { metaKey = 'qual', showOnFront = true } = attributes;

      const contextPostId = context?.postId;
      const contextPostType = context?.postType;

      const fallbackPostId = useSelect((s) => s('core/editor')?.getCurrentPostId?.(), []);
      const fallbackType = useSelect((s) => s('core/editor')?.getCurrentPostType?.(), []);

      const postId = contextPostId ?? fallbackPostId;
      const postType = contextPostType ?? fallbackType;

      const record = useSelect((s) => {
        if (!postId || !postType) return null;
        return s('core').getEditedEntityRecord('postType', postType, postId);
      }, [postId, postType]);

      const [meta, setMeta] = useEntityProp('postType', postType || 'post', 'meta', postId);
      const metaVal = (metaKey && meta) ? meta[metaKey] : undefined;

      const items = useMemo(() => {
        if (Array.isArray(metaVal)) {
          return metaVal.map((v) => decodeEntities(typeof v === 'string' ? v : String(v ?? '')));
        }
        if (typeof metaVal === 'string') {
          return htmlToArray(metaVal);
        }
        return [];
      }, [metaVal, metaKey, postId]);

      const htmlFromMeta = useMemo(() => itemsToHtml(items), [items]);
      const [html, setHtml] = useState(htmlFromMeta);

      useEffect(() => {
        setHtml(htmlFromMeta);
      }, [htmlFromMeta, postId, metaKey]);

      const onChangeHtml = (val) => {
        const safeVal = typeof val === 'string' ? val : '';
        setHtml(safeVal);
        const arr = htmlToArray(safeVal); // уже декодовані сирі рядки
        if (meta && metaKey) {
          setMeta({ ...meta, [metaKey]: arr });
        }
      };

      const blockProps = useBlockProps({ className: 'bullet-meta-list__editor' });
      const ensuredHtml = (typeof html === 'string' && /<li\b/i.test(html)) ? html : '<li></li>';

      const missingKey = !metaKey || !metaKey.trim();
      const hasResolved = !!record;
      const notRegistered = hasResolved && meta && metaKey && typeof meta[metaKey] === 'undefined';

      return (
        <>
          <InspectorControls>
            <PanelBody title={__('Налаштування списку', 'parts-blocks')}>
              <TextControl
                label={__('Meta key', 'parts-blocks')}
                value={metaKey}
                onChange={(v) => setAttributes({ metaKey: (v || '').trim() })}
                help={__('Напр.: "qual" або "spec"', 'parts-blocks')}
              />
              <ToggleControl
                label={__('Показувати на фронті', 'parts-blocks')}
                checked={!!showOnFront}
                onChange={(v) => setAttributes({ showOnFront: !!v })}
              />
            </PanelBody>
          </InspectorControls>

          <div {...blockProps}>
            {!hasResolved && <Spinner />}

            {missingKey && (
              <Notice status="warning" isDismissible={false}>
                {__('Вкажіть metaKey у панелі справа.', 'parts-blocks')}
              </Notice>
            )}

            {!missingKey && notRegistered && (
              <Notice status="error" isDismissible={false}>
                {__('Meta key не зареєстрований для цього типу запису.', 'parts-blocks')}
              </Notice>
            )}

            <RichText
              tagName="ul"
              multiline="li"
              value={ensuredHtml}
              onChange={onChangeHtml}
              placeholder={__('Додайте елемент списку…', 'parts-blocks')}
              allowedFormats={['core/bold', 'core/italic', 'core/link']}
            />
          </div>
        </>
      );
    },

    save: () => null,
  });
})(window.wp);
