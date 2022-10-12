/* eslint-disable no-unused-vars */
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	Placeholder,
	Button,
	__experimentalAlignmentMatrixControl as AlignmentMatrixControl,
} from '@wordpress/components';
import {
	useBlockProps,
	MediaUpload,
	BlockControls,
	AlignmentToolbar,
	InspectorControls,
} from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import PropTypes from 'prop-types';

/**
 * Edit component.
 * See https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/block-edit-save/#edit
 *
 * @param {Object}   props                      The block props.
 * @param {Object}   props.attributes           Block attributes.
 * @param {Object}   props.attributes.svgURL    SVG URL.
 * @param {boolean}  props.attributes.alignment Alignment of the SVG.
 * @param {string}   props.className            Class name for the block.
 * @param {Function} props.setAttributes        Sets the value for block attributes.
 * @return {Function} Render the edit screen
 */
const SafeSvgBlockEdit = ( props ) => {
	const { attributes, setAttributes } = props;
	const {
		contentPostType,
		svgURL,
		alignment
	} = attributes;
	const blockProps = useBlockProps();

	const onSelectImage = media => {
		setAttributes( { svgURL: media.url } );
	};

	return (
		<div { ...blockProps } style={{textAlign: alignment}}>
			<BlockControls>
				<AlignmentToolbar
					value={alignment}
					onChange={(newVal) => setAttributes({alignment: newVal})} />
			</BlockControls>
			<MediaUpload
				onSelect={onSelectImage}
				allowedTypes="image/svg+xml"
				accept="image/svg+xml"
				/*value={svgURL}*/ /* @TODO: add id instead of URL */
				render={({open}) => {
					return (
						<>
							{!svgURL &&
								<Button variant="tertiary" onClick={open}>
									{__('Media Library', 'newspack-blocks')}
								</Button>
							}
							{svgURL &&
								<svg width="90" height="90" onClick={onSelectImage}>
									<image xlinkHref={svgURL} src={svgURL} width="90" height="90"/>
								</svg>
							}
						</>
					);
				}}
			/>
			{ contentPostType && (
				<Placeholder
					label={ __( 'SafeSvg', 'safe-svg' ) }
				>
					<p>
						{ __(
							'Please select the SVG icon.',
							'safe-svg'
						) }
					</p>
				</Placeholder>
			) }
		</div>
	);
};
// Set the propTypes
SafeSvgBlockEdit.propTypes = {
	attributes: PropTypes.shape({
		svgURL: PropTypes.string,
		alignment: PropTypes.string,
	}).isRequired,
	className: PropTypes.string,
	clientId: PropTypes.string,
	setAttributes: PropTypes.func.isRequired,
};

export default SafeSvgBlockEdit;
