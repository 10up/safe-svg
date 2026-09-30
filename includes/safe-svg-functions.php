<?php
/**
 * Public helper functions.
 *
 * @package safe-svg
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'safe_svg_sanitize_markup' ) ) {
	/**
	 * Sanitize a string of SVG markup.
	 *
	 * Runs the same sanitizer the plugin uses on upload, so the returned markup
	 * honors the `svg_allowed_tags` and `svg_allowed_attributes` filters and the
	 * other sanitizer settings. Use this for SVG markup that comes from an API
	 * or any other third-party source before you output it.
	 *
	 * The return value is always plain XML; gzipped input is decoded first and
	 * is not re-compressed. It is `false` when the markup is empty, is not a
	 * string, or cannot be sanitized. The input must be a single SVG document
	 * with one `<svg>` root, so pass just the `<svg>` element rather than a
	 * larger chunk of HTML it happens to sit inside. Always check for `false`
	 * before echoing:
	 *
	 *     $svg = safe_svg_sanitize_markup( $raw_svg );
	 *
	 *     if ( false !== $svg ) {
	 *         echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	 *     }
	 *
	 * @since x.x.x
	 *
	 * @param string $svg Raw SVG markup, optionally gzip-compressed.
	 * @return string|false Sanitized SVG markup, or false on failure.
	 */
	function safe_svg_sanitize_markup( $svg ) {
		return \SafeSvg\Svg_Sanitizer::sanitize_markup( $svg );
	}
}
