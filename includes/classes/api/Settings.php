<?php
/**
 * API settings.
 *
 * @package SafeSvg
 */

namespace SafeSvg\API;

/**
 * Class for handling API settings.
 */
class Settings extends Module {

	/**
	 * Checks whether the Module should run within the current context.
	 *
	 * @return bool
	 */
	public function can_register() {
        return is_admin() && current_user_can( 'manage_options' );
    }

	/**
	 * Connects the Module with WordPress using Hooks and/or Filters.
	 *
	 * @return void
	 */
	public function register() {

    }

}