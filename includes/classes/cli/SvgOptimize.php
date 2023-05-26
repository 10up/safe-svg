<?php
/**
 * Implements optimize command.
 *
 * @package SafeSvg
 */

namespace SafeSvg\CLI;

use \WP_CLI;

/**
 * Optimize command class.
 */
class SvgOptimize {

	public function __invoke( $args, $assoc_args ) {
		WP_CLI::log( 'test' );
	}

}