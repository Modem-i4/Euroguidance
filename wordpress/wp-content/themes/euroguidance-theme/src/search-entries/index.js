import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { TextControl, PanelBody } from '@wordpress/components';
import './style.scss';
import metadata from './block.json';

registerBlockType(metadata.name, {
	edit: ({ attributes, setAttributes }) => {
		const { targetBlocks } = attributes;

		return (
			<div {...useBlockProps({ className: 'search-block' })}>
				<div
					className="search-block"
					data-target-blocks={targetBlocks}
				>
					<input
						inert="true"
						type="search"
						className="search-block__input"
						placeholder="Пошук (для користувача)"
					/>
				</div>
				<InspectorControls>
					<PanelBody title="Налаштування пошуку" initialOpen={true}>
						<TextControl
							label="Селектори блоків (через кому)"
							help="Наприклад: .file-card, .qa-item"
							value={targetBlocks}
							onChange={(val) => setAttributes({ targetBlocks: val })}
						/>
					</PanelBody>  
				</InspectorControls>
			</div>
		);
	},

	save: ({ attributes }) => {
		const { targetBlocks } = attributes;

		return (
			<div
				className="search-block"
				data-target-blocks={targetBlocks}
			>
				<input
					type="search"
					className="search-block__input"
					placeholder="Пошук…"
				/>
			</div>
		);
	}
});
