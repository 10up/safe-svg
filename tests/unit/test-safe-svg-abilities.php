<?php
/**
 * Test the Abilities API integration.
 *
 * @package safe-svg
 */

use WP_Mock\Tools\TestCase;

/**
 * SafeSvgAbilitiesTest tests ability registration, permissions and callbacks.
 */
class SafeSvgAbilitiesTest extends TestCase {
	/**
	 * Abilities instance under test.
	 *
	 * @var \SafeSvg\Abilities
	 */
	private $abilities;

	/**
	 * Set up WP Mock.
	 *
	 * @return void
	 */
	public function setUp(): void {
		\WP_Mock::setUp();
		\WP_Mock::passthruFunction( 'absint' );
		\WP_Mock::passthruFunction( '__' );
		$this->abilities = new \SafeSvg\Abilities();
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
	 * Mock the capability checks used by can_upload_svg().
	 *
	 * @param bool $upload_files Whether the user has upload_files.
	 * @param bool $upload_svg   Value of the safe_svg_current_user_can_upload filter.
	 * @param bool $edit_post    Whether the user can edit post 5.
	 * @return void
	 */
	protected function mock_upload_caps( $upload_files, $upload_svg = true, $edit_post = false ) {
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'safe_svg_upload_roles', array() ),
				'return' => array(),
			)
		);
		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => array( 'upload_files' ),
				'return' => $upload_files,
			)
		);
		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => array( 'edit_post', 5 ),
				'return' => $edit_post,
			)
		);
		\WP_Mock::onFilter( 'safe_svg_current_user_can_upload' )->with( $upload_files )->reply( $upload_svg );
	}

	/**
	 * Test that the constructor hooks into the Abilities API.
	 */
	public function test_constructor() {
		\WP_Mock::expectActionAdded( 'wp_abilities_api_categories_init', array( $this->abilities, 'register_category' ) );
		\WP_Mock::expectActionAdded( 'wp_abilities_api_init', array( $this->abilities, 'register_abilities' ) );

		$this->abilities->__construct();
		$this->assertConditionsMet();
	}

	/**
	 * Test that the category and both abilities are registered with REST exposure.
	 */
	public function test_register() {
		$registered = array();

		\WP_Mock::userFunction(
			'wp_register_ability_category',
			array(
				'times' => 1,
				'args'  => array( 'safe-svg', \WP_Mock\Functions::type( 'array' ) ),
			)
		);
		\WP_Mock::userFunction(
			'wp_register_ability',
			array(
				'times'  => 2,
				'return' => function ( $name, $args ) use ( &$registered ) {
					$registered[ $name ] = $args;
				},
			)
		);

		$this->abilities->register_category();
		$this->abilities->register_abilities();

		$this->assertSame( array( 'safe-svg/upload-svg', 'safe-svg/get-svg' ), array_keys( $registered ) );
		$this->assertTrue( $registered['safe-svg/upload-svg']['meta']['show_in_rest'] );
		$this->assertTrue( $registered['safe-svg/get-svg']['meta']['annotations']['readonly'] );
	}

	/**
	 * Test that upload_files is required.
	 */
	public function test_can_upload_svg_requires_upload_files() {
		$this->mock_upload_caps( false );

		$this->assertFalse( $this->abilities->can_upload_svg( array() ) );
	}

	/**
	 * Test that the plugin's SVG upload permission is required.
	 */
	public function test_can_upload_svg_requires_svg_permission() {
		$this->mock_upload_caps( true, false );

		$this->assertFalse( $this->abilities->can_upload_svg( array() ) );
	}

	/**
	 * Test that a permitted user can upload an unattached SVG.
	 */
	public function test_can_upload_svg_allows_unattached_upload() {
		$this->mock_upload_caps( true );

		$this->assertTrue( $this->abilities->can_upload_svg( array( 'post_id' => 0 ) ) );
	}

	/**
	 * Test that attaching to a post requires being able to edit it.
	 */
	public function test_can_upload_svg_requires_edit_post_to_attach() {
		$this->mock_upload_caps( true, true, false );
		$this->assertFalse( $this->abilities->can_upload_svg( array( 'post_id' => 5 ) ) );
	}

	/**
	 * Test that a user who can edit the post may attach to it.
	 */
	public function test_can_upload_svg_allows_attaching_to_editable_post() {
		$this->mock_upload_caps( true, true, true );
		$this->assertTrue( $this->abilities->can_upload_svg( array( 'post_id' => 5 ) ) );
	}

	/**
	 * Test that reading without an ID is refused.
	 */
	public function test_can_read_svg_rejects_missing_id() {
		$this->assertFalse( $this->abilities->can_read_svg( array() ) );
	}

	/**
	 * Test that get_svg() returns sanitized markup and the attachment URL.
	 */
	public function test_get_svg_returns_markup() {
		$attachment            = new \stdClass();
		$attachment->post_type = 'attachment';

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

		$result = $this->abilities->get_svg( array( 'id' => 12 ) );

		$this->assertStringContainsString( '<svg', $result['markup'] );
		$this->assertSame( 'https://example.com/wp-content/uploads/svgCleanOne.svg', $result['url'] );
	}

	/**
	 * Test that get_svg() passes on the error for an attachment that is not an SVG.
	 */
	public function test_get_svg_returns_error_for_non_svg() {
		$attachment            = new \stdClass();
		$attachment->post_type = 'attachment';

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
				'return' => 'image/png',
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

		$result = $this->abilities->get_svg( array( 'id' => 12 ) );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'safe_svg_not_svg', $result->get_error_code() );
	}
}
