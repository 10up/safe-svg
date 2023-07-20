<?php
/**
 * Safe SVG allowed tags
 *
 * @package safe-svg
 */

namespace SafeSvg\SafeSvgTags;

/**
 * SVG Allowed Tags class.
 */
class safe_svg_tags extends \enshrined\svgSanitize\data\AllowedTags {

	/**
	 * Returns an array of tags
	 *
	 * @return array
	 */
	public static function getTags() {

		/**
		 * var  array Tags that are allowed.
		 */
		return apply_filters( 'svg_allowed_tags', parent::getTags() );
	}

	/**
	 * Standard SVG settings for escaping through `wp_kses()` function.
	 *
	 * @return array Array of allowed HTML tags and their allowed attributes.
	 */
	public static function kses_allowed_html() {
		return array(
			'svg'            => array(
				'version'           => true,
				'class'             => true,
				'fill'              => true,
				'height'            => true,
				'xml:space'         => true,
				'xmlns'             => true,
				'xmlns:xlink'       => true,
				'viewbox'           => true,
				'enable-background' => true,
				'width'             => true,
				'x'                 => true,
				'y'                 => true,
			),
			'path'           => array(
				'clip-rule'    => true,
				'd'            => true,
				'fill'         => true,
				'fill-rule'    => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'g'              => array(
				'class'        => true,
				'clip-rule'    => true,
				'd'            => true,
				'transform'    => true,
				'fill'         => true,
				'fill-rule'    => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'rect'           => array(
				'clip-rule'    => true,
				'd'            => true,
				'transform'    => true,
				'fill'         => true,
				'fill-rule'    => true,
				'stroke'       => true,
				'stroke-width' => true,
				'width'        => true,
				'height'       => true,
			),
			'polygon'        => array(
				'clip-rule'    => true,
				'd'            => true,
				'fill'         => true,
				'fill-rule'    => true,
				'stroke'       => true,
				'stroke-width' => true,
				'points'       => true,
			),
			'circle'         => array(
				'clip-rule'    => true,
				'd'            => true,
				'fill'         => true,
				'fill-rule'    => true,
				'stroke'       => true,
				'stroke-width' => true,
				'cx'           => true,
				'cy'           => true,
				'r'            => true,
			),
			'lineargradient' => array(
				'id'                => true,
				'gradientunits'     => true,
				'x'                 => true,
				'y'                 => true,
				'x2'                => true,
				'y2'                => true,
				'gradienttransform' => true,
			),
			'stop'           => array(
				'offset' => true,
				'style'  => true,
			),
			'image'          => array(
				'height'     => true,
				'width'      => true,
				'xlink:href' => true,
			),
			'defs'           => array(
				'clipPath' => true,
			),
		);
	}
}
