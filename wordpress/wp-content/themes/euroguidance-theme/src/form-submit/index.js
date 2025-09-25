import { registerBlockType } from '@wordpress/blocks';
import {
  InspectorControls,
  useBlockProps,
} from '@wordpress/block-editor';
import {
  PanelBody,
  TextControl,
  SelectControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import './frontend.js'; 
import './style.scss'; 

registerBlockType(metadata.name, {
  edit({ attributes, setAttributes }) {
    const {
      submitText, scopeClass, action, method, successMessage, errorMessage
    } = attributes;

    const blockProps = useBlockProps({
      className: `form-component is-submit`
    });

    return (
      <div {...blockProps}>
        <InspectorControls>
          <PanelBody title={__('Налаштування', 'parts-blocks')} initialOpen>
            <TextControl
              label="Текст кнопки"
              value={submitText}
              onChange={(v) => setAttributes({ submitText: v })}
            />
            <TextControl
              label="scopeClass"
              help="CSS-клас контейнера (наприклад .form-scope), з якого збиратимуться всі поля"
              value={scopeClass}
              onChange={(v) => setAttributes({ scopeClass: v })}
            />
            <TextControl
              label="action (URL)"
              value={action}
              onChange={(v) => setAttributes({ action: v })}
            />
            <SelectControl
              label="method"
              value={method}
              options={[
                { label: 'POST', value: 'POST' },
                { label: 'GET', value: 'GET' }
              ]}
              onChange={(v) => setAttributes({ method: v })}
            />
            <TextControl
              label="Повідомлення успіху"
              value={successMessage}
              onChange={(v) => setAttributes({ successMessage: v })}
            />
            <TextControl
              label="Повідомлення помилки"
              value={errorMessage}
              onChange={(v) => setAttributes({ errorMessage: v })}
            />
          </PanelBody>
        </InspectorControls>

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
        </div>
      </div>
    );
  },

  save({ attributes }) {
    const {
      submitText, scopeClass, action, method, successMessage, errorMessage
    } = attributes;

    const blockProps = useBlockProps.save({
      className: `form-component is-submit`
    });

    return (
      <div {...blockProps}>
        <button
          type="button"
          className="fc-submit"
          data-form-submit="1"
          data-scope-class={scopeClass || '.form-scope'}
          data-action={action || '/'}
          data-method={method || 'POST'}
          data-success-message={successMessage || 'Форму надіслано успішно.'}
          data-error-message={errorMessage || 'Сталася помилка.'}
        >
          {submitText || 'Надіслати'}
        </button>
      </div>
    );
  }
});
