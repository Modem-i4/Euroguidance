import { registerBlockType } from '@wordpress/blocks';
import {
  InspectorControls,
  useBlockProps,
  RichText
} from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  SelectControl,
  ToggleControl,
  __experimentalNumberControl as NumberControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import './style.scss'; 

const INPUT_TYPES = [
  'text','email','tel','number','url','password','date','datetime-local','time'
];

registerBlockType(metadata.name, {
  edit({ attributes, setAttributes }) {
    const {
      variant, label, name, placeholder, required, inputType, rows
    } = attributes;

    const blockProps = useBlockProps({
      className: `form-component is-${variant}`
    });

    return (
      <div {...blockProps}>
        <InspectorControls>
          <PanelBody title={__('Налаштування', 'parts-blocks')} initialOpen>
            <SelectControl
              label="Variant"
              value={variant}
              options={[
                { label: 'Input', value: 'input' },
                { label: 'Textarea', value: 'textarea' }
              ]}
              onChange={(v) => setAttributes({ variant: v })}
            />

            {variant !== 'submit' && (
              <>
                <TextControl
                  label="Label"
                  value={label}
                  onChange={(v) => setAttributes({ label: v })}
                />
                <TextControl
                  label="name"
                  help="Атрибут name (обовʼязково для надсилання)"
                  value={name}
                  onChange={(v) => setAttributes({ name: v })}
                />
                <TextControl
                  label="placeholder"
                  value={placeholder}
                  onChange={(v) => setAttributes({ placeholder: v })}
                />
                <ToggleControl
                  label="required"
                  checked={!!required}
                  onChange={(v) => setAttributes({ required: !!v })}
                />
              </>
            )}

            {variant === 'input' && (
              <SelectControl
                label="type"
                value={inputType}
                options={INPUT_TYPES.map(t => ({ label: t, value: t }))}
                onChange={(v) => setAttributes({ inputType: v })}
              />
            )}

            {variant === 'textarea' && (
              <NumberControl
                label="rows"
                min={2}
                max={20}
                value={rows}
                onChange={(v) => setAttributes({ rows: Number(v) || 4 })}
              />
            )}
          </PanelBody>
        </InspectorControls>

        {/* Превʼю в редакторі */}
        {variant === 'input' && (
          <div className="fc-field">
            {label && <label className="fc-label">{label}</label>}
            <input
              className="fc-input"
              type={inputType}
              name={name || ''}
              placeholder={placeholder || ''}
              required={!!required}
              data-form-input="1"
              readOnly
            />
          </div>
        )}

        {variant === 'textarea' && (
          <div className="fc-field">
            {label && <label className="fc-label">{label}</label>}
            <textarea
              className="fc-textarea"
              name={name || ''}
              placeholder={placeholder || ''}
              rows={rows || 4}
              required={!!required}
              data-form-input="1"
              readOnly
            />
          </div>
        )}

        {variant === 'submit' && (
          <div className="fc-actions">
            <button
              type="button"
              className="fc-submit"
              data-form-submit="1"
              data-scope-class={scopeClass || '.form-scope'}
              data-action={action || '/'}
              data-method={method || 'POST'}
              data-success-message={successMessage || 'OK'}
              data-error-message={errorMessage || 'Error'}
            >
              {submitText || 'Надіслати'}
            </button>
            <div className="fc-hint">
              <code>scope: {scopeClass || '.form-scope'}</code>
            </div>
          </div>
        )}
      </div>
    );
  },

  save({ attributes }) {
    const {
      variant, label, name, placeholder, required, inputType, rows
    } = attributes;

    const blockProps = useBlockProps.save({
      className: `form-component is-${variant}`
    });

    if (variant === 'input') {
      return (
        <div {...blockProps}>
          {label ? <label className="fc-label">{label}</label> : null}
          <input
            className="fc-input"
            type={inputType || 'text'}
            name={name || ''}
            placeholder={placeholder || ''}
            required={!!required}
            data-form-input="1"
          />
        </div>
      );
    }

    if (variant === 'textarea') {
      return (
        <div {...blockProps}>
          {label ? <label className="fc-label">{label}</label> : null}
          <textarea
            className="fc-textarea"
            name={name || ''}
            placeholder={placeholder || ''}
            rows={rows || 4}
            required={!!required}
            data-form-input="1"
          />
        </div>
      );
    }
  }
});
