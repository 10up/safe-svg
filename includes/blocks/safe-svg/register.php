<?php
/**
 * SafeSvg Block setup
 *
 * @package SafeSvg\Blocks\SafeSvgBlock
 */

namespace SafeSvg\Blocks\SafeSvgBlock;

/**
 * Register the block
 */
function register() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};
	// Register the block.
	\register_block_type_from_metadata(
		SAFE_SVG_PLUGIN_DIR . '/includes/blocks/safe-svg',
		[
			'render_callback' => $n( 'render_block_callback' ),
		]
	);
}

/**
 * Render callback method for the block.
 *
 * @param array $attributes The blocks attributes
 *
 * @return string|\WP_Post[] The rendered block markup.
 */
function render_block_callback( $attributes ) {
	// If image is not an SVG return empty string
	if ( 'image/svg+xml' !== get_post_mime_type( $attributes['imageID'] ) ) {
		return '';
	}

	// If we couldn't get the contents of the file, empty string again
	if ( ! $contents = file_get_contents( get_attached_file( $attributes['imageID'] ) ) ) { // phpcs:ignore
		return '';
	}

	/**
	 * The wrapper class name.
	 *
	 * Allows a user to adjust the inline svg wrapper class name.
	 *
	 * @param string The class name.
	 *
	 * @since 2.1.0
	 */
	$class_name = apply_filters( 'safe_svg_inline_class', 'safe-svg-inline' );

	/**
	 * The wrapper markup.
	 *
	 * Allows a user to adjust the inline svg wrapper markup.
	 *
	 * @param string                The current wrapper markup.
	 * @param string $contents      The SVG contents.
	 * @param string $class_name    The wrapper class name.
	 * @param int    $attachment_id The ID of the attachment.
	 *
	 * @since 2.1.0
	 */
	return apply_filters(
		'safe_svg_inline_markup',
		sprintf(
			'<div class="safe-svg-cover" style="text-align:%s">
				<div class="safe-svg-inside %s" style="width: %spx; height: %spx;">%s</div>
			</div>',
			$attributes['alignment'] ?? 'left',
			$class_name,
			$attributes['dimensionWidth'],
			$attributes['dimensionHeight'],
			$contents
		),
		$contents,
		$class_name,
		$attributes['imageID']
	);
}
