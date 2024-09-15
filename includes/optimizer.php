<?php
/**
 * Safe SVG Optimizer.
 *
 * @package safe-svg
 */

namespace SafeSVG;

use enshrined\svgSanitize\Sanitizer;

if ( ! class_exists( '\SafeSVG\Optimizer' ) ) {

	/**
	 * Class \SafeSVG\Optimizer
	 */
	class Optimizer {

		/**
		 * The name of the nonce to send with the AJAX call.
		 *
		 * @var string
		 */
		private $nonce_name = 'safe-svg-optimizer';

		/**
		 * The class constructor.
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'init' ] );
		}

		/**
		 * Initialize actions.
		 *
		 * @return void
		 */
		public function init() {
			if ( true !== $this->is_enabled() ) {
				return;
			}

			add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
			add_action( 'wp_ajax_safe_svg_optimize', [ $this, 'optimize' ] );
		}

		/**
		 * Checks if the Optimizer is enabled.
		 *
		 * @return bool
		 */
		public function is_enabled(): bool {
			$has_svg_allowed_tags       = has_filter( 'svg_allowed_tags' );
			$has_svg_allowed_attributes = has_filter( 'svg_allowed_attributes' );

			/**
			 * If a dev has added allowed tags or attributes, we should not
			 * optimize the SVGs, because the optimizer will not respect their exclusions.
			 */
			if ( $has_svg_allowed_tags || $has_svg_allowed_attributes ) {
				return false;
			}

			/**
			 * Filter to enable the optimizer.
			 *
			 * Note: this feature is disabled by default.
			 *
			 * @since 2.2.0
			 * @hook safe_svg_optimizer_enabled
			 *
			 * @param bool $enabled Whether the optimizer is enabled.
			 * @return bool
			 */
			return apply_filters( 'safe_svg_optimizer_enabled', false );
		}

		/**
		 * The SVGO parameters.
		 *
		 * @return mixed|null
		 */
		public function svgo_params() {
			/**
			 * Filter the params we pass to SVGO.
			 *
			 * @since 2.2.0
			 * @hook safe_svg_svgo_params
			 *
			 * @param array $params The params we pass to SVGO.
			 * @return array
			 */
			return apply_filters(
				'safe_svg_svgo_params',
				[
					'multipass' => true,
				]
			);
		}

		/**
		 * Enqueue the necessary scripts.
		 *
		 * @param string $hook The current admin page.
		 *
		 * @return void
		 */
		public function enqueues( $hook ) {
			$allowed_hooks = [
				'options-media.php',
				'post.php',
				'post-new.php',
				'upload.php',
				'media-new.php',
			];

			if ( ! in_array( $hook, $allowed_hooks, true ) ) {
				return;
			}

			wp_enqueue_script(
				'safe-svg-admin-scripts',
				SAFE_SVG_PLUGIN_URL . 'dist/safe-svg-admin.js',
				[ 'wp-data', 'wp-editor', 'utils' ],
				SAFE_SVG_VERSION,
				true
			);

			$params = wp_json_encode(
				[
					'ajaxUrl'    => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
					'svgoParams' => $this->svgo_params(),
					'nonce'      => wp_create_nonce( $this->nonce_name ),
					'context'    => $hook,
				]
			);

			wp_add_inline_script(
				'safe-svg-admin-scripts',
				sprintf(
					'var safeSvgParams = %s;',
					$params
				),
				'before'
			);
		}

		/**
		 * Optimize the SVG file.
		 *
		 * @return void
		 */
		public function optimize() {
			$svg_url       = filter_input( INPUT_GET, 'svg_url', FILTER_SANITIZE_URL );
			$svg_id        = filter_input( INPUT_GET, 'svg_id', FILTER_SANITIZE_NUMBER_INT );
			$attachment_id = ! empty( $svg_id ) ? $svg_id : attachment_url_to_postid( $svg_url );

			if (
				empty( $_GET['optimized_svg'] ) ||
				empty( $attachment_id ) ||
				! current_user_can( 'edit_post', $attachment_id )
			) {
				return;
			}

			check_ajax_referer( $this->nonce_name, 'svg_nonce' );

			$svg_path = get_attached_file( $attachment_id );
			if ( empty( $svg_path ) ) {
				return;
			}

			$maybe_dirty = stripcslashes( $_GET['optimized_svg'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$sanitizer   = new Sanitizer();
			$sanitizer->minify( true );
			$sanitized = $sanitizer->sanitize( $maybe_dirty );

			if ( empty( $sanitized ) ) {
				return;
			}

			file_put_contents( $svg_path, $sanitized ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents

			wp_die();
		}

	}

}
