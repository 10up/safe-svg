<?php
/**
 * Plugin name: Safe SVG Playwright Test plugin
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
		$attachment_id = filter_input( INPUT_GET, 'safe_svg_attachment_id', FILTER_VALIDATE_INT );

		if ( empty( $attachment_id ) ) {
			return;
		}

		$attachment_id = absint( $attachment_id );

		echo wp_get_attachment_image( $attachment_id, 'thumbnail', false, [ 'id' => 'thumbnail-image' ] );
		echo wp_get_attachment_image( $attachment_id, 'medium', false, [ 'id' => 'medium-image' ] );
		echo wp_get_attachment_image( $attachment_id, 'large', false, [ 'id' => 'large-image' ] );
		echo wp_get_attachment_image( $attachment_id, 'full', false, [ 'id' => 'full-image' ] );
		echo wp_get_attachment_image( $attachment_id, [ 100, 120 ], false, [ 'id' => 'custom-image' ] );
		echo get_image_tag( $attachment_id, '', '', '', 'thumbnail' );
		echo get_image_tag( $attachment_id, '', '', '', 'medium' );
		echo get_image_tag( $attachment_id, '', '', '', 'large' );
		echo get_image_tag( $attachment_id, '', '', '', 'full' );
		echo get_image_tag( $attachment_id, '', '', '', [ 100, 120 ] );
	}
);
