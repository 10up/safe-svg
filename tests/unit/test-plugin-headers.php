<?php
/**
 * Test plugin headers are correct for both readme and main plugin file.
 *
 * @package safe-svg
 */

use PHPUnit\Framework\TestCase;

/**
 * The PluginHeadersTests class tests the plugin headers are in sync and located in the correct files.
 */
class PluginHeadersTests extends TestCase {

	const OPTIONAL  = 0;
	const REQUIRED  = 1;
	const FORBIDDEN = 2;

	const WP_ORG_ASSETS_DIR = __DIR__ . '/../../.wordpress-org';
	const PLUGIN_ROOT_DIR   = __DIR__ . '/../..';

	/**
	 * Readme headers specification
	 *
	 * @var array<string,int> Headers defined in the readme spec. Key: Header; Value: OPTIONAL, REQUIRED, FORBIDDEN.
	 */
	public static $readme_headers = array(
		'Contributors'      => self::REQUIRED,
		'Tags'              => self::OPTIONAL,
		'Donate link'       => self::OPTIONAL,
		'Tested up to'      => self::REQUIRED,
		'Stable tag'        => self::REQUIRED,
		'License'           => self::REQUIRED,
		'License URI'       => self::OPTIONAL,
		'Requires at least' => self::REQUIRED, // Opinionated: Allows out of release cycle bumps.
		'Requires PHP'      => self::REQUIRED, // Opinionated: Allows out of release cycle bumps.

		// Plugin file headers that do not belong in the readme.
		'Plugin Name'       => self::FORBIDDEN,
		'Plugin URI'        => self::FORBIDDEN,
		'Description'       => self::FORBIDDEN,
		'Version'           => self::FORBIDDEN,
		'Author'            => self::FORBIDDEN,
		'Author URI'        => self::FORBIDDEN,
		'Text Domain'       => self::FORBIDDEN,
		'Domain Path'       => self::FORBIDDEN,
		'Network'           => self::FORBIDDEN,
		'Update URI'        => self::FORBIDDEN,
		'Requires Plugins'  => self::FORBIDDEN,
	);

	/**
	 * Plugin headers specification
	 *
	 * @var array<string,int> Headers defined in the plugin spec. Key: Header; Value: OPTIONAL, REQUIRED, FORBIDDEN.
	 */
	public static $plugin_headers = array(
		'Plugin Name'       => self::REQUIRED,
		'Plugin URI'        => self::OPTIONAL,
		'Description'       => self::REQUIRED,
		'Version'           => self::REQUIRED,
		'Author'            => self::REQUIRED,
		'Author URI'        => self::OPTIONAL,
		'License'           => self::REQUIRED,
		'License URI'       => self::OPTIONAL,
		'Text Domain'       => self::OPTIONAL,
		'Domain Path'       => self::OPTIONAL,
		'Network'           => self::OPTIONAL,
		'Update URI'        => self::OPTIONAL,
		'Requires Plugins'  => self::OPTIONAL,

		// Readme file headers that do not belong in the plugin file.
		'Contributors'      => self::FORBIDDEN,
		'Tags'              => self::FORBIDDEN,
		'Donate link'       => self::FORBIDDEN,
		'Stable tag'        => self::FORBIDDEN,
		'Requires PHP'      => self::FORBIDDEN, // Opinionated: Allows out of release cycle bumps.
		'Requires at least' => self::FORBIDDEN, // Opinionated: Allows out of release cycle bumps.

		/*
		 * Opinionated: Allowed by the spec.
		 *
		 * The WordPress plugin directory will use the plugin file headers if
		 * it exists, and fall back to the readme file if it does not.
		 *
		 * However, the 10up Github Action for deploying updates to the
		 * directory will require a version bump if the plugin file is
		 * modified, so it's best to keep tested up to in the readme file.
		 *
		 * WordPress Core doesn't use the header, it pulls the data in
		 * from the plugin API.
		 */
		'Tested up to'      => self::FORBIDDEN,
	);

	/**
	 * Deprecated headers mapping.
	 *
	 * Opinionated: These headers are parsed correctly by the WordPress.org
	 * plugin repository but go against the recommended headers in the
	 * documentation.
	 *
	 * @var array<string,string> Mapping of deprecated header to current header.
	 */
	public static $deprecated_headers = array(
		'Tested'   => 'Tested up to',
		'Requires' => 'Requires at least',
	);

	/**
	 * Headers defined in the plugin's readme.txt file.
	 *
	 * @var string[] Headers defined in the readme spec Header => value.
	 */
	public static $defined_readme_headers = array();

	/**
	 * Headers defined in the plugin file.
	 *
	 * @var string[] Headers defined in the plugin spec Header => value.
	 */
	public static $defined_plugin_headers = array();

	/**
	 * Plugin file names.
	 *
	 * @var string[] The readme and plugin file names.
	 */
	public static $file_names = array();

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
	 * Set up shared fixtures.
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		// Get the file names.
		self::$file_names['readme'] = self::PLUGIN_ROOT_DIR . '/readme.txt';
		self::$file_names['plugin'] = self::PLUGIN_ROOT_DIR . '/safe-svg.php';

		// Get the readme headers.
		$readme_file_data = array();
		foreach ( self::$readme_headers as $header => $required ) {
			$readme_file_data[ $header ] = $header;
		}
		self::$defined_readme_headers = self::get_file_data(
			self::$file_names['readme'],
			$readme_file_data
		);
		self::$defined_readme_headers = array_filter( self::$defined_readme_headers );

		// Get the plugin headers.
		$plugin_file_data = array();
		foreach ( self::$plugin_headers as $header => $required ) {
			$plugin_file_data[ $header ] = $header;
		}

		self::$defined_plugin_headers = self::get_file_data(
			self::$file_names['plugin'],
			$plugin_file_data
		);
		self::$defined_plugin_headers = array_filter( self::$defined_plugin_headers );
	}

	/**
	 * Test that the readme file has all required headers.
	 *
	 * @dataProvider data_required_readme_headers
	 *
	 * @param string $header Header to test.
	 */
	public function test_required_readme_headers( $header ) {
		$this->assertArrayHasKey( $header, self::$defined_readme_headers, "The readme file header '{$header}' is missing." );
		$this->assertNotEmpty( self::$defined_readme_headers[ $header ], "The readme file header '{$header}' is empty." );
	}

	/**
	 * Data provider for test_required_readme_headers.
	 *
	 * @return array[] Data provider.
	 */
	public static function data_required_readme_headers() {
		$required_headers = array_filter(
			self::$readme_headers,
			function ( $status ) {
				return self::REQUIRED === $status;
			}
		);
		$headers          = array();
		foreach ( $required_headers as $header => $required ) {
			$headers[ $header ] = array( $header );
		}
		return $headers;
	}

	/**
	 * Test that the readme file does not have any forbidden headers.
	 *
	 * @dataProvider data_forbidden_readme_headers
	 *
	 * @param string $header Header to test.
	 */
	public function test_forbidden_readme_headers( $header ) {
		$this->assertArrayNotHasKey( $header, self::$defined_readme_headers, "The readme file header '{$header}' is forbidden." );
	}

	/**
	 * Data provider for test_forbidden_readme_headers.
	 *
	 * @return array[] Data provider.
	 */
	public static function data_forbidden_readme_headers() {
		$forbidden_headers = array_filter(
			self::$readme_headers,
			function ( $status ) {
				return self::FORBIDDEN === $status;
			}
		);
		$headers           = array();
		foreach ( $forbidden_headers as $header => $required ) {
			$headers[ $header ] = array( $header );
		}
		return $headers;
	}

	/**
	 * Test that the plugin file has all required headers.
	 *
	 * @dataProvider data_required_plugin_headers
	 *
	 * @param string $header Header to test.
	 */
	public function test_required_plugin_headers( $header ) {
		$this->assertArrayHasKey( $header, self::$defined_plugin_headers, "The plugin file header '{$header}' is missing." );
		$this->assertNotEmpty( self::$defined_plugin_headers[ $header ], "The plugin file header '{$header}' is empty." );
	}

	/**
	 * Data provider for test_required_plugin_headers.
	 *
	 * @return array[] Data provider.
	 */
	public static function data_required_plugin_headers() {
		$required_headers = array_filter(
			self::$plugin_headers,
			function ( $status ) {
				return self::REQUIRED === $status;
			}
		);
		$headers          = array();
		foreach ( $required_headers as $header => $required ) {
			$headers[ $header ] = array( $header );
		}
		return $headers;
	}

	/**
	 * Test that the plugin file does not have any forbidden headers.
	 *
	 * @dataProvider data_forbidden_plugin_headers
	 *
	 * @param string $header Header to test.
	 */
	public function test_forbidden_plugin_headers( $header ) {
		$this->assertArrayNotHasKey( $header, self::$defined_plugin_headers, "The plugin file header '{$header}' is forbidden." );
	}

	/**
	 * Data provider for test_forbidden_plugin_headers.
	 *
	 * @return array[] Data provider.
	 */
	public static function data_forbidden_plugin_headers() {
		$forbidden_headers = array_filter(
			self::$plugin_headers,
			function ( $status ) {
				return self::FORBIDDEN === $status;
			}
		);
		$headers           = array();
		foreach ( $forbidden_headers as $header => $required ) {
			$headers[ $header ] = array( $header );
		}
		return $headers;
	}

	/**
	 * Test that headers defined in both the readme and plugin file match.
	 *
	 * @dataProvider data_common_headers_match
	 *
	 * @param string      $plugin_header_name Plugin file header name to test.
	 * @param string|null $readme_header_name Readme file header name to test. If null, the plugin header name will be used.
	 */
	public function test_common_headers_match( $plugin_header_name, $readme_header_name = null ) {
		$readme_header_name = $readme_header_name ?? $plugin_header_name;
		if ( empty( self::$defined_plugin_headers[ $plugin_header_name ] ) || empty( self::$defined_readme_headers[ $readme_header_name ] ) ) {
			// The header is not common to both files so the test passes.
			$this->assertTrue( true );
			return;
		}

		$plugin_header = self::$defined_plugin_headers[ $plugin_header_name ];
		$readme_header = self::$defined_readme_headers[ $readme_header_name ];

		$message = "The header '{$plugin_header_name}' does not match between the readme and plugin file.";
		if ( $plugin_header_name !== $readme_header_name ) {
			$message = "The plugin header '{$plugin_header_name}' does not match the readme header '{$readme_header_name}'.";
		}

		$this->assertSame( $plugin_header, $readme_header, $message );
	}

	/**
	 * Data provider for test_common_headers_match.
	 *
	 * @return array[] Data provider.
	 */
	public static function data_common_headers_match() {
		// Can't use the defined headers as they are not defined until after this is called.
		$common_headers = array_intersect_key(
			self::$readme_headers,
			self::$plugin_headers
		);

		$headers = array();
		// Always test the version matches the stable tag.
		$headers['Stable tag matches version'] = array( 'Version', 'Stable tag' );

		foreach ( $common_headers as $header => $value ) {
			$headers[ $header ] = array( $header );
		}
		return $headers;
	}

	/**
	 * Test that no deprecated headers are used.
	 *
	 * @dataProvider data_no_deprecated_headers
	 *
	 * @param string $file              File to test, either 'readme' or 'plugin'.
	 * @param string $deprecated_header Deprecated header to test.
	 * @param string $correct_header    Correct header to use.
	 */
	public function test_no_deprecated_headers( $file, $deprecated_header, $correct_header ) {
		$file_name    = 'readme' === $file ? self::$file_names['readme'] : self::$file_names['plugin'];
		$file_data    = array(
			$deprecated_header => $deprecated_header,
		);
		$defined_data = self::get_file_data(
			$file_name,
			$file_data
		);
		$defined_data = array_filter( $defined_data );
		$this->assertArrayNotHasKey( $deprecated_header, $defined_data, "The {$file} file header '{$deprecated_header}' is deprecated. Use '{$correct_header}' instead." );
	}

	/**
	 * Data provider for test_no_deprecated_headers.
	 *
	 * @return array[] Data provider.
	 */
	public static function data_no_deprecated_headers() {
		$files = array( 'readme', 'plugin' );
		foreach ( $files as $file ) {
			foreach ( self::$deprecated_headers as $deprecated_header => $correct_header ) {
				$test_name           = "{$file} - {$deprecated_header}";
				$tests[ $test_name ] = array( $file, $deprecated_header, $correct_header );
			}
		}

		return $tests;
	}

	/**
	 * Test minimum PHP requirement matches across composer.json, readme.txt,
	 * and minimum_php_requirement() in the plugin file.
	 */
	public function test_minimum_php_requirement_matches_across_files() {
		$composer_file = self::PLUGIN_ROOT_DIR . '/composer.json';
		$plugin_file   = self::$file_names['plugin'];

		$composer_contents = file_get_contents( $composer_file );
		$this->assertNotFalse( $composer_contents, 'Unable to read composer.json.' );

		$composer_data = json_decode( $composer_contents, true );
		$this->assertIsArray( $composer_data, 'composer.json is not valid JSON.' );
		$this->assertArrayHasKey( 'require', $composer_data, 'composer.json is missing the require section.' );
		$this->assertArrayHasKey( 'php', $composer_data['require'], 'composer.json is missing require.php.' );

		preg_match( '/\d+(?:\.\d+)+/', (string) $composer_data['require']['php'], $composer_match );
		$this->assertNotEmpty( $composer_match, 'Unable to parse PHP minimum version from composer.json require.php.' );
		$composer_min_php = $composer_match[0];

		$this->assertArrayHasKey( 'Requires PHP', self::$defined_readme_headers, "The readme.txt header 'Requires PHP' is missing." );
		$readme_min_php = self::$defined_readme_headers['Requires PHP'];

		$plugin_contents = file_get_contents( $plugin_file );
		$this->assertNotFalse( $plugin_contents, 'Unable to read plugin file.' );

		$function_pattern = '/function\s+minimum_php_requirement\s*\(\s*\)\s*\{[\s\S]*?return\s+[\"\']([^\"\']+)[\"\']\s*;/';
		preg_match( $function_pattern, $plugin_contents, $plugin_match );
		$this->assertNotEmpty( $plugin_match, 'Unable to parse minimum_php_requirement() return value from plugin file.' );
		$function_min_php = $plugin_match[1];

		$this->assertSame( $function_min_php, $readme_min_php, 'Minimum PHP version mismatch between minimum_php_requirement() and readme.txt Requires PHP.' );
		$this->assertSame( $function_min_php, $composer_min_php, 'Minimum PHP version mismatch between minimum_php_requirement() and composer.json require.php.' );
	}

	/**
	 * Test that the minimum WordPress version in readme matches the Cypress test config.
	 */
	public function test_minimum_wordpress_version_matches_cypress_config() {
		$cypress_file = self::PLUGIN_ROOT_DIR . '/.github/workflows/cypress.yml';

		$this->assertFileExists( $cypress_file, 'Cypress workflow file does not exist.' );

		$cypress_contents = file_get_contents( $cypress_file );
		$this->assertNotFalse( $cypress_contents, 'Unable to read cypress.yml.' );

		// Extract the minimum WordPress version from the "WP minimum" matrix entry in cypress.yml.
		// Looking for: - {name: 'WP minimum', version: 'WordPress/WordPress#6.6'}
		$pattern = '/\-\s*\{\s*name:\s*\'WP minimum\'\s*,\s*version:\s*\'WordPress\/WordPress#([\d.]+)\'/';
		preg_match( $pattern, $cypress_contents, $cypress_match );
		$this->assertNotEmpty( $cypress_match, 'Unable to parse minimum WordPress version from the "WP minimum" matrix entry in cypress.yml.' );
		$cypress_min_wp = $cypress_match[1];

		// Get the minimum WordPress version from the readme.
		$this->assertArrayHasKey( 'Requires at least', self::$defined_readme_headers, "The readme.txt header 'Requires at least' is missing." );
		$readme_min_wp = self::$defined_readme_headers['Requires at least'];

		$this->assertSame( $readme_min_wp, $cypress_min_wp, "Minimum WordPress version mismatch between cypress.yml ({$cypress_min_wp}) and readme.txt Requires at least ({$readme_min_wp})." );
	}

	/**
	 * Ensure that the plugin banner includes a low resolution version.
	 *
	 * Per the plugin asset guidelines, the high resolution (retina) banner can
	 * not be used alone, it must be accompanied by a low resolution version.
	 *
	 * @dataProvider data_banner_includes_low_res_version
	 *
	 * @param string $banner Hi-res banner file name to test.
	 */
	public function test_banner_includes_low_res_version( $banner ) {
		// Remove the extension from the banner file name.
		$high_res_banner_prefix = pathinfo( $banner, PATHINFO_FILENAME ) . '.';

		$low_res_banner_prefix = str_replace(
			'1544x500',
			'772x250',
			$high_res_banner_prefix
		);

		$file_list = scandir( self::WP_ORG_ASSETS_DIR );
		// Search for the low resolution banner file.
		$low_res_files = array_filter(
			$file_list,
			function ( $file ) use ( $low_res_banner_prefix ) {
				return 0 === strpos( $file, $low_res_banner_prefix );
			}
		);

		$this->assertNotEmpty(
			$low_res_files,
			"Low resolution banner file for '{$banner}' does not exist."
		);
	}

	/**
	 * Data provider for test_banner_includes_low_res_version.
	 *
	 * @return array[] Data provider.
	 */
	public static function data_banner_includes_low_res_version() {
		if ( ! is_dir( self::WP_ORG_ASSETS_DIR ) ) {
			// No assets directory, so no banners.
			return array();
		}

		$file_list = scandir( self::WP_ORG_ASSETS_DIR );

		if ( false === $file_list ) {
			// No files found, so no banners.
			return array();
		}

		// Filter out the files that do not begin with `banner-1544x500`.
		$banner_files = array_filter(
			$file_list,
			function ( $file ) {
				return 0 === strpos( $file, 'banner-1544x500' );
			}
		);

		if ( empty( $banner_files ) ) {
			// No banners found.
			return array();
		}

		// Convert each file name to a data provider entry.
		$banner_data = array();
		foreach ( $banner_files as $banner_file ) {
			$banner_data[ $banner_file ] = array( $banner_file );
		}

		return $banner_data;
	}
}
