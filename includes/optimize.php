<?php
/**
 * Safe SVG Optimizer.
 *
 * @package safe-svg
 */

namespace SafeSVG;

if ( ! class_exists( '\SafeSVG\Optimize' ) ) {
	
	/**
	 * Class \SafeSVG\Optimize
	 */
	class Optimize {
		
		public function __construct() {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueues' ] );
		}
		
		public function enqueues( $hook ) {
			if ( 'options-media.php' !== $hook && 'post.php' !== $hook && 'upload.php' !== $hook ) {
				return;
			}
			wp_enqueue_script( 'safe-svg-scripts', plugins_url( '/dist/js/admin.js', dirname( __FILE__ ) ), [], SAFE_SVG_VERSION, true );
		}
	}
}