/* eslint-disable no-unused-vars */
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	Placeholder,
	Button,
	PanelBody,
	Dropdown,
	MenuItem,
	ToolbarButton,
} from '@wordpress/components';
import {
	useBlockProps,
	MediaUpload,
	BlockControls,
	AlignmentToolbar,
	InspectorControls,
	__experimentalImageSizeControl as ImageSizeControl,
} from '@wordpress/block-editor';
import { createRef } from '@wordpress/element';
import { media as mediaIcon } from '@wordpress/icons';
import { DOWN } from '@wordpress/keycodes';
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
		image,
		alignment,
		imageWidth,
		imageHeight,
		dimensionWidth,
		dimensionHeight
	} = attributes;

	const blockProps = useBlockProps();

	const onSelectImage = media => {
		setAttributes( { svgURL: media.url } );
	};

	const onKeyDown = ( event ) => {
		if ( event.keyCode === DOWN ) {
			event.preventDefault();
			event.stopPropagation();
			event.target.click();
		}
	};

	const mediaLibraryButton = ( { open } ) => (
		<MenuItem icon={ mediaIcon } onClick={ open }>
			{ __( 'Open Media Library' ) }
		</MenuItem>
	);

	const dropdownToggle = ( { isOpen, onToggle } ) => (
		<ToolbarButton
			ref={ createRef() }
			aria-expanded={ isOpen }
			aria-haspopup="true"
			onClick={ onToggle }
			onKeyDown={ onKeyDown }
		>
			{ __( 'Replace' ) }
		</ToolbarButton>
	);

	const onError = ( message ) => {
		alert( __(`Something went wrong, please try again. Message: ${message}`, 'safe-svg') );
	}

	const dropdownContent = ( { onClose } ) => (
		<MediaUpload
			title={ __( 'Select an image' ) }
			onSelect={ onSelectImage }
			onClick={ onClose }
			onError={ onError }
			allowedTypes={ 'image/svg+xml' }
			value={ image }
			render={ mediaLibraryButton }
		/>
	);

	return (
		<div { ...blockProps } style={{textAlign: alignment}}>
			<InspectorControls>
				<PanelBody
					title={ __(
						'Image settings',
						'safe-svg'
					) }
				>
					<ImageSizeControl
						width={ dimensionWidth }
						height={ dimensionHeight }
						imageWidth={ imageWidth }
						imageHeight={ imageHeight }
						imageSizeOptions={ [
							{ value: '{"width":"200","height":"200"}', label: '200/200' },
							{ value: '{"width":"100","height":"300"}', label: '100/300' },
							{ value: '{"width":"400","height":"800"}', label: '400/800' },
						] }
						slug={ JSON.stringify({
							width: imageWidth.toString(),
							height: imageHeight.toString()
						}) }
						onChange={ (dimensionSizes) => setAttributes({
							dimensionWidth: dimensionSizes.width ?? dimensionWidth,
							dimensionHeight: dimensionSizes.height ?? dimensionHeight
						}) }
						onChangeImage={ (imageSizes) => setAttributes({
							imageWidth: parseFloat(JSON.parse(imageSizes).width),
							imageHeight: parseFloat(JSON.parse(imageSizes).height),
							dimensionWidth: parseFloat(JSON.parse(imageSizes).width),
							dimensionHeight: parseFloat(JSON.parse(imageSizes).height)
						}) }
					/>
				</PanelBody>
			</InspectorControls>
			<BlockControls>
				<AlignmentToolbar
					value={alignment}
					onChange={(newVal) => setAttributes({alignment: newVal})} />
			</BlockControls>
			<BlockControls>
				{ svgURL && (
					<Dropdown
						contentClassName="block-editor-media-add__options"
						renderToggle={ dropdownToggle }
						renderContent={ dropdownContent }
					/>
				) }
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
									{__('Media Library', 'safe-svg')}
								</Button>
							}
							{svgURL &&
								<svg
									width={dimensionWidth}
									height={dimensionHeight}
								>
									<image
										xlinkHref={svgURL}
										src={svgURL}
										width={imageWidth}
										height={imageHeight}
									/>
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
