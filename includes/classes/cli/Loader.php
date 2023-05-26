<?php
/**
 * CLI Loader
 *
 * @package SafeSvg
 */

namespace SafeSvg\CLI;

use SafeSvg\Module;
use \WP_CLI;

/**
 * CLI loading class.
 */
class Loader extends Module {

	/**
	 * Checks whether the Module should run within the current context.
	 * Only register if in CLI context.
	 *
	 * @return bool
	 */
	public function can_register() {
		return defined( 'WP_CLI' ) && WP_CLI;
	}

	/**
	 * Connects the Module with WordPress using Hooks and/or Filters.
	 *
	 * @return void
	 */
	public function register() {
		WP_CLI::add_command( 'safe-svg optimize', __NAMESPACE__ . '\\SvgOptimize' );
	}

}