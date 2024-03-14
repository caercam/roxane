import { registerBlockType } from '@wordpress/blocks';

import { Critic } from './icons';
import Edit from './edit';
import Save from './save';

import metadata from './block.json';

import './style.scss';

registerBlockType( metadata.name, {
	icon: Critic,
	edit: Edit,
	save: Save,
} );
