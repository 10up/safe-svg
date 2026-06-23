<?php
/**
 * Test plugin version is consistent in all locations.
 *
 * @package safe-svg
 */

use PHPUnit\Framework\TestCase;

/**
 * The VersionTests class tests the plugin version is consistent in all locations.
 */
class PluginVersionTests extends TestCase {

	const PLUGIN_ROOT_DIR = __DIR__ . '/../..';

	/**
	 * Helper function to get the plugin version from the plugin file constant.
	 *
	 * This is necessary to avoid hardcoding the version in the test, which would require updates to the test for every version bump and increase the risk of human error.
	 *
	 * @return string The plugin version as defined in the plugin file constant.
	 * @throws RuntimeException If the plugin version cannot be determined.
	 */
	public static function get_plugin_version_constant() {
		$plugin_file_name = 'safe-svg.php';

		/*
		 * Determine the value of `SAFE_SVG_VERSION` constant.
		 *
		 * This uses regex rather than including the plugin file to avoid side effects
		 * caused by the use of mocks for the test suite.
		 */
		$plugin_file_contents = file_get_contents( self::PLUGIN_ROOT_DIR . "/{$plugin_file_name}" );
		if ( false === $plugin_file_contents ) {
			// phpcs:ignore
			throw new RuntimeException( 'Unable to read plugin file: ' . self::PLUGIN_ROOT_DIR . "/{$plugin_file_name}" );
		}

		if ( preg_match( '/define\(\s*[\'"]SAFE_SVG_VERSION[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\)/', $plugin_file_contents, $matches ) ) {
			return $matches[1];
		}

		// phpcs:ignore
		throw new RuntimeException( 'Unable to determine plugin version from file: ' . self::PLUGIN_ROOT_DIR . "/{$plugin_file_name}" );
	}

	/**
	 * Helper function to read the file headers.
	 *
	 * Based on the get_file_data function in wp-includes/functions.php.
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_file_data/
	 *
	 * @param string               $file            The file to read the headers from.
	 * @param array<string,string> $default_headers List of headers, in the format array( 'HeaderKey' => 'Header Name' ).
	 * @param string               $context         Unused. Included for consistency with the WP function signature.
	 * @return array<string,string> Array of file header values keyed by header name.
	 */
	public static function get_file_data( string $file, array $default_headers, $context = '' ): array {
		// Pull only the first 8 KB of the file in.
		$file_data = file_get_contents( $file, false, null, 0, 8 * 1024 );

		if ( false === $file_data ) {
			$file_data = '';
		}

		// Make sure we catch CR-only line endings.
		$file_data = str_replace( "\r", "\n", $file_data );

		$all_headers = $default_headers;

		foreach ( $all_headers as $field => $regex ) {
			if ( preg_match( '/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] ) {
				$all_headers[ $field ] = trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $match[1] ) );
			} else {
				$all_headers[ $field ] = '';
			}
		}

		return $all_headers;
	}

	/**
	 * Test Stable Tag in readme.txt matches plugin version.
	 */
	public function test_stable_tag_matches_plugin_version() {
		$readme_file = self::PLUGIN_ROOT_DIR . '/readme.txt';
		$readme_data = self::get_file_data(
			$readme_file,
			array(
				'Stable tag' => 'Stable tag',
			)
		);

		$this->assertSame( self::get_plugin_version_constant(), $readme_data['Stable tag'], 'The Stable tag in readme.txt does not match the plugin version.' );
	}

	/**
	 * Test version header in the plugin file matches plugin version constant.
	 */
	public function test_plugin_version_header() {
		// Get the plugin headers.
		// Plugin name.
		$plugin_file_name = 'safe-svg.php';

		$plugin_file_data = self::get_file_data(
			self::PLUGIN_ROOT_DIR . "/{$plugin_file_name}",
			array(
				'Version' => 'Version',
			)
		);

		$this->assertSame( self::get_plugin_version_constant(), $plugin_file_data['Version'], 'The Version header in the plugin file does not match the plugin version constant.' );
	}

	/**
	 * Test the plugin version in package.json matches the plugin version constant.
	 */
	public function test_package_json_version() {
		$package_file = self::PLUGIN_ROOT_DIR . '/package.json';
		if ( ! file_exists( $package_file ) ) {
			// Package file does not exist, consider this test passed.
			$this->assertTrue( true );
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- fine for the tests.
		$package_data = json_decode( file_get_contents( $package_file ), true );
		$this->assertSame( self::get_plugin_version_constant(), $package_data['version'], 'The version in package.json does not match the plugin version constant.' );
	}

	/**
	 * Test the plugin version in package-lock.json matches the plugin version constant.
	 */
	public function test_package_lock_json_version() {
		$package_lock_file = self::PLUGIN_ROOT_DIR . '/package-lock.json';
		if ( ! file_exists( $package_lock_file ) ) {
			// Package lock file does not exist, consider this test passed.
			$this->assertTrue( true );
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- fine for the tests.
		$package_lock_data = json_decode( file_get_contents( $package_lock_file ), true );
		$this->assertSame( self::get_plugin_version_constant(), $package_lock_data['version'], 'The version in package-lock.json does not match the plugin version constant.' );
		$this->assertSame( self::get_plugin_version_constant(), $package_lock_data['packages']['']['version'], "The packages['']['version'] in package-lock.json packages does not match the plugin version constant." );
	}

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
