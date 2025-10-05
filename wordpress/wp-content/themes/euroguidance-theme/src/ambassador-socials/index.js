import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import './style.scss';
import './editor.css';

registerBlockType( metadata.name, {
	edit: Edit,
	// без save: буде динамічний рендер пізніше
} );
