<?php
/**
 * Test safe_svg_attributes class
 *
 * @package safe-svg
 */

use \WP_Mock\Tools\TestCase;

/**
 * SafeSvgAttributesTest class tests the safe_svg_attributes class and functions.
 */
class SafeSvgAttributesTest extends TestCase {
	/**
	 * Set up our mocked WP functions. Rather than setting up a database we can mock the returns of core WordPress functions.
	 *
	 * @return void
	 */
	public function setUp(): void {
		\WP_Mock::setUp();
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
	 * Test get attributes and filter on it.
	 *
	 * @return void
	 */
	public function test_get_attributes() {
		$svg_attributes = SafeSvg\SafeSvgAttr\safe_svg_attributes::getAttributes();
		$this->assertIsArray( $svg_attributes );

		$filtered_svg_attributes = array_merge( $svg_attributes, array( 'customAttribute' ) );
		\WP_Mock::onFilter( 'svg_allowed_attributes' )
			->with( $svg_attributes )
			->reply( $filtered_svg_attributes );

		$svg_attributes = SafeSvg\SafeSvgAttr\safe_svg_attributes::getAttributes();
		$this->assertContains( 'customAttribute', $svg_attributes );
		$this->assertSame( $svg_attributes, $filtered_svg_attributes );
	}
}
