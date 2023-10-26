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
	// If image is not an SVG return empty string.
	if ( 'image/svg+xml' !== get_post_mime_type( $attributes['imageID'] ) ) {
		return '';
	}

	// If we couldn't get the contents of the file, empty string again.
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
			'<div class="wp-block-safe-svg-svg-icon safe-svg-cover" style="text-align: %s;">
				<div class="safe-svg-inside %s%s" style="width: %spx; height: %spx; background-color: var(--wp--preset--color--%s); color: var(--wp--preset--color--%s); padding-top: %s; padding-right: %s; padding-bottom: %s; padding-left: %s; margin-top: %s; margin-right: %s; margin-bottom: %s; margin-left: %s;">%s</div>
			</div>',
			isset( $attributes['alignment'] ) ? esc_attr( $attributes['alignment'] ) : 'left',
			esc_attr( $class_name ),
			isset( $attributes['className'] ) ? ' ' . esc_attr( $attributes['className'] ) : '',
			isset( $attributes['dimensionWidth'] ) ? esc_attr( $attributes['dimensionWidth'] ) : '',
			isset( $attributes['dimensionHeight'] ) ? esc_attr( $attributes['dimensionHeight'] ) : '',
			isset( $attributes['backgroundColor'] ) ? esc_attr( $attributes['backgroundColor'] ) : '',
			isset( $attributes['textColor'] ) ? esc_attr( $attributes['textColor'] ) : '',
			isset( $attributes['style']['spacing']['padding']['top'] ) ? esc_attr( convert_to_css_variable( $attributes['style']['spacing']['padding']['top'] ) ) : '',
			isset( $attributes['style']['spacing']['padding']['right'] ) ? esc_attr( convert_to_css_variable( $attributes['style']['spacing']['padding']['right'] ) ) : '',
			isset( $attributes['style']['spacing']['padding']['bottom'] ) ? esc_attr( convert_to_css_variable( $attributes['style']['spacing']['padding']['bottom'] ) ) : '',
			isset( $attributes['style']['spacing']['padding']['left'] ) ? esc_attr( convert_to_css_variable( $attributes['style']['spacing']['padding']['left'] ) ) : '',
			isset( $attributes['style']['spacing']['margin']['top'] ) ? esc_attr( convert_to_css_variable( $attributes['style']['spacing']['margin']['top'] ) ) : '',
			isset( $attributes['style']['spacing']['margin']['right'] ) ? esc_attr( convert_to_css_variable( $attributes['style']['spacing']['margin']['right'] ) ) : '',
			isset( $attributes['style']['spacing']['margin']['bottom'] ) ? esc_attr( convert_to_css_variable( $attributes['style']['spacing']['margin']['bottom'] ) ) : '',
			isset( $attributes['style']['spacing']['margin']['left'] ) ? esc_attr( convert_to_css_variable( $attributes['style']['spacing']['margin']['left'] ) ) : '',
			$contents
		),
		$contents,
		$class_name,
		$attributes['imageID']
	);
}

/**
 * Converts a given value to a CSS variable if it starts with 'var:'.
 *
 * @param string $value The value to be converted.
 * @return string The converted value or the original value if it doesn't start with 'var:'.
 */
function convert_to_css_variable( $value ) {
	if ( strpos( $value, 'var:' ) === 0 ) {
		$parts = explode( '|', $value );
		if ( count( $parts ) === 3 ) {
			return 'var(--wp--preset--' . $parts[1] . '--' . $parts[2] . ')';
		}
	}
	return $value;
}
