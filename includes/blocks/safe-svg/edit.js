/* eslint-disable no-unused-vars */
/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	Placeholder,
	PanelBody,
	Dropdown,
	Notice,
	ToolbarButton,
	TextControl,
} from '@wordpress/components';
import { link } from '@wordpress/icons';
import {
	useBlockProps,
	BlockControls,
	AlignmentToolbar,
	InspectorControls,
	__experimentalImageSizeControl as ImageSizeControl,
	MediaReplaceFlow,
	MediaPlaceholder,
	LinkControl,
	getColorClassName,
} from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import InlineSvg from './inline-svg';
import useSanitizedSvg from './use-sanitized-svg';

/**
 * Explain why an SVG could not be shown.
 *
 * @param {string} code The error code from the REST response.
 * @return {string} A message for the block editor.
 */
const errorMessage = (code) => {
	switch (code) {
		case 'rest_forbidden':
		case 'safe_svg_invalid_attachment':
		case 'safe_svg_not_svg':
		case 'safe_svg_unreadable':
			return __(
				'This SVG is not available to you. Pick another from the media library.',
				'safe-svg'
			);
		case 'safe_svg_sanitize_failed':
			return __(
				'This SVG could not be sanitized, so it has not been displayed.',
				'safe-svg'
			);
		default:
			return __('This SVG could not be loaded. Please try again.', 'safe-svg');
	}
};

/**
 * Edit component.
 * See https://wordpress.org/gutenberg/handbook/designers-developers/developers/block-api/block-edit-save/#edit
 *
 * @param {Object}   props                      The block props.
 * @param {Object}   props.attributes           Block attributes.
 * @param {Object}   props.attributes.svgURL    SVG URL. Legacy: stored for back compat, never read.
 * @param {boolean}  props.attributes.alignment Alignment of the SVG.
 * @param {string}   props.className            Class name for the block.
 * @param {Function} props.setAttributes        Sets the value for block attributes.
 * @return {Function} Render the edit screen
 */
const SafeSvgBlockEdit = ({ attributes, setAttributes }) => {

	const {
		contentPostType,
		type,
		imageID,
		imageSizes,
		alignment,
		imageWidth,
		imageHeight,
		dimensionWidth,
		dimensionHeight,
		textColor,
		href,
		linkTarget,
		nofollow,
		sponsored,
		linkLabel,
	} = attributes;

	// Get the markup and URL from the attachment ID.
	const { markup, url: mediaURL, error } = useSanitizedSvg(imageID);

	const blockProps = useBlockProps(
		{
			className: 'wp-block-safe-svg-svg-icon safe-svg-cover',
			style: {
				textAlign: alignment,
			}
		}
	);

	const { className, style, ...containerBlockProps } = blockProps;

	// Remove text alignment so we can apply to the parent container.
	delete style.textAlign;
	containerBlockProps.style = { textAlign: alignment };

	// Remove core background & text color classes, so we can add our own.
	const newClassName = className.replace(/has-[\w-]*-color|has-background/g, '').trim();
	containerBlockProps.className = newClassName;

	// Add the width and height to enforce dimensions and to keep parity with the frontend.
	style.width = `${dimensionWidth}px`;
	style.height = `${dimensionHeight}px`;

	const ALLOWED_MEDIA_TYPES = ['image/svg+xml'];

	const onSelectImage = media => {
		if (!media.sizes && !media.media_details?.sizes) {
			return;
		}

		if (media.media_details) {
			media.sizes = media.media_details.sizes;
		}

		const newURL = media.sizes.full.url ?? media.sizes.full.source_url;

		setAttributes({
			imageSizes: {
				full: media.sizes.full,
				medium: media.sizes.medium,
				thumbnail: media.sizes.thumbnail,
			},
			imageWidth: media.sizes.full.width,
			imageHeight: media.sizes.full.height,
			dimensionWidth: media.sizes.full.width,
			dimensionHeight: media.sizes.full.height,
			imageID: media.id,
			svgURL: newURL,
			type: 'full',
		});
	};

	const onError = (message) => {
		console.log(__(`Something went wrong, please try again. Message: ${message}`, 'safe-svg'));
	}

	const onChange = (dimensionSizes) => {
		if (!dimensionSizes.width && !dimensionSizes.height) {
			dimensionSizes.width = parseInt(imageSizes[type].width);
			dimensionSizes.height = parseInt(imageSizes[type].height);
		}
		setAttributes({
			dimensionWidth: dimensionSizes.width ?? dimensionWidth,
			dimensionHeight: dimensionSizes.height ?? dimensionHeight,
		})
	}

	const onChangeImage = (newSizeSlug) => {
		const newUrl = imageSizes[newSizeSlug].url ?? imageSizes[newSizeSlug].source_url;
		if (!newUrl) {
			return null;
		}
		let newWidth = parseInt(imageSizes[newSizeSlug].width);
		let newHeight = parseInt(imageSizes[newSizeSlug].height);
		if ('full' !== newSizeSlug) {
			if (imageSizes[newSizeSlug].width >= imageSizes[newSizeSlug].height) {
				newHeight = imageSizes[newSizeSlug].height * imageSizes['full'].height / imageSizes['full'].width;
			} else {
				newWidth = imageSizes[newSizeSlug].width * imageSizes['full'].width / imageSizes['full'].height;
			}
		}
		setAttributes({
			svgURL: newUrl,
			imageWidth: newWidth,
			imageHeight: newHeight,
			dimensionWidth: newWidth,
			dimensionHeight: newHeight,
			type: newSizeSlug
		})
	}

	const imageSizeOptions = [
		{
			value: 'full', label: __('Full Size', 'safe-svg')
		},
		{
			value: 'medium', label: __('Medium', 'safe-svg')
		},
		{
			value: 'thumbnail', label: __('Thumbnail', 'safe-svg')
		},
	];

	return (
		<>
			{!!imageID &&
				<>
					<InspectorControls>
						<PanelBody
							title={__(
								'Image settings',
								'safe-svg'
							)}
						>
							<ImageSizeControl
								width={dimensionWidth}
								height={dimensionHeight}
								imageWidth={imageWidth}
								imageHeight={imageHeight}
								imageSizeOptions={imageSizeOptions}
								slug={type}
								onChange={onChange}
								onChangeImage={onChangeImage} />
						</PanelBody>
					</InspectorControls>
					<BlockControls>
						<AlignmentToolbar
							value={alignment}
							onChange={(newVal) => setAttributes({ alignment: newVal })} />
					</BlockControls>
					<BlockControls>
						<MediaReplaceFlow
							mediaId={imageID}
							mediaURL={mediaURL}
							allowedTypes={ALLOWED_MEDIA_TYPES}
							accept={ALLOWED_MEDIA_TYPES}
							onSelect={onSelectImage}
							onError={onError} />
						<Dropdown
							className="safe-svg-link-dropdown"
							renderToggle={({ isOpen, onToggle }) => (
								<ToolbarButton
									icon={link}
									label={__('Link', 'safe-svg')}
									onClick={onToggle}
									aria-expanded={isOpen}
								/>
							)}
							renderContent={({ onClose }) => (
								<div className="block-editor-link-control">
									<LinkControl
										value={{
											url: href,
											opensInNewTab: linkTarget === '_blank',
											nofollow: !!nofollow,
											sponsored: !!sponsored,
										}}
										onChange={(linkSettings) => {
											setAttributes({
												href: linkSettings.url,
												linkTarget: linkSettings.opensInNewTab ? '_blank' : '',
												nofollow: !!linkSettings.nofollow,
												sponsored: !!linkSettings.sponsored,
											});
										}}
										onRemove={() => {
											setAttributes({
												href: '',
												linkTarget: '',
												nofollow: false,
												sponsored: false,
											});
										}}
										settings={[
											{
												id: 'opensInNewTab',
												title: __(
													'Open in new tab',
													'safe-svg'
												),
											},
											{
												id: 'nofollow',
												title: __(
													'Add rel="nofollow"',
													'safe-svg'
												),
											},
											{
												id: 'sponsored',
												title: __(
													'Add rel="sponsored"',
													'safe-svg'
												),
											},
										]}
										onClose={onClose}
									/>
									<TextControl
										label={__('Link Label (aria-label)', 'safe-svg')}
										value={linkLabel}
										onChange={(value) => setAttributes({ linkLabel: value })}
										help={__('Provides an accessible label for screen readers.', 'safe-svg')}
									/>
								</div>
							)}
						/>
					</BlockControls>
				</>
			}

			{!imageID &&
				<MediaPlaceholder
					onSelect={onSelectImage}
					allowedTypes={ALLOWED_MEDIA_TYPES}
					accept={ALLOWED_MEDIA_TYPES}
					value={imageID}
					labels={{
						title: __('Inline SVG', 'safe-svg'),
						instructions: __('Upload an SVG or pick one from your media library.', 'safe-svg')
					}}
				/>
			}

			{!!imageID && !!error &&
				<div {...containerBlockProps}>
					<Notice status="warning" isDismissible={false}>
						{errorMessage(error)}
					</Notice>
				</div>
			}

			{!!imageID && !error &&
				<div {...containerBlockProps}>
					<div
						style={style}
						className={classnames(
							'safe-svg-inside',
							getColorClassName('color', textColor) || ''
						)}
					>
						<InlineSvg
							markup={markup}
							width={dimensionWidth}
							height={dimensionHeight} />
					</div>
				</div>
			}

			{contentPostType && (
				<Placeholder
					label={__('SafeSvg', 'safe-svg')}
				>
					<p>
						{__(
							'Please select the SVG icon.',
							'safe-svg'
						)}
					</p>
				</Placeholder>
			)}
		</>
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
		href: PropTypes.string,
		linkTarget: PropTypes.string,
		nofollow: PropTypes.bool,
		sponsored: PropTypes.bool,
		linkLabel: PropTypes.string,
	}).isRequired,
	className: PropTypes.string,
	clientId: PropTypes.string,
	setAttributes: PropTypes.func.isRequired,
};

export default SafeSvgBlockEdit;
