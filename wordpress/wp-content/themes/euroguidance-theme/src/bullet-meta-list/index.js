import './style.scss';
( function ( wp ) {
  const { registerBlockType } = wp.blocks;
  const { useBlockProps, RichText, InspectorControls } = wp.blockEditor;
  const { PanelBody, TextControl, ToggleControl, Notice, Spinner } = wp.components;
  const { useEntityProp } = wp.coreData;
  const { useSelect } = wp.data;
  const { useEffect, useMemo, useState } = wp.element;
  const { __ } = wp.i18n;

  // Безпечне екранування (щоб не падати в редакторі)
  const esc = (s) => (s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const itemsToHtml = (items) => {
    if (!Array.isArray(items)) return '';
    return items.map((t) => `<li>${esc(String(t))}</li>`).join('');
  };

  const htmlToArray = (html) => {
    if (typeof html !== 'string') return [];
    const re = /<li\b[^>]*>([\s\S]*?)<\/li>/gi;
    const out = [];
    let m;
    while ((m = re.exec(html))) {
      const txt = m[1]
        .replace(/<br\s*\/?>/gi, '\n')
        .replace(/<\/?[^>]+>/g, '')
        .trim();
      if (txt) out.push(txt);
    }
    return out;
  };

  registerBlockType('parts-blocks/bullet-meta-list', {
    edit: ( { attributes, setAttributes, context } ) => {
      const { metaKey = 'qual', showOnFront = true } = attributes;

      const contextPostId   = context?.postId;
      const contextPostType = context?.postType;

      const fallbackPostId = useSelect( (s) => s('core/editor')?.getCurrentPostId?.(), [] );
      const fallbackType   = useSelect( (s) => s('core/editor')?.getCurrentPostType?.(), [] );

      const postId   = contextPostId   ?? fallbackPostId;
      const postType = contextPostType ?? fallbackType;

      // Отримуємо meta; якщо запис ще не завантажився — покажемо Spinner
      const record = useSelect( (s) => {
        if (!postId || !postType) return null;
        return s('core').getEditedEntityRecord('postType', postType, postId);
      }, [ postId, postType ] );

      const [ meta, setMeta ] = useEntityProp( 'postType', postType || 'post', 'meta', postId );

      const metaVal = (metaKey && meta) ? meta[metaKey] : undefined;

      const items = useMemo( () => {
        if (Array.isArray(metaVal)) return metaVal;
        if (typeof metaVal === 'string') return htmlToArray(metaVal);
        return [];
      }, [ metaVal, metaKey, postId ] );

      const htmlFromMeta = useMemo(() => itemsToHtml(items), [items]);

      const [ html, setHtml ] = useState( htmlFromMeta );

      useEffect(() => {
        setHtml(htmlFromMeta);
      }, [ htmlFromMeta, postId, metaKey ]);

      const onChangeHtml = (val) => {
        // RichText іноді дає undefined — нормалізуємо до рядка
        const safeVal = typeof val === 'string' ? val : '';
        setHtml(safeVal);
        const arr = htmlToArray(safeVal);
        if (meta && metaKey) {
          setMeta({ ...meta, [metaKey]: arr });
        }
      };

      const blockProps = useBlockProps({
        className: 'bullet-meta-list__editor',
      });

      // Щоб блок “не зникав” в редакторі — тримаємо хоч один <li>
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

          <div { ...blockProps }>
            { !hasResolved && <Spinner /> }

            { missingKey && (
              <Notice status="warning" isDismissible={false}>
                {__('Вкажіть metaKey у панелі справа.', 'parts-blocks')}
              </Notice>
            ) }

            { !missingKey && notRegistered && (
              <Notice status="error" isDismissible={false}>
                {__('Meta key не зареєстрований для цього типу запису.', 'parts-blocks')}
              </Notice>
            ) }

            <RichText
              tagName="ul"
              multiline="li"
              value={ ensuredHtml }
              onChange={ onChangeHtml }
              placeholder={ __('Додайте елемент списку…', 'parts-blocks') }
              allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
            />
          </div>
        </>
      );
    },

    save: () => null,
  });

} )( window.wp );
