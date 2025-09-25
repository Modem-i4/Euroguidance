import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit.js';

import metadata from './block.json';
import './style.scss';
import './script.js';

registerBlockType(metadata.name, {
	edit: Edit,
	save: () => null,
});
 