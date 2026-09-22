<?php
/**
 * REST API for sanitized inline SVG markup.
 *
 * @package safe-svg
 */

namespace SafeSvg;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Registers the endpoint that returns sanitized SVG markup.
 *
 * @since 2.5.1
 */
class Rest {

	/**
	 * Setup needed hooks.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 *
	 * @since 2.5.1
	 */
	public function register_routes() {
		register_rest_route(
			'safe-svg/v1',
			'/svg/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'type'              => 'integer',
						'minimum'           => 1,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	/**
	 * Whether the current user can request sanitized markup for an attachment.
	 *
	 * @since 2.5.1
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return bool
	 */
	public function permissions_check( $request ) {
		$id = absint( $request['id'] );

		if ( $id < 1 ) {
			return false;
		}

		return Svg_Sanitizer::current_user_can_read( $id );
	}

	/**
	 * Return sanitized SVG markup for an attachment.
	 *
	 * @since 2.5.1
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$id     = absint( $request['id'] );
		$markup = Svg_Sanitizer::from_attachment( $id );

		if ( is_wp_error( $markup ) ) {
			return $markup;
		}

		return rest_ensure_response(
			array(
				'markup' => $markup,
				'url'    => (string) wp_get_attachment_url( $id ),
			)
		);
	}
}
