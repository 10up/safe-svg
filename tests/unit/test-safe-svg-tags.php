<?php
/**
 * Test safe_svg_tags class
 *
 * @package safe-svg
 */

use \WP_Mock\Tools\TestCase;

/**
 * SafeSvgTagsTest class tests the safe_svg_tags class and functions.
 */
class SafeSvgTagsTest extends TestCase {
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
	 * Test get tags and filter on it.
	 *
	 * @return void
	 */
	public function test_get_tags() {
		$svg_tags = SafeSvg\SafeSvgTags\safe_svg_tags::getTags();
		$this->assertIsArray( $svg_tags );

		$filtered_svg_tags = array_merge( $svg_tags, array( 'customTag' ) );
		\WP_Mock::onFilter( 'svg_allowed_tags' )
			->with( $svg_tags )
			->reply( $filtered_svg_tags );

		$svg_tags = SafeSvg\SafeSvgTags\safe_svg_tags::getTags();
		$this->assertContains( 'customTag', $svg_tags );
		$this->assertSame( $svg_tags, $filtered_svg_tags );
	}
}
