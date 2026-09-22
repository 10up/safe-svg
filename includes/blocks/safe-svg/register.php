<?php
/**
 * SafeSvg Block setup
 *
 * @package SafeSvg\Blocks\SafeSvgBlock
 */

namespace SafeSvg\Blocks\SafeSvgBlock;

use SafeSvg\Svg_Sanitizer;

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

	add_filter( 'wp_insert_post_data', $n( 'drop_unreadable_attachment_ids' ) );
	add_filter( 'rest_request_before_callbacks', $n( 'drop_unreadable_rendered_attachment_id' ), 10, 3 );
}

/**
 * Drop attachment IDs the saving user is not allowed to see.
 *
 * @since x.x.x
 *
 * @param array $data Sanitized post data, about to be stored.
 * @return array The post data, with unreadable references removed.
 */
function drop_unreadable_attachment_ids( $data ) {
	if ( ! get_current_user_id() ) {
		return $data;
	}

	$content = wp_unslash( $data['post_content'] );

	if ( empty( $content ) || ! has_block( 'safe-svg/svg-icon', $content ) ) {
		return $data;
	}

	$changed = false;
	$blocks  = without_unreadable_attachment_ids( parse_blocks( $content ), $changed );

	// Only re-serialize when something actually changed.
	if ( $changed ) {
		$data['post_content'] = wp_slash( serialize_blocks( $blocks ) );
	}

	return $data;
}

/**
 * Zero out every SVG block reference the current user cannot read.
 *
 * @since x.x.x
 *
 * @param array $blocks  Parsed blocks.
 * @param bool  $changed Set to true when a reference is dropped. Passed by reference.
 * @return array The blocks, with unreadable references zeroed.
 */
function without_unreadable_attachment_ids( $blocks, &$changed ) {
	foreach ( $blocks as $index => $block ) {
		if ( ! empty( $block['innerBlocks'] ) ) {
			$blocks[ $index ]['innerBlocks'] = without_unreadable_attachment_ids( $block['innerBlocks'], $changed );
		}

		if ( ! isset( $block['blockName'] ) || 'safe-svg/svg-icon' !== $block['blockName'] ) {
			continue;
		}

		$attachment_id = isset( $block['attrs']['imageID'] ) ? (int) $block['attrs']['imageID'] : 0;

		if ( $attachment_id < 1 || Svg_Sanitizer::current_user_can_read( $attachment_id ) ) {
			continue;
		}

		// Zeroed rather than removed, so the block survives and the editor shows
		// its placeholder instead of the post silently losing a block.
		$blocks[ $index ]['attrs']['imageID'] = 0;
		$changed                              = true;
	}

	return $blocks;
}

/**
 * Whether a request is core's block renderer asking for this block.
 *
 * @since x.x.x
 *
 * @param array            $handler Route handler used for the request.
 * @param \WP_REST_Request $request Request used to generate the response.
 * @return bool True when this block is about to be rendered from request attributes.
 */
function is_block_renderer_request( $handler, $request ): bool {
	$callback = $handler['callback'] ?? null;

	if ( ! is_array( $callback ) || ! isset( $callback[0] ) || ! ( $callback[0] instanceof \WP_REST_Block_Renderer_Controller ) ) {
		return false;
	}

	return 'safe-svg/svg-icon' === $request['name'];
}

/**
 * Drop an unreadable attachment ID from an ad-hoc block render request.
 *
 * @since x.x.x
 *
 * @param \WP_REST_Response|\WP_HTTP_Response|\WP_Error|mixed $response Result to send to the client.
 * @param array                                               $handler  Route handler used for the request.
 * @param \WP_REST_Request                                    $request  Request used to generate the response.
 * @return \WP_REST_Response|\WP_HTTP_Response|\WP_Error|mixed The response, unchanged.
 */
function drop_unreadable_rendered_attachment_id( $response, $handler, $request ) {
	if ( is_wp_error( $response ) || ! is_block_renderer_request( $handler, $request ) ) {
		return $response;
	}

	$attributes = $request['attributes'];

	if ( ! is_array( $attributes ) || ! isset( $attributes['imageID'] ) ) {
		return $response;
	}

	$attachment_id = (int) $attributes['imageID'];

	if ( $attachment_id < 1 || Svg_Sanitizer::current_user_can_read( $attachment_id ) ) {
		return $response;
	}

	$attributes['imageID'] = 0;
	$request->set_param( 'attributes', $attributes );

	return $response;
}

/**
 * Render callback method for the block.
 *
 * @param array $attributes The blocks attributes
 *
 * @return string|\WP_Post[] The rendered block markup.
 */
function render_block_callback( $attributes ) {
	$attachment_id = isset( $attributes['imageID'] ) ? (int) $attributes['imageID'] : 0;

	if ( $attachment_id < 1 ) {
		return '';
	}

	// Sanitize at output; don't assume it was already sanitized.
	$contents = Svg_Sanitizer::from_attachment( $attachment_id );

	if ( is_wp_error( $contents ) ) {
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

	$has_stylesheet = svg_has_stylesheet( $contents );

	/**
	 * Whether to isolate this inline SVG inside a shadow root.
	 *
	 * @since x.x.x
	 *
	 * @param bool   $use_shadow_dom Whether to isolate the SVG. Defaults to true
	 *                               when the SVG carries its own stylesheet.
	 * @param string $contents       The SVG contents.
	 * @param int    $attachment_id  The ID of the attachment.
	 * @param bool   $has_stylesheet Whether the SVG has a stylesheet.
	 */
	$use_shadow_dom = (bool) apply_filters(
		'safe_svg_inline_use_shadow_dom',
		$has_stylesheet,
		$contents,
		$attachment_id,
		$has_stylesheet
	);

	if ( $use_shadow_dom ) {
		$contents = wrap_in_shadow_root( $contents, $attachment_id );
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
		$attachment_id
	);
}

/**
 * Check whether an SVG carries its own stylesheet.
 *
 * Keep in sync with hasStylesheet() in inline-svg.js.
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

	// Stop an unsanitized SVG closing the shadow template early and escaping.
	$svg = str_ireplace( '</template', '&lt;/template', $svg );

	// Stop a filtered value closing the <style> and injecting markup.
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
