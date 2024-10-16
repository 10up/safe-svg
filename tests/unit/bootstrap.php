<?php
/**
 * The bootstrap file for PHPUnit tests for the Safe SVG plugin.
 * Starts up WP_Mock and requires the files needed for testing.
 *
 * @package safe-svg
 */

define( 'TEST_PLUGIN_DIR', dirname( dirname( __DIR__ ) ) . '/' );

// First we need to load the composer autoloader so we can use WP Mock.
require_once TEST_PLUGIN_DIR . '/vendor/autoload.php';

// Now call the bootstrap method of WP Mock.
WP_Mock::bootstrap();

\WP_Mock::userFunction( 'plugin_dir_url' );
\WP_Mock::userFunction( 'remove_filter' );

require TEST_PLUGIN_DIR . '/safe-svg.php';
