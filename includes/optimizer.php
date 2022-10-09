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
			$params = $this->svgo_params();
			
			return ( ! empty( $params ) && is_array( $params ) );
		}
		
		/**
		 * The SVGO parameters. Developers can use this filter to pass additional parameters or completely disable the optimizer by passing:
		 * add_filter( 'safe_svg_svgo_params', '__return_false' );
		 *
		 * @return mixed|null
		 */
		public function svgo_params() {
			return apply_filters( 'safe_svg_svgo_params', [
				'multipass' => true,
			] );
		}
		
		/**
		 * Enqueue the necessary scripts.
		 *
		 * @param $hook
		 *
		 * @return void
		 */
		public function enqueues( $hook ) {
			if ( 'options-media.php' !== $hook && 'post.php' !== $hook && 'upload.php' !== $hook ) {
				return;
			}
			
			wp_enqueue_script( 'safe-svg-scripts', plugins_url( '/dist/js/admin.js', dirname( __FILE__ ) ), [], SAFE_SVG_VERSION, true );
			
			$params = wp_json_encode(
				[
					'ajaxUrl'    => esc_url( admin_url( 'admin-ajax.php' ) ),
					'svgoParams' => wp_json_encode( $this->svgo_params() ),
				]
			);
			
			wp_add_inline_script(
				'safe-svg-scripts',
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
			$svg_url  = filter_input( INPUT_GET, 'svg_url', FILTER_SANITIZE_URL );
			$svg_path = $this->url_to_path( $svg_url );
			if ( empty( $svg_path ) ) {
				return;
			}
			$maybe_dirty = $_GET['optimized_svg'];
			
			$sanitizer = new Sanitizer();
			$sanitizer->minify( true );
			$sanitized = $sanitizer->sanitize(
				sprintf(
					'<?xml version="1.0" encoding="UTF-8"?>%s', // Add the XML tag, or else the sanitizer will fail.
					stripcslashes( $maybe_dirty )
				)
			);
			
			$optimized = trim(
				stripcslashes(
					preg_replace( '/<\?xml(.*?)\?>/', '', $sanitized ) // Remove the XML tag.
				)
			);
			
			if ( empty( $optimized ) ) {
				return;
			}
			file_put_contents( $svg_path, $optimized );
			wp_die();
		}
		
		/**
		 * A helper method to get the file path from its URL.
		 *
		 * @param string $url The URL string.
		 *
		 * @return false|string
		 */
		protected function url_to_path( string $url = '' ) {
			if ( empty( $url ) ) {
				return '';
			}
			$parsed_url = parse_url( $url );
			if ( empty( $parsed_url['path'] ) ) {
				return false;
			}
			$file = ABSPATH . ltrim( $parsed_url['path'], '/' );
			if ( file_exists( $file ) ) {
				return $file;
			}
			
			return false;
		}
	}
}