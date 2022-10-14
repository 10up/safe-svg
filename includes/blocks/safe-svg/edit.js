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
		type,
		imageID,
		imageSizes,
		alignment,
		imageWidth,
		imageHeight,
		dimensionWidth,
		dimensionHeight
	} = attributes;
	const blockProps = useBlockProps();

	const onSelectImage = media => {
		setAttributes( {
			imageSizes: {
				full: media.sizes.full,
				medium: media.sizes.medium,
				thumbnail: media.sizes.thumbnail,
			},
			svgURL: media.sizes.full.url,
			type: 'full',
		} );
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
			value={ imageID }
			render={ mediaLibraryButton }
		/>
	);

	const onChangeImage = (type) => {
		setAttributes({
			svgURL: imageSizes[type].url,
			imageWidth: parseInt( imageSizes[type].width ),
			imageHeight: parseInt( imageSizes[type].height ),
			dimensionWidth: parseInt( imageSizes[type].width ),
			dimensionHeight: parseInt( imageSizes[type].height ),
			type
		})
	}

	const imageSizeOptions = [
		{ value: 'full', label: 'Full Size' },
		{ value: 'medium', label: 'Medium' },
		{ value: 'thumbnail', label: 'Thumbnail' },
	];

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
						imageSizeOptions={imageSizeOptions}
						slug={type}
						onChange={ (dimensionSizes) => setAttributes({
							dimensionWidth: dimensionSizes.width ?? dimensionWidth,
							dimensionHeight: dimensionSizes.height ?? dimensionHeight
						}) }
						onChangeImage={ onChangeImage }
					/>
				</PanelBody>
			</InspectorControls>
			<BlockControls>
				<AlignmentToolbar
					value={alignment}
					onChange={(newVal) => setAttributes({alignment: newVal})}
				/>
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
				value={imageID}
				render={({open}) => {
					return (
						<div
							style={{
								width: dimensionWidth,
								height: dimensionHeight,
								margin: 'auto'
							}}
						>
							{!svgURL &&
								<Button variant="tertiary" onClick={open}>
									{__('Media Library', 'safe-svg')}
								</Button>
							}
							{svgURL &&
								<svg
									width="100%"
									height={imageHeight}
								>
									<image
										xlinkHref={svgURL}
										src={svgURL}
										width="100%"
									/>
								</svg>
							}
						</div>
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
		imageID: PropTypes.number,
		imageWidth: PropTypes.number,
		imageHeight: PropTypes.number,
		dimensionWidth: PropTypes.number,
		dimensionHeight: PropTypes.number,
		imageSizes: PropTypes.object,
	}).isRequired,
	className: PropTypes.string,
	clientId: PropTypes.string,
	setAttributes: PropTypes.func.isRequired,
};

export default SafeSvgBlockEdit;
