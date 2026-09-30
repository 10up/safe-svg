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
		\WP_Mock::expectFilterAdded( 'wp_handle_upload', array( $this->instance, 'sanitize_uploaded_svg' ) );
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
		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => 'upload_files',
				'return' => true,
			)
		);

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
	 * Test that fix_mime_type_svg() doesn't vouch for an SVG the user can't upload.
	 *
	 * @return void
	 */
	public function test_fix_mime_type_svg_respects_upload_roles() {
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => [ 'safe_svg_upload_roles', [] ],
				'return' => [ 'administrator' ],
			)
		);

		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => 'safe_svg_upload_svg',
				'return' => false,
			)
		);

		$data = $this->instance->fix_mime_type_svg(
			array(
				'ext'  => false,
				'type' => false,
			),
			null,
			'test.svg',
			null
		);
		$this->assertFalse( $data['ext'] );
		$this->assertFalse( $data['type'] );

		$data = $this->instance->fix_mime_type_svg(
			array(
				'ext'  => false,
				'type' => false,
			),
			null,
			'test.svgz',
			null
		);
		$this->assertFalse( $data['ext'] );
		$this->assertFalse( $data['type'] );
	}

	/**
	 * Test that check_for_svg() gives the SVG-specific error to a user who can't upload SVGs.
	 *
	 * @return void
	 */
	public function test_check_for_svg_denies_user_without_svg_role() {
		\WP_Mock::userFunction(
			'wp_check_filetype_and_ext',
			array(
				'return' => array(
					'ext'  => false,
					'type' => false,
				),
			)
		);

		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => 'upload_files',
				'return' => false,
			)
		);

		$temp = tempnam( sys_get_temp_dir(), 'TMP_' );
		copy( __DIR__ . '/files/svgTestOne.svg', $temp );

		$result = $this->instance->check_for_svg(
			array(
				'tmp_name' => $temp,
				'name'     => 'svgTestOne.svg',
			)
		);

		$this->assertSame( 'Sorry, you are not allowed to upload SVG files.', $result['error'] );
		// The file was not sanitized, because it never gets moved into place.
		$this->assertFileEquals( __DIR__ . '/files/svgTestOne.svg', $temp );
		unlink( $temp );
	}

	/**
	 * Test that an SVG written into place without the upload prefilters is sanitized.
	 *
	 * @return void
	 */
	public function test_sanitize_uploaded_svg() {
		$files_dir = __DIR__ . '/files';
		$dir       = sys_get_temp_dir() . '/safe-svg-' . uniqid();
		mkdir( $dir );

		$path = $dir . '/replaced.svg';
		copy( "{$files_dir}/svgTestOne.svg", $path );

		$upload = array(
			'file' => $path,
			'url'  => 'https://example.test/wp-content/uploads/replaced.svg',
			'type' => 'image/svg+xml',
		);

		$this->assertSame( $upload, $this->instance->sanitize_uploaded_svg( $upload ) );

		// @phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$expected = str_replace( array( "\r", "\n" ), ' ', file_get_contents( "{$files_dir}/svgCleanOne.svg" ) );
		// @phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$this->assertXmlStringEqualsXmlString( $expected, file_get_contents( $path ) );

		// Typed by extension only, as some writers report a generic type.
		copy( "{$files_dir}/svgTestOne.svg", $path );
		$upload['type'] = 'application/octet-stream';
		$this->instance->sanitize_uploaded_svg( $upload );
		// @phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$this->assertXmlStringEqualsXmlString( $expected, file_get_contents( $path ) );

		unlink( $path );
		rmdir( $dir );
	}

	/**
	 * Test that an SVG that can't be sanitized is removed rather than left in place.
	 *
	 * @return void
	 */
	public function test_sanitize_uploaded_svg_removes_unsanitizable_file() {
		$dir = sys_get_temp_dir() . '/safe-svg-' . uniqid();
		mkdir( $dir );

		$path = $dir . '/bad.svg';
		copy( __DIR__ . '/files/badXmlTestOne.svg', $path );

		\WP_Mock::userFunction(
			'wp_delete_file',
			array(
				'args'   => $path,
				'times'  => 1,
				'return' => function ( $file ) {
					unlink( $file );
				},
			)
		);

		$result = $this->instance->sanitize_uploaded_svg(
			array(
				'file' => $path,
				'url'  => 'https://example.test/wp-content/uploads/bad.svg',
				'type' => 'image/svg+xml',
			)
		);

		$this->assertArrayHasKey( 'error', $result );
		$this->assertFileDoesNotExist( $path );
		rmdir( $dir );
	}

	/**
	 * Test that sanitize_uploaded_svg() leaves other uploads alone.
	 *
	 * @return void
	 */
	public function test_sanitize_uploaded_svg_ignores_other_uploads() {
		$path = tempnam( sys_get_temp_dir(), 'TMP_' );
		copy( __DIR__ . '/files/svgTestOne.svg', $path );

		// Not an SVG by type or extension.
		$upload = array(
			'file' => $path,
			'url'  => 'https://example.test/wp-content/uploads/image.png',
			'type' => 'image/png',
		);
		$this->assertSame( $upload, $this->instance->sanitize_uploaded_svg( $upload ) );
		$this->assertFileEquals( __DIR__ . '/files/svgTestOne.svg', $path );

		// An upload that already failed.
		$failed = array( 'error' => 'Upload failed.' );
		$this->assertSame( $failed, $this->instance->sanitize_uploaded_svg( $failed ) );

		unlink( $path );
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
		$image_sizes = $this->instance->one_pixel_fix( array(), 1, 'thumbnail', false );
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
				1 => 600,
				2 => 600,
			),
			$image_sizes
		);
	}

	/**
	 * Test `skip_svg_regeneration` function.
	 * Sizes registered without dimensions should be skipped, matching how WordPress
	 * core skips them when generating attachment metadata.
	 *
	 * @return void
	 */
	public function test_skip_svg_regeneration_skips_sizes_without_dimensions() {
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
				'args'   => 1,
				'return' => __DIR__ . '/files/svgCleanOne.svg',
			)
		);

		\WP_Mock::userFunction(
			'wp_upload_dir',
			array(
				'return' => array( 'basedir' => '/var/www/wp-content/uploads' ),
			)
		);

		\WP_Mock::userFunction(
			'trailingslashit',
			array(
				'return' => function ( $string ) {
					return rtrim( $string, '/\\' ) . '/';
				},
			)
		);

		\WP_Mock::userFunction(
			'wp_get_attachment_metadata',
			array(
				'args'   => 1,
				'return' => array(
					'width'  => 600,
					'height' => 600,
				),
			)
		);

		\WP_Mock::userFunction(
			'wp_get_additional_image_sizes',
			array(
				'return' => array(
					// A size disabled by registering it without dimensions.
					'medium_large' => array(
						'width'  => 0,
						'height' => 0,
						'crop'   => 0,
					),
					// A size with only a width set is still valid.
					'flexible'     => array(
						'width'  => 768,
						'height' => 0,
						'crop'   => 0,
					),
				),
			)
		);

		\WP_Mock::userFunction(
			'get_intermediate_image_sizes',
			array(
				'return' => array( 'thumbnail', 'medium_large', 'flexible' ),
			)
		);

		\WP_Mock::userFunction(
			'get_option',
			array(
				'return' => function ( $option, $default = false ) {
					if ( 'thumbnail_size_w' === $option ) {
						return 150;
					}

					return $default;
				},
			)
		);

		$metadata = $this->instance->skip_svg_regeneration( array(), 1 );

		$this->assertArrayHasKey( 'sizes', $metadata );
		$this->assertArrayHasKey( 'thumbnail', $metadata['sizes'] );
		$this->assertArrayHasKey( 'flexible', $metadata['sizes'] );
		$this->assertArrayNotHasKey( 'medium_large', $metadata['sizes'] );

		// A size reduced to width only keeps its width.
		$this->assertSame( 768, $metadata['sizes']['flexible']['width'] );
		$this->assertSame( 0, intval( $metadata['sizes']['flexible']['height'] ) );
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
