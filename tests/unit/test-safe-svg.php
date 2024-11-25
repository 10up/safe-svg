<?php
/**
 * Test safe_svg class
 *
 * @package safe-svg
 */

use \WP_Mock\Tools\TestCase;

/**
 * SafeSvgTest class tests the safe_svg class and functions.
 */
class SafeSvgTest extends TestCase {
	/**
	 * instance of safe_svg class.
	 *
	 * @var object
	 */
	private $instance;

	/**
	 * Set up our mocked WP functions. Rather than setting up a database we can mock the returns of core WordPress functions.
	 *
	 * @return void
	 */
	public function setUp(): void {
		\WP_Mock::setUp();
		$this->instance = new SafeSvg\safe_svg();
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
	 * Test constructor.
	 */
	public function test_constructor() {
		\WP_Mock::expectActionAdded( 'load-upload.php', array( $this->instance, 'allow_svg_from_upload' ) );
		\WP_Mock::expectFilterAdded( 'wp_handle_upload_prefilter', array( $this->instance, 'check_for_svg' ) );
		\WP_Mock::expectFilterAdded( 'wp_handle_sideload_prefilter', array( $this->instance, 'check_for_svg' ) );
		\WP_Mock::expectFilterAdded( 'wp_prepare_attachment_for_js', array( $this->instance, 'fix_admin_preview' ), 10, 3 );
		\WP_Mock::expectFilterAdded( 'wp_get_attachment_image_src', array( $this->instance, 'one_pixel_fix' ), 10, 4 );
		\WP_Mock::expectFilterAdded( 'admin_post_thumbnail_html', array( $this->instance, 'featured_image_fix' ), 10, 3 );
		\WP_Mock::expectActionAdded( 'admin_enqueue_scripts', array( $this->instance, 'load_custom_admin_style' ) );
		\WP_Mock::expectActionAdded( 'get_image_tag', array( $this->instance, 'get_image_tag_override' ), 10, 6 );
		\WP_Mock::expectFilterAdded( 'wp_generate_attachment_metadata', array( $this->instance, 'skip_svg_regeneration' ), 10, 2 );
		\WP_Mock::expectFilterAdded( 'wp_get_attachment_metadata', array( $this->instance, 'metadata_error_fix' ), 10, 2 );
		\WP_Mock::expectFilterAdded( 'wp_calculate_image_srcset_meta', array( $this->instance, 'disable_srcset' ), 10, 4 );

		$this->instance->__construct();
		$this->assertConditionsMet();
	}

	/**
	 * Test allow_svg function.
	 *
	 * @return void
	 */
	public function test_allow_svg() {
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => [ 'safe_svg_upload_roles', [] ],
				'return' => [ 'editor' ],
			)
		);

		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => 'safe_svg_upload_svg',
				'return' => true,
			)
		);

		$allowed_svg = $this->instance->allow_svg( array() );
		$this->assertNotEmpty( $allowed_svg );
		$this->assertContains( 'image/svg+xml', $allowed_svg );
	}

	/**
	 * Test dont_allow_svg function.
	 *
	 * @return void
	 */
	public function test_dont_allow_svg() {
		$allowed_svg = $this->instance->allow_svg( array() );
		$this->assertEmpty( $allowed_svg );
	}

	/**
	 * Test fix_mime_type_svg function.
	 *
	 * @return void
	 */
	public function test_fix_mime_type_svg() {
		$data = $this->instance->fix_mime_type_svg(
			array(
				'ext'  => 'svg',
				'type' => '',
			)
		);
		$this->assertSame( $data['ext'], 'svg' );
		$this->assertSame( $data['type'], 'image/svg+xml' );

		$data = $this->instance->fix_mime_type_svg(
			array(
				'ext'  => 'svgz',
				'type' => '',
			)
		);
		$this->assertSame( $data['ext'], 'svgz' );
		$this->assertSame( $data['type'], 'image/svg+xml' );

		$data = $this->instance->fix_mime_type_svg( null, null, 'test.svg', null );
		$this->assertSame( $data['ext'], 'svg' );
		$this->assertSame( $data['type'], 'image/svg+xml' );
	}

	/**
	 * Test `check_for_svg` function.
	 * - Test sanitize for valid svg
	 * - Test error for bad svg
	 *
	 * @return void
	 */
	public function test_check_for_svg() {
		\WP_Mock::userFunction(
			'wp_check_filetype_and_ext',
			array(
				'return' => array(
					'ext'  => 'svg',
					'type' => 'image/svg+xml',
				),
			)
		);

		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => 'upload_files',
				'return' => true,
			)
		);

		// Test sanitize on valid SVG.
		$temp      = tempnam( sys_get_temp_dir(), 'TMP_' );
		$files_dir = __DIR__ . '/files';
		copy( "{$files_dir}/svgTestOne.svg", $temp );

		$file = array(
			'tmp_name' => $temp,
			'name'     => 'svgTestOne.svg',
		);

		\WP_Mock::expectFilterAdded( 'upload_mimes', array( $this->instance, 'allow_svg' ) );
		\WP_Mock::expectFilterAdded( 'wp_check_filetype_and_ext', array( $this->instance, 'fix_mime_type_svg' ), 75, 4 );
		\WP_Mock::expectFilterAdded( 'pre_move_uploaded_file', array( $this->instance, 'pre_move_uploaded_file' ) );

		$this->instance->check_for_svg( $file );

		// @phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$expected = str_replace( array( "\r", "\n" ), ' ', file_get_contents( $files_dir . '/svgCleanOne.svg' ) );
		// @phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$sanitized = file_get_contents( $temp );
		$this->assertXmlStringEqualsXmlString( $expected, $sanitized );

		// Test bad SVG.
		$filename  = 'badXmlTestOne.svg';
		$temp      = tempnam( sys_get_temp_dir(), 'TMP_' );
		$files_dir = __DIR__ . '/files';
		copy( "{$files_dir}/{$filename}", $temp );

		$file = array(
			'tmp_name' => $temp,
			'name'     => $filename,
		);

		$result = $this->instance->check_for_svg( $file );
		$this->assertArrayHasKey( 'error', $result );
	}

	/**
	 * Test `one_pixel_fix` function.
	 * This function tests svg_dimensions() as well.
	 *
	 * @return void
	 */
	public function test_one_pixel_fix() {
		\WP_Mock::userFunction(
			'get_post_mime_type',
			array(
				'args'   => 1,
				'return' => 'image/svg+xml',
			)
		);

		\WP_Mock::userFunction(
			'get_attached_file',
			array(
				'args'            => 1,
				'return_in_order' => array(
					__DIR__ . '/files/svgCleanOne.svg',
					__DIR__ . '/files/svgNoDimensions.svg',
					__DIR__ . '/files/svgCleanOne.svg',
				),
			)
		);

		\WP_Mock::userFunction(
			'wp_get_attachment_metadata',
			array(
				'args' => 1,
				'return_in_order' => array(
					array(
						'width' => 600,
						'height' => 600,
						'file' => __DIR__ . '/files/svgCleanOne.svg',
						'sizes' => array(
							'thumbnail' => array(
								'width' => 150,
								'height' => 150,
								'crop' => 1,
								'file' => 'svgCleanOne.svg',
								'mime-type' => 'image/svg+xml',
							),
							'medium' => array(
								'width' => 300,
								'height' => 300,
								'crop' => 0, // Set to 0 if you don't want to crop
								'file' => 'svgCleanOne.svg',
								'mime-type' => 'image/svg+xml',
							),
							'medium_large' => array(
								'width' => 768,
								'height' => 0,
								'crop' => 0,
								'file' => 'svgCleanOne.svg',
								'mime-type' => 'image/svg+xml',
							),
							'large' => array(
								'width' => 1024,
								'height' => 1024,
								'crop' => 0,
								'file' => 'svgCleanOne.svg',
								'mime-type' => 'image/svg+xml',
							),
							'1536x1536' => array(
								'width' => 1536,
								'height' => 1536,
								'crop' => 0,
								'file' => 'svgCleanOne.svg',
								'mime-type' => 'image/svg+xml',
							),
							'2048x2048' => array(
								'width' => 2048,
								'height' => 2048,
								'crop' => 0,
								'file' => 'svgCleanOne.svg',
								'mime-type' => 'image/svg+xml',
							),
						),
					),
					array(
						'filesize' => 1001, // wp_get_attachment_metadata response for ./files/svgNoDimensions.svg file doesn't contain dimensions and sizes array.
					),
				),
			)
		);

		// Test SVG Dimensions
		$image_sizes = $this->instance->one_pixel_fix( array(), 1, 'full', false );
		if ( ! empty( $image_sizes ) ) {
			$image_sizes = array_map( 'intval', $image_sizes );
		}
		$this->assertSame(
			array(
				1 => 600,
				2 => 600,
			),
			$image_sizes
		);

		// Test Default Dimensions
		$image_sizes = $this->instance->one_pixel_fix( array(), 1, 'thumbnail', false );
		if ( ! empty( $image_sizes ) ) {
			$image_sizes = array_map( 'intval', $image_sizes );
		}
		$this->assertSame(
			array(
				1 => 100,
				2 => 100,
			),
			$image_sizes
		);

		// Test Custom Dimensions
		$image_sizes = $this->instance->one_pixel_fix( array(), 1, [ 500, 500 ], false );
		if ( ! empty( $image_sizes ) ) {
			$image_sizes = array_map( 'intval', $image_sizes );
		}
		$this->assertSame(
			array(
				1 => 500,
				2 => 500,
			),
			$image_sizes
		);
	}

	/**
	 * Test `fix_admin_preview` function.
	 *
	 * @return void
	 */
	public function test_fix_admin_preview() {
		\WP_Mock::userFunction(
			'get_attached_file',
			array(
				'args'   => 1,
				'return' => __DIR__ . '/files/svgCleanOne.svg',
			)
		);
		\WP_Mock::passthruFunction( 'get_option' );

		$response = $this->instance->fix_admin_preview(
			array(
				'mime' => 'image/svg+xml',
				'url'  => '',
			),
			(object) array( 'ID' => 1 ),
			array()
		);

		$this->assertIsArray( $response );
		$this->assertSame( 600, intval( $response['width'] ) );
		$this->assertSame( 600, intval( $response['height'] ) );
	}

	/**
	 * Test `featured_image_fix` function.
	 *
	 * @return void
	 */
	public function test_featured_image_fix() {
		\WP_Mock::userFunction(
			'get_post_mime_type',
			array(
				'args'   => 1,
				'return' => 'image/svg+xml',
			)
		);

		$response = $this->instance->featured_image_fix( 'test', 1, 1 );
		$this->assertSame( '<span class="svg">test</span>', $response );

		\WP_Mock::userFunction(
			'get_post_mime_type',
			array(
				'args'   => null,
				'return' => false,
			)
		);

		$response = $this->instance->featured_image_fix( 'test', 1 );
		$this->assertSame( 'test', $response );
	}
}
