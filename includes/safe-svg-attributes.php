<?php
/**
 * Safe SVG allowed attributes.
 *
 * @package safe-svg
 */

namespace SafeSvg\SafeSvgAttr;

/**
 * SVG Allowed Attributes lass.
 */
class safe_svg_attributes extends \enshrined\svgSanitize\data\AllowedAttributes {

	/**
	 * Returns an array of attributes
	 *
	 * @return array
	 */
	public static function getAttributes() {

		/**
		 * var  array Attributes that are allowed.
		 */
		return apply_filters( 'svg_allowed_attributes', parent::getAttributes() );
	}
}
