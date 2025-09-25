import { registerBlockType } from '@wordpress/blocks';
import { PanelBody, ToggleControl, __experimentalNumberControl as NumberControl, TextControl } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import metadata from './block.json';
import './style.scss';

registerBlockType(metadata.name, {
	edit: ({ attributes, setAttributes }) => {
		const { wpm, showLabel, label } = attributes;

		return (
			<>
				<InspectorControls>
					<PanelBody title="Налаштування">
						<NumberControl
							label="Слів за хвилину (WPM)"
							min={100}
							max={600}
							value={ wpm }
							onChange={(val) => setAttributes({ wpm: Number(val) || 200 })}
						/>
						<ToggleControl
							label="Показувати підпис"
							checked={ !!showLabel }
							onChange={(val) => setAttributes({ showLabel: !!val })}
						/>
						{ showLabel && (
							<TextControl
								label="Підпис"
								value={ label }
								onChange={(val) => setAttributes({ label: val })}
								placeholder="хв читання"
							/>
						) }
					</PanelBody>
				</InspectorControls>

				<div className="parts-reading-time-editor">
					<span>3{ showLabel && label ? ' ' + label : '' }</span>
				</div>
			</>
		);
	},
	save: () => null, 
});
