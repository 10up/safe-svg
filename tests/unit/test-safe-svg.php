<?php 
use \WP_Mock\Tools\TestCase;

/**
 * SafeSvgTest class tests the safe_svg class and functions.
 */
class SafeSvgTest extends TestCase {
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
	 * Sample Test
	 */
	public function test_sample() {
        $this->assertTrue( true );
	}
}