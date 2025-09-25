import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';
import './style.scss';
import metadata from './block.json';

registerBlockType(metadata.name, {
  ...metadata,
  edit,
  save: () => null,
});
  