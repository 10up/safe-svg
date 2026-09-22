<?php
/**
 * Test the sanitized SVG REST endpoint.
 *
 * @package safe-svg
 */

use WP_Mock\Tools\TestCase;

/**
 * SafeSvgRestTest tests REST permission and response callbacks.
 */
class SafeSvgRestTest extends TestCase {
	/**
	 * REST controller under test.
	 *
	 * @var \SafeSvg\Rest
	 */
	private $rest;

	/**
	 * Set up WP Mock.
	 *
	 * @return void
	 */
	public function setUp(): void {
		\WP_Mock::setUp();
		\WP_Mock::passthruFunction( 'absint' );
		$this->rest = new \SafeSvg\Rest();
	}

	/**
	 * Tear down WP Mock.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Build a request-like object with an attachment ID.
	 *
	 * @param int $id Attachment ID.
	 * @return ArrayObject
	 */
	protected function request( $id ) {
		return new ArrayObject( array( 'id' => $id ) );
	}

	/**
	 * Mock current_user_can() for the REST permission callback.
	 *
	 * @param bool $can_edit Whether the user can edit_posts.
	 * @param bool $can_read Whether the user can read_post.
	 * @param int  $id       Attachment ID used for the read_post check.
	 * @return void
	 */
	protected function mock_caps( $can_edit, $can_read = false, $id = 12 ) {
		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => array( 'edit_posts' ),
				'return' => $can_edit,
			)
		);
		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => array( 'read_post', $id ),
				'return' => $can_read,
			)
		);
	}

	/**
	 * Test that both capabilities are required by default.
	 */
	public function test_rest_can_read_svg_requires_both_caps() {
		$this->mock_caps( true, false );
		\WP_Mock::onFilter( 'safe_svg_require_read_post' )->with( true, 12 )->reply( true );

		$this->assertFalse( $this->rest->permissions_check( $this->request( 12 ) ) );
	}

	/**
	 * Test that edit_posts alone is not enough.
	 */
	public function test_rest_can_read_svg_rejects_missing_edit_posts() {
		$this->mock_caps( false, true );

		$this->assertFalse( $this->rest->permissions_check( $this->request( 12 ) ) );
	}

	/**
	 * Test that a permitted user can read the attachment.
	 */
	public function test_rest_can_read_svg_allows_editor_who_can_read() {
		$this->mock_caps( true, true );
		\WP_Mock::onFilter( 'safe_svg_require_read_post' )->with( true, 12 )->reply( true );

		$this->assertTrue( $this->rest->permissions_check( $this->request( 12 ) ) );
	}

	/**
	 * Test that a site can opt out of the per-attachment check.
	 */
	public function test_rest_can_read_svg_honours_filter_opt_out() {
		$this->mock_caps( true, false );
		\WP_Mock::onFilter( 'safe_svg_require_read_post' )->with( true, 12 )->reply( false );

		$this->assertTrue( $this->rest->permissions_check( $this->request( 12 ) ) );
	}

	/**
	 * Test that the filter cannot open the endpoint below the editor floor.
	 */
	public function test_rest_can_read_svg_filter_cannot_bypass_edit_posts() {
		$this->mock_caps( false, true );
		\WP_Mock::onFilter( 'safe_svg_require_read_post' )->with( true, 12 )->reply( false );

		$this->assertFalse( $this->rest->permissions_check( $this->request( 12 ) ) );
	}

	/**
	 * Test that an invalid ID is rejected before cap checks.
	 */
	public function test_rest_can_read_svg_rejects_invalid_id() {
		$this->assertFalse( $this->rest->permissions_check( $this->request( 0 ) ) );
	}

	/**
	 * Test that the callback wraps sanitized markup and the attachment URL.
	 */
	public function test_rest_get_sanitized_svg_returns_markup() {
		$attachment            = new \stdClass();
		$attachment->post_type = 'attachment';

		\WP_Mock::passthruFunction( '__' );
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'safe_svg_large_svg' ),
				'return' => 0,
			)
		);
		\WP_Mock::userFunction(
			'get_post',
			array(
				'args'   => array( 12 ),
				'return' => $attachment,
			)
		);
		\WP_Mock::userFunction(
			'get_post_mime_type',
			array(
				'args'   => array( 12 ),
				'return' => 'image/svg+xml',
			)
		);
		\WP_Mock::userFunction(
			'get_attached_file',
			array(
				'args'   => array( 12 ),
				'return' => TEST_PLUGIN_DIR . '/tests/unit/files/svgCleanOne.svg',
			)
		);
		\WP_Mock::userFunction(
			'is_wp_error',
			array(
				'return' => function ( $thing ) {
					return $thing instanceof \WP_Error;
				},
			)
		);
		\WP_Mock::userFunction(
			'wp_get_attachment_url',
			array(
				'args'   => array( 12 ),
				'return' => 'https://example.com/wp-content/uploads/svgCleanOne.svg',
			)
		);
		\WP_Mock::userFunction(
			'rest_ensure_response',
			array(
				'return' => function ( $data ) {
					return $data;
				},
			)
		);

		$response = $this->rest->get_item( $this->request( 12 ) );

		$this->assertIsArray( $response );
		$this->assertArrayHasKey( 'markup', $response );
		$this->assertStringContainsString( '<svg', $response['markup'] );

		// Returned so the editor never has to ask /wp/v2/media for it.
		$this->assertSame(
			'https://example.com/wp-content/uploads/svgCleanOne.svg',
			$response['url']
		);
	}

	/**
	 * Test that the route is registered with an ID-only path.
	 */
	public function test_register_rest_routes_is_id_only() {
		\WP_Mock::userFunction(
			'register_rest_route',
			array(
				'times'  => 1,
				'args'   => array(
					'safe-svg/v1',
					'/svg/(?P<id>\d+)',
					\WP_Mock\Functions::type( 'array' ),
				),
				'return' => true,
			)
		);

		$this->rest->register_routes();
		$this->assertConditionsMet();
	}
}
