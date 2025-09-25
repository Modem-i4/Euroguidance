import { registerBlockType } from '@wordpress/blocks'
import { InspectorControls, useBlockProps, InnerBlocks } from '@wordpress/block-editor'
import { TextControl, ToggleControl, PanelBody, RangeControl } from '@wordpress/components'
import './style.scss'
import metadata from './block.json'

registerBlockType(metadata.name, {
  edit: ({ attributes, setAttributes }) => {
    const { label, mobileOnly, vhSpc, vhSmobile } = attributes

    const blockProps = useBlockProps({
      className: 'va-show-more',
      'data-label': label,
      'data-mobile-only': mobileOnly ? '1' : '0',
      'data-vh-spc': vhSpc ?? 1,
      'data-vh-smobile': vhSmobile ?? 1
    })

    return (
      <div {...blockProps}>
        <div className="va-show-more__content">
          <InnerBlocks />
        </div>

        <div className="va-show-more__fade" />
        <button className="va-show-more__btn" type="button">{label || 'Переглянути всі'}</button>
        <div className="text-center">
          (кнопка для користувача{ mobileOnly ? ', тільки для телефонів' : '' })
        </div>

        <InspectorControls>
          <PanelBody title="Налаштування блоку" initialOpen={true}>
            <TextControl
              label="Текст кнопки"
              value={label}
              onChange={(val) => setAttributes({ label: val })}
            />
            <RangeControl
              label="Висота на ПК (екранів)"
              value={vhSpc}
              onChange={(val) => setAttributes({ vhSpc: val })}
              min={0}
              max={6}
              step={0.1}
            />
            <RangeControl
              label="Висота на телефоні (екранів)"
              value={vhSmobile}
              onChange={(val) => setAttributes({ vhSmobile: val })}
              min={0}
              max={6}
              step={0.1}
            />
            <ToggleControl
              label="Лише для мобільних пристроїв"
              checked={!!mobileOnly}
              onChange={(val) => setAttributes({ mobileOnly: !!val })}
            />
          </PanelBody>
        </InspectorControls>
      </div>
    )
  },

  save: ({ attributes }) => {
    const { label, mobileOnly, vhSpc, vhSmobile } = attributes

    return (
      <div
        {...useBlockProps.save({
          className: 'va-show-more',
          'data-label': label,
          'data-mobile-only': mobileOnly ? '1' : '0',
          'data-vh-spc': vhSpc ?? 1,
          'data-vh-smobile': vhSmobile ?? 1
        })}
      >
        <div className="va-show-more__content">
          <InnerBlocks.Content />
        </div>
        <div className="va-show-more__fade" />
        <button className="va-show-more__btn" type="button">{label || 'Переглянути всі'}</button>
      </div>
    )
  }
})
