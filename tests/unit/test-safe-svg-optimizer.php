<?php
/**
 * Test the optimizer's AJAX handler.
 *
 * @package safe-svg
 */

use WP_Mock\Tools\TestCase;

/**
 * SafeSvgOptimizerTest tests \SafeSVG\Optimizer::optimize().
 */
class SafeSvgOptimizerTest extends TestCase {
	/**
	 * Optimized markup the client would send, with a remote reference.
	 *
	 * @var string
	 */
	const OPTIMIZED = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><image href="https://attacker.example/track.png" width="10" height="10"/><rect width="10" height="10"/></svg>';

	/**
	 * Original file contents, to tell an untouched file from a rewritten one.
	 *
	 * @var string
	 */
	const ORIGINAL = 'original-contents';

	/**
	 * Temp file used as the attachment.
	 *
	 * @var string
	 */
	private $path = '';

	/**
	 * Set up WP Mock.
	 */
	public function setUp(): void {
		\WP_Mock::setUp();

		$_GET['optimized_svg'] = self::OPTIMIZED;
	}

	/**
	 * Tear down WP Mock.
	 */
	public function tearDown(): void {
		unset( $_GET['optimized_svg'] );

		if ( $this->path && file_exists( $this->path ) ) {
			unlink( $this->path );
		}

		\WP_Mock::tearDown();
	}

	/**
	 * Mock a request to optimize attachment 12.
	 *
	 * @param string $extension     Extension of the attached file.
	 * @param string $mime          Stored mime type.
	 * @param bool   $can_upload    Whether the user may upload SVGs.
	 * @param bool   $strip_remotes Whether remote references are filtered out.
	 */
	protected function mock_request( $extension = 'svg', $mime = 'image/svg+xml', $can_upload = true, $strip_remotes = false ) {
		$this->path = sys_get_temp_dir() . '/' . uniqid( 'safe-svg-', true ) . '.' . $extension;
		file_put_contents( $this->path, self::ORIGINAL ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		// filter_input() cannot see $_GET under the CLI, so the ID comes from the URL lookup.
		\WP_Mock::userFunction( 'attachment_url_to_postid', array( 'return' => 12 ) );
		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => array( 'edit_post', 12 ),
				'return' => true,
			)
		);
		\WP_Mock::userFunction(
			'current_user_can',
			array(
				'args'   => array( 'safe_svg_upload_svg' ),
				'return' => $can_upload,
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'safe_svg_upload_roles', array() ),
				'return' => array( 'administrator' ),
			)
		);
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'safe_svg_large_svg' ),
				'return' => 0,
			)
		);
		\WP_Mock::userFunction( 'check_ajax_referer', array( 'return' => 1 ) );
		\WP_Mock::userFunction( 'get_attached_file', array( 'return' => $this->path ) );
		\WP_Mock::userFunction( 'get_post_mime_type', array( 'return' => $mime ) );
		\WP_Mock::userFunction( 'wp_die' );

		\WP_Mock::onFilter( 'safe_svg_remove_remote_references' )->with( false )->reply( $strip_remotes );
	}

	/**
	 * Contents of the attached file after the request.
	 *
	 * @return string
	 */
	protected function file_contents() {
		return file_get_contents( $this->path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	/**
	 * Test that an SVG is rewritten with sanitized, optimized markup.
	 */
	public function test_optimize_writes_svg() {
		$this->mock_request();

		( new \SafeSVG\Optimizer() )->optimize();

		$this->assertStringContainsString( '<rect', $this->file_contents() );
	}

	/**
	 * Test that the site's remote-reference setting is applied.
	 */
	public function test_optimize_strips_remote_references_when_filtered() {
		$this->mock_request( 'svg', 'image/svg+xml', true, true );

		( new \SafeSVG\Optimizer() )->optimize();

		$this->assertStringContainsString( '<rect', $this->file_contents() );
		$this->assertStringNotContainsString( 'attacker.example', $this->file_contents() );
	}

	/**
	 * Test that a user outside the SVG upload roles cannot write SVG markup.
	 */
	public function test_optimize_requires_svg_upload_permission() {
		$this->mock_request( 'svg', 'image/svg+xml', false );

		( new \SafeSVG\Optimizer() )->optimize();

		$this->assertSame( self::ORIGINAL, $this->file_contents() );
	}

	/**
	 * Test that a non-SVG attachment is never overwritten.
	 */
	public function test_optimize_ignores_non_svg_attachment() {
		$this->mock_request( 'png', 'image/png' );

		( new \SafeSVG\Optimizer() )->optimize();

		$this->assertSame( self::ORIGINAL, $this->file_contents() );
	}

	/**
	 * Test that an SVG mime type on a non-SVG file is not trusted.
	 */
	public function test_optimize_ignores_svg_mime_on_other_file() {
		$this->mock_request( 'png', 'image/svg+xml' );

		( new \SafeSVG\Optimizer() )->optimize();

		$this->assertSame( self::ORIGINAL, $this->file_contents() );
	}

	/**
	 * Test that a gzipped .svgz is not overwritten with plain XML.
	 */
	public function test_optimize_ignores_svgz() {
		$this->mock_request( 'svgz', 'image/svg+xml' );

		( new \SafeSVG\Optimizer() )->optimize();

		$this->assertSame( self::ORIGINAL, $this->file_contents() );
	}
}
