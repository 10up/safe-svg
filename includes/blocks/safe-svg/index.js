/**
 * SafeSvg Icon Block
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';
import block from './block.json';

/* Uncomment for CSS overrides in the admin */
import './frontend.scss';

/**
 * Register block
 */
registerBlockType( block.name, {
	title: __( 'Inline SVG', 'safe-svg' ),
	description: __(
		'Display an SVG icon',
		'safe-svg'
	),
	edit,
	save,
	icon: 'format-image'
} );
