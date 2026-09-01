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
	$n = function ( $function_name ) {
		return __NAMESPACE__ . "\\$function_name";
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
	 * Whether to isolate this inline SVG inside a shadow root.
	 *
	 * @since x.x.x
	 *
	 * @param bool   $use_shadow_dom Whether to isolate the SVG. Defaults to true
	 *                               when the SVG carries its own stylesheet.
	 * @param string $contents       The SVG contents.
	 * @param int    $attachment_id  The ID of the attachment.
	 */
	$use_shadow_dom = (bool) apply_filters(
		'safe_svg_inline_use_shadow_dom',
		svg_has_stylesheet( $contents ),
		$contents,
		$attributes['imageID']
	);

	if ( $use_shadow_dom ) {
		$contents = wrap_in_shadow_root( $contents, $attributes['imageID'] );
	}

	if ( ! empty( $attributes['href'] ) ) {
		$link_target = ! empty( $attributes['linkTarget'] ) ? $attributes['linkTarget'] : false;

		$rel_parts = array();
		if ( ! empty( $attributes['nofollow'] ) ) {
			$rel_parts[] = 'nofollow';
		}
		if ( ! empty( $attributes['sponsored'] ) ) {
			$rel_parts[] = 'sponsored';
		}
		$rel      = implode( ' ', $rel_parts );
		$rel_attr = $rel ? ' rel="' . esc_attr( $rel ) . '"' : '';

		$aria_label = ! empty( $attributes['linkLabel'] ) ? ' aria-label="' . esc_attr( $attributes['linkLabel'] ) . '"' : '';

		$contents = sprintf(
			'<a href="%1$s"%2$s%3$s%4$s>%5$s</a>',
			esc_url( $attributes['href'] ),
			$link_target ? ' target="' . esc_attr( $link_target ) . '"' : '',
			$rel_attr,
			$aria_label,
			$contents
		);
	}

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
			'<div class="wp-block-safe-svg-svg-icon safe-svg-cover" style="text-align: %1$s;">
				<div class="safe-svg-inside %2$s%3$s" style="width: %4$spx; height: %5$spx; background-color: var(--wp--preset--color--%6$s); color: var(--wp--preset--color--%7$s); padding-top: %8$s; padding-right: %9$s; padding-bottom: %10$s; padding-left: %11$s; margin-top: %12$s; margin-right: %13$s; margin-bottom: %14$s; margin-left: %15$s;">%16$s</div>
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
 * Check whether an SVG carries its own stylesheet.
 *
 * @since x.x.x
 *
 * @param string $contents The SVG contents.
 * @return bool True if the SVG contains a style element.
 */
function svg_has_stylesheet( $contents ): bool {
	return 1 === preg_match( '#<\s*(?:[a-z0-9_.\-]+:)?style\b#i', $contents );
}

/**
 * Wrap an SVG in a declarative shadow root.
 *
 * @since x.x.x
 *
 * @param string $svg           The SVG contents.
 * @param int    $attachment_id The ID of the attachment.
 * @return string The SVG wrapped in a shadow host.
 */
function wrap_in_shadow_root( $svg, $attachment_id ): string {
	/**
	 * The styles applied inside the inline SVG's shadow root.
	 *
	 * @since x.x.x
	 *
	 * @param string $styles        The CSS to inject. Return an empty string for none.
	 * @param int    $attachment_id The ID of the attachment.
	 */
	$styles = (string) apply_filters(
		'safe_svg_inline_shadow_styles',
		'svg{fill:currentColor;width:100%;height:100%;max-width:100%;max-height:100%}',
		$attachment_id
	);

	$svg = str_ireplace( '</template', '&lt;/template', $svg );

	$styles = str_replace( '<', '', $styles );

	return sprintf(
		'<span class="safe-svg-shadow-guard" style="display: block; height: 100%%; contain: paint;"><span class="safe-svg-shadow-host" style="display: block; height: 100%%;"><template shadowrootmode="open">%1$s%2$s</template></span></span>',
		'' !== $styles ? '<style>' . $styles . '</style>' : '',
		$svg
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
