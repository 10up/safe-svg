<?php
/**
 * Test plugin version policy.
 *
 * @package safe-svg
 */

use PHPUnit\Framework\TestCase;

/**
 * The VersionTests class tests plugin version policy that is not handled by Consistent Versions.
 */
class PluginVersionTests extends TestCase {

	const PLUGIN_ROOT_DIR = __DIR__ . '/../..';

	/**
	 * Ensure that composer.json does not have a version key.
	 *
	 * Per the docs:
	 *
	 * > In most cases this is not required and should be omitted (see below).
	 * >
	 * > Packagist uses VCS repositories, so the statement above is very much true for Packagist
	 * > as well. Specifying the version yourself will most likely end up creating problems at
	 * > some point due to human error.
	 */
	public function test_composer_version_is_not_present() {
		$composer_file = self::PLUGIN_ROOT_DIR . '/composer.json';
		if ( ! file_exists( $composer_file ) ) {
			// Composer file does not exist, consider this test passed.
			$this->assertTrue( true );
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- fine for the tests.
		$composer_data = json_decode( file_get_contents( $composer_file ), true );
		$this->assertArrayNotHasKey( 'version', $composer_data, 'The version key should not be present in composer.json.' );
	}
}
