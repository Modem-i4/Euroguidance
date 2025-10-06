import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, __experimentalItemGroup as ItemGroup, __experimentalItem as Item, Button } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

registerBlockType('ntd/posttype-filter', {
  edit({ attributes, setAttributes }) {
    const { paramName, items } = attributes;
    const blockProps = useBlockProps({ className: 'ntd-posttype-filter__wrap' });

    const updateItem = (i, patch) => {
      const next = items.slice();
      next[i] = { ...next[i], ...patch };
      setAttributes({ items: next });
    };

    const addItem = () => setAttributes({ items: [...items, { key: 'custom', label: 'Новий пункт' }] });
    const removeItem = (i) => setAttributes({ items: items.filter((_, idx) => idx !== i) });

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title="Налаштування">
            <TextControl
              label="Назва параметра в URL"
              value={paramName}
              onChange={(v) => setAttributes({ paramName: v.replace(/[^a-z0-9_-]/gi, '') || 'type' })}
              help="Напр., type — отримаємо ?type=posts|materials"
            />
            <ItemGroup>
              {items.map((it, i) => (
                <Item key={i}>
                  <TextControl
                    label="Мітка"
                    value={it.label}
                    onChange={(v) => updateItem(i, { label: v })}
                  />
                  <TextControl
                    label="Ключ (у URL)"
                    value={it.key}
                    onChange={(v) => updateItem(i, { key: v.replace(/[^a-z0-9_-]/gi, '') })}
                    help="Напр., posts, materials, all"
                  />
                  <Button variant="secondary" onClick={() => removeItem(i)}>Видалити</Button>
                </Item>
              ))}
            </ItemGroup>
            <Button variant="primary" onClick={addItem}>Додати пункт</Button>
          </PanelBody>
        </InspectorControls>

        {/* Прев’ю в редакторі (вигляд «Перелік категорій») */}
        <div {...blockProps}>
          <ul className="wp-block-categories ntd-posttype-filter" data-param={paramName}>
            {items.map((it, i) => (
              <li className="cat-item" key={i}>
                <a href={`?${encodeURIComponent(paramName)}=${encodeURIComponent(it.key)}`}>{it.label}</a>
              </li>
            ))}
          </ul>
        </div>
      </Fragment>
    );
  },

  save({ attributes }) {
    const { paramName, items } = attributes;
    // Статичний HTML, який відрендерить редактор на фронті
    return (
      <ul className="wp-block-categories ntd-posttype-filter" data-param={paramName}>
        {items.map((it, i) => (
          <li className="cat-item" key={i} data-key={it.key}>
            <a href={`?${encodeURIComponent(paramName)}=${encodeURIComponent(it.key)}`}>{it.label}</a>
          </li>
        ))}
      </ul>
    );
  }
});
