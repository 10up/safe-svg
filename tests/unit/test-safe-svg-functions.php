<?php
/**
 * Test the public sanitize helper.
 *
 * @package safe-svg
 */

use WP_Mock\Tools\TestCase;

/**
 * SafeSvgFunctionsTest tests safe_svg_sanitize_markup().
 */
class SafeSvgFunctionsTest extends TestCase {
	/**
	 * Set up WP Mock.
	 *
	 * @return void
	 */
	public function setUp(): void {
		\WP_Mock::setUp();
		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( 'safe_svg_large_svg' ),
				'return' => 0,
			)
		);
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
	 * Test that the helper is defined and callable.
	 */
	public function test_helper_function_exists() {
		$this->assertTrue( function_exists( 'safe_svg_sanitize_markup' ) );
	}

	/**
	 * Test that a clean SVG is returned as sanitized markup.
	 */
	public function test_sanitizes_a_clean_svg() {
		$dirty = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><rect width="10" height="10"/></svg>';

		$clean = safe_svg_sanitize_markup( $dirty );

		$this->assertIsString( $clean );
		$this->assertStringContainsString( '<svg', $clean );
		$this->assertStringContainsString( '<rect', $clean );
	}

	/**
	 * Test that scripts and event handlers are stripped.
	 */
	public function test_strips_scripts_and_handlers() {
		$dirty = '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><script>alert(1)</script><rect width="10" height="10"/></svg>';

		$clean = safe_svg_sanitize_markup( $dirty );

		$this->assertIsString( $clean );
		$this->assertStringNotContainsString( '<script', strtolower( $clean ) );
		$this->assertStringNotContainsString( 'onload', strtolower( $clean ) );
		$this->assertStringContainsString( '<rect', $clean );
	}

	/**
	 * Test that gzipped input is decoded and returned as plain markup.
	 */
	public function test_decodes_gzipped_markup() {
		$plain   = '<svg xmlns="http://www.w3.org/2000/svg"><rect width="10" height="10"/></svg>';
		$gzipped = gzencode( $plain );

		$clean = safe_svg_sanitize_markup( $gzipped );

		$this->assertIsString( $clean );
		$this->assertFalse( \SafeSvg\Svg_Sanitizer::is_gzipped( $clean ) );
		$this->assertStringContainsString( '<svg', $clean );
	}

	/**
	 * Test that empty and non-string input returns false.
	 *
	 * @dataProvider provider_invalid_input
	 *
	 * @param mixed $input Input passed to the helper.
	 */
	public function test_returns_false_for_invalid_input( $input ) {
		$this->assertFalse( safe_svg_sanitize_markup( $input ) );
	}

	/**
	 * Provide input the helper cannot sanitize.
	 *
	 * @return array[]
	 */
	public function provider_invalid_input() {
		return array(
			'empty string' => array( '' ),
			'null'         => array( null ),
			'array'        => array( array( '<svg></svg>' ) ),
		);
	}

	/**
	 * Test that XML without an <svg> root returns false instead of fataling.
	 */
	public function test_returns_false_for_xml_without_svg_root() {
		$this->assertFalse( safe_svg_sanitize_markup( '<?xml version="1.0"?><note><to>Tove</to></note>' ) );
	}

	/**
	 * Test that an SVG wrapped in other HTML returns false.
	 *
	 * The sanitizer only accepts a single SVG document, so callers have to pass
	 * the <svg> element rather than a larger chunk of HTML it sits inside.
	 */
	public function test_returns_false_for_svg_wrapped_in_html() {
		$mixed = '<p>Hi</p><svg xmlns="http://www.w3.org/2000/svg"><rect width="10" height="10"/></svg>';

		$this->assertFalse( safe_svg_sanitize_markup( $mixed ) );
	}
}
