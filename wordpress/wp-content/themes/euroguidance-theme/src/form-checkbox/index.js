import { registerBlockType } from '@wordpress/blocks';
import {
	InspectorControls,
	useBlockProps,
	RichText
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl, 
	ToggleControl
} from '@wordpress/components';

import './style.scss';

registerBlockType('parts-blocks/consent-checkbox', {
	edit: ({ attributes, setAttributes }) => {
		const { labelBefore, linkText, labelAfter, linkUrl, isRequired, inputName } = attributes;
		const blockProps = useBlockProps({ className: 'consent-checkbox' });

		return (  
			<>
				<InspectorControls>
					<PanelBody title="Налаштування">
						<TextControl
							label="Name атрибут"
							value={inputName}
							onChange={(v) => setAttributes({ inputName: v })}
						/>
						<TextControl
							label="URL посилання"
							value={linkUrl}
							onChange={(v) => setAttributes({ linkUrl: v })}
							placeholder="https://site.tld/privacy"
						/>
						<ToggleControl
							label="Обов'язковий (required)"
							checked={isRequired}
							onChange={(v) => setAttributes({ isRequired: v })}
						/>
					</PanelBody>
				</InspectorControls>

				<div {...blockProps}>
					<input
						className="cc-input"
						type="checkbox"
						name={inputName}
						disabled
					/>
					<label className="cc-label">
						<div>
							<RichText
								tagName="span"
								className="cc-text cc-text--before"
								value={labelBefore}
								onChange={(v) => setAttributes({ labelBefore: v })}
								placeholder="Текст до посилання…"
								allowedFormats={[]} // простий текст
							/>
							<a className="cc-link" href={linkUrl} target="_blank" rel="noopener">
								<RichText
								tagName="span"
								className="cc-link__text"
								value={linkText}
								onChange={(v) => setAttributes({ linkText: v })}
								placeholder="Текст посилання…"
								allowedFormats={[]} // редагуємо тільки текст
								/>
							</a>
							<RichText
								tagName="span"
								className="cc-text cc-text--after"
								value={labelAfter}
								onChange={(v) => setAttributes({ labelAfter: v })}
								placeholder="Текст після посилання…"
								allowedFormats={[]}
							/>
						</div>
					</label>
				</div>
			</>
		);
	},

	save: ({ attributes }) => {
		const { labelBefore, linkText, labelAfter, linkUrl, isRequired, inputName } = attributes;
		const blockProps = useBlockProps.save({ className: 'consent-checkbox' });

		return (
			<div {...blockProps}>
				<label className="cc-label">
          <input
            className="cc-input"
            type="checkbox"
            name={inputName}
            {...(isRequired ? { required: true } : {})}
          /> 
          <div>
            <span className="cc-text cc-text--before">{labelBefore} </span>
            {/* <a className="cc-link" href={linkUrl} target="_blank" rel="noopener">
              <span className="cc-link__text">{linkText}</span>
            </a>
            <span className="cc-text cc-text--after">{labelAfter}</span> */}
          </div>
				</label>
			</div>
		);
	}
});
