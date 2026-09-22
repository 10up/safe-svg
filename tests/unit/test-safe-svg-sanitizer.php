<?php
/**
 * Test shared SVG sanitization helpers.
 *
 * @package safe-svg
 */

use WP_Mock\Tools\TestCase;

/**
 * SafeSvgSanitizerTest tests Svg_Sanitizer.
 */
class SafeSvgSanitizerTest extends TestCase {
	/**
	 * Set up WP Mock.
	 *
	 * @return void
	 */
	public function setUp(): void {
		\WP_Mock::setUp();
		\WP_Mock::passthruFunction( '__' );
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'safe_svg_large_svg' ),
				'return' => 0,
			)
		);
	}

	/**
	 * Temp files created by a test, removed on tear down.
	 *
	 * @var string[]
	 */
	private $temp_files = array();

	/**
	 * Tear down WP Mock.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		foreach ( $this->temp_files as $file ) {
			if ( file_exists( $file ) ) {
				unlink( $file );
			}
		}

		$this->temp_files = array();

		\WP_Mock::tearDown();
	}

	/**
	 * Write a file larger than the cache threshold and mock it as an attachment.
	 *
	 * @param string $contents File contents, padded to exceed the threshold.
	 * @param int    $id       Attachment ID.
	 * @return string Path to the file.
	 */
	protected function mock_large_attachment( $contents, $id = 12 ) {
		$path               = tempnam( sys_get_temp_dir(), 'safe-svg-' );
		$this->temp_files[] = $path;

		$padding = \SafeSvg\Svg_Sanitizer::CACHE_MIN_FILESIZE - strlen( $contents );

		if ( $padding > 0 ) {
			$contents .= str_repeat( '<!-- pad -->', (int) ceil( $padding / 12 ) );
		}

		file_put_contents( $path, $contents ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents

		$this->mock_attachment_meta( $id, 'image/svg+xml', $path );

		return $path;
	}

	/**
	 * Mock the meta read for a cache entry.
	 *
	 * @param array|string $cached Value get_post_meta() should return.
	 * @param int          $id     Attachment ID.
	 * @return void
	 */
	protected function mock_cache_read( $cached, $id = 12 ) {
		\WP_Mock::userFunction(
			'get_post_meta',
			array(
				'args'   => array( $id, \SafeSvg\Svg_Sanitizer::CACHE_META_KEY, true ),
				'return' => $cached,
			)
		);
	}

	/**
	 * Path to a fixture file.
	 *
	 * @param string $fixture File name within tests/unit/files.
	 * @return string
	 */
	protected function fixture_path( $fixture ) {
		return TEST_PLUGIN_DIR . '/tests/unit/files/' . $fixture;
	}

	/**
	 * Mock attachment post and mime lookups.
	 *
	 * @param int         $id   Attachment ID.
	 * @param string      $mime Mime type.
	 * @param string|null $path Attached file path, or null to skip the file mock.
	 * @return void
	 */
	protected function mock_attachment_meta( $id, $mime = 'image/svg+xml', $path = null ) {
		$attachment            = new \stdClass();
		$attachment->post_type = 'attachment';

		\WP_Mock::userFunction(
			'get_post',
			array(
				'args'   => array( $id ),
				'return' => $attachment,
			)
		);
		\WP_Mock::userFunction(
			'get_post_mime_type',
			array(
				'args'   => array( $id ),
				'return' => $mime,
			)
		);

		if ( null === $path ) {
			return;
		}

		\WP_Mock::userFunction(
			'get_attached_file',
			array(
				'args'   => array( $id ),
				'return' => $path,
			)
		);
	}

	/**
	 * Mock a readable SVG attachment pointing at a fixture.
	 *
	 * @param string $fixture File name within tests/unit/files.
	 * @param int    $id      Attachment ID.
	 * @return void
	 */
	protected function mock_attachment( $fixture, $id = 12 ) {
		$this->mock_attachment_meta( $id, 'image/svg+xml', $this->fixture_path( $fixture ) );
	}

	/**
	 * Test that a valid SVG is returned as markup.
	 */
	public function test_get_sanitized_svg_returns_markup() {
		$this->mock_attachment( 'svgCleanOne.svg' );

		$markup = \SafeSvg\Svg_Sanitizer::from_attachment( 12 );

		$this->assertIsString( $markup );
		$this->assertStringContainsString( '<svg', $markup );
	}

	/**
	 * Test that a script is stripped from a file the plugin never sanitized.
	 */
	public function test_get_sanitized_svg_strips_script() {
		$this->mock_attachment( 'svgTestOne.svg' );

		$markup = \SafeSvg\Svg_Sanitizer::from_attachment( 12 );

		$this->assertIsString( $markup );
		$this->assertStringNotContainsString( '<script', strtolower( $markup ) );
		$this->assertStringNotContainsString( 'onload=', strtolower( $markup ) );
		$this->assertStringContainsString( '<svg', $markup );
	}

	/**
	 * Test that a non-attachment post is rejected.
	 */
	public function test_get_sanitized_svg_rejects_non_attachment() {
		$post            = new \stdClass();
		$post->post_type = 'post';

		\WP_Mock::userFunction(
			'get_post',
			array(
				'args'   => array( 12 ),
				'return' => $post,
			)
		);

		$result = \SafeSvg\Svg_Sanitizer::from_attachment( 12 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'safe_svg_invalid_attachment', $result->get_error_code() );
	}

	/**
	 * Test that a missing ID is rejected.
	 */
	public function test_get_sanitized_svg_rejects_invalid_id() {
		$result = \SafeSvg\Svg_Sanitizer::from_attachment( 0 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'safe_svg_invalid_attachment', $result->get_error_code() );
	}

	/**
	 * Test that a non-SVG attachment is rejected.
	 */
	public function test_get_sanitized_svg_rejects_wrong_mime() {
		$this->mock_attachment_meta( 12, 'image/png' );

		$result = \SafeSvg\Svg_Sanitizer::from_attachment( 12 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'safe_svg_not_svg', $result->get_error_code() );
	}

	/**
	 * Test that a missing file is rejected.
	 */
	public function test_get_sanitized_svg_rejects_missing_file() {
		$this->mock_attachment_meta( 12, 'image/svg+xml', '/tmp/safe-svg-missing.svg' );

		$result = \SafeSvg\Svg_Sanitizer::from_attachment( 12 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'safe_svg_unreadable', $result->get_error_code() );
	}

	/**
	 * Test that gzipped SVG bytes are decoded before sanitization.
	 */
	public function test_sanitize_svg_markup_decodes_gzip() {
		$plain   = file_get_contents( $this->fixture_path( 'svgCleanOne.svg' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$gzipped = gzencode( $plain );

		$clean = \SafeSvg\Svg_Sanitizer::sanitize_markup( $gzipped );

		$this->assertIsString( $clean );
		$this->assertFalse( \SafeSvg\Svg_Sanitizer::is_gzipped( $clean ) );
		$this->assertStringContainsString( '<svg', $clean );
	}

	/**
	 * Test that a disallowed tag is stripped by the shared sanitizer.
	 */
	public function test_sanitize_svg_markup_strips_disallowed_tag() {
		$dirty = '<svg xmlns="http://www.w3.org/2000/svg"><rect width="10" height="10"/><this>nope</this></svg>';
		$clean = \SafeSvg\Svg_Sanitizer::sanitize_markup( $dirty );

		$this->assertIsString( $clean );
		$this->assertStringContainsString( '<rect', $clean );
		$this->assertStringNotContainsString( '<this', $clean );
	}

	/**
	 * Test that a fresh cache entry is served without re-sanitizing the file.
	 */
	public function test_get_sanitized_svg_returns_cached_markup() {
		// Contents that could never survive sanitization, so a result other than
		// an error proves the file was not read through the sanitizer.
		$path = $this->mock_large_attachment( 'this is not xml at all' );

		$this->mock_cache_read(
			array(
				'markup'    => '<svg data-from-cache="1"></svg>',
				'mtime'     => filemtime( $path ),
				'size'      => filesize( $path ),
				'signature' => \SafeSvg\Svg_Sanitizer::cache_signature(),
			)
		);

		$this->assertSame(
			'<svg data-from-cache="1"></svg>',
			\SafeSvg\Svg_Sanitizer::from_attachment( 12 )
		);
	}

	/**
	 * Test that changing what sanitization produces invalidates the cache.
	 */
	public function test_get_sanitized_svg_ignores_cache_on_signature_change() {
		$path = $this->mock_large_attachment( '<svg xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1"/></svg>' );

		$this->mock_cache_read(
			array(
				'markup'    => '<svg data-from-cache="1"></svg>',
				'mtime'     => filemtime( $path ),
				'size'      => filesize( $path ),
				'signature' => 'built-under-different-rules',
			)
		);

		\WP_Mock::passthruFunction( 'wp_slash' );
		\WP_Mock::userFunction(
			'update_post_meta',
			array( 'times' => 1 )
		);

		$markup = \SafeSvg\Svg_Sanitizer::from_attachment( 12 );

		$this->assertStringNotContainsString( 'data-from-cache', $markup );
		$this->assertStringContainsString( '<rect', $markup );
		$this->assertConditionsMet();
	}

	/**
	 * Test that a stale mtime is not served.
	 */
	public function test_get_sanitized_svg_ignores_cache_on_stale_mtime() {
		$path = $this->mock_large_attachment( '<svg xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1"/></svg>' );

		$this->mock_cache_read(
			array(
				'markup'    => '<svg data-from-cache="1"></svg>',
				'mtime'     => filemtime( $path ) - 1,
				'size'      => filesize( $path ),
				'signature' => \SafeSvg\Svg_Sanitizer::cache_signature(),
			)
		);

		\WP_Mock::passthruFunction( 'wp_slash' );
		\WP_Mock::userFunction( 'update_post_meta' );

		$this->assertStringNotContainsString(
			'data-from-cache',
			\SafeSvg\Svg_Sanitizer::from_attachment( 12 )
		);
	}

	/**
	 * Build an allowlist holding a value JSON cannot represent.
	 *
	 * @return array
	 */
	protected function unencodable_allowlist() {
		return array( NAN );
	}

	/**
	 * Test that an allowlist entry cannot impersonate the separator between two.
	 */
	public function test_cache_signature_distinguishes_allowlist_boundaries() {
		\WP_Mock::onFilter( 'svg_allowed_tags' )->with( \enshrined\svgSanitize\data\AllowedTags::getTags() )->reply( array( 'a|b' ) );
		$joined = \SafeSvg\Svg_Sanitizer::cache_signature();

		\WP_Mock::onFilter( 'svg_allowed_tags' )->with( \enshrined\svgSanitize\data\AllowedTags::getTags() )->reply( array( 'a', 'b' ) );
		$split = \SafeSvg\Svg_Sanitizer::cache_signature();

		$this->assertNotSame( $joined, $split );
	}

	/**
	 * Test that a nested allowlist still produces a usable signature.
	 */
	public function test_cache_signature_handles_nested_allowlist() {
		\WP_Mock::onFilter( 'svg_allowed_attributes' )->with( \enshrined\svgSanitize\data\AllowedAttributes::getAttributes() )->reply(
			array( 'svg' => array( 'width', 'height' ) )
		);

		$signature = \SafeSvg\Svg_Sanitizer::cache_signature();

		$this->assertMatchesRegularExpression( '/^[0-9a-f]{32}$/', $signature );
	}

	/**
	 * Test that an unencodable allowlist disables the cache rather than hashing alike.
	 */
	public function test_cache_signature_is_empty_when_parts_cannot_be_encoded() {
		\WP_Mock::onFilter( 'svg_allowed_attributes' )->with( \enshrined\svgSanitize\data\AllowedAttributes::getAttributes() )->reply( $this->unencodable_allowlist() );

		$this->assertSame( '', \SafeSvg\Svg_Sanitizer::cache_signature() );
	}

	/**
	 * Test that a cache entry is never served when the signature cannot be built.
	 */
	public function test_get_sanitized_svg_ignores_cache_without_a_signature() {
		$this->mock_large_attachment( '<svg xmlns="http://www.w3.org/2000/svg"><rect width="1" height="1"/></svg>' );

		\WP_Mock::onFilter( 'svg_allowed_attributes' )->with( \enshrined\svgSanitize\data\AllowedAttributes::getAttributes() )->reply( $this->unencodable_allowlist() );
		\WP_Mock::userFunction( 'get_post_meta', array( 'times' => 0 ) );
		\WP_Mock::userFunction( 'update_post_meta', array( 'times' => 0 ) );

		$this->assertStringNotContainsString(
			'data-from-cache',
			\SafeSvg\Svg_Sanitizer::from_attachment( 12 )
		);
	}

	/**
	 * Test that an ordinary icon never touches the meta table.
	 */
	public function test_get_sanitized_svg_skips_cache_for_small_files() {
		$this->mock_attachment( 'svgCleanOne.svg' );

		\WP_Mock::userFunction( 'get_post_meta', array( 'times' => 0 ) );
		\WP_Mock::userFunction( 'update_post_meta', array( 'times' => 0 ) );

		$markup = \SafeSvg\Svg_Sanitizer::from_attachment( 12 );

		$this->assertStringContainsString( '<svg', $markup );
		$this->assertConditionsMet();
	}
}
