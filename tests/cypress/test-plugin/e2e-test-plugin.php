<?php
/**
 * Plugin name: Safe SVG Cypress Test plugin
 *
 * @package safe-svg
 */

add_filter(
	'svg_allowed_attributes',
	function ( $attributes ) {
		$attributes[] = 'customTestAttribute'; // This would allow the customTestAttribute="" attribute.
		return $attributes;
	}
);


add_filter(
	'svg_allowed_tags',
	function ( $tags ) {
		$tags[] = 'customTestTag'; // This would allow the <customTestTag> element.
		return $tags;
	}
);

add_action(
	'wp_body_open',
	function () {
		echo wp_get_attachment_image( 6, 'thumbnail', false, [ 'id' => 'thumbnail-image' ] );
		echo wp_get_attachment_image( 6, 'medium', false, [ 'id' => 'medium-image' ] );
		echo wp_get_attachment_image( 6, 'large', false, [ 'id' => 'large-image' ] );
		echo wp_get_attachment_image( 6, 'full', false, [ 'id' => 'full-image' ] );
		echo wp_get_attachment_image( 6, [ 100, 120 ], false, [ 'id' => 'custom-image' ] );
		echo get_image_tag( 6, '', '', '', 'thumbnail' );
		echo get_image_tag( 6, '', '', '', 'medium' );
		echo get_image_tag( 6, '', '', '', 'large' );
		echo get_image_tag( 6, '', '', '', 'full' );
		echo get_image_tag( 6, '', '', '', [ 100, 120 ] );
	}
);
