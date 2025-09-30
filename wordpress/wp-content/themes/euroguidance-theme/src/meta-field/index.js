( function ( wp ) {
  const { useEntityProp } = wp.coreData;
  const { useSelect } = wp.data;
  const { TextControl, TextareaControl, SelectControl, Notice, PanelBody } = wp.components;
  const { useBlockProps, InspectorControls } = wp.blockEditor;
  const { createElement: h, Fragment } = wp.element;

  const Edit = ( { attributes, setAttributes } ) => {
    const { key, label, fieldType, placeholder } = attributes;

    const postType = useSelect( s => s('core/editor').getCurrentPostType(), [] );
    const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

    const val = key ? ( (meta && meta[key]) ?? '' ) : '';
    const update = (v) => key && setMeta( Object.assign( {}, meta, { [key]: v } ) );

    const bp = useBlockProps( { className: 'cust-meta-field' } );

    return h( Fragment, null,
      h( InspectorControls, null,
        h( PanelBody, { title: 'Налаштування поля' },
          h( TextControl, { label: 'Meta key', value: key, onChange: v => setAttributes( { key: v.trim() } ) } ),
          h( TextControl, { label: 'Ярлик (label)', value: label, onChange: v => setAttributes( { label: v } ) } ),
          h( SelectControl, {
            label: 'Тип поля', value: fieldType,
            options: [
              { label: 'Текст', value: 'text' },
              { label: 'URL', value: 'url' },
              { label: 'Число', value: 'number' },
              { label: 'Багаторядкове', value: 'textarea' }
            ],
            onChange: v => setAttributes( { fieldType: v } )
          } ),
          h( TextControl, { label: 'Placeholder', value: placeholder, onChange: v => setAttributes( { placeholder: v } ) } )
        )
      ),
      h( 'div', bp,
        !key && h( Notice, { status: 'warning', isDismissible: false }, 'Вкажіть meta key у властивостях блока' ),
        label && h( 'label', { style: { display:'block', marginBottom:'6px' } }, label ),
        fieldType === 'textarea'
          ? h( TextareaControl, { value: val, onChange: update, placeholder } )
          : h( TextControl, { type: fieldType, value: val, onChange: update, placeholder } )
      )
    );
  };

  wp.blocks.registerBlockType( 'parts-blocks/meta-field', {
    edit: Edit,
    save: () => null // збереження йде в post meta; фронт рендеримо PHP
  } );
} )( window.wp );
