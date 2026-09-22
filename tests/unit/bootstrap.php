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

if ( ! function_exists( 'wp_json_encode' ) ) {
	/**
	 * Stand-in for wp_json_encode(). Defined outright rather than mocked so it
	 * survives WP_Mock's per-test reset.
	 *
	 * @param mixed $data    Value to encode.
	 * @param int   $options json_encode() options.
	 * @param int   $depth   Maximum depth.
	 * @return string|false Encoded value, or false on failure.
	 */
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	/**
	 * Minimal WP_Error stand-in for unit tests.
	 */
	class WP_Error {
		/**
		 * Messages keyed by error code.
		 *
		 * @var array
		 */
		public $errors = array();

		/**
		 * Arbitrary data keyed by error code.
		 *
		 * @var array
		 */
		public $error_data = array();

		/**
		 * Constructor.
		 *
		 * @param string $code    Error code.
		 * @param string $message Error message.
		 * @param mixed  $data    Optional error data.
		 */
		public function __construct( $code = '', $message = '', $data = '' ) {
			if ( empty( $code ) ) {
				return;
			}

			$this->errors[ $code ][] = $message;

			if ( ! empty( $data ) ) {
				$this->error_data[ $code ] = $data;
			}
		}

		/**
		 * First registered error code.
		 *
		 * @return string
		 */
		public function get_error_code() {
			$codes = array_keys( $this->errors );
			return $codes ? $codes[0] : '';
		}

		/**
		 * Message for a code, or the first message.
		 *
		 * @param string $code Optional error code.
		 * @return string
		 */
		public function get_error_message( $code = '' ) {
			if ( empty( $code ) ) {
				$code = $this->get_error_code();
			}

			return isset( $this->errors[ $code ][0] ) ? $this->errors[ $code ][0] : '';
		}

		/**
		 * Data for a code, or the first error's data.
		 *
		 * @param string $code Optional error code.
		 * @return mixed
		 */
		public function get_error_data( $code = '' ) {
			if ( empty( $code ) ) {
				$code = $this->get_error_code();
			}

			return isset( $this->error_data[ $code ] ) ? $this->error_data[ $code ] : null;
		}
	}
}

if ( ! class_exists( 'WP_REST_Block_Renderer_Controller' ) ) {
	/**
	 * Minimal stand-in for core's block renderer controller.
	 *
	 * The guard in the block identifies core's block renderer by its controller
	 * class, so the class has to exist for that check to be exercised.
	 */
	class WP_REST_Block_Renderer_Controller {} // phpcs:ignore Generic.Classes.OpeningBraceSameLine.ContentAfterBrace
}

require TEST_PLUGIN_DIR . '/safe-svg.php';
